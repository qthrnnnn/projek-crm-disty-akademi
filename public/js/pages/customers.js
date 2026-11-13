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

            const res = await api.list('customers', params);
            const items = res.data || [];

            this.renderList(items, res);
        },

        renderList(items, meta) {
            const user = App.state.user || {};
            const isAdmin = user.role === 'admin';

            const rows = items.map(c => {
                const instName = c.institution?.name || c.company || '-';
                const cleanPhone = (c.phone || '').replace(/[^0-9]/g, '');
                const waLink = cleanPhone ? (cleanPhone.startsWith('0') ? '62' + cleanPhone.substring(1) : cleanPhone) : null;

                return `
                    <tr class="hover:bg-orange-50/20 transition-colors">
                        <td class="py-3.5 px-4">
                            <a href="/customers/${c.id}" onclick="event.preventDefault(); App.navigate('/customers/${c.id}')"
                               class="font-bold text-gray-900 hover:text-orange-600 block text-xs">${c.name || '-'}</a>
                            <p class="text-[10px] text-gray-400 font-mono mt-0.5">${c.customer_code || '-'}</p>
                        </td>

                        <td class="py-3.5 px-4 text-xs">
                            <span class="text-gray-800 font-medium">${instName}</span>
                            ${c.institution?.type ? `<p class="text-[10px] text-gray-400">${c.institution.type}</p>` : ''}
                        </td>

                        <td class="py-3.5 px-4 text-xs">
                            ${waLink ? `
                                <a href="https://wa.me/${waLink}" target="_blank" class="text-emerald-600 font-semibold hover:underline">
                                    ${c.phone}
                                </a>
                            ` : `<span class="text-gray-600">${c.phone || '-'}</span>`}
                            <p class="text-[10px] text-gray-400">${c.email || '-'}</p>
                        </td>

                        <td class="py-3.5 px-4 text-xs text-gray-500">
                            ${[c.city, c.province].filter(Boolean).join(', ') || '-'}
                        </td>

                        <td class="py-3.5 px-4">
                            ${App.statusBadge(c.status || 'active')}
                        </td>

                        <td class="py-3.5 px-4 text-right space-x-1.5 whitespace-nowrap">
                            <a href="/customers/${c.id}" onclick="event.preventDefault(); App.navigate('/customers/${c.id}')"
                               class="p-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 inline-block" title="Detail">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                            ${isAdmin ? `
                                <a href="/customers/${c.id}/edit" onclick="event.preventDefault(); App.navigate('/customers/${c.id}/edit')"
                                   class="p-1.5 rounded-lg text-gray-400 hover:text-orange-600 hover:bg-orange-50 inline-block" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                </a>
                                <button onclick="App.pages.customers.deleteCustomer(${c.id})" class="p-1.5 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 inline-block" title="Hapus">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            ` : ''}
                        </td>
                    </tr>
                `;
            });

            const actionButtons = `
                ${isAdmin ? `
                    <button onclick="App.pages.customers.openImportModal()" class="px-3 py-2 rounded-xl border border-gray-200 text-gray-700 font-semibold text-xs hover:bg-gray-50 flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                        Import
                    </button>
                    <button onclick="api.exportCustomers()" class="px-3 py-2 rounded-xl border border-gray-200 text-gray-700 font-semibold text-xs hover:bg-gray-50 flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l4 4m0 0l4-4m-4 4V4"/></svg>
                        Export CSV
                    </button>
                    <button onclick="App.navigate('/customers/create')" class="px-4 py-2 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-semibold text-xs shadow-sm flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                        Tambah Customer
                    </button>
                ` : ''}
            `;

            this.container.innerHTML = `
                <div class="space-y-4">
                    ${App.pageHeader('Daftar Customer & Alumni Pelatihan', 'Database peserta yang sudah pernah mengikuti pelatihan & sertifikasi', actionButtons)}

                    <!-- Search Bar -->
                    <div class="flex items-center gap-2 bg-white p-3 rounded-2xl border border-gray-100 shadow-2xs">
                        <div class="flex-1 relative">
                            <input type="text" id="cust-search" value="${this.state.search}" placeholder="Cari nama customer, email, kode, atau instansi..."
                                   class="w-full pl-9 pr-3 py-1.5 rounded-xl border border-gray-200 text-xs focus:ring-1 focus:ring-orange-500 outline-none"
                                   onkeydown="if(event.key==='Enter') App.pages.customers.handleSearch()">
                            <svg class="w-4 h-4 text-gray-400 absolute left-2.5 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <button onclick="App.pages.customers.handleSearch()" class="px-4 py-1.5 rounded-xl bg-gray-900 text-white font-semibold text-xs hover:bg-black">
                            Cari
                        </button>
                    </div>

                    ${App.tableShell(
                        ['Customer / Peserta', 'Instansi', 'Kontak', 'Kota & Provinsi', 'Status', 'Aksi'],
                        rows,
                        'Belum ada data customer.'
                    )}

                    ${App.pagination(meta)}
                </div>
            `;
        },

        handleSearch() {
            const input = document.getElementById('cust-search');
            this.state.search = input ? input.value : '';
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
