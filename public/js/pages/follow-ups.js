/**
 * Follow-Ups Page Module
 * Schedule, Complete, Cancel, & Prioritize Follow-ups
 */
(function() {
    App.pages['follow-ups'] = {
        state: {
            page: 1,
            leads: [],
        },

        async index(container) {
            this.container = container;
            await this.loadList();
        },

        async loadList() {
            const res = await api.list('follow-ups', { page: this.state.page, per_page: 20 });
            const items = res.data || [];
            this.renderList(items, res);
        },

        renderList(items, meta) {
            const rows = items.map(fu => {
                const priorityBadge = {
                    urgent: 'bg-red-50 text-red-700 border-red-200',
                    high: 'bg-orange-50 text-orange-700 border-orange-200',
                    normal: 'bg-blue-50 text-blue-700 border-blue-200',
                    low: 'bg-gray-100 text-gray-500 border-gray-200',
                }[fu.priority] || 'bg-gray-100 text-gray-600';

                const isPending = fu.status === 'pending';

                return `
                    <tr class="hover:bg-orange-50/20 transition-colors">
                        <td class="py-3.5 px-4 text-xs font-bold text-gray-900">
                            <a href="/leads/${fu.lead_id}" onclick="event.preventDefault(); App.navigate('/leads/${fu.lead_id}')" class="hover:text-orange-600">
                                ${fu.lead?.name || 'Lead #' + fu.lead_id}
                            </a>
                            <p class="text-[10px] text-gray-400 font-normal mt-0.5">${fu.title || 'Follow-up Jadwal'}</p>
                        </td>

                        <td class="py-3.5 px-4 text-xs capitalize text-gray-700 font-medium">
                            <span class="inline-flex items-center gap-1.5">
                                <span class="w-1.5 h-1.5 rounded-full ${fu.method === 'whatsapp' ? 'bg-emerald-500' : 'bg-blue-500'}"></span>
                                ${fu.method || 'whatsapp'}
                            </span>
                        </td>

                        <td class="py-3.5 px-4 text-xs text-gray-600">
                            ${App.formatDateTime(fu.follow_up_at)}
                        </td>

                        <td class="py-3.5 px-4">
                            <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wider border ${priorityBadge}">
                                ${fu.priority || 'normal'}
                            </span>
                        </td>

                        <td class="py-3.5 px-4">
                            ${App.statusBadge(fu.status || 'pending')}
                        </td>

                        <td class="py-3.5 px-4 text-right space-x-1 whitespace-nowrap">
                            ${isPending ? `
                                <button onclick="App.pages['follow-ups'].handleComplete(${fu.id})" class="px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-[10px] shadow-xs">
                                    Selesai
                                </button>
                                <button onclick="App.pages['follow-ups'].handleCancel(${fu.id})" class="px-2 py-1 rounded-lg border border-gray-200 text-gray-500 hover:text-red-600 text-[10px]">
                                    Batal
                                </button>
                            ` : `<span class="text-gray-400 text-xs">${fu.status === 'completed' ? 'Telah Selesai' : 'Dibatalkan'}</span>`}
                        </td>
                    </tr>
                `;
            });

            this.container.innerHTML = `
                <div class="space-y-4">
                    ${App.pageHeader(
                        'Jadwal Follow-Up Prospek',
                        'Pantau dan eksekusi komunikasi lanjutan kepada calon peserta pelatihan',
                        `<button onclick="App.navigate('/follow-ups/create')" class="px-4 py-2 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-semibold text-xs shadow-sm flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Jadwalkan Follow-up
                        </button>`
                    )}

                    ${App.tableShell(
                        ['Prospek / Lead', 'Metode Kontak', 'Waktu Dijadwalkan', 'Prioritas', 'Status', 'Aksi'],
                        rows,
                        'Belum ada jadwal follow-up.'
                    )}

                    ${App.pagination(meta)}
                </div>
            `;
        },

        goToPage(page) {
            this.state.page = page;
            this.loadList();
        },

        async handleComplete(id) {
            const note = prompt('Tuliskan catatan hasil follow-up (opsional):', '');
            if (note === null) return;
            try {
                await api.completeFollowUp(id, { notes: note });
                App.showToast('Follow-up berhasil ditandai selesai!');
                await this.loadList();
            } catch (err) {
                App.showToast(err.message, 'error');
            }
        },

        async handleCancel(id) {
            if (!confirm('Yakin ingin membatalkan jadwal follow-up ini?')) return;
            try {
                await api.cancelFollowUp(id);
                App.showToast('Follow-up dibatalkan.');
                await this.loadList();
            } catch (err) {
                App.showToast(err.message, 'error');
            }
        },

        // ── Create & Edit Form ──
        async create(container) {
            const res = await api.list('leads', { per_page: 100 });
            const leads = res.data || [];
            this.renderForm(container, false, {}, leads);
        },

        async edit(container, id) {
            const [fuRes, lRes] = await Promise.all([
                api.get('follow-ups', id),
                api.list('leads', { per_page: 100 })
            ]);
            this.renderForm(container, true, fuRes.data || fuRes, lRes.data || []);
        },

        renderForm(container, isEdit, data = {}, leads = []) {
            const leadOptions = leads.map(l => ({
                value: l.id,
                label: `${l.name} (${l.company || 'Perorangan'})`
            }));

            const methodOptions = [
                { value: 'whatsapp', label: 'Chat WhatsApp' },
                { value: 'call', label: 'Telepon Langsung' },
                { value: 'email', label: 'Kirim Email Penawaran' },
                { value: 'meeting', label: 'Meeting / Presentasi' },
            ];

            const priorityOptions = [
                { value: 'urgent', label: 'Mendesak (Urgent)' },
                { value: 'high', label: 'Tinggi (High)' },
                { value: 'normal', label: 'Normal' },
                { value: 'low', label: 'Rendah (Low)' },
            ];

            container.innerHTML = `
                <div class="max-w-xl mx-auto space-y-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-extrabold text-gray-900">${isEdit ? 'Edit Follow-Up' : 'Jadwalkan Follow-Up'}</h2>
                            <p class="text-xs text-gray-400 mt-0.5">Atur pengingat komunikasi berikutnya ke calon peserta</p>
                        </div>
                        <button onclick="App.navigate('/follow-ups')" class="text-xs text-gray-500 hover:text-gray-900 font-semibold flex items-center gap-1">
                            &larr; Kembali
                        </button>
                    </div>

                    <form id="fu-form" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-4">
                        ${App.selectField('Pilih Lead / Prospek', 'lead_id', leadOptions, data.lead_id || '', true)}
                        ${App.inputField('Judul Tindak Lanjut', 'title', data.title || '', 'text', true, 'Contoh: Konfirmasi invoice DP pelatihan')}

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            ${App.selectField('Metode Komunikasi', 'method', methodOptions, data.method || 'whatsapp', true)}
                            ${App.selectField('Tingkat Prioritas', 'priority', priorityOptions, data.priority || 'normal', true)}
                        </div>

                        ${App.inputField('Waktu Jadwal Follow-Up', 'follow_up_at', data.follow_up_at ? data.follow_up_at.replace(' ', 'T') : '', 'datetime-local', true)}

                        ${App.textareaField('Catatan Rencana Pembicaraan', 'notes', data.notes || '', false, 'Fokus penawaran sertifikasi, negosiasi diskon instansi, dsb...')}

                        <div class="flex items-center justify-end gap-2 pt-4 border-t border-gray-100">
                            ${App.secondaryBtn('Batal', "App.navigate('/follow-ups')")}
                            ${App.primaryBtn(isEdit ? 'Simpan Perubahan' : 'Jadwalkan')}
                        </div>
                    </form>
                </div>
            `;

            App.bindForm('fu-form', 'follow-ups', '/follow-ups', isEdit, data.id);
        }
    };
})();
