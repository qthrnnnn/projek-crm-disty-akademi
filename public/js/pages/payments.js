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
            const params = { page: this.state.page, per_page: 20 };
            if (this.state.statusFilter) params.status = this.state.statusFilter;

            const res = await api.list('payments', params);
            const items = res.data || [];
            this.renderList(items, res);
        },

        renderList(items, meta) {
            const user = App.state.user || {};
            const isAdmin = user.role === 'admin';

            const statusStyles = {
                valid: 'bg-emerald-50 text-emerald-700 border-emerald-200',
                invalid: 'bg-red-50 text-red-700 border-red-200',
                recheck: 'bg-orange-50 text-orange-700 border-orange-200',
                pending: 'bg-amber-50 text-amber-700 border-amber-200',
            };

            const rowsHtml = items.length === 0
                ? `<tr><td colspan="6" class="py-12 text-center text-gray-400 text-xs">Belum ada pembayaran</td></tr>`
                : items.map(p => {
                    const leadName = p.lead?.name || 'Peserta #' + p.lead_id;
                    const isPending = p.status === 'pending' || p.status === 'recheck';

                    return `
                        <tr class="hover:bg-blue-50/20 transition-colors">
                            <td class="py-3 px-4">
                                <a href="/leads/${p.lead_id}" onclick="event.preventDefault(); App.navigate('/leads/${p.lead_id}')"
                                   class="font-semibold text-gray-900 hover:text-orange-600 block text-xs">${leadName}</a>
                                <div class="text-[11px] text-gray-400 font-normal">${App.formatDate(p.payment_date || p.created_at)}</div>
                            </td>

                            <td class="py-3 px-4 text-xs font-medium text-gray-800">
                                ${p.product?.name || (p.lead?.product?.name ?? '-')}
                            </td>

                            <td class="py-3 px-4 text-xs font-bold text-gray-900">
                                ${App.formatRupiah(p.amount)}
                            </td>

                            <td class="py-3 px-4 text-xs">
                                <div class="text-gray-800 font-medium">${p.bank_name || (p.payment_method ? p.payment_method.charAt(0).toUpperCase() + p.payment_method.slice(1) : 'Transfer')}</div>
                                <div class="text-[11px] text-gray-400">${p.account_name ? 'a.n ' + p.account_name : ''}</div>
                            </td>

                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 text-[10px] font-semibold border rounded ${statusStyles[p.status] || 'bg-gray-50 border-gray-200'}">
                                    ${(p.status || '').charAt(0).toUpperCase() + (p.status || '').slice(1)}
                                </span>
                            </td>

                            <td class="py-3 px-4 text-right space-x-2 whitespace-nowrap">
                                <a href="/payments/${p.id}" onclick="event.preventDefault(); App.navigate('/payments/${p.id}')"
                                   class="text-xs text-blue-600 hover:text-blue-800 font-medium p-1">Detail</a>
                                ${(isAdmin && isPending) ? `
                                    <button onclick="App.pages.payments.verify(${p.id})"
                                            class="px-2.5 py-1 rounded bg-orange-500 hover:bg-orange-500 text-white font-semibold text-xs shadow-xs">
                                        Verifikasi
                                    </button>
                                ` : ''}
                            </td>
                        </tr>
                    `;
                }).join('');

            this.container.innerHTML = `
                <div class="space-y-4">
                    <!-- Header -->
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div>
                            <h1 class="text-xl font-bold text-gray-900">Pembayaran & Bukti Transfer</h1>
                            <p class="text-xs text-gray-500 mt-0.5">Kelola verifikasi transfer peserta dan pencatatan deal akademi</p>
                        </div>
                        <div>
                            <a href="/payments/create" onclick="event.preventDefault(); App.navigate('/payments/create')"
                               class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-semibold text-white bg-orange-500 rounded-full hover:bg-orange-500 transition-colors shadow-xs">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                Input Pembayaran
                            </a>
                        </div>
                    </div>

                    <!-- Filter Card -->
                    <div class="bg-white border border-gray-100 p-3.5 rounded-xl shadow-xs">
                        <div class="flex flex-wrap items-center gap-2.5">
                            <select id="pay-status" class="text-xs border border-gray-200 rounded-lg px-3 py-2 text-gray-700 focus:outline-none focus:border-orange-500"
                                    onchange="App.pages.payments.handleStatusChange(this.value)">
                                <option value="">Semua Status Pembayaran</option>
                                <option value="pending" ${this.state.statusFilter === 'pending' ? 'selected' : ''}>Menunggu Verifikasi</option>
                                <option value="valid" ${this.state.statusFilter === 'valid' ? 'selected' : ''}>Valid / Diterima</option>
                                <option value="invalid" ${this.state.statusFilter === 'invalid' ? 'selected' : ''}>Tidak Valid</option>
                                <option value="recheck" ${this.state.statusFilter === 'recheck' ? 'selected' : ''}>Perlu Cek Ulang</option>
                            </select>
                            <button onclick="App.pages.payments.loadList()"
                                    class="text-xs bg-orange-500 text-white font-semibold py-2 px-5 rounded-lg hover:bg-orange-500 shadow-xs transition-colors">
                                Filter
                            </button>
                        </div>
                    </div>

                    <!-- Table Card -->
                    <div class="bg-white border border-gray-100 rounded-xl overflow-hidden shadow-xs">
                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[640px] text-left text-xs text-gray-600">
                                <thead class="bg-gray-50/70 border-b border-gray-100 text-gray-400 font-semibold uppercase tracking-wider text-[10.5px]">
                                    <tr>
                                        <th class="py-3 px-4">Tanggal & Peserta</th>
                                        <th class="py-3 px-4">Program Pelatihan</th>
                                        <th class="py-3 px-4">Nominal</th>
                                        <th class="py-3 px-4">Metode / Bank</th>
                                        <th class="py-3 px-4">Status</th>
                                        <th class="py-3 px-4 text-right">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    ${rowsHtml}
                                </tbody>
                            </table>
                        </div>
                    </div>

                    ${App.pagination(meta)}
                </div>
            `;
        },

        handleStatusChange(val) {
            this.state.statusFilter = val;
            this.state.page = 1;
            this.loadList();
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
