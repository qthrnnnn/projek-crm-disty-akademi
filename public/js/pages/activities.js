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
            const typeBadges = {
                call: 'bg-blue-50 text-blue-700 border-blue-200',
                email: 'bg-purple-50 text-purple-700 border-purple-200',
                whatsapp: 'bg-emerald-50 text-emerald-700 border-emerald-200',
                meeting: 'bg-amber-50 text-amber-700 border-amber-200',
                note: 'bg-gray-100 text-gray-700 border-gray-200',
                status_change: 'bg-indigo-50 text-indigo-700 border-indigo-200',
            };

            const rows = items.map(act => `
                <tr class="hover:bg-orange-50/20 transition-colors">
                    <td class="py-3.5 px-4 text-xs font-bold text-gray-900">
                        ${act.lead ? `
                            <a href="/leads/${act.lead.id}" onclick="event.preventDefault(); App.navigate('/leads/${act.lead.id}')" class="hover:text-orange-600">
                                ${act.lead.name}
                            </a>
                            <p class="text-[10px] text-gray-400 font-mono mt-0.5">${act.lead.lead_code || '-'}</p>
                        ` : '-'}
                    </td>

                    <td class="py-3.5 px-4">
                        <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wider border ${typeBadges[act.type] || 'bg-gray-100 text-gray-600'}">
                            ${act.type || 'note'}
                        </span>
                    </td>

                    <td class="py-3.5 px-4 text-xs">
                        <p class="font-bold text-gray-900">${act.subject || '-'}</p>
                        ${act.description ? `<p class="text-gray-500 text-[11px] mt-0.5 max-w-md">${act.description}</p>` : ''}
                    </td>

                    <td class="py-3.5 px-4 text-xs text-gray-700 font-medium">
                        ${act.user?.name || 'Sistem CRM'}
                    </td>

                    <td class="py-3.5 px-4 text-xs text-gray-400 whitespace-nowrap">
                        ${App.formatDateTime(act.activity_at || act.created_at)}
                    </td>
                </tr>
            `);

            this.container.innerHTML = `
                <div class="space-y-4">
                    ${App.pageHeader('Riwayat Aktivitas CRM', 'Catatan lengkap komunikasi, panggilan, chat WhatsApp, & perubahan status')}

                    <!-- Search Bar -->
                    <div class="flex items-center gap-2 bg-white p-3 rounded-2xl border border-gray-100 shadow-2xs">
                        <div class="flex-1 relative">
                            <input type="text" id="act-search" value="${this.state.search}" placeholder="Cari subjek atau catatan aktivitas..."
                                   class="w-full pl-9 pr-3 py-1.5 rounded-xl border border-gray-200 text-xs focus:ring-1 focus:ring-orange-500 outline-none"
                                   onkeydown="if(event.key==='Enter') App.pages.activities.handleSearch()">
                            <svg class="w-4 h-4 text-gray-400 absolute left-2.5 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <button onclick="App.pages.activities.handleSearch()" class="px-4 py-1.5 rounded-xl bg-gray-900 text-white font-semibold text-xs hover:bg-black">
                            Cari
                        </button>
                    </div>

                    ${App.tableShell(
                        ['Prospek / Lead', 'Tipe', 'Subjek & Catatan', 'Sales / Petugas', 'Waktu'],
                        rows,
                        'Belum ada riwayat aktivitas yang tercatat.'
                    )}

                    ${App.pagination(meta)}
                </div>
            `;
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
