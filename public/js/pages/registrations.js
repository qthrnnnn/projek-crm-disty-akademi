/**
 * Registrations Page Module
 * Enroll Customers into Scheduled Training Batches
 */
(function() {
    App.pages.registrations = {
        state: { page: 1, customers: [], schedules: [] },

        async index(container) {
            this.container = container;
            await this.loadList();
        },

        async loadList() {
            const res = await api.list('registrations', { page: this.state.page, per_page: 20 });
            const items = res.data || [];
            this.renderList(items, res);
        },

        renderList(items, meta) {
            const rows = items.map(reg => {
                const custName = reg.customer?.name || 'Customer #' + reg.customer_id;
                const scheduleInfo = reg.training_schedule ?
                    `${reg.training_schedule.product?.name || 'Program'} (${App.formatDate(reg.training_schedule.training_date)})` :
                    'Jadwal #' + reg.training_schedule_id;

                const statusBadges = {
                    registered: 'bg-blue-50 text-blue-700 border-blue-200',
                    confirmed: 'bg-emerald-50 text-emerald-700 border-emerald-200',
                    attended: 'bg-purple-50 text-purple-700 border-purple-200',
                    cancelled: 'bg-gray-100 text-gray-500 border-gray-200',
                };

                return `
                    <tr class="hover:bg-orange-50/20 transition-colors">
                        <td class="py-3.5 px-4 text-xs font-bold text-gray-900">
                            ${custName}
                            <p class="text-[10px] text-gray-400 font-normal mt-0.5">${reg.customer?.email || reg.customer?.phone || '-'}</p>
                        </td>

                        <td class="py-3.5 px-4 text-xs font-semibold text-gray-800">
                            ${scheduleInfo}
                        </td>

                        <td class="py-3.5 px-4 text-xs font-extrabold text-emerald-600">
                            ${App.formatRupiah(reg.amount)}
                        </td>

                        <td class="py-3.5 px-4">
                            <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-semibold border ${statusBadges[reg.status] || 'bg-gray-100 text-gray-600'}">
                                ${App.statusLabel(reg.status)}
                            </span>
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
                    ${App.pageHeader(
                        'Pendaftaran Peserta Pelatihan',
                        'Daftarkan customer tetap ke jadwal kelas pelatihan & uji sertifikasi yang aktif',
                        `<button onclick="App.navigate('/registrations/create')" class="px-4 py-2 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-semibold text-xs shadow-sm flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Daftarkan Peserta
                        </button>`
                    )}

                    ${App.tableShell(
                        ['Peserta (Customer)', 'Jadwal / Program Pelatihan', 'Biaya Pendaftaran', 'Status', 'Aksi'],
                        rows,
                        'Belum ada data pendaftaran peserta kelas.'
                    )}

                    ${App.pagination(meta)}
                </div>
            `;
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
