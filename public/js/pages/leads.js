/**
 * Leads Page Module
 * Full CRUD, Scope Tabs, 10% Commission Calculator, Pipeline Status Flow, & Activity Log
 */
(function() {
    App.pages.leads = {
        state: {
            page: 1,
            search: '',
            statusFilter: '',
            scopeTab: 'all', // 'all' | 'my' | 'unassigned' | 'lost'
            products: [],
            sources: [],
            users: [],
            reasons: [],
        },

        async index(container) {
            this.container = container;
            await this.loadDependencies();
            await this.loadList();
        },

        async loadDependencies() {
            try {
                const [pRes, sRes, uRes, rRes] = await Promise.all([
                    api.list('products', { per_page: 100 }),
                    api.list('lead-sources', { per_page: 100 }),
                    api.isAdmin() ? api.list('users', { per_page: 100 }) : Promise.resolve({ data: [] }),
                    api.list('rejection-reasons', { per_page: 100 })
                ]);
                this.state.products = pRes.data || [];
                this.state.sources = sRes.data || [];
                this.state.users = uRes.data || [];
                this.state.reasons = rRes.data || [];
            } catch (e) { /* ignore */ }
        },

        async loadList() {
            const params = {
                page: this.state.page,
                per_page: 20,
            };
            if (this.state.search) params.search = this.state.search;
            if (this.state.statusFilter) params.status = this.state.statusFilter;

            const res = await api.list('leads', params);
            let items = res.data || [];
            const user = App.state.user || {};

            // Client-side scope filtering if needed
            if (this.state.scopeTab === 'my' && user.id) {
                items = items.filter(l => l.assigned_to === user.id);
            } else if (this.state.scopeTab === 'unassigned') {
                items = items.filter(l => !l.assigned_to);
            } else if (this.state.scopeTab === 'lost') {
                items = items.filter(l => l.status === 'lost');
            }

            this.renderList(items, res);
        },

        renderList(items, meta) {
            const user = App.state.user || {};
            const isAdmin = user.role === 'admin';

            const scopeTabs = `
                <div class="flex items-center gap-1 p-1 bg-gray-100 rounded-xl mb-4 overflow-x-auto text-xs">
                    <button type="button" onclick="App.pages.leads.setScope('all')"
                            class="px-3.5 py-1.5 rounded-lg font-semibold transition-all shrink-0 ${this.state.scopeTab === 'all' ? 'bg-white text-gray-900 shadow-xs' : 'text-gray-500 hover:text-gray-900'}">
                        Semua Lead
                    </button>
                    ${!isAdmin ? `
                        <button type="button" onclick="App.pages.leads.setScope('my')"
                                class="px-3.5 py-1.5 rounded-lg font-semibold transition-all shrink-0 ${this.state.scopeTab === 'my' ? 'bg-white text-gray-900 shadow-xs' : 'text-gray-500 hover:text-gray-900'}">
                            Lead Saya
                        </button>
                    ` : ''}
                    <button type="button" onclick="App.pages.leads.setScope('unassigned')"
                            class="px-3.5 py-1.5 rounded-lg font-semibold transition-all shrink-0 ${this.state.scopeTab === 'unassigned' ? 'bg-white text-gray-900 shadow-xs' : 'text-gray-500 hover:text-gray-900'}">
                        Belum Ditugaskan (Pool)
                    </button>
                    <button type="button" onclick="App.pages.leads.setScope('lost')"
                            class="px-3.5 py-1.5 rounded-lg font-semibold transition-all shrink-0 ${this.state.scopeTab === 'lost' ? 'bg-white text-gray-900 shadow-xs' : 'text-gray-500 hover:text-gray-900'}">
                        Lost / Siap Re-claim
                    </button>
                </div>
            `;

            const rows = items.map(item => {
                const isOwner = item.assigned_to === user.id;
                const canClaim = !item.assigned_to || item.status === 'lost';
                const cleanPhone = (item.whatsapp || item.phone || '').replace(/[^0-9]/g, '');
                const waLink = cleanPhone ? (cleanPhone.startsWith('0') ? '62' + cleanPhone.substring(1) : cleanPhone) : null;

                const basePrice = item.product ? (item.product.price || 0) : 0;
                const dealPrice = item.deal_price || basePrice;
                const estCommission = Math.round(dealPrice * 0.10);

                return `
                    <tr class="hover:bg-orange-50/20 transition-colors">
                        <td class="py-3.5 px-4">
                            <div class="flex items-center gap-2">
                                <a href="/leads/${item.id}" onclick="event.preventDefault(); App.navigate('/leads/${item.id}')"
                                   class="font-bold text-gray-900 hover:text-orange-600 block text-xs">${item.name || '-'}</a>
                                ${isOwner ? '<span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-orange-100 text-orange-700">PIC Saya</span>' : ''}
                            </div>
                            <p class="text-[10px] text-gray-400 font-mono mt-0.5">${item.lead_code || '-'}${item.company ? ' · ' + item.company : ''}</p>
                        </td>

                        <td class="py-3.5 px-4 text-xs">
                            ${waLink ? `
                                <a href="https://wa.me/${waLink}" target="_blank" class="inline-flex items-center gap-1 text-emerald-600 font-semibold hover:underline">
                                    <span>${item.whatsapp || item.phone}</span>
                                </a>
                            ` : `<span class="text-gray-400">${item.phone || '-'}</span>`}
                            <p class="text-[10px] text-gray-400">${item.email || '-'}</p>
                        </td>

                        <td class="py-3.5 px-4 text-xs">
                            <p class="font-semibold text-gray-800">${item.product ? item.product.name : 'Program Pelatihan'}</p>
                            <p class="text-[10.5px] text-emerald-600 font-bold mt-0.5">${App.formatRupiah(dealPrice)}</p>
                            <span class="text-[9.5px] text-gray-400">Est. Komisi 10%: ${App.formatRupiah(estCommission)}</span>
                        </td>

                        <td class="py-3.5 px-4 text-xs">
                            ${item.assigned_to ? `
                                <div class="flex items-center gap-1.5">
                                    <div class="w-5 h-5 rounded-full bg-orange-100 text-orange-600 flex items-center justify-center text-[9px] font-bold">
                                        ${(item.assigned_user?.name || 'S').charAt(0)}
                                    </div>
                                    <span class="text-gray-800 font-medium">${item.assigned_user?.name || 'Sales PIC'}</span>
                                </div>
                            ` : (canClaim ? `
                                <button type="button" onclick="App.pages.leads.handleClaim(${item.id})"
                                        class="px-2.5 py-1 rounded-lg bg-orange-500 hover:bg-orange-600 text-white font-bold text-[10px] shadow-xs">
                                    + Ambil Lead
                                </button>
                            ` : '<span class="text-gray-400 italic text-[11px]">Belum Ada PIC</span>')}
                        </td>

                        <td class="py-3.5 px-4">
                            ${App.statusBadge(item.status)}
                        </td>

                        <td class="py-3.5 px-4 text-right space-x-1.5 whitespace-nowrap">
                            <a href="/leads/${item.id}" onclick="event.preventDefault(); App.navigate('/leads/${item.id}')"
                               class="p-1.5 rounded-lg text-gray-400 hover:text-gray-700 hover:bg-gray-100 inline-block" title="Detail">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                            </a>
                            ${(isAdmin || isOwner) ? `
                                <a href="/leads/${item.id}/edit" onclick="event.preventDefault(); App.navigate('/leads/${item.id}/edit')"
                                   class="p-1.5 rounded-lg text-gray-400 hover:text-orange-600 hover:bg-orange-50 inline-block" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                </a>
                            ` : ''}
                        </td>
                    </tr>
                `;
            });

            this.container.innerHTML = `
                <div class="space-y-4">
                    ${App.pageHeader(
                        'Semua Leads & Prospek',
                        'Daftar kontak calon peserta pelatihan & sertifikasi',
                        `
                            <button onclick="App.pages.leads.openImportModal()" class="px-3.5 py-2 rounded-xl border border-gray-200 text-gray-700 font-semibold text-xs hover:bg-gray-50 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                                Import CSV
                            </button>
                            <button onclick="App.navigate('/leads/create')" class="px-4 py-2 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-semibold text-xs shadow-sm flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                                Tambah Lead
                            </button>
                        `
                    )}

                    ${scopeTabs}

                    <!-- Search & Filter Bar -->
                    <div class="flex flex-col sm:flex-row items-center gap-2 bg-white p-3 rounded-2xl border border-gray-100 shadow-2xs">
                        <div class="flex-1 w-full relative">
                            <input type="text" id="lead-search" value="${this.state.search}" placeholder="Cari nama, email, no HP, kode..."
                                   class="w-full pl-9 pr-3 py-1.5 rounded-xl border border-gray-200 text-xs focus:ring-1 focus:ring-orange-500 outline-none"
                                   onkeydown="if(event.key==='Enter') App.pages.leads.handleSearch()">
                            <svg class="w-4 h-4 text-gray-400 absolute left-2.5 top-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        </div>
                        <select id="lead-status-filter" onchange="App.pages.leads.handleStatusFilter(this.value)"
                                class="w-full sm:w-44 px-3 py-1.5 rounded-xl border border-gray-200 text-xs bg-white outline-none">
                            <option value="">Semua Status</option>
                            <option value="new" ${this.state.statusFilter === 'new' ? 'selected' : ''}>Baru</option>
                            <option value="contacted" ${this.state.statusFilter === 'contacted' ? 'selected' : ''}>Dikontak</option>
                            <option value="active" ${this.state.statusFilter === 'active' ? 'selected' : ''}>Aktif</option>
                            <option value="negotiation" ${this.state.statusFilter === 'negotiation' ? 'selected' : ''}>Negosiasi</option>
                            <option value="won" ${this.state.statusFilter === 'won' ? 'selected' : ''}>Berhasil (Won)</option>
                            <option value="lost" ${this.state.statusFilter === 'lost' ? 'selected' : ''}>Ditolak (Lost)</option>
                        </select>
                        <button onclick="App.pages.leads.handleSearch()" class="px-4 py-1.5 rounded-xl bg-gray-900 text-white font-semibold text-xs hover:bg-black">
                            Cari
                        </button>
                    </div>

                    <!-- Table -->
                    ${App.tableShell(
                        ['Calon Pelanggan / Lead', 'Kontak', 'Program & Deal', 'Sales PIC', 'Status', 'Aksi'],
                        rows,
                        'Belum ada data lead pada kriteria ini.'
                    )}

                    ${App.pagination(meta)}
                </div>
            `;
        },

        setScope(tab) {
            this.state.scopeTab = tab;
            this.state.page = 1;
            this.loadList();
        },

        handleSearch() {
            const input = document.getElementById('lead-search');
            this.state.search = input ? input.value : '';
            this.state.page = 1;
            this.loadList();
        },

        handleStatusFilter(val) {
            this.state.statusFilter = val;
            this.state.page = 1;
            this.loadList();
        },

        goToPage(page) {
            this.state.page = page;
            this.loadList();
        },

        async handleClaim(leadId) {
            try {
                await api.claimLead(leadId);
                App.showToast('Lead berhasil Anda ambil alih!');
                await this.loadList();
            } catch (err) {
                App.showToast(err.message, 'error');
            }
        },

        // ── Create & Edit Form with 10% Math ──
        async create(container) {
            await this.loadDependencies();
            this.renderForm(container, false);
        },

        async edit(container, id) {
            await this.loadDependencies();
            const res = await api.get('leads', id);
            this.renderForm(container, true, res.data || res);
        },

        renderForm(container, isEdit, data = {}) {
            const user = App.state.user || {};
            const isAdmin = user.role === 'admin';

            const productOptions = this.state.products.map(p => ({
                value: p.id,
                label: `${p.name} - ${App.formatRupiah(p.price)}`,
                price: p.price
            }));

            const sourceOptions = this.state.sources.map(s => ({
                value: s.id,
                label: s.name
            }));

            const userOptions = this.state.users.map(u => ({
                value: u.id,
                label: u.name + (u.role === 'sales' ? ' (Sales)' : ' (Admin)')
            }));

            container.innerHTML = `
                <div class="max-w-3xl mx-auto space-y-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-lg font-extrabold text-gray-900">${isEdit ? 'Edit Data Lead' : 'Tambah Lead Baru'}</h2>
                            <p class="text-xs text-gray-400 mt-0.5">${isEdit ? 'Perbarui informasi prospek & harga penawaran' : 'Catat data kontak calon peserta pelatihan & sertifikasi'}</p>
                        </div>
                        <button onclick="App.navigate('/leads')" class="text-xs text-gray-500 hover:text-gray-900 font-semibold flex items-center gap-1">
                            &larr; Kembali
                        </button>
                    </div>

                    <form id="lead-form" class="bg-white rounded-2xl border border-gray-100 shadow-sm p-6 space-y-5">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                            ${App.inputField('Nama Lengkap', 'name', data.name || '', 'text', true, 'Contoh: Ahmad Fauzi')}
                            ${App.inputField('Email', 'email', data.email || '', 'email', false, 'ahmad@example.com')}
                            ${App.inputField('Nomor WhatsApp', 'whatsapp', data.whatsapp || '', 'text', true, '08123456789')}
                            ${App.inputField('Nomor Telepon Alternatif', 'phone', data.phone || '', 'text', false, '08123456789')}
                            ${App.inputField('Instansi / Perusahaan', 'company', data.company || '', 'text', false, 'Contoh: Universitas Brawijaya')}
                            ${App.inputField('Jabatan / Posisi', 'job_title', data.job_title || '', 'text', false, 'Contoh: Dosen / IT Support')}
                            ${App.inputField('Kota', 'city', data.city || '', 'text', false, 'Surabaya')}
                            ${App.inputField('Provinsi', 'province', data.province || '', 'text', false, 'Jawa Timur')}
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-3 border-t border-gray-100">
                            <div>
                                <label class="block text-[11px] font-semibold text-gray-700 mb-1">Program yang Diminati</label>
                                <select name="product_id" id="form-product-id" onchange="App.pages.leads.recalcCommission()"
                                        class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs bg-white outline-none">
                                    <option value="">-- Pilih Program --</option>
                                    ${productOptions.map(o => `<option value="${o.value}" data-price="${o.price}" ${data.product_id == o.value ? 'selected' : ''}>${o.label}</option>`).join('')}
                                </select>
                            </div>

                            ${App.selectField('Sumber Prospek (Lead Source)', 'lead_source_id', sourceOptions, data.lead_source_id || '')}

                            ${isAdmin ? App.selectField('Sales Penanggung Jawab', 'assigned_to', userOptions, data.assigned_to || user.id) : `
                                <input type="hidden" name="assigned_to" value="${user.id}">
                            `}

                            <div>
                                <label class="block text-[11px] font-semibold text-gray-700 mb-1">Status Prospek</label>
                                <select name="status" class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs bg-white outline-none">
                                    <option value="new" ${data.status === 'new' ? 'selected' : ''}>Baru</option>
                                    <option value="contacted" ${data.status === 'contacted' ? 'selected' : ''}>Dikontak</option>
                                    <option value="active" ${data.status === 'active' ? 'selected' : ''}>Aktif</option>
                                    <option value="negotiation" ${data.status === 'negotiation' ? 'selected' : ''}>Negosiasi</option>
                                    <option value="won" ${data.status === 'won' ? 'selected' : ''}>Berhasil (Won)</option>
                                    <option value="lost" ${data.status === 'lost' ? 'selected' : ''}>Ditolak (Lost)</option>
                                </select>
                            </div>
                        </div>

                        <!-- 10% Commission Math Box -->
                        <div class="p-4 bg-orange-50/60 border border-orange-100 rounded-2xl space-y-3" id="commission-calc-box">
                            <div class="flex items-center justify-between">
                                <span class="text-xs font-bold text-orange-950">Skema Komisi Sales 10%</span>
                                <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-orange-500 text-white">Otomatis</span>
                            </div>

                            <div class="grid grid-cols-3 gap-3 text-xs">
                                <div>
                                    <p class="text-[10px] text-gray-500">Harga Pokok Program</p>
                                    <p class="font-extrabold text-gray-900 mt-0.5" id="calc-base-price">Rp 0</p>
                                </div>
                                <div>
                                    <p class="text-[10px] text-gray-500">Hak Komisi 10%</p>
                                    <p class="font-extrabold text-orange-600 mt-0.5" id="calc-comm-amount">Rp 0</p>
                                </div>
                                <div>
                                    <p class="text-[10px] text-gray-500">Saran Harga Penawaran</p>
                                    <p class="font-extrabold text-emerald-600 mt-0.5" id="calc-offer-price">Rp 0</p>
                                </div>
                            </div>

                            <div>
                                <label class="block text-[11px] font-semibold text-gray-700 mb-1">Nilai Deal / Penawaran ke Peserta (Rp)</label>
                                <input type="number" name="deal_price" id="form-deal-price" value="${data.deal_price || 0}"
                                       class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs font-bold outline-none"
                                       oninput="App.pages.leads.checkDealAlert()">
                                <p class="text-[10.5px] text-gray-400 mt-1" id="calc-deal-alert">Masukkan nilai total yang ditagihkan ke pelanggan.</p>
                            </div>
                        </div>

                        ${App.textareaField('Catatan / Kebutuhan Khusus Prospek', 'notes', data.notes || '', false, 'Tulis catatan negosiasi atau kendala calon peserta...')}

                        <div class="flex items-center justify-end gap-2 pt-4 border-t border-gray-100">
                            ${App.secondaryBtn('Batal', "App.navigate('/leads')")}
                            ${App.primaryBtn(isEdit ? 'Simpan Perubahan' : 'Simpan Lead')}
                        </div>
                    </form>
                </div>
            `;

            this.recalcCommission();
            App.bindForm('lead-form', 'leads', '/leads', isEdit, data.id);
        },

        recalcCommission() {
            const select = document.getElementById('form-product-id');
            if (!select) return;
            const opt = select.options[select.selectedIndex];
            const basePrice = opt ? parseInt(opt.dataset.price || '0') : 0;
            const comm = Math.round(basePrice * 0.10);
            const offer = basePrice + comm;

            const baseEl = document.getElementById('calc-base-price');
            const commEl = document.getElementById('calc-comm-amount');
            const offerEl = document.getElementById('calc-offer-price');
            const dealInput = document.getElementById('form-deal-price');

            if (baseEl) baseEl.textContent = App.formatRupiah(basePrice);
            if (commEl) commEl.textContent = App.formatRupiah(comm);
            if (offerEl) offerEl.textContent = App.formatRupiah(offer);

            if (dealInput && (!dealInput.value || dealInput.value === '0')) {
                dealInput.value = offer > 0 ? offer : basePrice;
            }
            this.checkDealAlert();
        },

        checkDealAlert() {
            const dealInput = document.getElementById('form-deal-price');
            const select = document.getElementById('form-product-id');
            const alertEl = document.getElementById('calc-deal-alert');
            if (!dealInput || !select || !alertEl) return;

            const deal = parseInt(dealInput.value || '0');
            const opt = select.options[select.selectedIndex];
            const base = opt ? parseInt(opt.dataset.price || '0') : 0;
            const offer = base + Math.round(base * 0.10);

            if (base > 0) {
                if (deal >= offer) {
                    alertEl.className = 'text-[10.5px] text-emerald-600 font-medium mt-1';
                    alertEl.textContent = '✓ Penawaran optimal: komisi sales 10% penuh terjamin.';
                } else if (deal >= base) {
                    alertEl.className = 'text-[10.5px] text-amber-600 font-medium mt-1';
                    alertEl.textContent = '⚠ Ada potongan negosiasi: komisi disesuaikan selisih deal.';
                } else {
                    alertEl.className = 'text-[10.5px] text-red-600 font-bold mt-1';
                    alertEl.textContent = '✕ Di bawah harga pokok akademi! Memerlukan persetujuan Admin.';
                }
            }
        },

        // ── Show Detail View ──
        async show(container, id) {
            const res = await api.get('leads', id);
            const lead = res.data || res;
            const user = App.state.user || {};
            const isAdmin = user.role === 'admin';
            const isOwner = lead.assigned_to === user.id;

            const stages = [
                { key: 'new', label: 'Baru' },
                { key: 'contacted', label: 'Dikontak' },
                { key: 'active', label: 'Aktif' },
                { key: 'negotiation', label: 'Negosiasi' },
                { key: 'won', label: 'Berhasil' },
                { key: 'lost', label: 'Ditolak' },
            ];

            const cleanPhone = (lead.whatsapp || lead.phone || '').replace(/[^0-9]/g, '');
            const waLink = cleanPhone ? (cleanPhone.startsWith('0') ? '62' + cleanPhone.substring(1) : cleanPhone) : null;

            container.innerHTML = `
                <div class="space-y-6 max-w-5xl mx-auto">
                    <!-- Top Action Bar -->
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <button onclick="App.navigate('/leads')" class="p-2 rounded-xl border border-gray-200 text-gray-500 hover:bg-gray-50">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                            </button>
                            <div>
                                <div class="flex items-center gap-2">
                                    <h2 class="text-xl font-extrabold text-gray-900">${lead.name || '-'}</h2>
                                    ${App.statusBadge(lead.status)}
                                </div>
                                <p class="text-xs text-gray-400 font-mono mt-0.5">${lead.lead_code || '-'} · ${lead.company || 'Perorangan'}</p>
                            </div>
                        </div>

                        <div class="flex items-center gap-2">
                            ${waLink ? `
                                <a href="https://wa.me/${waLink}?text=${encodeURIComponent('Halo Kak ' + lead.name + ', salam kenal dari Disty Akademi.')}"
                                   target="_blank" class="px-3.5 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-xs flex items-center gap-1.5 shadow-sm">
                                    Chat WhatsApp
                                </a>
                            ` : ''}
                            ${(isAdmin || isOwner) ? `
                                <button onclick="App.navigate('/leads/${lead.id}/edit')" class="px-3.5 py-2 rounded-xl border border-gray-200 text-gray-700 font-semibold text-xs hover:bg-gray-50">
                                    Edit Data
                                </button>
                            ` : ''}
                            <button onclick="App.navigate('/payments/create?lead_id=${lead.id}')" class="px-3.5 py-2 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-semibold text-xs shadow-sm">
                                + Input Pembayaran
                            </button>
                        </div>
                    </div>

                    <!-- Pipeline Stage Progress Bar -->
                    <div class="bg-white p-4 rounded-2xl border border-gray-100 shadow-2xs">
                        <p class="text-[10px] font-bold uppercase tracking-wider text-gray-400 mb-2.5">Progres Pipeline Prospek</p>
                        <div class="grid grid-cols-2 sm:grid-cols-6 gap-2">
                            ${stages.map(s => {
                                const isCurrent = lead.status === s.key;
                                return `
                                    <button type="button" onclick="App.pages.leads.changeStatus(${lead.id}, '${s.key}')"
                                            class="py-2 px-3 rounded-xl text-center text-xs font-semibold transition-all ${isCurrent ? 'bg-orange-500 text-white shadow-xs' : 'bg-gray-50 text-gray-600 hover:bg-gray-100'}">
                                        ${s.label}
                                    </button>
                                `;
                            }).join('')}
                        </div>
                    </div>

                    <!-- 2 Columns: Details & Interactions -->
                    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                        <!-- Left: Contact & Deal Info -->
                        <div class="space-y-4">
                            <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-2xs space-y-3.5 text-xs">
                                <h3 class="font-bold text-gray-900 border-b border-gray-100 pb-2">Informasi Kontak</h3>
                                <div><span class="text-gray-400 block text-[10px]">WhatsApp:</span> <strong class="text-gray-900">${lead.whatsapp || '-'}</strong></div>
                                <div><span class="text-gray-400 block text-[10px]">Telepon:</span> <span class="text-gray-900">${lead.phone || '-'}</span></div>
                                <div><span class="text-gray-400 block text-[10px]">Email:</span> <span class="text-gray-900">${lead.email || '-'}</span></div>
                                <div><span class="text-gray-400 block text-[10px]">Lokasi:</span> <span class="text-gray-900">${[lead.city, lead.province].filter(Boolean).join(', ') || '-'}</span></div>
                                <div><span class="text-gray-400 block text-[10px]">Jabatan:</span> <span class="text-gray-900">${lead.job_title || '-'}</span></div>
                            </div>

                            <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-2xs space-y-3 text-xs">
                                <h3 class="font-bold text-gray-900 border-b border-gray-100 pb-2">Program & Nilai Deal</h3>
                                <div><span class="text-gray-400 block text-[10px]">Program Minat:</span> <strong class="text-gray-900">${lead.product?.name || '-'}</strong></div>
                                <div><span class="text-gray-400 block text-[10px]">Nilai Deal Closing:</span> <strong class="text-emerald-600 text-sm">${App.formatRupiah(lead.deal_price)}</strong></div>
                                <div><span class="text-gray-400 block text-[10px]">Komisi Sales 10%:</span> <strong class="text-orange-600">${App.formatRupiah(Math.round((lead.deal_price || 0) * 0.10))}</strong></div>
                                <div><span class="text-gray-400 block text-[10px]">PIC Ditugaskan:</span> <span class="text-gray-900 font-semibold">${lead.assigned_user?.name || 'Belum Ada PIC'}</span></div>
                            </div>
                        </div>

                        <!-- Right: Activity Log & History -->
                        <div class="lg:col-span-2 space-y-4">
                            <!-- Quick Log Form -->
                            <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-2xs space-y-3">
                                <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400">Catat Interaksi Cepat</h3>
                                <form id="quick-activity-form" onsubmit="App.pages.leads.handleSaveActivity(event, ${lead.id})" class="space-y-3">
                                    <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                                        <label class="p-2 rounded-xl border border-gray-200 text-center text-xs font-semibold cursor-pointer has-checked:bg-orange-500 has-checked:text-white has-checked:border-orange-500">
                                            <input type="radio" name="type" value="whatsapp" checked class="hidden"> WhatsApp
                                        </label>
                                        <label class="p-2 rounded-xl border border-gray-200 text-center text-xs font-semibold cursor-pointer has-checked:bg-orange-500 has-checked:text-white has-checked:border-orange-500">
                                            <input type="radio" name="type" value="call" class="hidden"> Panggilan
                                        </label>
                                        <label class="p-2 rounded-xl border border-gray-200 text-center text-xs font-semibold cursor-pointer has-checked:bg-orange-500 has-checked:text-white has-checked:border-orange-500">
                                            <input type="radio" name="type" value="meeting" class="hidden"> Meeting
                                        </label>
                                        <label class="p-2 rounded-xl border border-gray-200 text-center text-xs font-semibold cursor-pointer has-checked:bg-orange-500 has-checked:text-white has-checked:border-orange-500">
                                            <input type="radio" name="type" value="note" class="hidden"> Catatan
                                        </label>
                                    </div>
                                    <input type="text" name="subject" required placeholder="Subjek aktivitas (misal: Follow-up penawaran via WA)"
                                           class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs outline-none">
                                    <textarea name="description" rows="2" placeholder="Detail respon atau hasil diskusi..."
                                              class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs outline-none resize-none"></textarea>
                                    <button type="submit" class="px-4 py-2 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-bold text-xs shadow-xs">
                                        Simpan Aktivitas
                                    </button>
                                </form>
                            </div>

                            <!-- Timeline List -->
                            <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-2xs space-y-3">
                                <h3 class="text-xs font-bold uppercase tracking-wider text-gray-400">Riwayat Timeline Aktivitas</h3>
                                <div id="lead-timeline" class="space-y-3 divide-y divide-gray-50">
                                    ${(lead.activities || []).length === 0 ? '<p class="text-xs text-gray-400 py-4 text-center">Belum ada riwayat aktivitas untuk lead ini.</p>' : (lead.activities || []).map(act => `
                                        <div class="pt-3 flex items-start gap-3">
                                            <div class="w-6 h-6 rounded-full bg-orange-100 text-orange-600 flex items-center justify-center text-[10px] font-bold shrink-0">
                                                ${act.type?.charAt(0).toUpperCase() || 'A'}
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-xs font-bold text-gray-900">${act.subject || act.type}</p>
                                                ${act.description ? `<p class="text-xs text-gray-600 mt-0.5">${act.description}</p>` : ''}
                                                <p class="text-[10px] text-gray-400 mt-1">${App.formatDateTime(act.created_at)}</p>
                                            </div>
                                        </div>
                                    `).join('')}
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            `;
        },

        async changeStatus(leadId, newStatus) {
            try {
                await api.updateLeadStatus(leadId, { status: newStatus });
                App.showToast(`Status berhasil diubah menjadi ${newStatus}`);
                await this.show(this.container, leadId);
            } catch (err) {
                App.showToast(err.message, 'error');
            }
        },

        async handleSaveActivity(e, leadId) {
            e.preventDefault();
            const form = e.target;
            const data = {
                type: form.type.value,
                subject: form.subject.value,
                description: form.description.value,
            };

            try {
                await api.addLeadActivity(leadId, data);
                App.showToast('Aktivitas berhasil dicatat!');
                await this.show(this.container, leadId);
            } catch (err) {
                App.showToast(err.message, 'error');
            }
        },

        // ── Import Modal ──
        openImportModal() {
            App.openModal(`
                <div class="p-6 space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                        <h3 class="font-bold text-gray-900 text-sm">Import Data Leads (CSV/Excel)</h3>
                        <button onclick="App.closeModal()" class="text-gray-400 hover:text-gray-600">&times;</button>
                    </div>

                    <form id="lead-import-form" onsubmit="App.pages.leads.handleImport(event)" class="space-y-4 text-xs">
                        <div>
                            <label class="block font-semibold text-gray-700 mb-1">Pilih File CSV</label>
                            <input type="file" name="file" accept=".csv,.xlsx,.xls" required
                                   class="w-full px-3 py-2 border border-gray-200 rounded-xl">
                            <p class="text-[10px] text-gray-400 mt-1">Format kolom: Nama, Email, WhatsApp, Instansi, dsb.</p>
                        </div>

                        <div class="flex justify-between items-center pt-3 border-t border-gray-100">
                            <button type="button" onclick="api.downloadLeadTemplate()" class="text-[11px] text-orange-600 hover:underline font-semibold">
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
                await api.importLeads(formData);
                App.showToast('Data leads berhasil diimport!');
                App.closeModal();
                await this.loadList();
            } catch (err) {
                App.showToast(err.message, 'error');
            }
        }
    };
})();
