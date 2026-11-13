/**
 * Training Schedules Page Module
 * Class Schedule Batches, Capacities, & Trainer Assignment
 */
(function() {
    App.pages['training-schedules'] = {
        state: { page: 1, search: '', status: '' },

        async index(container) {
            this.container = container;
            await this.loadList();
        },

        async loadList() {
            const params = { page: this.state.page, per_page: 20 };
            if (this.state.search) params.search = this.state.search;
            if (this.state.status) params.status = this.state.status;

            const res = await api.list('training-schedules', params);
            const items = res.data || [];
            this.renderList(items, res);
        },

        renderList(items, meta) {
            const user = App.state.user || {};
            const isAdmin = user.role === 'admin';

            const scStyles = {
                scheduled: 'bg-blue-50 text-blue-700 border-blue-200',
                ongoing: 'bg-amber-50 text-amber-700 border-amber-200',
                completed: 'bg-emerald-50 text-emerald-700 border-emerald-200',
                cancelled: 'bg-gray-100 text-gray-600 border-gray-200',
            };

            const rows = items.map(s => {
                const prodName = s.product?.name || s.product_name || ('Program #' + s.product_id);
                const trainerName = s.trainer?.name || s.trainer_name || '-';
                const regCount = s.registrations_count ?? (s.registrations?.length || 0);

                return `
                    <tr class="hover:bg-blue-50/20 transition-colors">
                        <td class="py-3.5 px-4 font-semibold text-gray-900">
                            ${prodName}
                            ${s.location ? `<div class="text-[11px] text-gray-400 font-normal">${s.location}</div>` : ''}
                        </td>

                        <td class="py-3.5 px-4 text-gray-800 font-medium">
                            ${trainerName}
                        </td>

                        <td class="py-3.5 px-4 text-gray-900 font-medium whitespace-nowrap">
                            ${App.formatDate(s.training_date)}
                        </td>

                        <td class="py-3.5 px-4 text-gray-600 whitespace-nowrap">
                            ${s.start_time ? s.start_time.substring(0, 5) : '-'}
                        </td>

                        <td class="py-3.5 px-4 text-gray-600 whitespace-nowrap">
                            ${s.end_time ? s.end_time.substring(0, 5) : '-'}
                        </td>

                        <td class="py-3.5 px-4">
                            <span class="inline-block px-2 py-0.5 border text-[10px] font-semibold uppercase tracking-wider ${scStyles[s.status] || 'bg-gray-50 border-gray-200'}">
                                ${App.statusLabel(s.status)}
                            </span>
                        </td>

                        <td class="py-3.5 px-4 text-xs">
                            <span class="font-extrabold text-orange-600">${regCount}</span> / <span class="text-gray-400">${s.capacity || 0} Kursi</span>
                        </td>

                        <td class="py-3.5 px-4 text-right space-x-2 whitespace-nowrap">
                            <a href="/registrations/create?schedule_id=${s.id}" onclick="event.preventDefault(); App.navigate('/registrations/create?schedule_id=${s.id}')"
                               class="text-xs text-orange-600 hover:text-orange-800 font-semibold p-1">+ Daftar</a>
                            ${isAdmin ? `
                                <a href="/training-schedules/${s.id}/edit" onclick="event.preventDefault(); App.navigate('/training-schedules/${s.id}/edit')"
                                   class="text-xs text-blue-600 hover:text-blue-800 font-medium p-1">Edit</a>
                                <button onclick="App.pages['training-schedules'].deleteSchedule(${s.id})" class="text-xs text-red-500 hover:text-red-700 p-1" title="Hapus">
                                    Hapus
                                </button>
                            ` : ''}
                        </td>
                    </tr>
                `;
            });

            this.container.innerHTML = `
                <div class="space-y-4">
                    <!-- Header -->
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div>
                            <h1 class="text-xl font-bold text-gray-900">Jadwal yang Tersedia</h1>
                            <p class="text-xs text-gray-500 mt-0.5">Setiap jadwal memiliki trainer, waktu, lokasi, dan kapasitas.</p>
                        </div>
                        <div class="flex items-center gap-2">
                            ${isAdmin ? `
                                <a href="/trainers" onclick="event.preventDefault(); App.navigate('/trainers')" class="px-3.5 py-2 text-xs font-medium text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 shadow-xs transition-colors">
                                    Kelola Trainer
                                </a>
                                <a href="/training-schedules/create" onclick="event.preventDefault(); App.navigate('/training-schedules/create')" class="px-4 py-2 text-xs font-semibold text-white bg-orange-500 rounded-full hover:bg-orange-500 shadow-xs transition-colors">
                                    + Buat Jadwal
                                </a>
                            ` : ''}
                        </div>
                    </div>

                    <!-- Filter Card -->
                    <div class="bg-white border border-gray-100 p-3.5 rounded-xl shadow-xs">
                        <div class="flex flex-col sm:flex-row items-center gap-2.5">
                            <div class="relative flex-1 w-full">
                                <input type="text" id="sched-search" value="${this.state.search}"
                                       placeholder="Cari pelatihan, trainer, atau lokasi..."
                                       class="w-full text-xs border border-gray-200 rounded-lg px-3 py-2 text-gray-800 placeholder-gray-400 focus:outline-none focus:border-orange-500"
                                       onkeydown="if(event.key==='Enter') App.pages['training-schedules'].handleSearch()">
                            </div>
                            <div class="w-full sm:w-48">
                                <select id="sched-status" class="w-full text-xs border border-gray-200 rounded-lg px-3 py-2 text-gray-700 focus:outline-none focus:border-orange-500"
                                        onchange="App.pages['training-schedules'].handleStatusChange(this.value)">
                                    <option value="">Semua status</option>
                                    <option value="scheduled" ${this.state.status === 'scheduled' ? 'selected' : ''}>Scheduled</option>
                                    <option value="ongoing" ${this.state.status === 'ongoing' ? 'selected' : ''}>Ongoing</option>
                                    <option value="completed" ${this.state.status === 'completed' ? 'selected' : ''}>Completed</option>
                                    <option value="cancelled" ${this.state.status === 'cancelled' ? 'selected' : ''}>Cancelled</option>
                                </select>
                            </div>
                            <button type="button" onclick="App.pages['training-schedules'].handleSearch()"
                                    class="w-full sm:w-auto px-5 py-2 text-xs font-semibold text-white bg-orange-500 rounded-lg hover:bg-orange-500 shadow-xs transition-colors whitespace-nowrap">
                                Cari
                            </button>
                        </div>
                    </div>

                    <!-- Table Card -->
                    <div class="bg-white border border-gray-100 rounded-xl overflow-hidden shadow-xs">
                        <div class="flex flex-col sm:flex-row items-center gap-2.5">
                            <div class="relative flex-1 w-full">
                                <input type="text" id="sched-search" value="${this.state.search}"
                                       placeholder="Cari pelatihan, trainer, atau lokasi..."
                                       class="w-full text-xs border border-gray-200 px-3 py-2 text-gray-800 placeholder-gray-400 focus:outline-none focus:border-blue-500 rounded"
                                       onkeydown="if(event.key==='Enter') App.pages['training-schedules'].handleSearch()">
                            </div>
                            <div class="w-full sm:w-48">
                                <select id="sched-status" class="w-full text-xs border border-gray-200 px-3 py-2 text-gray-700 focus:outline-none focus:border-blue-500 rounded"
                                        onchange="App.pages['training-schedules'].handleStatusChange(this.value)">
                                    <option value="">Semua status</option>
                                    <option value="scheduled" ${this.state.status === 'scheduled' ? 'selected' : ''}>Scheduled</option>
                                    <option value="ongoing" ${this.state.status === 'ongoing' ? 'selected' : ''}>Ongoing</option>
                                    <option value="completed" ${this.state.status === 'completed' ? 'selected' : ''}>Completed</option>
                                    <option value="cancelled" ${this.state.status === 'cancelled' ? 'selected' : ''}>Cancelled</option>
                                </select>
                            </div>
                            <button type="button" onclick="App.pages['training-schedules'].handleSearch()" class="w-full sm:w-auto px-5 py-2 text-xs font-semibold text-white bg-slate-900 rounded-lg hover:bg-slate-800 shadow-sm transition-colors">
                                Cari
                            </button>
                            ${(this.state.search || this.state.status) ? `
                                <button type="button" onclick="App.pages['training-schedules'].resetFilter()" class="px-3 py-2 text-xs text-gray-500 hover:text-gray-700 border border-gray-200 rounded-lg">Reset</button>
                            ` : ''}
                        </div>
                    </div>

                    <!-- Table Card -->
                    <div class="bg-white border border-blue-100/70 overflow-hidden shadow-sm">
                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[700px] text-left text-xs text-gray-600">
                                <thead class="bg-gray-50/70 border-b border-gray-100 text-gray-400 font-semibold uppercase tracking-wider text-[10.5px]">
                                    <tr>
                                        <th class="py-3.5 px-4">PELATIHAN</th>
                                        <th class="py-3.5 px-4">TRAINER</th>
                                        <th class="py-3.5 px-4">TANGGAL</th>
                                        <th class="py-3.5 px-4">JAM MULAI</th>
                                        <th class="py-3.5 px-4">JAM SELESAI</th>
                                        <th class="py-3.5 px-4">STATUS</th>
                                        <th class="py-3.5 px-4">PESERTA</th>
                                        <th class="py-3.5 px-4 text-right">AKSI</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    ${items.length === 0 ? `
                                        <tr>
                                            <td colspan="8" class="py-8 text-center text-gray-400 text-xs">
                                                Belum ada jadwal pelatihan.
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
            const input = document.getElementById('sched-search');
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
                            ${App.inputField('Tanggal Pelaksanaan', 'training_date', data.training_date ? data.training_date.substring(0, 10) : '', 'date', true)}
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
