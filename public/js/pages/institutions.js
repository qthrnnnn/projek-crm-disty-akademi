/**
 * Institutions Page Module
 * Partner Institutions (Universities, Government, Corporate)
 */
(function() {
    App.pages.institutions = {
        state: { page: 1, search: '' },

        async index(container) {
            this.container = container;
            await this.loadList();
        },

        async loadList() {
            const params = { page: this.state.page, per_page: 20 };
            if (this.state.search) params.search = this.state.search;

            const res = await api.list('institutions', params);
            const items = res.data || [];
            this.renderList(items, res);
        },

        renderList(items, meta) {
            const user = App.state.user || {};
            const isAdmin = user.role === 'admin';

            const rows = items.map(inst => `
                <tr class="hover:bg-orange-50/20 transition-colors">
                    <td class="py-3.5 px-4 text-xs font-bold text-gray-900">
                        ${inst.name}
                        ${inst.industry ? `<p class="text-[10px] text-gray-400 font-normal mt-0.5">${inst.industry}</p>` : ''}
                    </td>

                    <td class="py-3.5 px-4">
                        <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                            ${inst.type || 'Mitra'}
                        </span>
                    </td>

                    <td class="py-3.5 px-4 text-xs text-gray-600">
                        ${[inst.city, inst.province].filter(Boolean).join(', ') || '-'}
                    </td>

                    <td class="py-3.5 px-4 text-xs">
                        <p class="text-gray-900">${inst.phone || '-'}</p>
                        <p class="text-[10px] text-gray-400">${inst.email || '-'}</p>
                    </td>

                    <td class="py-3.5 px-4 text-right space-x-1 whitespace-nowrap">
                        ${isAdmin ? `
                            <button onclick="App.navigate('/institutions/${inst.id}/edit')" class="p-1.5 rounded-lg text-gray-400 hover:text-orange-600 hover:bg-orange-50 inline-block" title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                            </button>
                            <button onclick="App.pages.institutions.deleteInstitution(${inst.id})" class="p-1.5 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 inline-block" title="Hapus">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        ` : ''}
                    </td>
                </tr>
            `);

            this.container.innerHTML = `
                <div class="space-y-4">
                    ${App.pageHeader(
                        'Daftar Instansi & Mitra Kerjasama',
                        'Kelola data universitas, kantor pemerintahan, BUMN, dan perusahaan swasta',
                        isAdmin ? `<button onclick="App.navigate('/institutions/create')" class="px-4 py-2 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-semibold text-xs shadow-sm flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Tambah Instansi
                        </button>` : ''
                    )}

                    <!-- Search Bar -->
                    <div class="flex items-center gap-2 bg-white p-3 rounded-2xl border border-gray-100 shadow-2xs">
                        <div class="flex-1 relative">
                            <input type="text" id="inst-search" value="${this.state.search}" placeholder="Cari nama instansi, tipe, atau kota..."
                                   class="w-full pl-9 pr-3 py-1.5 rounded-xl border border-gray-200 text-xs focus:ring-1 focus:ring-orange-500 outline-none"
                                   onkeydown="if(event.key==='Enter') App.pages.institutions.handleSearch()">
                            <svg class="w-4 h-4 text-gray-400 absolute left-2.5 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <button onclick="App.pages.institutions.handleSearch()" class="px-4 py-1.5 rounded-xl bg-gray-900 text-white font-semibold text-xs hover:bg-black">
                            Cari
                        </button>
                    </div>

                    ${App.tableShell(
                        ['Nama Instansi', 'Jenis Lembaga', 'Kota & Provinsi', 'Kontak', 'Aksi'],
                        rows,
                        'Belum ada data instansi mitra.'
                    )}

                    ${App.pagination(meta)}
                </div>
            `;
        },

        handleSearch() {
            const input = document.getElementById('inst-search');
            this.state.search = input ? input.value : '';
            this.state.page = 1;
            this.loadList();
        },

        goToPage(page) {
            this.state.page = page;
            this.loadList();
        },

        async deleteInstitution(id) {
            if (!confirm('Hapus instansi mitra ini?')) return;
            try {
                await api.remove('institutions', id);
                App.showToast('Instansi berhasil dihapus.');
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
            const res = await api.get('institutions', id);
            this.renderForm(container, true, res.data || res);
        },

        renderForm(container, isEdit, data = {}) {
            const typeOptions = [
                { value: 'Perguruan Tinggi', label: 'Perguruan Tinggi / Universitas' },
                { value: 'Pemerintahan', label: 'Instansi Pemerintahan / Dinas' },
                { value: 'BUMN', label: 'BUMN / BUMD' },
                { value: 'Swasta / Corporate', label: 'Swasta / Corporate' },
                { value: 'Sekolah / SMK', label: 'Sekolah / SMK' },
                { value: 'Lainnya', label: 'Lainnya' },
            ];

            container.innerHTML = `
                <div class="max-w-xl mx-auto space-y-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-extrabold text-gray-900">${isEdit ? 'Edit Instansi' : 'Tambah Instansi Baru'}</h2>
                            <p class="text-xs text-gray-400 mt-0.5">Kelola data mitra kampus, dinas, dan perusahaan</p>
                        </div>
                        <button onclick="App.navigate('/institutions')" class="text-xs text-gray-500 hover:text-gray-900 font-semibold flex items-center gap-1">
                            &larr; Kembali
                        </button>
                    </div>

                    <form id="inst-form" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-4">
                        ${App.inputField('Nama Instansi / Lembaga', 'name', data.name || '', 'text', true, 'Contoh: Universitas Brawijaya')}

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            ${App.selectField('Jenis Lembaga', 'type', typeOptions, data.type || 'Perguruan Tinggi', true)}
                            ${App.inputField('Bidang Industri / Sektor', 'industry', data.industry || '', 'text', false, 'Pendidikan / Teknologi')}
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            ${App.inputField('Email Resmi', 'email', data.email || '', 'email', false, 'humas@kampus.ac.id')}
                            ${App.inputField('Nomor Telepon Kantor', 'phone', data.phone || '', 'text', false, '0341-123456')}
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            ${App.inputField('Kota', 'city', data.city || '', 'text', false, 'Malang')}
                            ${App.inputField('Provinsi', 'province', data.province || '', 'text', false, 'Jawa Timur')}
                        </div>

                        ${App.textareaField('Alamat Lengkap', 'address', data.address || '', false, 'Jl. Veteran No. 1, Ketawanggede...')}

                        <div class="flex items-center justify-end gap-2 pt-4 border-t border-gray-100">
                            ${App.secondaryBtn('Batal', "App.navigate('/institutions')")}
                            ${App.primaryBtn(isEdit ? 'Simpan Perubahan' : 'Tambah Instansi')}
                        </div>
                    </form>
                </div>
            `;

            App.bindForm('inst-form', 'institutions', '/institutions', isEdit, data.id);
        }
    };
})();
