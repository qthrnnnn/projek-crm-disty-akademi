/**
 * Trainers Page Module
 * Instructors & Expert Assessors Directory
 */
(function() {
    App.pages.trainers = {
        state: { page: 1, search: '' },

        async index(container) {
            this.container = container;
            await this.loadList();
        },

        async loadList() {
            const params = { page: this.state.page, per_page: 20 };
            if (this.state.search) params.search = this.state.search;

            const res = await api.list('trainers', params);
            const items = res.data || [];
            this.renderList(items, res);
        },

        renderList(items, meta) {
            const user = App.state.user || {};
            const isAdmin = user.role === 'admin';

            const rows = items.map(t => `
                <tr class="hover:bg-orange-50/20 transition-colors">
                    <td class="py-3.5 px-4 text-xs font-bold text-gray-900">
                        ${t.name}
                        ${t.notes ? `<p class="text-[10px] text-gray-400 font-normal line-clamp-1 mt-0.5">${t.notes}</p>` : ''}
                    </td>

                    <td class="py-3.5 px-4 text-xs text-gray-700">
                        ${t.email || '-'}
                    </td>

                    <td class="py-3.5 px-4 text-xs text-gray-700">
                        ${t.phone || '-'}
                    </td>

                    <td class="py-3.5 px-4">
                        <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-semibold border ${t.is_active ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-gray-100 text-gray-500 border-gray-200'}">
                            ${t.is_active ? 'Aktif' : 'Non-aktif'}
                        </span>
                    </td>

                    <td class="py-3.5 px-4 text-right space-x-1 whitespace-nowrap">
                        ${isAdmin ? `
                            <button onclick="App.navigate('/trainers/${t.id}/edit')" class="p-1.5 rounded-lg text-gray-400 hover:text-orange-600 hover:bg-orange-50 inline-block" title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                            </button>
                            <button onclick="App.pages.trainers.deleteTrainer(${t.id})" class="p-1.5 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 inline-block" title="Hapus">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        ` : ''}
                    </td>
                </tr>
            `);

            this.container.innerHTML = `
                <div class="space-y-4">
                    ${App.pageHeader(
                        'Daftar Trainer & Instruktur Ahli',
                        'Instruktur pengajar pelatihan dan asesor sertifikasi BNSP',
                        isAdmin ? `<button onclick="App.navigate('/trainers/create')" class="px-4 py-2 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-semibold text-xs shadow-sm flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Tambah Trainer
                        </button>` : ''
                    )}

                    <!-- Search Bar -->
                    <div class="flex items-center gap-2 bg-white p-3 rounded-2xl border border-gray-100 shadow-2xs">
                        <div class="flex-1 relative">
                            <input type="text" id="trn-search" value="${this.state.search}" placeholder="Cari nama trainer, email, atau keahlian..."
                                   class="w-full pl-9 pr-3 py-1.5 rounded-xl border border-gray-200 text-xs focus:ring-1 focus:ring-orange-500 outline-none"
                                   onkeydown="if(event.key==='Enter') App.pages.trainers.handleSearch()">
                            <svg class="w-4 h-4 text-gray-400 absolute left-2.5 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <button onclick="App.pages.trainers.handleSearch()" class="px-4 py-1.5 rounded-xl bg-gray-900 text-white font-semibold text-xs hover:bg-black">
                            Cari
                        </button>
                    </div>

                    ${App.tableShell(
                        ['Nama Trainer & Keahlian', 'Email', 'No. WhatsApp / HP', 'Status', 'Aksi'],
                        rows,
                        'Belum ada data trainer.'
                    )}

                    ${App.pagination(meta)}
                </div>
            `;
        },

        handleSearch() {
            const input = document.getElementById('trn-search');
            this.state.search = input ? input.value : '';
            this.state.page = 1;
            this.loadList();
        },

        goToPage(page) {
            this.state.page = page;
            this.loadList();
        },

        async deleteTrainer(id) {
            if (!confirm('Hapus instruktur ini?')) return;
            try {
                await api.remove('trainers', id);
                App.showToast('Trainer berhasil dihapus.');
                await this.loadList();
            } catch (err) {
                App.showToast(err.message, 'error');
            }
        },

        // ── Form ──
        async create(container) {
            this.renderForm(container, false);
        },

        async edit(container, id) {
            const res = await api.get('trainers', id);
            this.renderForm(container, true, res.data || res);
        },

        renderForm(container, isEdit, data = {}) {
            container.innerHTML = `
                <div class="max-w-xl mx-auto space-y-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-extrabold text-gray-900">${isEdit ? 'Edit Trainer' : 'Tambah Trainer Baru'}</h2>
                            <p class="text-xs text-gray-400 mt-0.5">Kelola identitas instruktur pengajar & sertifikasi</p>
                        </div>
                        <button onclick="App.navigate('/trainers')" class="text-xs text-gray-500 hover:text-gray-900 font-semibold flex items-center gap-1">
                            &larr; Kembali
                        </button>
                    </div>

                    <form id="trainer-form" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-4">
                        ${App.inputField('Nama Lengkap Trainer & Gelar', 'name', data.name || '', 'text', true, 'Contoh: Ir. Budi Wicaksono, M.Kom.')}

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            ${App.inputField('Email', 'email', data.email || '', 'email', false, 'budi@example.com')}
                            ${App.inputField('Nomor WhatsApp / HP', 'phone', data.phone || '', 'text', false, '08123456789')}
                        </div>

                        ${App.textareaField('Bio, Spesialisasi & Portofolio', 'notes', data.notes || '', false, 'Keahlian digital marketing, sertifikasi BNSP, dsb...')}

                        <div class="pt-2">
                            <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-gray-700">
                                <input type="checkbox" name="is_active" value="1" ${data.is_active !== false ? 'checked' : ''} class="rounded text-orange-500">
                                Instruktur Aktif Mengajar
                            </label>
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-4 border-t border-gray-100">
                            ${App.secondaryBtn('Batal', "App.navigate('/trainers')")}
                            ${App.primaryBtn(isEdit ? 'Simpan Perubahan' : 'Tambah Trainer')}
                        </div>
                    </form>
                </div>
            `;

            App.bindForm('trainer-form', 'trainers', '/trainers', isEdit, data.id);
        }
    };
})();
