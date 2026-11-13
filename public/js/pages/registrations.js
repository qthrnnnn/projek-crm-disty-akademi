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
                registered: 'bg-blue-50/70 text-blue-700 border-blue-200',
                confirmed: 'bg-emerald-50/70 text-emerald-700 border-emerald-200',
                attended: 'bg-purple-50/70 text-purple-700 border-purple-200',
                cancelled: 'bg-gray-100 text-gray-600 border-gray-200',
            };

            const rows = items.map(reg => {
                const sched = reg.schedule || reg.training_schedule;
                const prodName = reg.product?.name || reg.product_name || sched?.product?.name || '-';

                return `
                    <tr class="hover:bg-blue-50/20 transition-colors">
                        <td class="py-3.5 px-4 font-mono font-bold text-gray-900 text-xs">
                            ${reg.registration_number || ('#REG-' + reg.id)}
                        </td>

                        <td class="py-3.5 px-4 text-xs">
                            <a href="/customers/${reg.customer_id}" onclick="event.preventDefault(); App.navigate('/customers/${reg.customer_id}')" class="font-semibold text-blue-600 hover:underline">
                                ${reg.customer?.name || ('Customer #' + reg.customer_id)}
                            </a>
                            <div class="text-[11px] text-gray-400 font-mono">${reg.customer?.customer_code || '-'}</div>
                        </td>

                        <td class="py-3.5 px-4 font-bold text-gray-800 text-xs">
                            ${prodName}
                        </td>

                        <td class="py-3.5 px-4 text-xs">
                            ${sched ? `
                                <div class="font-bold text-gray-800">${App.formatDate(sched.training_date)}</div>
                                <div class="text-[11px] text-gray-400">Trainer: ${sched.trainer?.name || '-'}</div>
                            ` : '<span class="text-gray-400">-</span>'}
                        </td>

                        <td class="py-3.5 px-4">
                            <span class="inline-block px-2.5 py-0.5 border text-[10px] font-semibold uppercase tracking-wider rounded ${rStyles[reg.status] || 'bg-gray-50 border-gray-200'}">
                                ${App.statusLabel(reg.status)}
                            </span>
                        </td>

                        <td class="py-3.5 px-4 font-bold text-gray-900 text-xs">
                            ${App.formatRupiah(reg.amount)}
                        </td>

                        <td class="py-3.5 px-4 text-right whitespace-nowrap">
                            <a href="/registrations/${reg.id}/edit" onclick="event.preventDefault(); App.navigate('/registrations/${reg.id}/edit')"
                               class="text-blue-600 hover:underline font-semibold text-xs">Edit</a>
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
                            <a href="/training-schedules" onclick="event.preventDefault(); App.navigate('/training-schedules')" class="px-3.5 py-2 text-xs font-medium text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 shadow-xs transition-colors">
                                Lihat Jadwal
                            </a>
                            <a href="/registrations/create" onclick="event.preventDefault(); App.navigate('/registrations/create')" class="px-4 py-2 text-xs font-semibold text-white bg-orange-500 rounded-full hover:bg-orange-500 shadow-xs transition-colors">
                                + Daftarkan Customer
                            </a>
                        </div>
                    </div>

                    <!-- Filter Card -->
                    <div class="bg-white border border-gray-100 p-3.5 rounded-xl shadow-xs">
                        <div class="flex flex-col sm:flex-row items-center gap-2.5">
                            <div class="relative flex-1 w-full">
                                <input type="text" id="reg-search" value="${this.state.search}"
                                       placeholder="Cari nomor, customer, training, trainer..."
                                       class="w-full text-xs border border-gray-200 px-3 py-2 text-gray-800 placeholder-gray-400 focus:outline-none focus:border-orange-500 rounded-lg"
                                       onkeydown="if(event.key==='Enter') App.pages.registrations.handleSearch()">
                            </div>
                            <div class="w-full sm:w-48">
                                <select id="reg-status" class="w-full text-xs border border-gray-200 px-3 py-2 text-gray-700 focus:outline-none focus:border-orange-500 rounded-lg"
                                        onchange="App.pages.registrations.handleStatusChange(this.value)">
                                    <option value="">Semua status</option>
                                    <option value="registered" ${this.state.status === 'registered' ? 'selected' : ''}>Registered</option>
                                    <option value="confirmed" ${this.state.status === 'confirmed' ? 'selected' : ''}>Confirmed</option>
                                    <option value="attended" ${this.state.status === 'attended' ? 'selected' : ''}>Attended</option>
                                    <option value="cancelled" ${this.state.status === 'cancelled' ? 'selected' : ''}>Cancelled</option>
                                </select>
                            </div>
                            <button type="button" onclick="App.pages.registrations.handleSearch()" class="w-full sm:w-auto px-5 py-2 text-xs font-semibold text-white bg-orange-500 rounded-lg hover:bg-orange-500 shadow-xs transition-colors whitespace-nowrap">
                                Filter
                            </button>
                            ${(this.state.search || this.state.status) ? `
                                <button type="button" onclick="App.pages.registrations.resetFilter()" class="px-3 py-2 text-xs text-gray-500 hover:text-gray-700 border border-gray-200 rounded-lg">Reset</button>
                            ` : ''}
                        </div>
                    </div>

                    <!-- Table Card -->
                    <div class="bg-white border border-gray-100 rounded-xl overflow-hidden shadow-xs">
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
