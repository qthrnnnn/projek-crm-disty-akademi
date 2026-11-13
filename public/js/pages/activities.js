/**
 * Activities Page Module
 * View Sales Activity Logs & Audit Trail
 */
(function() {
    App.pages.activities = {
        state: {
            page: 1,
            search: '',
        },

        async index(container) {
            this.container = container;
            await this.loadList();
        },

        async loadList() {
            const params = { page: this.state.page, per_page: 25 };
            if (this.state.search) params.search = this.state.search;

            const res = await api.list('activities', params);
            const items = res.data || [];
            this.renderList(items, res);
        },

        renderList(items, meta) {
            const typeLabels = {
                call: 'Telepon',
                email: 'Email',
                whatsapp: 'WhatsApp',
                meeting: 'Meeting',
                note: 'Catatan',
                status_change: 'Ubah Status',
                admin_reminder: 'Instruksi Admin',
            };

            const rows = items.map(act => `
                <tr class="hover:bg-blue-50/30 transition-colors">
                    <td class="py-3 px-4 whitespace-nowrap text-gray-500">
                        ${App.formatDateTime(act.activity_at || act.created_at)}
                    </td>
                    <td class="py-3 px-4">
                        <span class="px-2 py-0.5 text-[10px] font-medium border bg-gray-50 border-gray-200">
                            ${typeLabels[act.type] || act.type}
                        </span>
                    </td>
                    <td class="py-3 px-4">
                        ${act.lead ? `
                            <a href="/leads/${act.lead.id}" onclick="event.preventDefault(); App.navigate('/leads/${act.lead.id}')" class="font-semibold text-blue-600 hover:underline">
                                ${act.lead.name}
                            </a>
                        ` : '<span class="text-gray-400">-</span>'}
                    </td>
                    <td class="py-3 px-4">
                        <div class="font-medium text-gray-900">${act.subject || act.notes || act.description || '-'}</div>
                    </td>
                    <td class="py-3 px-4">
                        ${act.user?.name || '-'}
                    </td>
                </tr>
            `);

            this.container.innerHTML = `
                <div class="space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div>
                            <h1 class="text-xl font-semibold text-gray-900">Log Aktivitas & Riwayat Interaksi</h1>
                            <p class="text-xs text-gray-500 mt-0.5">Semua jejak kontak prospek, telepon, whatsapp, & status change</p>
                        </div>
                    </div>

                    <!-- Filter Card -->
                    <div class="bg-white border border-blue-100 p-3.5 shadow-sm">
                        <form onsubmit="event.preventDefault(); App.pages.activities.handleTypeFilter(document.getElementById('act-type-select').value);" class="flex flex-wrap items-center gap-2.5">
                            <select id="act-type-select" class="text-xs border border-gray-200 px-3 py-2 text-gray-700 focus:outline-none focus:border-orange-500">
                                <option value="">Semua Jenis Aktivitas</option>
                                <option value="status_change">Ubah Status</option>
                                <option value="call">Telepon</option>
                                <option value="whatsapp">WhatsApp</option>
                                <option value="meeting">Meeting</option>
                                <option value="note">Catatan</option>
                                <option value="email">Email</option>
                                <option value="admin_reminder">Instruksi Admin</option>
                            </select>
                            <button type="submit" class="text-xs bg-orange-600 text-white font-medium py-2 px-3 rounded-lg hover:bg-orange-700">
                                Filter
                            </button>
                        </form>
                    </div>

                    <!-- Activities Table Card -->
                    <div class="bg-white border border-blue-100 overflow-hidden shadow-sm">
                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[620px] text-left text-xs text-gray-600">
                                <thead class="bg-gray-50/70 border-b border-gray-100 text-gray-500 font-medium uppercase tracking-wider text-[11px]">
                                    <tr>
                                        <th class="py-3 px-4">WAKTU</th>
                                        <th class="py-3 px-4">TIPE</th>
                                        <th class="py-3 px-4">PROSPEK</th>
                                        <th class="py-3 px-4">SUBJEK / DETAIL</th>
                                        <th class="py-3 px-4">SALES USER</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    ${rows.length ? rows.join('') : '<tr><td colspan="5" class="p-8 text-center text-gray-400">Belum ada riwayat aktivitas yang tercatat.</td></tr>'}
                                </tbody>
                            </table>
                        </div>
                    </div>

                    ${App.pagination(meta)}
                </div>
            `;
        },

        handleTypeFilter(type) {
            this.state.type = type;
            this.state.page = 1;
            this.loadList();
        },

        handleSearch() {
            const input = document.getElementById('act-search');
            this.state.search = input ? input.value : '';
            this.state.page = 1;
            this.loadList();
        },

        goToPage(page) {
            this.state.page = page;
            this.loadList();
        }
    };
})();
