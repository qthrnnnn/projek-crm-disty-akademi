/**
 * Products Page Module
 * Training Program & BNSP Certification Catalog
 */
(function() {
    App.pages.products = {
        state: { page: 1, search: '', category: '', product_type: '' },

        async index(container) {
            this.container = container;
            await this.loadList();
        },

        async loadList() {
            const params = { page: this.state.page, per_page: 20 };
            if (this.state.search) params.search = this.state.search;
            if (this.state.category) params.category = this.state.category;
            if (this.state.product_type) params.product_type = this.state.product_type;

            const res = await api.list('products', params);
            const items = res.data || [];
            this.renderList(items, res);
        },

        renderList(items, meta) {
            const user = App.state.user || {};
            const isAdmin = user.role === 'admin';

            const typeLabel = (p) => {
                if (p.product_type === 'certification') return 'Uji Sertifikasi BNSP';
                if (p.product_type === 'bundle') return 'Paket Bundling';
                return 'Pelatihan / Kelas';
            };

            const typeBadgeClass = (p) => {
                if (p.product_type === 'certification') return 'bg-red-50 text-red-700 border-red-200';
                if (p.product_type === 'bundle') return 'bg-amber-50 text-amber-700 border-amber-200';
                return 'bg-blue-50 text-blue-700 border-blue-200';
            };

            const rows = items.map(p => `
                <tr class="hover:bg-blue-50/20 transition-colors">
                    <td class="py-3.5 px-4">
                        <div class="font-semibold text-gray-900 text-sm">${p.name || '-'}</div>
                        <div class="text-[11px] text-gray-400 font-mono mt-0.5">${p.code || '-'}</div>
                        ${p.description ? `<div class="text-[11px] text-gray-500 line-clamp-1 mt-0.5">${p.description}</div>` : ''}
                    </td>

                    <td class="py-3.5 px-4 whitespace-nowrap">
                        <span class="inline-flex items-center px-2 py-0.5 text-[10.5px] font-semibold ${typeBadgeClass(p)} border">
                            ${typeLabel(p)}
                        </span>
                    </td>

                    <td class="py-3.5 px-4 whitespace-nowrap">
                        <span class="inline-flex items-center px-2 py-0.5 text-[10.5px] font-semibold border ${p.category === 'Sertifikasi BNSP' ? 'bg-red-50 text-red-700 border-red-200' : 'bg-blue-50 text-blue-700 border-blue-200'}">
                            ${p.category || 'Pelatihan / Bootcamp'}
                        </span>
                    </td>

                    <td class="py-3.5 px-4 whitespace-nowrap">
                        ${Number(p.price) === 0 ? `
                            <span class="inline-flex items-center px-2 py-0.5 text-[10.5px] font-bold bg-slate-900 text-white">Gratis</span>
                        ` : `
                            <span class="inline-flex items-center px-2 py-0.5 text-[10.5px] font-bold bg-blue-900 text-white">Berbayar</span>
                        `}
                    </td>

                    <td class="py-3.5 px-4 font-semibold text-gray-900 whitespace-nowrap">
                        ${Number(p.price) === 0 ? '<span class="text-emerald-700 font-bold">Gratis (Rp 0)</span>' : `<span>${App.formatRupiah(p.price)}</span>`}
                    </td>

                    <td class="py-3.5 px-4 text-gray-700 whitespace-nowrap text-xs">
                        ${p.duration_hours ? p.duration_hours + ' Jam' : '-'}
                    </td>

                    <td class="py-3.5 px-4 whitespace-nowrap">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10.5px] font-medium border ${p.is_active ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-gray-100 text-gray-500 border-gray-200'}">
                            ${p.is_active ? 'Aktif' : 'Non-aktif'}
                        </span>
                    </td>

                    ${isAdmin ? `
                        <td class="py-3.5 px-4 text-right whitespace-nowrap">
                            <div class="inline-flex items-center gap-1">
                                <a href="/products/${p.id}/edit" onclick="event.preventDefault(); App.navigate('/products/${p.id}/edit')" class="p-1.5 text-gray-400 hover:text-blue-600 rounded hover:bg-blue-50" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </a>
                                <button onclick="App.pages.products.deleteProduct(${p.id})" class="p-1.5 text-gray-400 hover:text-red-600 rounded hover:bg-red-50" title="Hapus">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </div>
                        </td>
                    ` : ''}
                </tr>
            `);

            const isCurrentFilter = (cat, type) => {
                return (this.state.category === cat && this.state.product_type === type);
            };

            this.container.innerHTML = `
                <div class="space-y-4">
                    {{-- Header --}}
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div>
                            <h1 class="text-xl font-bold text-gray-900">Katalog Program Pelatihan & Sertifikasi</h1>
                            <p class="text-xs text-gray-500 mt-0.5">Pelatihan, uji sertifikasi BNSP, dan paket bundling dipisahkan</p>
                        </div>
                        ${isAdmin ? `
                            <a href="/products/create" onclick="event.preventDefault(); App.navigate('/products/create')" class="px-4 py-2 text-xs font-semibold text-white bg-gray-900 rounded-full hover:bg-black shadow-sm transition-colors self-start sm:self-auto">
                                + Tambah Program
                            </a>
                        ` : ''}
                    </div>

                    {{-- Filter Kategori & Pencarian --}}
                    <div class="bg-white border border-blue-100/70 p-3.5 shadow-sm space-y-3">
                        <div class="flex flex-wrap items-center gap-1.5 border-b border-gray-100 pb-3 text-xs">
                            <button type="button" onclick="App.pages.products.setFilter('', '')"
                                    class="px-3 py-1.5 rounded-lg font-medium transition-colors ${isCurrentFilter('', '') ? 'bg-slate-900 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'}">
                                Semua Program
                            </button>
                            <button type="button" onclick="App.pages.products.setFilter('Pelatihan / Bootcamp', '')"
                                    class="px-3 py-1.5 rounded-lg font-medium transition-colors ${isCurrentFilter('Pelatihan / Bootcamp', '') ? 'bg-slate-900 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'}">
                                Pelatihan / Bootcamp
                            </button>
                            <button type="button" onclick="App.pages.products.setFilter('Sertifikasi BNSP', '')"
                                    class="px-3 py-1.5 rounded-lg font-medium transition-colors ${isCurrentFilter('Sertifikasi BNSP', '') ? 'bg-slate-900 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'}">
                                Sertifikasi BNSP
                            </button>
                            <button type="button" onclick="App.pages.products.setFilter('', 'training')"
                                    class="px-3 py-1.5 rounded-lg font-medium transition-colors ${isCurrentFilter('', 'training') ? 'bg-slate-900 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'}">
                                Pelatihan
                            </button>
                            <button type="button" onclick="App.pages.products.setFilter('', 'certification')"
                                    class="px-3 py-1.5 rounded-lg font-medium transition-colors ${isCurrentFilter('', 'certification') ? 'bg-slate-900 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'}">
                                Uji Sertifikasi
                            </button>
                            <button type="button" onclick="App.pages.products.setFilter('', 'bundle')"
                                    class="px-3 py-1.5 rounded-lg font-medium transition-colors ${isCurrentFilter('', 'bundle') ? 'bg-slate-900 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200'}">
                                Paket Bundling
                            </button>
                        </div>

                        {{-- Search Input --}}
                        <div class="flex items-center gap-2">
                            <div class="relative flex-1">
                                <input type="text" id="prod-search" value="${this.state.search}"
                                       placeholder="Cari nama program, kode, atau materi..."
                                       class="w-full text-xs border border-gray-200 px-3 py-2 text-gray-800 placeholder-gray-400 focus:outline-none focus:border-blue-500 rounded"
                                       onkeydown="if(event.key==='Enter') App.pages.products.handleSearch()">
                            </div>
                            <button type="button" onclick="App.pages.products.handleSearch()" class="px-5 py-2 text-xs font-semibold text-white bg-slate-900 rounded-lg hover:bg-slate-800 shadow-sm transition-colors">
                                Cari
                            </button>
                            ${(this.state.search || this.state.category || this.state.product_type) ? `
                                <button type="button" onclick="App.pages.products.resetFilter()" class="px-3 py-2 text-xs text-gray-500 hover:text-gray-700 border border-gray-200 rounded-lg">Reset</button>
                            ` : ''}
                        </div>
                    </div>

                    {{-- Table Card --}}
                    <div class="bg-white border border-blue-100/70 overflow-hidden shadow-sm">
                        <div class="overflow-x-auto">
                            <table class="w-full min-w-[620px] text-left text-xs text-gray-600">
                                <thead class="bg-gray-50/70 border-b border-gray-100 text-gray-400 font-semibold uppercase tracking-wider text-[10.5px]">
                                    <tr>
                                        <th class="py-3.5 px-4">KODE & NAMA PROGRAM</th>
                                        <th class="py-3.5 px-4">TIPE LAYANAN</th>
                                        <th class="py-3.5 px-4">KATEGORI</th>
                                        <th class="py-3.5 px-4">TIPE BIAYA</th>
                                        <th class="py-3.5 px-4">HARGA</th>
                                        <th class="py-3.5 px-4">DURASI</th>
                                        <th class="py-3.5 px-4">STATUS</th>
                                        ${isAdmin ? '<th class="py-3.5 px-4 text-right">AKSI</th>' : ''}
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-100">
                                    ${items.length === 0 ? `
                                        <tr>
                                            <td colspan="${isAdmin ? 8 : 7}" class="py-8 text-center text-gray-400 text-xs">
                                                Tidak ada program pelatihan atau sertifikasi ditemukan.
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

        setFilter(category, type) {
            this.state.category = category;
            this.state.product_type = type;
            this.state.page = 1;
            this.loadList();
        },

        resetFilter() {
            this.state.category = '';
            this.state.product_type = '';
            this.state.search = '';
            this.state.page = 1;
            this.loadList();
        },

        handleSearch() {
            const input = document.getElementById('prod-search');
            this.state.search = input ? input.value : '';
            this.state.page = 1;
            this.loadList();
        },

        goToPage(page) {
            this.state.page = page;
            this.loadList();
        },

        async deleteProduct(id) {
            if (!confirm('Hapus program ini dari katalog?')) return;
            try {
                await api.remove('products', id);
                App.showToast('Program berhasil dihapus.');
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
            const res = await api.get('products', id);
            this.renderForm(container, true, res.data || res);
        },

        renderForm(container, isEdit, data = {}) {
            const typeOptions = [
                { value: 'training', label: 'Pelatihan / Kelas Reguler' },
                { value: 'certification', label: 'Uji Sertifikasi BNSP Saja' },
                { value: 'bundle', label: 'Paket Pelatihan + Sertifikasi' },
            ];

            const categoryOptions = [
                { value: 'Pelatihan / Bootcamp', label: 'Pelatihan / Bootcamp' },
                { value: 'Sertifikasi BNSP', label: 'Sertifikasi BNSP' },
                { value: 'Workshop & Seminar', label: 'Workshop & Seminar' },
            ];

            container.innerHTML = `
                <div class="max-w-xl mx-auto space-y-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-extrabold text-gray-900">${isEdit ? 'Edit Program' : 'Tambah Program Baru'}</h2>
                            <p class="text-xs text-gray-400 mt-0.5">Atur harga pokok resmi dan spesifikasi pelatihan</p>
                        </div>
                        <button onclick="App.navigate('/products')" class="text-xs text-gray-500 hover:text-gray-900 font-semibold flex items-center gap-1">
                            &larr; Kembali
                        </button>
                    </div>

                    <form id="prod-form" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            ${App.inputField('Kode Program', 'code', data.code || '', 'text', true, 'Contoh: TR-001')}
                            ${App.inputField('Durasi (Jam)', 'duration_hours', data.duration_hours || '', 'number', false, 'Contoh: 32')}
                        </div>

                        ${App.inputField('Nama Lengkap Program', 'name', data.name || '', 'text', true, 'Contoh: Pelatihan Digital Marketing & BNSP')}

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            ${App.selectField('Tipe Program', 'product_type', typeOptions, data.product_type || 'training', true)}
                            ${App.selectField('Kategori', 'category', categoryOptions, data.category || 'Pelatihan / Bootcamp', true)}
                        </div>

                        ${App.inputField('Harga Pokok Akademi (Rp)', 'price', data.price || 0, 'number', true, '0')}

                        ${App.textareaField('Deskripsi & Silabus Singkat', 'description', data.description || '', false, 'Ringkasan materi pembelajaran...')}

                        <div class="pt-2">
                            <label class="flex items-center gap-2 cursor-pointer text-xs font-semibold text-gray-700">
                                <input type="checkbox" name="is_active" value="1" ${data.is_active !== false ? 'checked' : ''} class="rounded text-orange-500">
                                Program Aktif di Katalog
                            </label>
                        </div>

                        <div class="flex items-center justify-end gap-2 pt-4 border-t border-gray-100">
                            ${App.secondaryBtn('Batal', "App.navigate('/products')")}
                            ${App.primaryBtn(isEdit ? 'Simpan Perubahan' : 'Tambah Program')}
                        </div>
                    </form>
                </div>
            `;

            App.bindForm('prod-form', 'products', '/products', isEdit, data.id);
        }
    };
})();
