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
            const rowsHtml = items.length === 0
                ? `<tr><td colspan="5" class="py-12 text-center text-gray-400 text-sm">Belum ada trainer.</td></tr>`
                : items.map(t => `
                    <tr class="hover:bg-blue-50/20 transition-colors">
                        <td class="py-3.5 px-4 font-semibold text-gray-900 text-xs">
                            ${t.name}
                        </td>

                        <td class="py-3.5 px-4 text-xs text-gray-600">
                            ${t.email || '-'}
                        </td>

                        <td class="py-3.5 px-4 text-xs text-gray-600">
                            ${t.phone || '-'}
                        </td>

                        <td class="py-3.5 px-4">
                            <span class="inline-block px-2 py-0.5 border text-[10px] font-semibold uppercase tracking-wider rounded ${t.is_active ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-gray-100 text-gray-600 border-gray-200'}">
                                ${t.is_active ? 'Aktif' : 'Non-aktif'}
                            </span>
                        </td>

                        <td class="py-3.5 px-4 text-right whitespace-nowrap">
                            <a href="/trainers/${t.id}/edit" onclick="event.preventDefault(); App.navigate('/trainers/${t.id}/edit')"
                               class="text-xs text-blue-600 hover:text-blue-800 font-medium p-1">Edit</a>
                        </td>
                    </tr>
                `).join('');

            this.container.innerHTML = `
                <div class="space-y-4">
                    <!-- Header -->
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div>
                            <h1 class="text-xl font-bold text-gray-900">Daftar Trainer</h1>
                            <p class="text-xs text-gray-500 mt-0.5">Trainer digunakan saat membuat jadwal pelatihan.</p>
                        </div>
                        <div>
                            <a href="/trainers/create" onclick="event.preventDefault(); App.navigate('/trainers/create')"
                               class="px-4 py-2 text-xs font-semibold text-white bg-orange-500 rounded-full hover:bg-orange-500 shadow-xs transition-colors inline-block">
                                + Tambah Trainer
                            </a>
                        </div>
                    </div>

                    <!-- Filter Card -->
                    <div class="bg-white border border-gray-100 p-3.5 rounded-xl shadow-xs">
                        <div class="flex flex-col sm:flex-row items-center gap-2.5">
                            <div class="relative flex-1 w-full">
                                <input type="text" id="trn-search" value="${this.state.search}"
                                       placeholder="Cari trainer, email, atau telepon..."
                                       class="w-full text-xs border border-gray-200 rounded-lg px-3 py-2 text-gray-800 placeholder-gray-400 focus:outline-none focus:border-orange-500"
                                       onkeydown="if(event.key==='Enter') App.pages.trainers.handleSearch()">
                            </div>
                            <button onclick="App.pages.trainers.handleSearch()"
                                    class="w-full sm:w-auto px-5 py-2 text-xs font-semibold text-white bg-orange-500 rounded-lg hover:bg-orange-500 shadow-xs transition-colors whitespace-nowrap">
                                Cari
                            </button>
                        </div>
                    </div>

                    <!-- Table Card -->
                    <div class="bg-white border border-gray-100 rounded-xl overflow-hidden shadow-xs">
                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[550px] text-left text-xs text-gray-600">
                                <thead class="bg-gray-50/70 border-b border-gray-100 text-gray-400 font-semibold uppercase tracking-wider text-[10.5px]">
                                    <tr>
                                        <th class="py-3.5 px-4">NAMA ▲</th>
                                        <th class="py-3.5 px-4">EMAIL</th>
                                        <th class="py-3.5 px-4">TELEPON</th>
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
