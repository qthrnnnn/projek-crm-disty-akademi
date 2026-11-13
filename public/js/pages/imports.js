/**
 * Imports Page Module
 * Batch CSV Imports & History Tracking
 */
(function() {
    App.pages['lead-imports'] = {
        state: { page: 1, sources: [], users: [] },

        async index(container) {
            this.container = container;
            await this.loadList();
        },

        async loadList() {
            const res = await api.list('import-batches', { page: this.state.page, per_page: 20 });
            const items = res.data || [];
            this.renderList(items, res);
        },

        renderList(items, meta) {
            const rows = items.map(imp => {
                const statusBadges = {
                    completed: 'bg-emerald-50 text-emerald-700 border-emerald-200',
                    processing: 'bg-amber-50 text-amber-700 border-amber-200',
                    failed: 'bg-red-50 text-red-700 border-red-200',
                };

                return `
                    <tr class="hover:bg-orange-50/20 transition-colors">
                        <td class="py-3.5 px-4 text-xs font-bold text-gray-900">
                            ${imp.original_filename || 'Batch #' + imp.id}
                        </td>

                        <td class="py-3.5 px-4 text-xs text-gray-700">
                            ${imp.user?.name || 'Admin'}
                        </td>

                        <td class="py-3.5 px-4 text-xs text-center font-bold text-gray-900">
                            ${imp.total_rows || 0}
                        </td>

                        <td class="py-3.5 px-4 text-xs text-center font-bold text-emerald-600">
                            ${imp.success_rows || 0}
                        </td>

                        <td class="py-3.5 px-4 text-xs text-center font-bold text-red-600">
                            ${imp.failed_rows || 0}
                        </td>

                        <td class="py-3.5 px-4">
                            <span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-semibold border ${statusBadges[imp.status] || 'bg-gray-100 text-gray-600'}">
                                ${App.statusLabel(imp.status)}
                            </span>
                        </td>

                        <td class="py-3.5 px-4 text-xs text-gray-400">
                            ${App.formatDateTime(imp.created_at)}
                        </td>

                        <td class="py-3.5 px-4 text-right whitespace-nowrap">
                            <button onclick="App.navigate('/lead-imports/${imp.id}')" class="p-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 inline-block" title="Detail">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </button>
                        </td>
                    </tr>
                `;
            });

            this.container.innerHTML = `
                <div class="space-y-4">
                    ${App.pageHeader(
                        'Riwayat Batch Import Data CRM',
                        'Daftar file CSV yang telah diproses masuk ke database leads',
                        `<button onclick="App.navigate('/lead-imports/create')" class="px-4 py-2 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-semibold text-xs shadow-sm flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                            Upload File Baru
                        </button>`
                    )}

                    ${App.tableShell(
                        ['Nama File CSV', 'Pengunggah', 'Total', 'Berhasil', 'Gagal', 'Status', 'Waktu Upload', 'Aksi'],
                        rows,
                        'Belum ada riwayat batch import.'
                    )}

                    ${App.pagination(meta)}
                </div>
            `;
        },

        goToPage(page) {
            this.state.page = page;
            this.loadList();
        },

        // ── Form ──
        async create(container) {
            const [sRes, uRes] = await Promise.all([
                api.list('lead-sources', { per_page: 100 }),
                api.list('users', { per_page: 100 })
            ]);
            const sources = sRes.data || [];
            const users = (uRes.data || []).filter(u => u.role === 'sales');

            const sourceOptions = sources.map(s => ({ value: s.id, label: s.name }));
            const userOptions = users.map(u => ({ value: u.id, label: u.name }));

            container.innerHTML = `
                <div class="max-w-xl mx-auto space-y-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-extrabold text-gray-900">Upload File Import Batch</h2>
                            <p class="text-xs text-gray-400 mt-0.5">Unggah CSV untuk memasukkan ratusan lead sekaligus</p>
                        </div>
                        <button onclick="App.navigate('/lead-imports')" class="text-xs text-gray-500 hover:text-gray-900 font-semibold flex items-center gap-1">
                            &larr; Kembali
                        </button>
                    </div>

                    <form id="import-form" onsubmit="App.pages['lead-imports'].handleSubmit(event)" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-4 text-xs">
                        <div>
                            <label class="block font-semibold text-gray-700 mb-1">File CSV Data Leads <span class="text-red-400">*</span></label>
                            <input type="file" name="file" accept=".csv" required class="w-full px-3 py-2 border border-gray-200 rounded-xl">
                            <p class="text-[10px] text-gray-400 mt-1">Pastikan header kolom sesuai: nama, email, whatsapp, dsb.</p>
                        </div>

                        ${App.selectField('Sumber Prospek (Lead Source)', 'lead_source_id', sourceOptions, '')}
                        ${App.selectField('Tugaskan ke Sales Tertentu (Opsional)', 'assigned_to', userOptions, '')}

                        <div class="flex items-center justify-between pt-4 border-t border-gray-100">
                            <button type="button" onclick="api.downloadLeadTemplate()" class="text-[11px] text-orange-600 font-semibold hover:underline">
                                Unduh Template CSV
                            </button>
                            <div class="flex gap-2">
                                ${App.secondaryBtn('Batal', "App.navigate('/lead-imports')")}
                                ${App.primaryBtn('Mulai Proses Import')}
                            </div>
                        </div>
                    </form>
                </div>
            `;
        },

        async handleSubmit(e) {
            e.preventDefault();
            const form = e.target;
            const formData = new FormData(form);
            const btn = form.querySelector('button[type="submit"]');

            btn.disabled = true;
            btn.textContent = 'Memproses...';

            try {
                await api.importLeads(formData);
                App.showToast('Import batch berhasil diproses!');
                App.navigate('/lead-imports');
            } catch (err) {
                App.showToast(err.message, 'error');
                btn.disabled = false;
                btn.textContent = 'Mulai Proses Import';
            }
        },

        // ── Show Detail ──
        async show(container, id) {
            const res = await api.get('import-batches', id);
            const b = res.data || res;

            container.innerHTML = `
                <div class="max-w-2xl mx-auto space-y-6">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <button onclick="App.navigate('/lead-imports')" class="p-2 rounded-xl border border-gray-200 text-gray-500 hover:bg-gray-50">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                            </button>
                            <div>
                                <h2 class="text-xl font-extrabold text-gray-900">${b.original_filename || 'Batch #' + b.id}</h2>
                                <p class="text-xs text-gray-400 mt-0.5">Detail hasil import massal</p>
                            </div>
                        </div>
                    </div>

                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-4 text-xs">
                        <div class="grid grid-cols-3 gap-3 text-center border-b border-gray-100 pb-4">
                            <div class="bg-gray-50 p-3 rounded-xl">
                                <p class="text-[10px] text-gray-400 uppercase font-semibold">Total Baris</p>
                                <p class="text-xl font-extrabold text-gray-900 mt-0.5">${b.total_rows || 0}</p>
                            </div>
                            <div class="bg-emerald-50 p-3 rounded-xl">
                                <p class="text-[10px] text-emerald-600 uppercase font-semibold">Berhasil</p>
                                <p class="text-xl font-extrabold text-emerald-700 mt-0.5">${b.success_rows || 0}</p>
                            </div>
                            <div class="bg-red-50 p-3 rounded-xl">
                                <p class="text-[10px] text-red-600 uppercase font-semibold">Gagal</p>
                                <p class="text-xl font-extrabold text-red-700 mt-0.5">${b.failed_rows || 0}</p>
                            </div>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div><span class="text-gray-400 block text-[10px]">Status:</span> ${App.statusBadge(b.status)}</div>
                            <div><span class="text-gray-400 block text-[10px]">Pengunggah:</span> <strong class="text-gray-900">${b.user?.name || 'Admin'}</strong></div>
                            <div><span class="text-gray-400 block text-[10px]">Waktu Unggah:</span> <span class="text-gray-900">${App.formatDateTime(b.created_at)}</span></div>
                        </div>
                    </div>
                </div>
            `;
        }
    };
})();
