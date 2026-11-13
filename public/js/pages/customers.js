/**
 * Customers Page Module
 * Customer List, Master Data, Import/Export CSV, Full CRUD
 */
(function() {
    App.pages.customers = {
        state: {
            page: 1,
            search: '',
            statusFilter: '',
            institutions: [],
        },

        async index(container) {
            this.container = container;
            await this.loadInstitutions();
            await this.loadList();
        },

        async loadInstitutions() {
            try {
                const res = await api.list('institutions', { per_page: 100 });
                this.state.institutions = res.data || [];
            } catch (e) { /* ignore */ }
        },

        async loadList() {
            const params = {
                page: this.state.page,
                per_page: 20,
            };
            if (this.state.search) params.search = this.state.search;
            if (this.state.statusFilter) params.status = this.state.statusFilter;
            if (this.state.institutionFilter) params.institution_id = this.state.institutionFilter;

            const res = await api.list('customers', params);
            const items = res.data || [];

            this.renderList(items, res);
        },

        renderList(items, meta) {
            const user = App.state.user || {};
            const isAdmin = user.role === 'admin';

            const rowsHtml = items.length === 0
                ? `<tr><td colspan="7" class="py-8 text-center text-gray-400">Belum ada data customer</td></tr>`
                : items.map(c => {
                    const instName = c.institution?.name || c.company || '-';
                    const instType = c.institution?.type || '';

                    return `
                        <tr class="hover:bg-orange-50/20 transition-colors">
                            <td class="py-3.5 px-4 font-bold text-gray-900 text-xs">
                                <a href="/customers/${c.id}" onclick="event.preventDefault(); App.navigate('/customers/${c.id}')"
                                   class="hover:text-orange-600 block">${c.name || '-'}</a>
                            </td>
                            <td class="py-3.5 px-4 text-xs font-mono text-gray-500">
                                ${c.customer_code || '-'}
                            </td>
                            <td class="py-3.5 px-4 text-xs">
                                <span class="font-bold text-gray-800 block">${instName}</span>
                                ${instType ? `<span class="text-[11px] text-gray-400">${instType}</span>` : ''}
                            </td>
                            <td class="py-3.5 px-4 text-xs text-gray-600">
                                ${c.email || '-'}
                            </td>
                            <td class="py-3.5 px-4 text-xs text-gray-600">
                                ${c.phone || '-'}
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2.5 py-0.5 text-[11px] font-semibold rounded-full bg-emerald-50 text-emerald-600 border border-emerald-200">
                                    ${(c.status || 'Active').charAt(0).toUpperCase() + (c.status || 'active').slice(1)}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right space-x-2 whitespace-nowrap text-gray-400">
                                <a href="/customers/${c.id}" onclick="event.preventDefault(); App.navigate('/customers/${c.id}')"
                                   class="hover:text-gray-700 inline-block" title="Detail">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                                <a href="/customers/${c.id}/edit" onclick="event.preventDefault(); App.navigate('/customers/${c.id}/edit')"
                                   class="hover:text-orange-600 inline-block" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                </a>
                                <button onclick="App.pages.customers.deleteCustomer(${c.id})" class="hover:text-red-600 inline-block" title="Hapus">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </td>
                        </tr>
                    `;
                }).join('');

            const instOptions = this.state.institutions.map(inst => `
                <option value="${inst.id}" ${this.state.institutionFilter == inst.id ? 'selected' : ''}>${inst.name}</option>
            `).join('');

            this.container.innerHTML = `
                <div class="space-y-4">
                    <!-- Header -->
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div>
                            <h1 class="text-xl font-bold text-gray-900">Daftar Customer</h1>
                            <p class="text-xs text-gray-500 mt-0.5">Data pelanggan, peserta pelatihan, dan instansi mitra Disty Akademi.</p>
                        </div>
                        <div class="flex flex-wrap items-center gap-2">
                            <button type="button" onclick="App.pages.customers.openImportModal()"
                                    class="px-3.5 py-2 text-xs font-medium text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 shadow-xs transition-colors flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                </svg>
                                Import Data
                            </button>
                            <button type="button" onclick="api.exportCustomers()"
                                    class="px-3.5 py-2 text-xs font-medium text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 shadow-xs transition-colors flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                </svg>
                                Export Excel/CSV
                            </button>
                            <a href="/customers/template" target="_blank"
                               class="px-3 py-2 text-xs text-gray-500 hover:text-gray-700 border border-gray-200 rounded-lg bg-white">
                                Template CSV
                            </a>
                            <a href="/customers/create" onclick="event.preventDefault(); App.navigate('/customers/create')"
                               class="px-4 py-2 text-xs font-semibold text-white bg-orange-600 rounded-full hover:bg-orange-500 shadow-xs transition-colors">
                                + Tambah Customer
                            </a>
                        </div>
                    </div>

                    <!-- Filter Card (Sesuai Screenshot 8) -->
                    <div class="bg-white border border-gray-100 p-3.5 rounded-xl shadow-xs">
                        <div class="flex flex-col sm:flex-row items-center gap-2.5">
                            <div class="relative flex-1 w-full">
                                <input type="text" id="cust-search" value="${this.state.search}"
                                       placeholder="Cari customer, email, telepon, instansi..."
                                       class="w-full text-xs border border-gray-200 rounded-lg px-3 py-2 text-gray-800 placeholder-gray-400 focus:outline-none focus:border-orange-500"
                                       onkeydown="if(event.key==='Enter') App.pages.customers.handleFilter()">
                            </div>
                            <div class="w-full sm:w-44">
                                <select id="cust-status" class="w-full text-xs border border-gray-200 rounded-lg px-3 py-2 text-gray-700 focus:outline-none focus:border-orange-500">
                                    <option value="">Semua Status</option>
                                    <option value="active" ${this.state.statusFilter === 'active' ? 'selected' : ''}>Active</option>
                                    <option value="prospect" ${this.state.statusFilter === 'prospect' ? 'selected' : ''}>Prospect</option>
                                    <option value="inactive" ${this.state.statusFilter === 'inactive' ? 'selected' : ''}>Inactive</option>
                                </select>
                            </div>
                            <div class="w-full sm:w-52">
                                <select id="cust-institution" class="w-full text-xs border border-gray-200 rounded-lg px-3 py-2 text-gray-700 focus:outline-none focus:border-orange-500">
                                    <option value="">Semua Instansi</option>
                                    ${instOptions}
                                </select>
                            </div>
                            <button onclick="App.pages.customers.handleFilter()"
                                    class="w-full sm:w-auto px-5 py-2 text-xs font-semibold text-white bg-orange-600 rounded-lg hover:bg-orange-500 shadow-xs transition-colors whitespace-nowrap">
                                Terapkan Filter
                            </button>
                        </div>
                    </div>

                    <!-- Table Card (Sesuai Screenshot 8) -->
                    <div class="bg-white border border-gray-100 rounded-xl overflow-hidden shadow-xs">
                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[700px] text-left text-xs text-gray-600">
                                <thead class="bg-gray-50/70 border-b border-gray-100 text-gray-400 font-semibold uppercase tracking-wider text-[10.5px]">
                                    <tr>
                                        <th class="py-3.5 px-4">CUSTOMER</th>
                                        <th class="py-3.5 px-4">CUSTOMER CODE</th>
                                        <th class="py-3.5 px-4">INSTANSI</th>
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

        handleFilter() {
            const input = document.getElementById('cust-search');
            const status = document.getElementById('cust-status');
            const inst = document.getElementById('cust-institution');
            this.state.search = input ? input.value : '';
            this.state.statusFilter = status ? status.value : '';
            this.state.institutionFilter = inst ? inst.value : '';
            this.state.page = 1;
            this.loadList();
        },

        handleSearch() {
            this.handleFilter();
            this.state.page = 1;
            this.loadList();
        },

        goToPage(page) {
            this.state.page = page;
            this.loadList();
        },

        async deleteCustomer(id) {
            if (!confirm('Hapus data customer ini secara permanen?')) return;
            try {
                await api.remove('customers', id);
                App.showToast('Customer berhasil dihapus.');
                await this.loadList();
            } catch (err) {
                App.showToast(err.message, 'error');
            }
        },

        // ── Create & Edit Form ──
        async create(container) {
            await this.loadInstitutions();
            this.renderForm(container, false);
        },

        async edit(container, id) {
            await this.loadInstitutions();
            const res = await api.get('customers', id);
            this.renderForm(container, true, res.data || res);
        },

        renderForm(container, isEdit, data = {}) {
            const instOptions = this.state.institutions.map(i => ({
                value: i.id,
                label: `${i.name} (${i.type || 'Mitra'})`
            }));

            const statusOptions = [
                { value: 'active', label: 'Aktif (Alumni)' },
                { value: 'prospect', label: 'Prospek' },
                { value: 'inactive', label: 'Tidak Aktif' },
            ];

            container.innerHTML = `
                <div class="max-w-2xl mx-auto space-y-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-extrabold text-gray-900">${isEdit ? 'Edit Customer' : 'Tambah Customer Baru'}</h2>
                            <p class="text-xs text-gray-400 mt-0.5">Kelola identitas peserta & mitra instansi</p>
                        </div>
                        <button onclick="App.navigate('/customers')" class="text-xs text-gray-500 hover:text-gray-900 font-semibold flex items-center gap-1">
                            &larr; Kembali
                        </button>
                    </div>

                    <form id="customer-form" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-4">
                        ${App.inputField('Nama Lengkap', 'name', data.name || '', 'text', true, 'Contoh: Dewi Rahmawati')}
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            ${App.inputField('Email', 'email', data.email || '', 'email', false, 'dewi@example.com')}
                            ${App.inputField('Nomor WhatsApp / HP', 'phone', data.phone || '', 'text', true, '08123456789')}
                        </div>

                        ${App.selectField('Instansi / Perusahaan Mitra', 'institution_id', instOptions, data.institution_id || '')}

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            ${App.inputField('Kota', 'city', data.city || '', 'text', false, 'Surabaya')}
                            ${App.inputField('Provinsi', 'province', data.province || '', 'text', false, 'Jawa Timur')}
                        </div>

                        ${App.selectField('Status Customer', 'status', statusOptions, data.status || 'active', true)}

                        ${App.textareaField('Catatan Tambahan', 'notes', data.notes || '', false, 'Riwayat sertifikasi, nomor sertifikat, dsb...')}

                        <div class="flex items-center justify-end gap-2 pt-4 border-t border-gray-100">
                            ${App.secondaryBtn('Batal', "App.navigate('/customers')")}
                            ${App.primaryBtn(isEdit ? 'Simpan Perubahan' : 'Tambah Customer')}
                        </div>
                    </form>
                </div>
            `;

            App.bindForm('customer-form', 'customers', '/customers', isEdit, data.id);
        },

        // ── Show Detail ──
        async show(container, id) {
            const res = await api.get('customers', id);
            const c = res.data || res;

            container.innerHTML = `
                <div class="max-w-3xl mx-auto space-y-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <button onclick="App.navigate('/customers')" class="p-2 rounded-xl border border-gray-200 text-gray-500 hover:bg-gray-50">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                            </button>
                            <div>
                                <h2 class="text-xl font-extrabold text-gray-900">${c.name}</h2>
                                <p class="text-xs text-gray-400 font-mono mt-0.5">${c.customer_code || '-'}</p>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <button onclick="App.navigate('/customers/${c.id}/edit')" class="px-4 py-2 rounded-xl border border-gray-200 text-gray-700 font-semibold text-xs hover:bg-gray-50">
                                Edit Data
                            </button>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-4 text-xs">
                        <h3 class="font-bold text-gray-900 text-sm border-b border-gray-100 pb-2">Profil Customer</h3>
                        <div class="grid grid-cols-2 gap-4">
                            <div><span class="text-gray-400 block text-[10px]">Email:</span> <strong class="text-gray-900">${c.email || '-'}</strong></div>
                            <div><span class="text-gray-400 block text-[10px]">Telepon:</span> <strong class="text-gray-900">${c.phone || '-'}</strong></div>
                            <div><span class="text-gray-400 block text-[10px]">Instansi:</span> <span class="text-gray-900">${c.institution?.name || c.company || '-'}</span></div>
                            <div><span class="text-gray-400 block text-[10px]">Status:</span> ${App.statusBadge(c.status || 'active')}</div>
                            <div><span class="text-gray-400 block text-[10px]">Lokasi:</span> <span class="text-gray-900">${[c.city, c.province].filter(Boolean).join(', ') || '-'}</span></div>
                            <div><span class="text-gray-400 block text-[10px]">Terdaftar Sejak:</span> <span class="text-gray-900">${App.formatDate(c.created_at)}</span></div>
                        </div>
                        ${c.notes ? `
                            <div class="pt-3 border-t border-gray-100">
                                <span class="text-gray-400 block text-[10px] mb-1">Catatan:</span>
                                <p class="text-gray-700 bg-gray-50 p-3 rounded-xl">${c.notes}</p>
                            </div>
                        ` : ''}
                    </div>
                </div>
            `;
        },

        // ── Import Modal ──
        openImportModal() {
            App.openModal(`
                <div class="p-6 space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                        <h3 class="font-bold text-gray-900 text-sm">Import Data Customer (CSV/Excel)</h3>
                        <button onclick="App.closeModal()" class="text-gray-400 hover:text-gray-600">&times;</button>
                    </div>

                    <form id="cust-import-form" onsubmit="App.pages.customers.handleImport(event)" class="space-y-4 text-xs">
                        <div>
                            <label class="block font-semibold text-gray-700 mb-1">Pilih File CSV</label>
                            <input type="file" name="file" accept=".csv,.xlsx,.xls" required
                                   class="w-full px-3 py-2 border border-gray-200 rounded-xl">
                            <p class="text-[10px] text-gray-400 mt-1">Format kolom: Nama, Email, Nomor Telepon, Instansi, dsb.</p>
                        </div>

                        <div class="flex justify-between items-center pt-3 border-t border-gray-100">
                            <button type="button" onclick="api.downloadCustomerTemplate()" class="text-[11px] text-orange-600 hover:underline font-semibold">
                                Unduh Template CSV
                            </button>
                            <div class="flex gap-2">
                                <button type="button" onclick="App.closeModal()" class="px-3.5 py-2 rounded-xl border border-gray-200 font-semibold text-gray-600">Batal</button>
                                <button type="submit" class="px-4 py-2 rounded-xl bg-orange-500 text-white font-bold hover:bg-orange-600">Mulai Import</button>
                            </div>
                        </div>
                    </form>
                </div>
            `);
        },

        async handleImport(e) {
            e.preventDefault();
            const form = e.target;
            const formData = new FormData(form);

            try {
                await api.importCustomers(formData);
                App.showToast('Data customer berhasil diimport!');
                App.closeModal();
                await this.loadList();
            } catch (err) {
                App.showToast(err.message, 'error');
            }
        }
    };
})();
