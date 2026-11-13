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

            const statusBadges = {
                pending: 'bg-amber-50 text-amber-700 border-amber-200',
                approved: 'bg-blue-50 text-blue-700 border-blue-200',
                paid: 'bg-emerald-50 text-emerald-700 border-emerald-200',
            };

            const rowsHtml = items.length === 0
                ? `<tr><td colspan="7" class="py-8 text-center text-gray-400">Belum ada data komisi penjualan</td></tr>`
                : items.map(c => {
                    const salesName = c.user?.name || c.sales_name || 'Tim Sales';
                    const leadName = c.lead?.name || 'Lead #' + c.lead_id;
                    const dealAmt = c.lead?.deal_price || (parseFloat(c.commission_amount) * 10) || 0;
                    const rateStr = c.commission_rate ? `${parseFloat(c.commission_rate).toFixed(2)}%` : '5.00%';

                    return `
                        <tr class="hover:bg-orange-50/20 transition-colors">
                            <td class="py-3.5 px-4 text-xs font-semibold text-gray-900">
                                ${salesName}
                            </td>

                            <td class="py-3.5 px-4 text-xs">
                                <a href="/leads/${c.lead_id}" onclick="event.preventDefault(); App.navigate('/leads/${c.lead_id}')"
                                   class="font-medium text-blue-600 hover:underline">${leadName}</a>
                            </td>

                            <td class="py-3.5 px-4 text-xs text-gray-700">
                                ${App.formatRupiah(dealAmt)}
                            </td>

                            <td class="py-3.5 px-4 text-xs text-gray-600 font-medium">
                                ${rateStr}
                            </td>

                            <td class="py-3.5 px-4 text-xs font-bold text-emerald-600">
                                ${App.formatRupiah(c.commission_amount)}
                            </td>

                            <td class="py-3.5 px-4">
                                <span class="inline-block px-2.5 py-0.5 rounded text-[11px] font-semibold border ${statusBadges[c.status] || 'bg-gray-100 text-gray-600'}">
                                    ${(c.status || '').charAt(0).toUpperCase() + (c.status || '').slice(1)}
                                </span>
                            </td>

                            <td class="py-3.5 px-4 text-right space-x-1 whitespace-nowrap">
                                ${isAdmin ? `
                                    ${c.status === 'pending' ? `
                                        <button onclick="App.pages.commissions.approve(${c.id})" class="px-3 py-1 rounded-lg bg-blue-50 border border-blue-200 hover:bg-blue-100 text-blue-700 font-semibold text-xs">
                                            Setujui
                                        </button>
                                    ` : ''}
                                    ${c.status === 'approved' ? `
                                        <button onclick="App.pages.commissions.pay(${c.id})" class="px-3 py-1 rounded-lg bg-emerald-50 border border-emerald-200 hover:bg-emerald-100 text-emerald-700 font-semibold text-xs">
                                            Bayar
                                        </button>
                                    ` : ''}
                                ` : ''}
                            </td>
                        </tr>
                    `;
                }).join('');

            this.container.innerHTML = `
                <div class="space-y-4">
                    <!-- Header -->
                    <div>
                        <h1 class="text-xl font-bold text-gray-900">Manajemen Komisi Sales</h1>
                        <p class="text-xs text-gray-500 mt-0.5">Penetapan dan pembayaran insentif atas setiap peserta (pelanggan) yang berhasil mendaftar</p>
                    </div>

                    <!-- 2 Summary Cards (Sesuai Screenshot 11) -->
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-xs flex items-center justify-between">
                            <div>
                                <p class="text-xs font-medium text-gray-500">Komisi Menunggu / Disetujui (Pending)</p>
                                <p class="text-2xl font-bold text-orange-600 mt-1">${App.formatRupiah(totalPending)}</p>
                            </div>
                            <div class="w-10 h-10 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                        </div>

                        <div class="bg-white p-5 rounded-xl border border-gray-100 shadow-xs flex items-center justify-between">
                            <div>
                                <p class="text-xs font-medium text-gray-500">Total Komisi Sudah Dibayarkan</p>
                                <p class="text-2xl font-bold text-emerald-600 mt-1">${App.formatRupiah(totalPaid)}</p>
                            </div>
                            <div class="w-10 h-10 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            </div>
                        </div>
                    </div>

                    <!-- Filter Card (Sesuai Screenshot 11) -->
                    <div class="bg-white border border-gray-100 p-3.5 rounded-xl shadow-xs">
                        <div class="flex flex-col sm:flex-row items-center gap-2.5">
                            <div class="w-full sm:w-56">
                                <select id="comm-status" class="w-full text-xs border border-gray-200 rounded-lg px-3 py-2 text-gray-700 focus:outline-none focus:border-orange-500"
                                        onchange="App.pages.commissions.handleStatusChange(this.value)">
                                    <option value="">Semua Status Komisi</option>
                                    <option value="pending" ${this.state.statusFilter === 'pending' ? 'selected' : ''}>Pending</option>
                                    <option value="approved" ${this.state.statusFilter === 'approved' ? 'selected' : ''}>Approved</option>
                                    <option value="paid" ${this.state.statusFilter === 'paid' ? 'selected' : ''}>Paid</option>
                                </select>
                            </div>
                            <div class="w-full sm:w-56">
                                <select id="comm-sales" class="w-full text-xs border border-gray-200 rounded-lg px-3 py-2 text-gray-700 focus:outline-none focus:border-orange-500">
                                    <option value="">Semua Sales</option>
                                </select>
                            </div>
                            <button onclick="App.pages.commissions.loadList()"
                                    class="w-full sm:w-auto px-5 py-2 text-xs font-semibold text-white bg-orange-500 rounded-lg hover:bg-orange-500 shadow-xs transition-colors whitespace-nowrap">
                                Filter
                            </button>
                        </div>
                    </div>

                    <!-- Table Card (Sesuai Screenshot 11) -->
                    <div class="bg-white border border-gray-100 rounded-xl overflow-hidden shadow-xs">
                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[700px] text-left text-xs text-gray-600">
                                <thead class="bg-gray-50/70 border-b border-gray-100 text-gray-400 font-semibold uppercase tracking-wider text-[10.5px]">
                                    <tr>
                                        <th class="py-3.5 px-4">SALES PENERIMA</th>
                                        <th class="py-3.5 px-4">PESERTA / LEAD</th>
                                        <th class="py-3.5 px-4">NILAI DEAL</th>
                                        <th class="py-3.5 px-4">RATE (%)</th>
                                        <th class="py-3.5 px-4">NOMINAL KOMISI</th>
                                        <th class="py-3.5 px-4">STATUS</th>
                                        <th class="py-3.5 px-4 text-right">AKSI</th>
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
