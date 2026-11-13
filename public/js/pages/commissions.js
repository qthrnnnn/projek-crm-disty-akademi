/**
 * Commissions Page Module
 * 10% Sales Affiliate Commissions, Approval, & Payout Tracking
 */
(function() {
    App.pages.commissions = {
        state: {
            page: 1,
            statusFilter: '',
        },

        async index(container) {
            this.container = container;
            await this.loadList();
        },

        async loadList() {
            const params = { page: this.state.page, per_page: 25 };
            if (this.state.statusFilter) params.status = this.state.statusFilter;

            const res = await api.list('commissions', params);
            const items = res.data || [];
            this.renderList(items, res);
        },

        renderList(items, meta) {
            const user = App.state.user || {};
            const isAdmin = user.role === 'admin';

            // Calculate totals
            const totalPending = items.filter(c => c.status === 'pending' || c.status === 'approved').reduce((s, c) => s + (parseFloat(c.commission_amount) || 0), 0);
            const totalPaid = items.filter(c => c.status === 'paid').reduce((s, c) => s + (parseFloat(c.commission_amount) || 0), 0);

            const rows = items.map(c => {
                const salesName = c.user?.name || c.sales_name || 'Tim Sales';
                const leadName = c.lead?.name || 'Lead #' + c.lead_id;
                const dealAmt = c.lead?.deal_price || (parseFloat(c.commission_amount) * 10) || 0;

                const statusBadges = {
                    pending: 'bg-amber-50 text-amber-700 border-amber-200',
                    approved: 'bg-blue-50 text-blue-700 border-blue-200',
                    paid: 'bg-emerald-50 text-emerald-700 border-emerald-200',
                };

                return `
                    <tr class="hover:bg-orange-50/20 transition-colors">
                        <td class="py-3.5 px-4 text-xs font-bold text-gray-900">
                            ${salesName}
                            <p class="text-[10px] text-gray-400 font-normal mt-0.5">${c.user?.email || '-'}</p>
                        </td>

                        <td class="py-3.5 px-4 text-xs">
                            <span class="font-bold text-gray-800">${leadName}</span>
                            <p class="text-[10px] text-gray-400 font-mono mt-0.5">${c.lead?.lead_code || '-'}</p>
                        </td>

                        <td class="py-3.5 px-4 text-xs font-semibold text-gray-700">
                            ${App.formatRupiah(dealAmt)}
                        </td>

                        <td class="py-3.5 px-4 text-xs text-gray-500 font-medium">
                            <span class="px-2 py-0.5 rounded-full bg-orange-100 text-orange-700 font-bold text-[10px]">10%</span>
                        </td>

                        <td class="py-3.5 px-4 text-xs font-extrabold text-orange-600">
                            ${App.formatRupiah(c.commission_amount)}
                        </td>

                        <td class="py-3.5 px-4">
                            <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wider border ${statusBadges[c.status] || 'bg-gray-100 text-gray-600'}">
                                ${App.statusLabel(c.status)}
                            </span>
                        </td>

                        <td class="py-3.5 px-4 text-right space-x-1 whitespace-nowrap">
                            ${isAdmin ? `
                                ${c.status === 'pending' ? `
                                    <button onclick="App.pages.commissions.approve(${c.id})" class="px-2.5 py-1 rounded-lg bg-blue-600 hover:bg-blue-700 text-white font-semibold text-[10px] shadow-xs">
                                        Setujui
                                    </button>
                                ` : ''}
                                ${c.status === 'approved' ? `
                                    <button onclick="App.pages.commissions.pay(${c.id})" class="px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-[10px] shadow-xs">
                                        Bayar Sekarang
                                    </button>
                                ` : ''}
                                ${c.status === 'paid' ? `<span class="text-xs text-emerald-600 font-semibold">✓ Telah Dicairkan</span>` : ''}
                            ` : `<span class="text-xs text-gray-400">${c.status === 'paid' ? 'Dicairkan' : 'Menunggu Admin'}</span>`}
                        </td>
                    </tr>
                `;
            });

            this.container.innerHTML = `
                <div class="space-y-6">
                    ${App.pageHeader(
                        isAdmin ? 'Komisi Seluruh Tim Sales (10%)' : 'Komisi Penjualan Saya (10%)',
                        'Setiap closing peserta pelatihan yang diverifikasi otomatis menghasilkan 10% insentif sales'
                    )}

                    <!-- 2 Summary KPI Cards -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-2xs space-y-1">
                            <p class="text-xs font-semibold text-gray-400">Komisi Menunggu / Disetujui (Pending)</p>
                            <p class="text-2xl sm:text-3xl font-extrabold text-amber-600">${App.formatRupiah(totalPending)}</p>
                            <p class="text-[11px] text-gray-400">Siap dicairkan setelah proses verifikasi</p>
                        </div>

                        <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-2xs space-y-1">
                            <p class="text-xs font-semibold text-gray-400">Total Komisi Sudah Dibayarkan</p>
                            <p class="text-2xl sm:text-3xl font-extrabold text-emerald-600">${App.formatRupiah(totalPaid)}</p>
                            <p class="text-[11px] text-emerald-600 font-medium">Telah ditransfer ke rekening sales</p>
                        </div>
                    </div>

                    ${App.tableShell(
                        ['Sales Penerima', 'Peserta / Lead', 'Nilai Deal', 'Rate Komisi', 'Nominal Komisi', 'Status', 'Aksi'],
                        rows,
                        'Belum ada data komisi penjualan.'
                    )}

                    ${App.pagination(meta)}
                </div>
            `;
        },

        goToPage(page) {
            this.state.page = page;
            this.loadList();
        },

        async approve(id) {
            if (!confirm('Setujui komisi sales ini?')) return;
            try {
                await api.approveCommission(id);
                App.showToast('Komisi berhasil disetujui.');
                await this.loadList();
            } catch (err) {
                App.showToast(err.message, 'error');
            }
        },

        async pay(id) {
            if (!confirm('Tandai komisi ini sudah dibayar/ditransfer ke rekening sales?')) return;
            try {
                await api.payCommission(id);
                App.showToast('Komisi berhasil ditandai telah dibayar! Notifikasi dikirimkan ke sales.');
                await this.loadList();
            } catch (err) {
                App.showToast(err.message, 'error');
            }
        }
    };
})();
