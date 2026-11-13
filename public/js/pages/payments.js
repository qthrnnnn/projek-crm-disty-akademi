/**
 * Payments Page Module
 * Payment Proof Upload, Verification, and Automated 10% Commission Trigger
 */
(function() {
    App.pages.payments = {
        state: {
            page: 1,
            leads: [],
            products: [],
        },

        async index(container) {
            this.container = container;
            await this.loadList();
        },

        async loadList() {
            const res = await api.list('payments', { page: this.state.page, per_page: 20 });
            const items = res.data || [];
            this.renderList(items, res);
        },

        renderList(items, meta) {
            const user = App.state.user || {};
            const isAdmin = user.role === 'admin';

            const rows = items.map(p => {
                const leadName = p.lead?.name || 'Lead #' + p.lead_id;
                const isPending = p.status === 'pending' || p.status === 'recheck';

                return `
                    <tr class="hover:bg-orange-50/20 transition-colors">
                        <td class="py-3.5 px-4 text-xs font-mono font-bold text-gray-900">
                            ${p.payment_code || '#' + p.id}
                            <p class="text-[10px] text-gray-400 font-sans mt-0.5">${App.formatDate(p.payment_date || p.created_at)}</p>
                        </td>

                        <td class="py-3.5 px-4 text-xs font-bold text-gray-900">
                            <a href="/leads/${p.lead_id}" onclick="event.preventDefault(); App.navigate('/leads/${p.lead_id}')" class="hover:text-orange-600">
                                ${leadName}
                            </a>
                            <p class="text-[10px] text-gray-400 font-normal mt-0.5">${p.product?.name || 'Program Akademi'}</p>
                        </td>

                        <td class="py-3.5 px-4 text-xs font-extrabold text-emerald-600">
                            ${App.formatRupiah(p.amount)}
                        </td>

                        <td class="py-3.5 px-4 text-xs text-gray-700">
                            <span class="font-medium">${p.bank_name || p.payment_method || 'Transfer Bank'}</span>
                            ${p.account_name ? `<p class="text-[10px] text-gray-400">a.n. ${p.account_name}</p>` : ''}
                        </td>

                        <td class="py-3.5 px-4">
                            ${App.statusBadge(p.status || 'pending')}
                        </td>

                        <td class="py-3.5 px-4 text-right space-x-1.5 whitespace-nowrap">
                            <a href="/payments/${p.id}" onclick="event.preventDefault(); App.navigate('/payments/${p.id}')"
                               class="p-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 inline-block" title="Lihat Bukti">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                            ${(isAdmin && isPending) ? `
                                <button onclick="App.pages.payments.verify(${p.id})"
                                        class="px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-[10px] shadow-xs">
                                    Verifikasi
                                </button>
                            ` : ''}
                        </td>
                    </tr>
                `;
            });

            this.container.innerHTML = `
                <div class="space-y-4">
                    ${App.pageHeader(
                        'Verifikasi Pembayaran & Bukti Transfer',
                        'Daftar transaksi masuk calon peserta untuk pelatihan & sertifikasi',
                        `<button onclick="App.navigate('/payments/create')" class="px-4 py-2 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-semibold text-xs shadow-sm flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Kirim Bukti Bayar
                        </button>`
                    )}

                    ${App.tableShell(
                        ['Kode Bayar', 'Peserta / Program', 'Nominal Bayar', 'Metode & Rekening', 'Status', 'Aksi'],
                        rows,
                        'Belum ada transaksi pembayaran yang tercatat.'
                    )}

                    ${App.pagination(meta)}
                </div>
            `;
        },

        goToPage(page) {
            this.state.page = page;
            this.loadList();
        },

        async verify(id) {
            if (!confirm('Verifikasi pembayaran ini? Tindakan ini akan mengonversi lead menjadi Customer dan menerbitkan komisi sales 10% secara otomatis.')) return;
            try {
                await api.verifyPayment(id);
                App.showToast('Pembayaran berhasil diverifikasi! Komisi sales otomatis diterbitkan.');
                await this.loadList();
            } catch (err) {
                App.showToast(err.message, 'error');
            }
        },

        // ── Create Payment Form ──
        async create(container) {
            const urlParams = new URLSearchParams(window.location.search);
            const preselectedLeadId = urlParams.get('lead_id');

            const [lRes, pRes] = await Promise.all([
                api.list('leads', { per_page: 100 }),
                api.list('products', { per_page: 100 })
            ]);
            this.state.leads = lRes.data || [];
            this.state.products = pRes.data || [];

            const leadOptions = this.state.leads.map(l => ({
                value: l.id,
                label: `${l.name} - ${l.product?.name || 'Program'} (${App.formatRupiah(l.deal_price)})`,
                amount: l.deal_price || (l.product?.price || 0),
                productId: l.product_id
            }));

            const productOptions = this.state.products.map(p => ({
                value: p.id,
                label: `${p.name} - ${App.formatRupiah(p.price)}`
            }));

            const methodOptions = [
                { value: 'transfer_bank', label: 'Transfer Rekening Bank' },
                { value: 'qris', label: 'QRIS / E-Wallet' },
                { value: 'cash', label: 'Tunai / Cash Kantor' },
            ];

            container.innerHTML = `
                <div class="max-w-2xl mx-auto space-y-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-extrabold text-gray-900">Kirim Bukti Pembayaran</h2>
                            <p class="text-xs text-gray-400 mt-0.5">Upload bukti transfer peserta untuk diverifikasi oleh Admin</p>
                        </div>
                        <button onclick="App.navigate('/payments')" class="text-xs text-gray-500 hover:text-gray-900 font-semibold flex items-center gap-1">
                            &larr; Kembali
                        </button>
                    </div>

                    <!-- Notice 10% -->
                    <div class="p-4 bg-emerald-50/70 border border-emerald-200 rounded-2xl flex items-start gap-3">
                        <div class="w-8 h-8 rounded-xl bg-emerald-600 text-white flex items-center justify-center font-bold text-xs shrink-0 mt-0.5">
                            10%
                        </div>
                        <div class="text-xs">
                            <p class="font-bold text-emerald-900">Komisi Sales 10% Otomatis</p>
                            <p class="text-emerald-700 text-[11px] mt-0.5">Setelah pembayaran ini diverifikasi oleh Admin, komisi 10% sebesar nominal closing akan otomatis masuk ke akun sales yang bersangkutan.</p>
                        </div>
                    </div>

                    <form id="payment-form" onsubmit="App.pages.payments.handleFormSubmit(event)" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-4">
                        <div>
                            <label class="block text-[11px] font-semibold text-gray-700 mb-1">Pilih Prospek / Peserta <span class="text-red-400">*</span></label>
                            <select name="lead_id" id="pay-lead-select" required onchange="App.pages.payments.syncLeadAmount(this)"
                                    class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs bg-white outline-none">
                                <option value="">-- Pilih Lead --</option>
                                ${leadOptions.map(o => `<option value="${o.value}" data-amount="${o.amount}" data-product="${o.productId || ''}" ${preselectedLeadId == o.value ? 'selected' : ''}>${o.label}</option>`).join('')}
                            </select>
                        </div>

                        ${App.selectField('Program Pelatihan Terkait', 'product_id', productOptions, '')}

                        <div>
                            <label class="block text-[11px] font-semibold text-gray-700 mb-1">Jumlah Nominal yang Ditransfer (Rp) <span class="text-red-400">*</span></label>
                            <input type="number" name="amount" id="pay-amount-input" required placeholder="Contoh: 1500000"
                                   class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs font-bold outline-none">
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            ${App.selectField('Metode Pembayaran', 'payment_method', methodOptions, 'transfer_bank', true)}
                            ${App.inputField('Bank Tujuan / Pengirim', 'bank_name', '', 'text', false, 'Contoh: Bank BCA / Mandiri')}
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            ${App.inputField('Nama Rekening Pengirim', 'account_name', '', 'text', false, 'Contoh: Ahmad Fauzi')}
                            ${App.inputField('Tanggal Transfer', 'payment_date', new Date().toISOString().split('T')[0], 'date', true)}
                        </div>

                        <div>
                            <label class="block text-[11px] font-semibold text-gray-700 mb-1">Upload Foto / File Bukti Transfer (JPG, PNG, PDF)</label>
                            <input type="file" name="proof_file" accept="image/*,.pdf"
                                   class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs">
                        </div>

                        ${App.textareaField('Catatan Pembayaran (Opsional)', 'notes', '', false, 'Pelunasan / pembayaran DP tahap 1...')}

                        <div class="flex items-center justify-end gap-2 pt-4 border-t border-gray-100">
                            ${App.secondaryBtn('Batal', "App.navigate('/payments')")}
                            ${App.primaryBtn('Kirim Bukti Pembayaran')}
                        </div>
                    </form>
                </div>
            `;

            // Auto sync preselected lead
            const select = document.getElementById('pay-lead-select');
            if (select && select.value) this.syncLeadAmount(select);
        },

        syncLeadAmount(select) {
            const opt = select.options[select.selectedIndex];
            if (!opt) return;
            const amount = opt.dataset.amount;
            const amtInput = document.getElementById('pay-amount-input');
            if (amtInput && amount) amtInput.value = amount;

            const prodId = opt.dataset.product;
            const prodSelect = document.querySelector('select[name="product_id"]');
            if (prodSelect && prodId) prodSelect.value = prodId;
        },

        async handleFormSubmit(e) {
            e.preventDefault();
            const form = e.target;
            const formData = new FormData(form);
            const btn = form.querySelector('button[type="submit"]');

            btn.disabled = true;
            btn.textContent = 'Mengupload...';

            try {
                await api.storePayment(formData);
                App.showToast('Bukti pembayaran berhasil diupload!');
                App.navigate('/payments');
            } catch (err) {
                App.showToast(err.message, 'error');
                btn.disabled = false;
                btn.textContent = 'Kirim Bukti Pembayaran';
            }
        },

        // ── Show Detail ──
        async show(container, id) {
            const res = await api.get('payments', id);
            const p = res.data || res;
            const user = App.state.user || {};
            const isAdmin = user.role === 'admin';
            const isPending = p.status === 'pending' || p.status === 'recheck';

            container.innerHTML = `
                <div class="max-w-2xl mx-auto space-y-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <button onclick="App.navigate('/payments')" class="p-2 rounded-xl border border-gray-200 text-gray-500 hover:bg-gray-50">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                            </button>
                            <div>
                                <h2 class="text-xl font-extrabold text-gray-900">${p.payment_code || '#' + p.id}</h2>
                                <p class="text-xs text-gray-400 mt-0.5">Detail transaksi pembayaran peserta</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            ${(isAdmin && isPending) ? `
                                <button onclick="App.pages.payments.verify(${p.id})" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-sm">
                                    Verifikasi Pembayaran
                                </button>
                            ` : ''}
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-4 text-xs">
                        <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                            <div>
                                <span class="text-gray-400 block text-[10px]">Nominal Transfer:</span>
                                <p class="text-2xl font-extrabold text-emerald-600">${App.formatRupiah(p.amount)}</p>
                            </div>
                            <div>
                                <span class="text-gray-400 block text-[10px] text-right">Status:</span>
                                ${App.statusBadge(p.status || 'pending')}
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div><span class="text-gray-400 block text-[10px]">Peserta / Lead:</span> <strong class="text-gray-900">${p.lead?.name || '-'}</strong></div>
                            <div><span class="text-gray-400 block text-[10px]">Program:</span> <span class="text-gray-900">${p.product?.name || '-'}</span></div>
                            <div><span class="text-gray-400 block text-[10px]">Metode:</span> <span class="text-gray-900 capitalize">${p.payment_method || 'Transfer Bank'}</span></div>
                            <div><span class="text-gray-400 block text-[10px]">Bank / Instansi:</span> <span class="text-gray-900">${p.bank_name || '-'}</span></div>
                            <div><span class="text-gray-400 block text-[10px]">Nama Pengirim:</span> <span class="text-gray-900">${p.account_name || '-'}</span></div>
                            <div><span class="text-gray-400 block text-[10px]">Tanggal Bayar:</span> <span class="text-gray-900">${App.formatDate(p.payment_date || p.created_at)}</span></div>
                        </div>

                        <!-- Proof Image Display -->
                        <div class="pt-4 border-t border-gray-100">
                            <span class="text-gray-400 block text-[10px] mb-2">Lampiran Bukti Transfer:</span>
                            ${p.proof_file ? `
                                <div class="bg-gray-50 p-2 rounded-xl border border-gray-200 inline-block max-w-full">
                                    <a href="/storage/${p.proof_file}" target="_blank">
                                        <img src="/storage/${p.proof_file}" alt="Bukti Transfer" class="max-h-80 rounded-lg object-contain" onerror="this.outerHTML='<span class=\\'text-xs text-gray-500\\'>File lampiran: '+this.src+'</span>'">
                                    </a>
                                </div>
                            ` : '<p class="text-gray-400 italic">Tidak ada foto/lampiran bukti transfer.</p>'}
                        </div>
                    </div>
                </div>
            `;
        }
    };
})();
