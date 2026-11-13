/**
 * Training Schedules Page Module
 * Class Schedule Batches, Capacities, & Trainer Assignment
 */
(function() {
    App.pages['training-schedules'] = {
        state: { page: 1, products: [], trainers: [] },

        async index(container) {
            this.container = container;
            await this.loadList();
        },

        async loadList() {
            const res = await api.list('training-schedules', { page: this.state.page, per_page: 20 });
            const items = res.data || [];
            this.renderList(items, res);
        },

        renderList(items, meta) {
            const rows = items.map(s => {
                const prodName = s.product?.name || 'Program #' + s.product_id;
                const trainerName = s.trainer?.name || 'Trainer #' + s.trainer_id;
                const regCount = s.registrations_count ?? (s.registrations?.length || 0);

                const statusBadges = {
                    scheduled: 'bg-blue-50 text-blue-700 border-blue-200',
                    ongoing: 'bg-cyan-50 text-cyan-700 border-cyan-200',
                    completed: 'bg-emerald-50 text-emerald-700 border-emerald-200',
                    cancelled: 'bg-gray-100 text-gray-500 border-gray-200',
                };

                return `
                    <tr class="hover:bg-orange-50/20 transition-colors">
                        <td class="py-3.5 px-4 text-xs font-bold text-gray-900">
                            ${prodName}
                            <p class="text-[10px] text-gray-400 font-normal mt-0.5">${s.location || 'Online'}</p>
                        </td>

                        <td class="py-3.5 px-4 text-xs font-semibold text-gray-700">
                            ${trainerName}
                        </td>

                        <td class="py-3.5 px-4 text-xs text-gray-800">
                            <span class="font-bold">${App.formatDate(s.training_date)}</span>
                            ${s.start_time ? `<p class="text-[10px] text-gray-400 font-mono mt-0.5">${s.start_time.substring(0, 5)} - ${s.end_time ? s.end_time.substring(0, 5) : 'Selesai'}</p>` : ''}
                        </td>

                        <td class="py-3.5 px-4 text-xs">
                            <span class="font-extrabold text-orange-600">${regCount}</span> / <span class="text-gray-400">${s.capacity || 0} Kursi</span>
                        </td>

                        <td class="py-3.5 px-4">
                            <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-semibold border ${statusBadges[s.status] || 'bg-gray-100 text-gray-600'}">
                                ${App.statusLabel(s.status)}
                            </span>
                        </td>

                        <td class="py-3.5 px-4 text-right space-x-1 whitespace-nowrap">
                            <button onclick="App.navigate('/training-schedules/${s.id}/edit')" class="p-1.5 rounded-lg text-gray-400 hover:text-orange-600 hover:bg-orange-50 inline-block" title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                            </button>
                            <button onclick="App.pages['training-schedules'].deleteSchedule(${s.id})" class="p-1.5 rounded-lg text-gray-400 hover:text-red-600 hover:bg-red-50 inline-block" title="Hapus">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </td>
                    </tr>
                `;
            });

            this.container.innerHTML = `
                <div class="space-y-4">
                    ${App.pageHeader(
                        'Jadwal Pelatihan & Uji Sertifikasi',
                        'Atur batch pelaksanaan kelas, instruktur pengajar, lokasi, dan batas kuota peserta',
                        `<button onclick="App.navigate('/training-schedules/create')" class="px-4 py-2 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-semibold text-xs shadow-sm flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Tambah Jadwal
                        </button>`
                    )}

                    ${App.tableShell(
                        ['Program / Materi', 'Trainer / Instruktur', 'Tanggal & Waktu', 'Peserta Terisi', 'Status', 'Aksi'],
                        rows,
                        'Belum ada jadwal pelatihan.'
                    )}

                    ${App.pagination(meta)}
                </div>
            `;
        },

        goToPage(page) {
            this.state.page = page;
            this.loadList();
        },

        async deleteSchedule(id) {
            if (!confirm('Hapus jadwal pelatihan ini?')) return;
            try {
                await api.remove('training-schedules', id);
                App.showToast('Jadwal berhasil dihapus.');
                await this.loadList();
            } catch (err) {
                App.showToast(err.message, 'error');
            }
        },

        // ── Form ──
        async create(container) {
            const [pRes, tRes] = await Promise.all([
                api.list('products', { per_page: 100 }),
                api.list('trainers', { per_page: 100 })
            ]);
            this.renderForm(container, false, {}, pRes.data || [], tRes.data || []);
        },

        async edit(container, id) {
            const [sRes, pRes, tRes] = await Promise.all([
                api.get('training-schedules', id),
                api.list('products', { per_page: 100 }),
                api.list('trainers', { per_page: 100 })
            ]);
            this.renderForm(container, true, sRes.data || sRes, pRes.data || [], tRes.data || []);
        },

        renderForm(container, isEdit, data = {}, products = [], trainers = []) {
            const prodOptions = products.map(p => ({
                value: p.id,
                label: `${p.name} (${p.code || 'Program'})`
            }));

            const trainerOptions = trainers.map(t => ({
                value: t.id,
                label: t.name
            }));

            const statusOptions = [
                { value: 'scheduled', label: 'Terjadwal (Pendaftaran Dibuka)' },
                { value: 'ongoing', label: 'Sedang Berlangsung' },
                { value: 'completed', label: 'Selesai Pelaksanaan' },
                { value: 'cancelled', label: 'Dibatalkan' },
            ];

            container.innerHTML = `
                <div class="max-w-xl mx-auto space-y-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-extrabold text-gray-900">${isEdit ? 'Edit Jadwal Kelas' : 'Buat Jadwal Pelatihan Baru'}</h2>
                            <p class="text-xs text-gray-400 mt-0.5">Tentukan program, trainer, tanggal, waktu, dan kuota</p>
                        </div>
                        <button onclick="App.navigate('/training-schedules')" class="text-xs text-gray-500 hover:text-gray-900 font-semibold flex items-center gap-1">
                            &larr; Kembali
                        </button>
                    </div>

                    <form id="sched-form" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-4">
                        ${App.selectField('Pilih Program Pelatihan', 'product_id', prodOptions, data.product_id || '', true)}
                        ${App.selectField('Pilih Trainer / Instruktur', 'trainer_id', trainerOptions, data.trainer_id || '', true)}

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                            ${App.inputField('Tanggal Pelaksanaan', 'training_date', data.training_date || '', 'date', true)}
                            ${App.inputField('Jam Mulai', 'start_time', data.start_time ? data.start_time.substring(0, 5) : '09:00', 'time', true)}
                            ${App.inputField('Jam Selesai', 'end_time', data.end_time ? data.end_time.substring(0, 5) : '16:00', 'time', true)}
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            ${App.inputField('Kapasitas / Kuota Peserta', 'capacity', data.capacity || 20, 'number', true, '20')}
                            ${App.selectField('Status Jadwal', 'status', statusOptions, data.status || 'scheduled', true)}
                        </div>

                        ${App.inputField('Lokasi / Link Zoom', 'location', data.location || '', 'text', false, 'Contoh: Ruang Training Lt. 2 / Zoom Meeting')}
                        ${App.textareaField('Catatan Khusus Batch', 'notes', data.notes || '', false, 'Persyaratan laptop, sertifikat BNSP, dsb...')}

                        <div class="flex items-center justify-end gap-2 pt-4 border-t border-gray-100">
                            ${App.secondaryBtn('Batal', "App.navigate('/training-schedules')")}
                            ${App.primaryBtn(isEdit ? 'Simpan Perubahan' : 'Buat Jadwal')}
                        </div>
                    </form>
                </div>
            `;

            App.bindForm('sched-form', 'training-schedules', '/training-schedules', isEdit, data.id);
        }
    };
})();
