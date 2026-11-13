/**
 * Registrations Page Module
 * Enroll Customers into Scheduled Training Batches
 */
(function() {
    App.pages.registrations = {
        state: { page: 1, search: '', status: '' },

        async index(container) {
            this.container = container;
            await this.loadList();
        },

        async loadList() {
            const params = { page: this.state.page, per_page: 20 };
            if (this.state.search) params.search = this.state.search;
            if (this.state.status) params.status = this.state.status;

            const res = await api.list('registrations', params);
            const items = res.data || [];
            this.renderList(items, res);
        },

        renderList(items, meta) {
            const rStyles = {
                registered: 'bg-blue-50 text-blue-700 border-blue-200',
                confirmed: 'bg-emerald-50 text-emerald-700 border-emerald-200',
                attended: 'bg-purple-50 text-purple-700 border-purple-200',
                cancelled: 'bg-gray-100 text-gray-600 border-gray-200',
            };

            const rows = items.map(reg => {
                const sched = reg.schedule || reg.training_schedule;
                const prodName = reg.product?.name || reg.product_name || sched?.product?.name || '-';

                return `
                    <tr class="hover:bg-blue-50/20 transition-colors">
                        <td class="py-3.5 px-4 font-mono font-semibold text-gray-900">
                            ${reg.registration_number || ('#REG-' + reg.id)}
                        </td>

                        <td class="py-3.5 px-4">
                            <a href="/customers/${reg.customer_id}" onclick="event.preventDefault(); App.navigate('/customers/${reg.customer_id}')" class="font-medium text-blue-600 hover:underline">
                                ${reg.customer?.name || ('Customer #' + reg.customer_id)}
                            </a>
                            <div class="text-[11px] text-gray-400">${reg.customer?.customer_code || '-'}</div>
                        </td>

                        <td class="py-3.5 px-4 font-medium text-gray-800">
                            ${prodName}
                        </td>

                        <td class="py-3.5 px-4">
                            ${sched ? `
                                <div class="font-medium text-gray-800">${App.formatDate(sched.training_date)}</div>
                                <div class="text-[11px] text-gray-400">Trainer: ${sched.trainer?.name || '-'}</div>
                            ` : '<span class="text-gray-400">-</span>'}
                        </td>

                        <td class="py-3.5 px-4">
                            <span class="inline-block px-2 py-0.5 border text-[10px] font-semibold uppercase tracking-wider ${rStyles[reg.status] || 'bg-gray-50 border-gray-200'}">
                                ${App.statusLabel(reg.status)}
                            </span>
                        </td>

                        <td class="py-3.5 px-4 font-bold text-gray-900">
                            ${App.formatRupiah(reg.amount)}
                        </td>

                        <td class="py-3.5 px-4 text-right space-x-1 whitespace-nowrap">
                            <button onclick="App.navigate('/registrations/${reg.id}/edit')" class="p-1.5 rounded-lg text-gray-400 hover:text-orange-600 hover:bg-orange-50 inline-block" title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                            </button>
                            <button onclick="App.pages.registrations.deleteReg(${reg.id})" class="p-1.5 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 inline-block" title="Hapus">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </td>
                    </tr>
                `;
            });

            this.container.innerHTML = `
                <div class="space-y-4">
                    <!-- Header -->
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div>
                            <h1 class="text-xl font-bold text-gray-900">Pendaftaran Peserta</h1>
                            <p class="text-xs text-gray-500 mt-0.5">Customer didaftarkan ke jadwal yang telah dibuat, bukan menentukan tanggal sendiri.</p>
                        </div>
                        <div class="flex items-center gap-2">
                            <a href="/training-schedules" onclick="event.preventDefault(); App.navigate('/training-schedules')" class="px-3.5 py-2 text-xs font-medium text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 shadow-sm transition-colors">
                                Lihat Jadwal
                            </a>
                            <a href="/registrations/create" onclick="event.preventDefault(); App.navigate('/registrations/create')" class="px-4 py-2 text-xs font-semibold text-white bg-gray-900 rounded-full hover:bg-black shadow-sm transition-colors">
                                + Daftarkan Customer
                            </a>
                        </div>
                    </div>

                    <!-- Filter Card -->
                    <div class="bg-white border border-blue-100/70 p-3.5 shadow-sm">
                        <div class="flex flex-col sm:flex-row items-center gap-2.5">
                            <div class="relative flex-1 w-full">
                                <input type="text" id="reg-search" value="${this.state.search}"
                                       placeholder="Cari nomor, customer, training, trainer..."
                                       class="w-full text-xs border border-gray-200 px-3 py-2 text-gray-800 placeholder-gray-400 focus:outline-none focus:border-blue-500 rounded"
                                       onkeydown="if(event.key==='Enter') App.pages.registrations.handleSearch()">
                            </div>
                            <div class="w-full sm:w-48">
                                <select id="reg-status" class="w-full text-xs border border-gray-200 px-3 py-2 text-gray-700 focus:outline-none focus:border-blue-500 rounded"
                                        onchange="App.pages.registrations.handleStatusChange(this.value)">
                                    <option value="">Semua status</option>
                                    <option value="registered" ${this.state.status === 'registered' ? 'selected' : ''}>Registered</option>
                                    <option value="confirmed" ${this.state.status === 'confirmed' ? 'selected' : ''}>Confirmed</option>
                                    <option value="attended" ${this.state.status === 'attended' ? 'selected' : ''}>Attended</option>
                                    <option value="cancelled" ${this.state.status === 'cancelled' ? 'selected' : ''}>Cancelled</option>
                                </select>
                            </div>
                            <button type="button" onclick="App.pages.registrations.handleSearch()" class="w-full sm:w-auto px-5 py-2 text-xs font-semibold text-white bg-slate-900 rounded-lg hover:bg-slate-800 shadow-sm transition-colors">
                                Filter
                            </button>
                            ${(this.state.search || this.state.status) ? `
                                <button type="button" onclick="App.pages.registrations.resetFilter()" class="px-3 py-2 text-xs text-gray-500 hover:text-gray-700 border border-gray-200 rounded-lg">Reset</button>
                            ` : ''}
                        </div>
                    </div>

                    <!-- Table Card -->
                    <div class="bg-white border border-blue-100/70 overflow-hidden shadow-sm">
                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[680px] text-left text-xs text-gray-600">
                                <thead class="bg-gray-50/70 border-b border-gray-100 text-gray-400 font-semibold uppercase tracking-wider text-[10.5px]">
                                    <tr>
                                        <th class="py-3.5 px-4">NO. REGISTRASI</th>
                                        <th class="py-3.5 px-4">CUSTOMER</th>
                                        <th class="py-3.5 px-4">PELATIHAN</th>
                                        <th class="py-3.5 px-4">JADWAL</th>
                                        <th class="py-3.5 px-4">STATUS</th>
                                        <th class="py-3.5 px-4">AMOUNT</th>
                                        <th class="py-3.5 px-4 text-right">AKSI</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    ${items.length === 0 ? `
                                        <tr>
                                            <td colspan="7" class="py-8 text-center text-gray-400 text-xs">
                                                Belum ada pendaftaran peserta.
                                            </td>
                                        </tr>
                                    ` : rows.join('')}
                                </tbody>
                            </table>
                        </div>
                        ${App.pagination(meta)}
                    </div>
                </div>
            `;
        },

        handleSearch() {
            const input = document.getElementById('reg-search');
            this.state.search = input ? input.value : '';
            this.state.page = 1;
            this.loadList();
        },

        handleStatusChange(val) {
            this.state.status = val;
            this.state.page = 1;
            this.loadList();
        },

        resetFilter() {
            this.state.search = '';
            this.state.status = '';
            this.state.page = 1;
            this.loadList();
        },

        goToPage(page) {
            this.state.page = page;
            this.loadList();
        },

        async deleteReg(id) {
            if (!confirm('Hapus pendaftaran peserta ini?')) return;
            try {
                await api.remove('registrations', id);
                App.showToast('Pendaftaran berhasil dihapus.');
                await this.loadList();
            } catch (err) {
                App.showToast(err.message, 'error');
            }
        },

        // ── Form ──
        async create(container) {
            const [cRes, sRes] = await Promise.all([
                api.list('customers', { per_page: 100 }),
                api.list('training-schedules', { per_page: 100 })
            ]);
            this.renderForm(container, false, {}, cRes.data || [], sRes.data || []);
        },

        async edit(container, id) {
            const [regRes, cRes, sRes] = await Promise.all([
                api.get('registrations', id),
                api.list('customers', { per_page: 100 }),
                api.list('training-schedules', { per_page: 100 })
            ]);
            this.renderForm(container, true, regRes.data || regRes, cRes.data || [], sRes.data || []);
        },

        renderForm(container, isEdit, data = {}, customers = [], schedules = []) {
            const custOptions = customers.map(c => ({
                value: c.id,
                label: `${c.name} (${c.institution?.name || c.phone || 'Perorangan'})`
            }));

            const schedOptions = schedules.map(s => ({
                value: s.id,
                label: `${s.product?.name || 'Program'} - Tanggal: ${App.formatDate(s.training_date)} (${s.location || 'Online'})`
            }));

            const statusOptions = [
                { value: 'registered', label: 'Terdaftar (Menunggu Pembayaran)' },
                { value: 'confirmed', label: 'Dikonfirmasi (Lunas & Terjadwal)' },
                { value: 'attended', label: 'Hadir Pelatihan' },
                { value: 'cancelled', label: 'Dibatalkan' },
            ];

            container.innerHTML = `
                <div class="max-w-xl mx-auto space-y-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-extrabold text-gray-900">${isEdit ? 'Edit Pendaftaran' : 'Daftarkan Peserta ke Kelas'}</h2>
                            <p class="text-xs text-gray-400 mt-0.5">Pilih customer dan tautkan ke jadwal pelatihan yang aktif</p>
                        </div>
                        <button onclick="App.navigate('/registrations')" class="text-xs text-gray-500 hover:text-gray-900 font-semibold flex items-center gap-1">
                            &larr; Kembali
                        </button>
                    </div>

                    <form id="reg-form" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-4">
                        ${App.selectField('Pilih Customer / Peserta', 'customer_id', custOptions, data.customer_id || '', true)}
                        ${App.selectField('Pilih Jadwal Pelatihan', 'training_schedule_id', schedOptions, data.training_schedule_id || '', true)}

                        ${App.inputField('Biaya Pendaftaran (Rp)', 'amount', data.amount || 0, 'number', true, '0')}
                        ${App.selectField('Status Pendaftaran', 'status', statusOptions, data.status || 'confirmed', true)}
                        ${App.textareaField('Catatan Tambahan', 'notes', data.notes || '', false, 'Kebutuhan akomodasi, no sertifikat, dsb...')}

                        <div class="flex items-center justify-end gap-2 pt-4 border-t border-gray-100">
                            ${App.secondaryBtn('Batal', "App.navigate('/registrations')")}
                            ${App.primaryBtn(isEdit ? 'Simpan Perubahan' : 'Daftarkan')}
                        </div>
                    </form>
                </div>
            `;

            App.bindForm('reg-form', 'registrations', '/registrations', isEdit, data.id);
        }
    };
})();
