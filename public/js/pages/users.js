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
            const rowsHtml = items.length === 0
                ? `<tr><td colspan="5" class="py-8 text-center text-gray-400">Belum ada data pengguna</td></tr>`
                : items.map(u => {
                    const isAdm = u.role === 'admin';
                    const roleBadge = isAdm
                        ? `<span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-purple-50 text-purple-700 border border-purple-200">ADMIN</span>`
                        : `<span class="inline-block px-2 py-0.5 rounded text-[10px] font-bold uppercase tracking-wider bg-emerald-50 text-emerald-700 border border-emerald-200">SALES</span>`;

                    return `
                        <tr class="hover:bg-orange-50/20 transition-colors">
                            <td class="py-3.5 px-4 text-xs">
                                <span class="font-bold text-gray-900 block">${u.name}</span>
                                <span class="text-[11px] text-gray-400 font-normal">${u.email}</span>
                            </td>

                            <td class="py-3.5 px-4">
                                ${roleBadge}
                            </td>

                            <td class="py-3.5 px-4 text-xs text-gray-700 font-mono">
                                ${u.phone || '-'}
                            </td>

                            <td class="py-3.5 px-4">
                                <span class="inline-block px-2 py-0.5 rounded text-[10px] font-semibold border ${u.is_active ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-gray-100 text-gray-500 border-gray-200'}">
                                    ${u.is_active ? 'Aktif' : 'Non-aktif'}
                                </span>
                            </td>

                            <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                <a href="/users/${u.id}/edit" onclick="event.preventDefault(); App.navigate('/users/${u.id}/edit')"
                                   class="text-gray-400 hover:text-gray-700 text-xs font-medium">Edit</a>
                            </td>
                        </tr>
                    `;
                }).join('');

            this.container.innerHTML = `
                <div class="space-y-4">
                    <!-- Header -->
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div>
                            <h1 class="text-xl font-bold text-gray-900">Pengguna & Akun Tim</h1>
                            <p class="text-xs text-gray-500 mt-0.5">Kelola akun Admin dan Sales tim Disty Akademi</p>
                        </div>
                        <div>
                            <a href="/users/create" onclick="event.preventDefault(); App.navigate('/users/create')"
                               class="px-4 py-2 text-xs font-semibold text-white bg-orange-500 rounded-full hover:bg-orange-500 shadow-xs transition-colors inline-block">
                                + Tambah Pengguna
                            </a>
                        </div>
                    </div>

                    <!-- Table Card (Sesuai Screenshot 12) -->
                    <div class="bg-white border border-gray-100 rounded-xl overflow-hidden shadow-xs">
                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[700px] text-left text-xs text-gray-600">
                                <thead class="bg-gray-50/70 border-b border-gray-100 text-gray-400 font-semibold uppercase tracking-wider text-[10.5px]">
                                    <tr>
                                        <th class="py-3.5 px-4">NAMA & EMAIL</th>
                                        <th class="py-3.5 px-4">ROLE</th>
                                        <th class="py-3.5 px-4">NO. HP / WA</th>
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
