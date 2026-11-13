/**
 * Users Page Module
 * User & Team Management (Admin / Sales Roles)
 */
(function() {
    App.pages.users = {
        state: { page: 1, search: '' },

        async index(container) {
            this.container = container;
            await this.loadList();
        },

        async loadList() {
            const params = { page: this.state.page, per_page: 20 };
            if (this.state.search) params.search = this.state.search;

            const res = await api.list('users', params);
            const items = res.data || [];
            this.renderList(items, res);
        },

        renderList(items, meta) {
            const rows = items.map(u => `
                <tr class="hover:bg-orange-50/20 transition-colors">
                    <td class="py-3.5 px-4 text-xs font-bold text-gray-900">
                        ${u.name}
                    </td>

                    <td class="py-3.5 px-4 text-xs text-gray-700">
                        ${u.email}
                    </td>

                    <td class="py-3.5 px-4 text-xs text-gray-700">
                        ${u.phone || '-'}
                    </td>

                    <td class="py-3.5 px-4">
                        <span class="inline-block px-2.5 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wider ${u.role === 'admin' ? 'bg-orange-50 text-orange-700 border border-orange-200' : 'bg-emerald-50 text-emerald-700 border border-emerald-200'}">
                            ${u.role}
                        </span>
                    </td>

                    <td class="py-3.5 px-4">
                        <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-semibold border ${u.is_active ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-gray-100 text-gray-500 border-gray-200'}">
                            ${u.is_active ? 'Aktif' : 'Non-aktif'}
                        </span>
                    </td>

                    <td class="py-3.5 px-4 text-right space-x-1 whitespace-nowrap">
                        <button onclick="App.navigate('/users/${u.id}/edit')" class="p-1.5 rounded-lg text-gray-400 hover:text-orange-600 hover:bg-orange-50 inline-block" title="Edit">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                        </button>
                        <button onclick="App.pages.users.deleteUser(${u.id})" class="p-1.5 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 inline-block" title="Hapus">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </td>
                </tr>
            `);

            this.container.innerHTML = `
                <div class="space-y-4">
                    ${App.pageHeader(
                        'Manajemen Pengguna CRM',
                        'Kelola akun login untuk tim Admin dan Sales Affiliate',
                        `<button onclick="App.navigate('/users/create')" class="px-4 py-2 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-semibold text-xs shadow-sm flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Tambah Pengguna
                        </button>`
                    )}

                    <!-- Search Bar -->
                    <div class="flex items-center gap-2 bg-white p-3 rounded-2xl border border-gray-100 shadow-2xs">
                        <div class="flex-1 relative">
                            <input type="text" id="usr-search" value="${this.state.search}" placeholder="Cari nama atau email pengguna..."
                                   class="w-full pl-9 pr-3 py-1.5 rounded-xl border border-gray-200 text-xs focus:ring-1 focus:ring-orange-500 outline-none"
                                   onkeydown="if(event.key==='Enter') App.pages.users.handleSearch()">
                            <svg class="w-4 h-4 text-gray-400 absolute left-2.5 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <button onclick="App.pages.users.handleSearch()" class="px-4 py-1.5 rounded-xl bg-gray-900 text-white font-semibold text-xs hover:bg-black">
                            Cari
                        </button>
                    </div>

                    ${App.tableShell(
                        ['Nama Pengguna', 'Email', 'No. WhatsApp / HP', 'Role Akses', 'Status', 'Aksi'],
                        rows,
                        'Belum ada data pengguna.'
                    )}

                    ${App.pagination(meta)}
                </div>
            `;
        },

        handleSearch() {
            const input = document.getElementById('usr-search');
            this.state.search = input ? input.value : '';
            this.state.page = 1;
            this.loadList();
        },

        goToPage(page) {
            this.state.page = page;
            this.loadList();
        },

        async deleteUser(id) {
            if (!confirm('Hapus akun pengguna ini?')) return;
            try {
                await api.remove('users', id);
                App.showToast('Pengguna berhasil dihapus.');
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
            const res = await api.get('users', id);
            this.renderForm(container, true, res.data || res);
        },

        renderForm(container, isEdit, data = {}) {
            const roleOptions = [
                { value: 'admin', label: 'Admin (Akses Penuh Seluruh Modul)' },
                { value: 'sales', label: 'Sales (Akses Lead, Komisi & Katalog)' },
            ];

            container.innerHTML = `
                <div class="max-w-xl mx-auto space-y-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-extrabold text-gray-900">${isEdit ? 'Edit Pengguna' : 'Tambah Pengguna Baru'}</h2>
                            <p class="text-xs text-gray-400 mt-0.5">Atur hak akses role dan informasi login CRM</p>
                        </div>
                        <button onclick="App.navigate('/users')" class="text-xs text-gray-500 hover:text-gray-900 font-semibold flex items-center gap-1">
                            &larr; Kembali
                        </button>
                    </div>

                    <form id="user-form" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-4">
                        ${App.inputField('Nama Lengkap', 'name', data.name || '', 'text', true, 'Contoh: Rian Pratama')}

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            ${App.inputField('Email Login', 'email', data.email || '', 'email', true, 'rian@distyakademi.com')}
                            ${App.inputField('Nomor WhatsApp / HP', 'phone', data.phone || '', 'text', false, '08123456789')}
                        </div>

                        ${App.selectField('Role Akses Sistem', 'role', roleOptions, data.role || 'sales', true)}

                        <div>
                            <label class="block text-[11px] font-semibold text-gray-700 mb-1">Kata Sandi ${isEdit ? '(Kosongkan jika tidak diubah)' : '<span class="text-red-400">*</span>'}</label>
                            <input type="password" name="password" ${isEdit ? '' : 'required'} placeholder="••••••••"
                                   class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs outline-none">
                        </div>

                        <div class="pt-2">
                            <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-gray-700">
                                <input type="checkbox" name="is_active" value="1" ${data.is_active !== false ? 'checked' : ''} class="rounded text-orange-500">
                                Akun Aktif & Bisa Login
                            </label>
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-4 border-t border-gray-100">
                            ${App.secondaryBtn('Batal', "App.navigate('/users')")}
                            ${App.primaryBtn(isEdit ? 'Simpan Perubahan' : 'Buat Pengguna')}
                        </div>
                    </form>
                </div>
            `;

            App.bindForm('user-form', 'users', '/users', isEdit, data.id);
        }
    };
})();
