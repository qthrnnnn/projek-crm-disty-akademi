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
                const isCompleted = fu.status === 'completed';
                const isCancelled = fu.status === 'cancelled';
                const statusBadge = isCompleted
                    ? '<span class="px-2 py-0.5 text-[10px] font-medium border bg-emerald-50 text-emerald-700 border-emerald-200">Completed</span>'
                    : (isCancelled
                        ? '<span class="px-2 py-0.5 text-[10px] font-medium border bg-gray-100 text-gray-500 border-gray-200">Cancelled</span>'
                        : '<span class="px-2 py-0.5 text-[10px] font-medium border bg-amber-50 text-amber-700 border-amber-200">Pending</span>');

                return `
                    <tr class="hover:bg-blue-50/30 transition-colors">
                        <td class="py-3 px-4">
                            <div class="font-semibold text-gray-900">${fu.title || '-'}</div>
                            ${fu.lead ? `
                                <a href="/leads/${fu.lead.id}" onclick="event.preventDefault(); App.navigate('/leads/${fu.lead.id}')" class="text-[11px] text-blue-600 hover:underline">
                                    ${fu.lead.name} (${fu.lead.lead_code || '-'})
                                </a>
                            ` : '<span class="text-gray-400 text-[11px]">-</span>'}
                        </td>

                        <td class="py-3 px-4 text-xs text-gray-700">
                            ${App.formatDateTime(fu.follow_up_at)}
                        </td>

                        <td class="py-3 px-4 uppercase text-[10px] font-semibold text-gray-500">
                            ${fu.method || 'WHATSAPP'}
                        </td>

                        <td class="py-3 px-4 text-xs text-gray-700">
                            ${fu.assigned_user?.name || fu.assignedUser?.name || '-'}
                        </td>

                        <td class="py-3 px-4">
                            ${statusBadge}
                        </td>

                        <td class="py-3 px-4 text-right">
                            <a href="/follow-ups/${fu.id}/edit" onclick="event.preventDefault(); App.navigate('/follow-ups/${fu.id}/edit')" class="text-xs text-blue-600 hover:underline">
                                Edit
                            </a>
                        </td>
                    </tr>
                `;
            });

            this.container.innerHTML = `
                <div class="space-y-4">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div>
                            <h1 class="text-xl font-semibold text-gray-900">Jadwal Follow-up Prospek</h1>
                            <p class="text-xs text-gray-500 mt-0.5">Kelola janji tindak lanjut dan respon calon peserta</p>
                        </div>
                        <button onclick="App.navigate('/follow-ups/create')" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-medium text-white bg-orange-500 rounded-full hover:bg-orange-600 transition-colors shadow-sm">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Tambah Jadwal
                        </button>
                    </div>

                    <!-- Filter Card -->
                    <div class="bg-white border border-blue-100 p-3.5 shadow-sm">
                        <form onsubmit="event.preventDefault(); App.pages['follow-ups'].handleFilter();" class="flex flex-wrap items-center gap-2.5">
                            <select id="fu-status-filter" class="text-xs border border-gray-200 px-3 py-2 text-gray-700 focus:outline-none focus:border-orange-500">
                                <option value="">Semua Status</option>
                                <option value="pending" ${this.state.statusFilter === 'pending' ? 'selected' : ''}>Pending</option>
                                <option value="completed" ${this.state.statusFilter === 'completed' ? 'selected' : ''}>Selesai</option>
                                <option value="cancelled" ${this.state.statusFilter === 'cancelled' ? 'selected' : ''}>Dibatalkan</option>
                            </select>
                            <button type="submit" class="text-xs bg-orange-500 text-white font-medium py-2 px-3 rounded-lg hover:bg-orange-600 transition-colors">
                                Filter
                            </button>
                        </form>
                    </div>

                    <!-- Table -->
                    <div class="bg-white border border-blue-100 overflow-hidden shadow-sm">
                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[620px] text-left text-xs text-gray-600">
                                <thead class="bg-gray-50/70 border-b border-gray-100 text-gray-500 font-medium uppercase tracking-wider text-[11px]">
                                    <tr>
                                        <th class="py-3 px-4">AGENDA & LEAD</th>
                                        <th class="py-3 px-4">WAKTU</th>
                                        <th class="py-3 px-4">METODE</th>
                                        <th class="py-3 px-4">SALES PIC</th>
                                        <th class="py-3 px-4">STATUS</th>
                                        <th class="py-3 px-4 text-right">AKSI</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    ${rows.length ? rows.join('') : '<tr><td colspan="6" class="p-8 text-center text-gray-400">Belum ada agenda follow-up.</td></tr>'}
                                </tbody>
                            </table>
                        </div>
                    </div>

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
