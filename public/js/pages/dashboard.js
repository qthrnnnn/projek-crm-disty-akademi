/**
 * Dashboard Page Module
 * 1:1 Replica of Blade Dashboard with interactive SVG Donut, Calendar, & Period Filters
 */
(function() {
    const STAGES = [
        { key: 'new', label: 'Baru', dot: 'bg-slate-400', hex: '#94a3b8' },
        { key: 'contacted', label: 'Dikontak', dot: 'bg-blue-400', hex: '#60a5fa' },
        { key: 'active', label: 'Aktif', dot: 'bg-cyan-500', hex: '#06b6d4' },
        { key: 'negotiation', label: 'Negosiasi', dot: 'bg-amber-400', hex: '#fbbf24' },
        { key: 'won', label: 'Berhasil', dot: 'bg-emerald-500', hex: '#10b981' },
        { key: 'lost', label: 'Ditolak', dot: 'bg-red-400', hex: '#f87171' },
        { key: 'inactive', label: 'Tidak Aktif', dot: 'bg-gray-300', hex: '#cbd5e1' },
    ];

    const MONTH_NAMES = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
    const MONTH_NAMES_SHORT = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
    const WEEKDAYS = ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'];

    App.pages.dashboard = {
        state: {
            selectedDate: new Date().toISOString().split('T')[0],
            todayIso: new Date().toISOString().split('T')[0],
            calendarYear: new Date().getFullYear(),
            calendarMonth: new Date().getMonth(),
            filterMode: 'today', // 'today' | 'month' | 'all'
            selectedFilterYear: new Date().getFullYear(),
            selectedFilterMonth: new Date().getMonth() + 1,
            activeStage: 'all',
            hoverStage: null,
            data: null,
        },

        async index(container) {
            this.container = container;
            await this.loadData();
        },

        async loadData() {
            try {
                const res = await api.request(`/dashboard?date=${this.state.selectedDate}`);
                this.state.data = res.data || {};
                this.render();
            } catch (err) {
                this.container.innerHTML = `
                    <div class="p-6 rounded-2xl bg-red-50 border border-red-200 text-red-700 space-y-2">
                        <h3 class="font-bold text-sm">Gagal memuat Dashboard:</h3>
                        <p class="text-xs font-mono">${err.message}</p>
                        <button onclick="App.pages.dashboard.loadData()" class="mt-3 px-4 py-2 text-xs font-semibold rounded-xl bg-red-600 text-white hover:bg-red-700">Coba Lagi</button>
                    </div>
                `;
            }
        },

        render() {
            const d = this.state.data || {};
            const user = App.state.user || {};
            const isAdmin = user.role === 'admin';
            const isSales = user.role === 'sales';

            // Top KPI values
            const todayIn = d.todayLeadsIn ?? 0;
            const todayAnswered = d.todayLeadsAnswered ?? 0;
            const todayUnanswered = d.todayLeadsUnanswered ?? 0;
            const todayResponded = d.todayLeadsResponded ?? 0;

            this.container.innerHTML = `
                <!-- Header -->
                <div class="mb-6">
                    <h1 class="text-xl font-bold text-gray-900">${isSales ? 'Dashboard Sales' : 'Dashboard Manajemen'}</h1>
                    <p class="text-xs text-gray-400 mt-1">
                        ${isSales ? 'Menampilkan data lead yang ditugaskan kepada Anda & perolehan komisi Anda' : 'Ringkasan performa seluruh tim sales, verifikasi transaksi, & katalog akademi'}
                    </p>
                </div>

                <!-- Row 1: KPI Cards + Calendar -->
                <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,1fr)_280px] gap-3 sm:gap-4 mb-6 items-stretch">
                    <!-- 4 KPI Cards -->
                    <div class="h-full">
                        <div class="grid grid-cols-2 gap-2.5 sm:gap-3 h-full auto-rows-fr">
                            <div class="bg-white border border-gray-100 p-3 sm:p-4 h-full flex flex-col justify-between shadow-xs hover:shadow-sm transition-all rounded-xl">
                                <div>
                                    <p class="text-[11px] sm:text-xs text-gray-400 mb-1">Lead Masuk</p>
                                    <p class="text-2xl sm:text-3xl font-bold text-gray-900">${todayIn}</p>
                                </div>
                                <p class="mt-2 text-[10.5px] sm:text-xs text-gray-400">Total masuk tanggal ini</p>
                            </div>

                            <div class="bg-white border border-gray-100 p-3 sm:p-4 h-full flex flex-col justify-between shadow-xs hover:shadow-sm transition-all rounded-xl">
                                <div>
                                    <p class="text-[11px] sm:text-xs text-gray-400 mb-1">Sudah Dijawab</p>
                                    <p class="text-2xl sm:text-3xl font-bold text-gray-900">${todayAnswered}</p>
                                </div>
                                <p class="mt-2 text-[10.5px] sm:text-xs text-emerald-600 font-medium">Sudah dikontak sales</p>
                            </div>

                            <div class="bg-white border border-gray-100 p-3 sm:p-4 h-full flex flex-col justify-between shadow-xs hover:shadow-sm transition-all rounded-xl">
                                <div>
                                    <p class="text-[11px] sm:text-xs text-gray-400 mb-1">Belum Dijawab</p>
                                    <p class="text-2xl sm:text-3xl font-bold ${todayUnanswered > 0 ? 'text-amber-600' : 'text-gray-900'}">${todayUnanswered}</p>
                                </div>
                                <p class="mt-2 text-[10.5px] sm:text-xs ${todayUnanswered > 0 ? 'text-amber-600 font-medium' : 'text-gray-400'}">Perlu segera dikontak</p>
                            </div>

                            <div class="bg-white border border-gray-100 p-3 sm:p-4 h-full flex flex-col justify-between shadow-xs hover:shadow-sm transition-all rounded-xl">
                                <div>
                                    <p class="text-[11px] sm:text-xs text-gray-400 mb-1">Sudah Merespon</p>
                                    <p class="text-2xl sm:text-3xl font-bold text-gray-900">${todayResponded}</p>
                                </div>
                                <p class="mt-2 text-[10.5px] sm:text-xs text-blue-600 font-medium">Aktif / negosiasi</p>
                            </div>
                        </div>
                    </div>

                    <!-- Calendar Card -->
                    <div class="bg-white border border-gray-100 p-3 sm:p-4 h-full shadow-xs rounded-xl flex flex-col justify-between" id="dashboard-calendar-card">
                        ${this.renderCalendar()}
                    </div>
                </div>

                <!-- Row 2: Lead Status Distribution (Donut) + Lead Period Selector -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-5 items-stretch">
                    <!-- Donut Card (2 cols) -->
                    <div class="lg:col-span-2 bg-white border border-gray-100 p-5 flex flex-col justify-between shadow-xs rounded-xl" id="dashboard-donut-card">
                        ${this.renderDonutSection()}
                    </div>

                    <!-- Period Filter Card (1 col) -->
                    <div class="bg-white border border-gray-100 p-5 flex flex-col justify-between shadow-xs rounded-xl" id="dashboard-period-card">
                        ${this.renderPeriodSection()}
                    </div>
                </div>

                <!-- Row 3: Operational Modules (Admin) or Affiliate Actions (Sales) -->
                ${this.renderOperationalModules(isAdmin)}

                <!-- Row 4: 6-Month Lead Trend Chart -->
                ${this.renderSixMonthChart(d.sixMonthLeads || [])}

                <!-- Row 5: Recent Activities + Upcoming Follow-ups -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-5">
                    <!-- Activities -->
                    <div class="bg-white border border-gray-100 p-5 shadow-xs rounded-xl">
                        <div class="flex items-center justify-between mb-4">
                            <p class="text-sm font-semibold text-gray-900">Aktivitas Terbaru</p>
                        </div>
                        ${this.renderActivities(d.recentActivities || [])}
                    </div>

                    <!-- Follow-ups -->
                    <div class="bg-white border border-gray-100 p-5 shadow-xs rounded-xl">
                        <div class="flex items-center justify-between mb-4">
                            <p class="text-sm font-semibold text-gray-900">Follow-up Mendatang</p>
                        </div>
                        ${this.renderFollowUps(d.upcomingFollowUps || [])}
                    </div>
                </div>
            `;

            this.bindEvents();
        },

        // ── Calendar Rendering & Logic ──
        renderCalendar() {
            const { calendarYear, calendarMonth, selectedDate, todayIso } = this.state;
            const monthName = MONTH_NAMES[calendarMonth];
            const isDifferentFromToday = selectedDate !== todayIso;

            const firstDay = new Date(calendarYear, calendarMonth, 1).getDay();
            const leadingDays = (firstDay + 6) % 7;
            const daysInMonth = new Date(calendarYear, calendarMonth + 1, 0).getDate();

            let cells = '';
            for (let i = 0; i < leadingDays; i++) {
                cells += '<span></span>';
            }
            for (let day = 1; day <= daysInMonth; day++) {
                const dayStr = String(day).padStart(2, '0');
                const mStr = String(calendarMonth + 1).padStart(2, '0');
                const iso = `${calendarYear}-${mStr}-${dayStr}`;
                const isSel = iso === selectedDate;
                const isTod = iso === todayIso;

                let cls = 'w-7 h-7 sm:w-8 sm:h-8 rounded-full text-[11px] sm:text-xs transition-all flex items-center justify-center mx-auto ';
                if (isSel) {
                    cls += 'bg-orange-500 text-white font-bold shadow-xs';
                } else if (isTod) {
                    cls += 'border border-orange-500 text-orange-600 font-semibold hover:bg-orange-50';
                } else {
                    cls += 'text-gray-700 hover:bg-gray-100';
                }

                cells += `
                    <span>
                        <button type="button" onclick="App.pages.dashboard.selectDay('${iso}')" class="${cls}">
                            ${day}
                        </button>
                    </span>
                `;
            }

            return `
                <div class="flex items-center justify-between mb-2.5">
                    <button type="button" onclick="App.pages.dashboard.prevCalendarMonth()" class="w-8 h-8 rounded-lg text-gray-400 hover:bg-orange-50 hover:text-orange-600 transition-colors flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    <div class="flex items-center gap-1.5">
                        <p class="text-xs sm:text-sm font-semibold text-gray-900">${monthName} ${calendarYear}</p>
                        ${isDifferentFromToday ? `
                            <button type="button" onclick="App.pages.dashboard.resetToToday()" class="text-[10px] text-orange-600 font-bold hover:underline bg-orange-50 px-1.5 py-0.5 rounded transition-all">Hari Ini</button>
                        ` : ''}
                    </div>
                    <button type="button" onclick="App.pages.dashboard.nextCalendarMonth()" class="w-8 h-8 rounded-lg text-gray-400 hover:bg-orange-50 hover:text-orange-600 transition-colors flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </button>
                </div>
                <div class="grid grid-cols-7 gap-1 text-center">
                    ${WEEKDAYS.map(w => `<span class="text-[10px] font-medium text-gray-400 py-1">${w}</span>`).join('')}
                    ${cells}
                </div>
            `;
        },

        prevCalendarMonth() {
            if (this.state.calendarMonth === 0) {
                this.state.calendarMonth = 11;
                this.state.calendarYear--;
            } else {
                this.state.calendarMonth--;
            }
            const el = document.getElementById('dashboard-calendar-card');
            if (el) el.innerHTML = this.renderCalendar();
        },

        nextCalendarMonth() {
            if (this.state.calendarMonth === 11) {
                this.state.calendarMonth = 0;
                this.state.calendarYear++;
            } else {
                this.state.calendarMonth++;
            }
            const el = document.getElementById('dashboard-calendar-card');
            if (el) el.innerHTML = this.renderCalendar();
        },

        async selectDay(iso) {
            this.state.selectedDate = iso;
            const [y, m] = iso.split('-');
            this.state.calendarYear = parseInt(y);
            this.state.calendarMonth = parseInt(m) - 1;
            await this.loadData();
        },

        async resetToToday() {
            await this.selectDay(this.state.todayIso);
        },

        // ── Donut Chart & Lead Distribution ──
        getPeriodLeads() {
            const d = this.state.data || {};
            const allLeads = d.pipelineLeadsData || (d.data && d.data.pipelineLeadsData) || [];
            const { filterMode, selectedDate, selectedFilterYear, selectedFilterMonth } = this.state;

            return allLeads.filter(lead => {
                if (filterMode === 'today') {
                    return lead.date_iso === selectedDate;
                } else if (filterMode === 'month') {
                    return Number(lead.year) === Number(selectedFilterYear) && Number(lead.month) === Number(selectedFilterMonth);
                }
                return true; // 'all' -> Semua data tanpa filter tanggal
            });
        },

        getFilteredLeads() {
            const leads = this.getPeriodLeads();
            const { activeStage } = this.state;
            if (activeStage && activeStage !== 'all') {
                return leads.filter(lead => lead.status === activeStage);
            }
            return leads;
        },

        getPeriodLabel() {
            const { filterMode, selectedDate, selectedFilterYear, selectedFilterMonth } = this.state;
            if (filterMode === 'today') {
                const parts = selectedDate.split('-');
                const day = parseInt(parts[2]);
                const mon = MONTH_NAMES_SHORT[parseInt(parts[1]) - 1];
                return `Hari Ini (${String(day).padStart(2, '0')} ${mon} ${parts[0]})`;
            } else if (filterMode === 'month') {
                return `${MONTH_NAMES[selectedFilterMonth - 1]} ${selectedFilterYear}`;
            }
            return 'Sepanjang Waktu (Semua Data)';
        },

        renderDonutSection() {
            const { activeStage, hoverStage } = this.state;
            const periodLeads = this.getPeriodLeads();
            const total = periodLeads.length;

            // Counts per stage based on the full period (always accurate, never wiped out by clicking)
            const stageCounts = {};
            STAGES.forEach(s => {
                stageCounts[s.key] = periodLeads.filter(l => l.status === s.key).length;
            });

            // SVG Donut calculation - stroke-width tetap 20, bentuk bunderan tidak berubah/membesar saat di-hover
            const circumference = 339.292;
            let currentOffset = 0;
            const circles = STAGES.map(s => {
                const cnt = stageCounts[s.key] || 0;
                if (cnt === 0 || total === 0) return '';
                const slice = (cnt / total) * circumference;
                const dashArray = `${slice} ${circumference}`;
                const dashOffset = -currentOffset;
                currentOffset += slice;

                const isHovered = hoverStage === s.key;
                const opacity = hoverStage ? (isHovered ? 1 : 0.45) : 1;

                return `
                    <circle id="donut-slice-${s.key}" cx="80" cy="80" r="54" fill="none"
                            stroke="${s.hex}" stroke-width="20"
                            stroke-dasharray="${dashArray}"
                            stroke-dashoffset="${dashOffset}"
                            style="opacity: ${opacity}; transition: opacity 0.15s ease;"
                            class="cursor-pointer"
                            onclick="App.pages.dashboard.selectStage('${s.key}')"
                            onmouseenter="App.pages.dashboard.hoverStage('${s.key}')"
                            onmouseleave="App.pages.dashboard.hoverStage(null)">
                    </circle>
                `;
            }).join('');

            // Center count & label:
            // Langsung menyesuaikan data secara otomatis (default: total dan 'TOTAL LEAD')
            // Bila di-hover, otomatis menampilkan status yang di-hover saja tanpa ada embel-embel tooltip
            let centerCount = total;
            let centerLabel = 'TOTAL LEAD';
            const displayStage = hoverStage || (activeStage !== 'all' ? activeStage : null);
            if (displayStage) {
                const st = STAGES.find(s => s.key === displayStage);
                if (st) {
                    centerCount = stageCounts[st.key] || 0;
                    centerLabel = st.label.toUpperCase();
                }
            }

            // Won closing conversion rate
            const wonCount = stageCounts['won'] || 0;
            const convRate = total > 0 ? ((wonCount / total) * 100).toFixed(1) : '0.0';

            return `
                <div>
                    <!-- Header -->
                    <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-3">
                        <div>
                            <p class="text-xs font-bold text-gray-800 uppercase tracking-wide">Distribusi Status Lead</p>
                            <p class="text-[11px] text-gray-400 mt-0.5">${this.getPeriodLabel()}</p>
                        </div>
                        <span class="text-xs text-gray-500">Total: <strong class="text-gray-900 font-bold">${total}</strong></span>
                    </div>

                    <!-- Donut + Grid -->
                    <div class="flex flex-col sm:flex-row items-center gap-6 pt-2">
                        <!-- Donut Chart -->
                        <div class="relative shrink-0 mx-auto sm:mx-0">
                            <svg viewBox="0 0 160 160" class="w-44 h-44 sm:w-52 sm:h-52 -rotate-90 transform origin-center">
                                <circle cx="80" cy="80" r="54" fill="none" stroke="#f1f5f9" stroke-width="20" />
                                ${circles}
                            </svg>
                            <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                                <span id="donut-center-count" class="text-2xl sm:text-3xl font-extrabold text-gray-900 leading-none">${centerCount}</span>
                                <span id="donut-center-label" class="text-[10px] text-gray-400 font-semibold tracking-wider uppercase mt-1 leading-none">${centerLabel}</span>
                            </div>
                        </div>

                        <!-- 7 Interactive Status Buttons (Hover Langsung Mengubah Tampilan di Tengah, Tanpa Tooltip/Resize) -->
                        <div class="flex-1 w-full">
                            <div class="grid grid-cols-4 lg:grid-cols-7 gap-2">
                                ${STAGES.map(s => {
                                    const cnt = stageCounts[s.key] || 0;
                                    const isAct = activeStage === s.key;
                                    const isHov = hoverStage === s.key;
                                    return `
                                        <button id="donut-btn-${s.key}" type="button"
                                                onclick="App.pages.dashboard.selectStage('${s.key}')"
                                                onmouseenter="App.pages.dashboard.hoverStage('${s.key}')"
                                                onmouseleave="App.pages.dashboard.hoverStage(null)"
                                                class="text-center p-1.5 transition-colors rounded-lg cursor-pointer ${
                                                    isAct
                                                        ? 'bg-orange-50 border border-orange-300 ring-1 ring-orange-200'
                                                        : (isHov ? 'bg-gray-100' : 'border border-transparent hover:bg-gray-50')
                                                }">
                                            <span class="inline-block w-1.5 h-1.5 rounded-full ${s.dot} mb-1"></span>
                                            <p class="text-base sm:text-lg font-bold text-gray-900 leading-tight">${cnt}</p>
                                            <p class="text-[10px] text-gray-500 mt-0.5 leading-tight truncate">${s.label}</p>
                                        </button>
                                    `;
                                }).join('')}
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Bar -->
                <div class="pt-3 border-t border-gray-100 mt-2 flex items-center justify-between text-xs text-gray-500">
                    <span>Conversion rate: <strong class="text-gray-900 font-bold">${convRate}%</strong></span>
                    ${activeStage !== 'all' ? `
                        <button type="button" onclick="App.pages.dashboard.selectStage('all')" class="text-[11px] text-orange-600 hover:text-orange-700 font-semibold underline">
                            Tampilkan Semua Status
                        </button>
                    ` : ''}
                </div>
            `;
        },

        selectStage(stageKey) {
            this.state.activeStage = this.state.activeStage === stageKey ? 'all' : stageKey;
            this.state.hoverStage = null;
            this.updateDonutAndPeriod();
        },

        hoverStage(stageKey) {
            this.state.hoverStage = stageKey;
            const countEl = document.getElementById('donut-center-count');
            const labelEl = document.getElementById('donut-center-label');
            if (!countEl || !labelEl) return;

            const periodLeads = this.getPeriodLeads();
            const total = periodLeads.length;

            if (stageKey) {
                const st = STAGES.find(s => s.key === stageKey);
                const cnt = periodLeads.filter(l => l.status === stageKey).length;
                countEl.textContent = cnt;
                labelEl.textContent = st ? st.label.toUpperCase() : 'LEAD';
            } else {
                const act = this.state.activeStage;
                if (act && act !== 'all') {
                    const st = STAGES.find(s => s.key === act);
                    const cnt = periodLeads.filter(l => l.status === act).length;
                    countEl.textContent = cnt;
                    labelEl.textContent = st ? st.label.toUpperCase() : 'LEAD';
                } else {
                    countEl.textContent = total;
                    labelEl.textContent = 'TOTAL LEAD';
                }
            }

            // Opacity highlight only - TIDAK MENGUBAH UKURAN / BENTUK LINGKARAN & TOMBOL
            STAGES.forEach(s => {
                const circle = document.getElementById(`donut-slice-${s.key}`);
                const btn = document.getElementById(`donut-btn-${s.key}`);
                const isAct = this.state.activeStage === s.key;
                const isHov = stageKey === s.key;

                if (circle) {
                    circle.style.opacity = stageKey ? (isHov ? '1' : '0.45') : '1';
                }

                if (btn) {
                    if (isAct) {
                        btn.className = 'text-center p-1.5 transition-colors rounded-lg cursor-pointer bg-orange-50 border border-orange-300 ring-1 ring-orange-200';
                    } else if (isHov) {
                        btn.className = 'text-center p-1.5 transition-colors rounded-lg cursor-pointer bg-gray-100 border border-transparent';
                    } else {
                        btn.className = 'text-center p-1.5 transition-colors rounded-lg cursor-pointer border border-transparent hover:bg-gray-50';
                    }
                }
            });
        },

        updateDonutAndPeriod() {
            const donutEl = document.getElementById('dashboard-donut-card');
            const periodEl = document.getElementById('dashboard-period-card');
            if (donutEl) donutEl.innerHTML = this.renderDonutSection();
            if (periodEl) periodEl.innerHTML = this.renderPeriodSection();
        },

        // ── Period Selector & Real-Time Leads ──
        renderPeriodSection() {
            const { filterMode, selectedFilterYear, selectedFilterMonth, activeStage } = this.state;
            const periodLeads = this.getPeriodLeads();
            const periodTotal = periodLeads.length;
            const periodWon = periodLeads.filter(l => l.status === 'won').length;
            const periodConv = periodTotal > 0 ? ((periodWon / periodTotal) * 100).toFixed(1) : '0.0';

            const leads = this.getFilteredLeads();
            const total = leads.length;
            const wonCount = leads.filter(l => l.status === 'won').length;
            const convRate = total > 0 ? ((wonCount / total) * 100).toFixed(1) : 0;

            const activeStageObj = STAGES.find(s => s.key === activeStage);
            const activeStageLabel = activeStageObj ? activeStageObj.label : 'Semua Status';

            return `
                <div>
                    <!-- Header -->
                    <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-3">
                        <div>
                            <p class="text-xs font-bold text-gray-800 uppercase tracking-wide">Pilihan Periode Prospek</p>
                            <p class="text-[11px] text-gray-400 mt-0.5">${this.getPeriodLabel()}</p>
                        </div>
                        ${activeStage !== 'all' ? `
                            <button type="button" onclick="App.pages.dashboard.selectStage('all')" class="text-[11px] text-orange-600 hover:text-orange-700 font-semibold flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                Semua Status
                            </button>
                        ` : ''}
                    </div>

                    <!-- 3 Segmented Tabs -->
                    <div class="grid grid-cols-3 gap-1 p-1 bg-gray-100 rounded-lg mb-2.5">
                        <button type="button" onclick="App.pages.dashboard.setFilterMode('today')"
                                class="py-1.5 px-2 text-center text-xs rounded-md transition-all ${filterMode === 'today' ? 'bg-orange-500 text-white font-bold shadow-xs' : 'text-gray-600 hover:text-gray-900'}">
                            Hari Ini
                        </button>
                        <button type="button" onclick="App.pages.dashboard.setFilterMode('month')"
                                class="py-1.5 px-2 text-center text-xs rounded-md transition-all ${filterMode === 'month' ? 'bg-orange-500 text-white font-bold shadow-xs' : 'text-gray-600 hover:text-gray-900'}">
                            Pilih Bulan
                        </button>
                        <button type="button" onclick="App.pages.dashboard.setFilterMode('all')"
                                class="py-1.5 px-2 text-center text-xs rounded-md transition-all ${filterMode === 'all' ? 'bg-orange-500 text-white font-bold shadow-xs' : 'text-gray-600 hover:text-gray-900'}">
                            Semua Data
                        </button>
                    </div>

                    <!-- Mode controls -->
                    ${filterMode === 'today' ? `
                        <div class="flex items-center justify-between p-2 px-2.5 bg-orange-50/60 border border-orange-100 rounded-lg mb-2.5 text-xs">
                            <span class="text-gray-600 flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-orange-500 animate-pulse"></span>
                                Target Hari Ini:
                            </span>
                            <span class="font-bold text-gray-900">${this.state.selectedDate}</span>
                        </div>
                    ` : ''}

                    ${filterMode === 'month' ? `
                        <div class="flex items-center gap-1.5 p-1 bg-gray-50 border border-gray-200 rounded-lg mb-2.5">
                            <button type="button" onclick="App.pages.dashboard.prevFilterMonth()" class="p-1 rounded hover:bg-gray-200 text-gray-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                            </button>
                            <select onchange="App.pages.dashboard.setFilterMonth(this.value)" class="flex-1 text-xs font-semibold text-gray-800 bg-white border border-gray-300 py-1 px-2 rounded outline-none">
                                ${MONTH_NAMES.map((name, idx) => `<option value="${idx + 1}" ${selectedFilterMonth === idx + 1 ? 'selected' : ''}>${name}</option>`).join('')}
                            </select>
                            <select onchange="App.pages.dashboard.setFilterYear(this.value)" class="w-20 text-xs font-semibold text-gray-800 bg-white border border-gray-300 py-1 px-2 rounded outline-none">
                                ${[2024, 2025, 2026, 2027, 2028, 2029, 2030].map(y => `<option value="${y}" ${selectedFilterYear === y ? 'selected' : ''}>${y}</option>`).join('')}
                            </select>
                            <button type="button" onclick="App.pages.dashboard.nextFilterMonth()" class="p-1 rounded hover:bg-gray-200 text-gray-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </button>
                        </div>
                    ` : ''}

                    ${filterMode === 'all' ? `
                        <div class="flex items-center justify-between p-2 px-2.5 bg-blue-50/60 border border-blue-100 rounded-lg mb-2.5 text-xs">
                            <span class="text-gray-600 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                Akumulasi Historis:
                            </span>
                            <span class="font-bold text-blue-900">Sepanjang Waktu</span>
                        </div>
                    ` : ''}

                    <!-- 3 Stats Summary -->
                    <div class="grid grid-cols-3 gap-2 p-2 bg-gray-50 border border-gray-100 rounded-lg text-center mb-2.5">
                        <div>
                            <p class="text-[10px] text-gray-500 uppercase tracking-wide">Total Lead</p>
                            <p class="text-base sm:text-lg font-bold text-gray-900">${periodTotal}</p>
                        </div>
                        <div>
                            <p class="text-[10px] text-gray-500 uppercase tracking-wide">Closing</p>
                            <p class="text-base sm:text-lg font-bold text-emerald-600">${periodWon}</p>
                        </div>
                        <div>
                            <p class="text-[10px] text-gray-500 uppercase tracking-wide">Konversi</p>
                            <p class="text-base sm:text-lg font-bold text-gray-900">${periodConv}%</p>
                        </div>
                    </div>

                    <!-- Lead List -->
                    <div class="overflow-y-auto max-h-[175px] space-y-2 pr-1 scrollbar-thin">
                        ${leads.length === 0 ? `
                            <div class="py-6 text-center text-gray-400">
                                <svg class="w-7 h-7 mx-auto mb-1.5 opacity-40 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                                <p class="text-xs text-gray-400">Tidak ada prospek pada periode & status ini</p>
                            </div>
                        ` : leads.slice(0, 5).map(lead => `
                            <div class="p-2.5 border border-gray-100 bg-white rounded-lg hover:border-orange-200 transition-all">
                                <div class="flex items-start justify-between gap-2 mb-1">
                                    <div class="min-w-0 flex-1">
                                        <a href="/leads/${lead.id}" onclick="event.preventDefault(); App.navigate('/leads/${lead.id}')" class="text-xs font-bold text-gray-900 hover:text-orange-600 truncate block">${lead.name}</a>
                                        <p class="text-[10px] text-gray-400 truncate">${lead.product || '-'}</p>
                                    </div>
                                    <span class="text-[9px] px-1.5 py-0.5 font-bold uppercase tracking-wider rounded shrink-0 ${this.getStatusPillClass(lead.status)}">${lead.status}</span>
                                </div>
                                <div class="flex items-center justify-between pt-1 text-[11px]">
                                    <span class="text-gray-400 text-[10px]">${lead.phone || '-'}</span>
                                    <div class="flex items-center gap-1.5">
                                        ${lead.wa_link ? `
                                            <a href="${lead.wa_link}" target="_blank" class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-600 hover:bg-emerald-700 text-white transition-colors">
                                                Chat WA
                                            </a>
                                        ` : ''}
                                        <a href="/leads/${lead.id}" onclick="event.preventDefault(); App.navigate('/leads/${lead.id}')" class="px-2 py-0.5 rounded text-[10px] font-semibold bg-gray-100 hover:bg-gray-200 text-gray-700 transition-colors">
                                            Detail
                                        </a>
                                    </div>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                </div>

                <!-- Footer Action Button -->
                <div class="pt-3 border-t border-gray-100 mt-2">
                    <button type="button" onclick="App.navigate('/leads')"
                       class="w-full inline-flex items-center justify-center gap-2 px-3 py-2 text-xs font-semibold rounded-xl bg-orange-500 hover:bg-orange-600 text-white shadow-xs transition-colors">
                        <span>${activeStage === 'all' ? 'Buka Daftar Lead di CRM' : 'Lihat Semua Prospek ' + activeStageLabel}</span>
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                    </button>
                </div>
            `;
        },

        getStatusPillClass(status) {
            switch (status) {
                case 'won': return 'bg-emerald-50 text-emerald-700';
                case 'negotiation': return 'bg-amber-50 text-amber-700';
                case 'active': return 'bg-cyan-50 text-cyan-700';
                case 'contacted': return 'bg-blue-50 text-blue-700';
                case 'new': return 'bg-slate-100 text-slate-700';
                case 'lost': return 'bg-red-50 text-red-700';
                default: return 'bg-gray-100 text-gray-500';
            }
        },

        setFilterMode(mode) {
            this.state.filterMode = mode;
            this.state.activeStage = 'all';
            this.state.hoverStage = null;
            this.updateDonutAndPeriod();
        },

        prevFilterMonth() {
            if (this.state.selectedFilterMonth === 1) {
                this.state.selectedFilterMonth = 12;
                this.state.selectedFilterYear--;
            } else {
                this.state.selectedFilterMonth--;
            }
            this.updateDonutAndPeriod();
        },

        nextFilterMonth() {
            if (this.state.selectedFilterMonth === 12) {
                this.state.selectedFilterMonth = 1;
                this.state.selectedFilterYear++;
            } else {
                this.state.selectedFilterMonth++;
            }
            this.updateDonutAndPeriod();
        },

        setFilterMonth(val) {
            this.state.selectedFilterMonth = parseInt(val);
            this.updateDonutAndPeriod();
        },

        setFilterYear(val) {
            this.state.selectedFilterYear = parseInt(val);
            this.updateDonutAndPeriod();
        },

        // ── Operational Modules (Admin / Sales) ──
        renderOperationalModules(isAdmin) {
            if (isAdmin) {
                const modules = [
                    { line1: 'Pendaftaran', line2: 'Peserta', sub: 'Daftarkan ke jadwal aktif', url: '/registrations' },
                    { line1: 'Jadwal', line2: 'Pelatihan', sub: 'Cek kuota & instruktur', url: '/training-schedules' },
                    { line1: 'Daftar', line2: 'Customer', sub: 'Export CSV & Data Peserta', url: '/customers' },
                    { line1: 'Daftar', line2: 'Instansi', sub: 'Mitra kampus & kantor', url: '/institutions' },
                    { line1: 'Daftar', line2: 'Trainer', sub: 'Instruktur & expert', url: '/trainers' },
                ];
                return `
                    <div class="mb-5">
                        <div class="mb-3">
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Modul Operasional & Pelatihan (Khusus Admin)</p>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-2.5 sm:gap-3">
                            ${modules.map(m => `
                                <a href="${m.url}" onclick="event.preventDefault(); App.navigate('${m.url}')"
                                   class="group bg-white border border-blue-100 p-3 sm:p-4 rounded-xl text-left hover:bg-orange-500 hover:border-orange-500 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 flex flex-col justify-between min-h-[130px] sm:min-h-[150px]">
                                    <div class="flex items-start justify-between text-left">
                                        <p class="text-sm sm:text-base font-bold leading-tight text-gray-900 group-hover:text-white 2xl:whitespace-nowrap">
                                            <span>${m.line1}</span><br class="card-title-br 2xl:hidden"><span class="card-title-space hidden 2xl:inline">&nbsp;</span><span>${m.line2}</span>
                                        </p>
                                        <span class="w-6 h-6 sm:w-7 sm:h-7 rounded-full bg-gray-100 text-gray-500 flex items-center justify-center group-hover:bg-white/20 group-hover:text-white transition-colors shrink-0">
                                            <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 17L17 7m0 0H9m8 0v8"/></svg>
                                        </span>
                                    </div>
                                    <div class="mt-auto">
                                        <p class="text-[11px] sm:text-xs text-gray-400 group-hover:text-white/80 mt-1">${m.sub}</p>
                                    </div>
                                </a>
                            `).join('')}
                        </div>
                    </div>
                `;
            } else {
                const modules = [
                    { line1: 'Input', line2: 'Prospek Baru', sub: 'Kenalan sales / calon deal', url: '/leads/create' },
                    { line1: 'Kirim', line2: 'Bukti Bayar', sub: 'Upload transfer DP / lunas', url: '/payments/create' },
                    { line1: 'Komisi 10%', line2: 'Saya', sub: 'Cek insentif & pencairan', url: '/commissions' },
                    { line1: 'Katalog &', line2: 'Harga Patokan', sub: 'Harga resmi sertifikasi', url: '/products' },
                    { line1: 'Follow-up', line2: 'Prospek', sub: 'Jadwal tindak lanjut', url: '/follow-ups' },
                ];
                return `
                    <div class="mb-5">
                        <div class="flex items-center justify-between mb-3">
                            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Aktivitas Affiliate & Penjualan</p>
                            <span class="text-[11px] text-emerald-600 font-semibold">Komisi 10% dari setiap deal closing</span>
                        </div>
                        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-2.5 sm:gap-3">
                            ${modules.map(m => `
                                <a href="${m.url}" onclick="event.preventDefault(); App.navigate('${m.url}')"
                                   class="group bg-white border border-emerald-100 p-3 sm:p-4 rounded-xl text-left hover:bg-orange-500 hover:border-orange-500 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 flex flex-col justify-between min-h-[130px] sm:min-h-[150px]">
                                    <div class="flex items-start justify-between text-left">
                                        <p class="text-sm sm:text-base font-bold leading-tight text-gray-900 group-hover:text-white 2xl:whitespace-nowrap">
                                            <span>${m.line1}</span><br class="card-title-br 2xl:hidden"><span class="card-title-space hidden 2xl:inline">&nbsp;</span><span>${m.line2}</span>
                                        </p>
                                        <span class="w-6 h-6 sm:w-7 sm:h-7 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center group-hover:bg-white/20 group-hover:text-white transition-colors shrink-0">
                                            <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 17L17 7m0 0H9m8 0v8"/></svg>
                                        </span>
                                    </div>
                                    <div class="mt-auto">
                                        <p class="text-[11px] sm:text-xs text-gray-400 group-hover:text-white/80 mt-1">${m.sub}</p>
                                    </div>
                                </a>
                            `).join('')}
                        </div>
                    </div>
                `;
            }
        },

        // ── 6-Month Lead Chart ──
        renderSixMonthChart(sixMonthLeads) {
            // Default 6 months if empty
            if (!sixMonthLeads || sixMonthLeads.length === 0) {
                const now = new Date();
                sixMonthLeads = [];
                for (let i = 5; i >= 0; i--) {
                    const d = new Date(now.getFullYear(), now.getMonth() - i, 1);
                    sixMonthLeads.push({
                        label: `${MONTH_NAMES_SHORT[d.getMonth()]} ${d.getFullYear()}`,
                        total: 0,
                        won: 0,
                    });
                }
            }

            const maxLead = Math.max(1, ...sixMonthLeads.map(m => m.total || 0));
            const currentMonthLabel = sixMonthLeads[sixMonthLeads.length - 1]?.label || 'Nov 2026';

            return `
                <div class="bg-white border border-gray-100 p-5 mb-5 shadow-xs rounded-xl">
                    <div class="flex items-center justify-between mb-5">
                        <p class="text-sm font-semibold text-gray-900">Lead 6 Bulan sampai ${currentMonthLabel}</p>
                        <div class="flex items-center gap-1.5 text-xs text-gray-400">
                            <span class="inline-block w-2.5 h-2.5 bg-emerald-400 rounded-xs"></span>
                            <span>Won</span>
                        </div>
                    </div>
                    <div class="flex items-end gap-2.5 h-44">
                        ${sixMonthLeads.map(m => {
                            const tot = m.total || 0;
                            const won = m.won || 0;
                            const barHeight = Math.max(4, (tot / maxLead) * 140);

                            return `
                                <div class="flex-1 flex flex-col items-center gap-1">
                                    <span class="text-xs text-gray-500 font-medium">${tot}</span>
                                    <div class="w-full flex flex-col rounded-t overflow-hidden transition-all duration-500" style="height: ${barHeight}px">
                                        ${tot > 0 ? `<div class="bg-emerald-400 w-full h-full rounded-t"></div>` : `<div class="bg-gray-100 w-full h-1"></div>`}
                                    </div>
                                    <span class="text-[10px] text-gray-400 leading-tight text-center">${m.label}</span>
                                </div>
                            `;
                        }).join('')}
                    </div>
                </div>
            `;
        },

        // ── Recent Activities ──
        renderActivities(activities) {
            if (!activities || activities.length === 0) {
                return '<p class="text-sm text-gray-300 text-center py-8">Belum ada aktivitas</p>';
            }

            const typeColors = {
                call: 'bg-blue-100 text-blue-600',
                email: 'bg-purple-100 text-purple-600',
                whatsapp: 'bg-green-100 text-green-600',
                meeting: 'bg-amber-100 text-amber-600',
                note: 'bg-gray-100 text-gray-500',
                status_change: 'bg-indigo-100 text-indigo-600',
            };

            return `
                <div class="space-y-3">
                    ${activities.map(act => `
                        <div class="flex items-start gap-3">
                            <div class="shrink-0 w-7 h-7 rounded-lg ${typeColors[act.type] || 'bg-gray-100 text-gray-500'} flex items-center justify-center text-xs font-bold">
                                ${act.type ? act.type.charAt(0).toUpperCase() : 'A'}
                            </div>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm text-gray-900 truncate font-medium">${act.lead ? act.lead.name : '-'}</p>
                                <p class="text-xs text-gray-400 truncate">
                                    ${act.subject || act.type} ${act.user ? '· ' + act.user.name : ''}
                                </p>
                            </div>
                            <span class="shrink-0 text-[10px] text-gray-300 whitespace-nowrap">
                                ${App.formatDate(act.activity_at || act.created_at)}
                            </span>
                        </div>
                    `).join('')}
                </div>
            `;
        },

        // ── Upcoming Follow-ups ──
        renderFollowUps(followUps) {
            if (!followUps || followUps.length === 0) {
                return '<p class="text-sm text-gray-300 text-center py-8">Tidak ada follow-up mendatang</p>';
            }

            const priorityDots = {
                urgent: 'bg-red-500',
                high: 'bg-orange-400',
                normal: 'bg-blue-400',
                low: 'bg-gray-300',
            };

            return `
                <div class="space-y-3">
                    ${followUps.map(fu => `
                        <div class="flex items-start gap-3 p-3 bg-gray-50 hover:bg-gray-100 rounded-xl transition-colors">
                            <span class="shrink-0 mt-1.5 w-2 h-2 rounded-full ${priorityDots[fu.priority] || 'bg-gray-300'}"></span>
                            <div class="min-w-0 flex-1">
                                <p class="text-sm font-medium text-gray-900 truncate">${fu.title || 'Follow-up'}</p>
                                <p class="text-xs text-gray-400 truncate">${fu.lead ? fu.lead.name : '-'} · ${fu.assigned_user ? fu.assigned_user.name : '-'}</p>
                                <p class="text-[10px] text-gray-400 mt-1">${App.formatDateTime(fu.follow_up_at)}</p>
                            </div>
                        </div>
                    `).join('')}
                </div>
            `;
        },

        bindEvents() {
            // Additional custom bindings if needed
        }
    };
})();
