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

            const rowsHtml = items.length === 0
                ? `<tr><td colspan="5" class="py-8 text-center text-gray-400">Belum ada data instansi mitra</td></tr>`
                : items.map(inst => `
                    <tr class="hover:bg-orange-50/20 transition-colors">
                        <td class="py-3.5 px-4 text-xs font-bold text-gray-900">
                            ${inst.name}
                            ${(inst.email || inst.phone) ? `
                                <p class="text-[11px] text-gray-400 font-normal mt-0.5">
                                    ${[inst.email, inst.phone].filter(Boolean).join(' · ')}
                                </p>
                            ` : ''}
                        </td>

                        <td class="py-3.5 px-4">
                            <span class="inline-block px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-gray-100 text-gray-700">
                                ${inst.type || 'Perguruan Tinggi'}
                            </span>
                        </td>

                        <td class="py-3.5 px-4 text-xs text-gray-600">
                            ${inst.city ? `${inst.city} ${inst.province ? `, ${inst.province}` : ''}` : '-'}
                        </td>

                        <td class="py-3.5 px-4 text-xs font-semibold text-blue-600">
                            <a href="/customers?institution_id=${inst.id}" onclick="event.preventDefault(); App.navigate('/customers?institution_id=${inst.id}')"
                               class="hover:underline">
                                ${inst.customers_count ?? (inst.customers ? inst.customers.length : 0)} customer
                            </a>
                        </td>

                        <td class="py-3.5 px-4 text-right space-x-2 whitespace-nowrap text-gray-400">
                            ${isAdmin ? `
                                <a href="/institutions/${inst.id}/edit" onclick="event.preventDefault(); App.navigate('/institutions/${inst.id}/edit')"
                                   class="hover:text-orange-600 inline-block" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                </a>
                                <button onclick="App.pages.institutions.deleteInstitution(${inst.id})" class="hover:text-red-600 inline-block" title="Hapus">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            ` : ''}
                        </td>
                    </tr>
                `).join('');

            this.container.innerHTML = `
                <div class="space-y-4">
                    <!-- Header -->
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div>
                            <h1 class="text-xl font-bold text-gray-900">Daftar Instansi</h1>
                            <p class="text-xs text-gray-500 mt-0.5">Kelola data instansi, perusahaan, kampus, atau organisasi mitra.</p>
                        </div>
                        <div>
                            <a href="/institutions/create" onclick="event.preventDefault(); App.navigate('/institutions/create')"
                               class="px-4 py-2 text-xs font-semibold text-white bg-orange-600 rounded-full hover:bg-orange-500 shadow-xs transition-colors inline-block">
                                + Tambah Instansi
                            </a>
                        </div>
                    </div>

                    <!-- Filter Card (Sesuai Screenshot 9) -->
                    <div class="bg-white border border-gray-100 p-3.5 rounded-xl shadow-xs">
                        <div class="flex flex-col sm:flex-row items-center gap-2.5">
                            <div class="relative flex-1 w-full">
                                <input type="text" id="inst-search" value="${this.state.search}"
                                       placeholder="Cari instansi, kota, atau jenis instansi..."
                                       class="w-full text-xs border border-gray-200 rounded-lg px-3 py-2 text-gray-800 placeholder-gray-400 focus:outline-none focus:border-orange-500"
                                       onkeydown="if(event.key==='Enter') App.pages.institutions.handleSearch()">
                            </div>
                            <button onclick="App.pages.institutions.handleSearch()"
                                    class="w-full sm:w-auto px-5 py-2 text-xs font-semibold text-white bg-orange-600 rounded-lg hover:bg-orange-500 shadow-xs transition-colors whitespace-nowrap">
                                Cari
                            </button>
                        </div>
                    </div>

                    <!-- Table Card (Sesuai Screenshot 9) -->
                    <div class="bg-white border border-gray-100 rounded-xl overflow-hidden shadow-xs">
                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[700px] text-left text-xs text-gray-600">
                                <thead class="bg-gray-50/70 border-b border-gray-100 text-gray-400 font-semibold uppercase tracking-wider text-[10.5px]">
                                    <tr>
                                        <th class="py-3.5 px-4">INSTANSI</th>
                                        <th class="py-3.5 px-4">JENIS</th>
                                        <th class="py-3.5 px-4">KOTA</th>
                                        <th class="py-3.5 px-4">CUSTOMER</th>
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
