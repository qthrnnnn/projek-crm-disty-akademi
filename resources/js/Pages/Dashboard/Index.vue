<script setup>
import { computed, onMounted, ref } from 'vue';
import { api } from '../../services/api';
import StatCard from '../../Components/StatCard.vue';
import Badge from '../../Components/Badge.vue';

const props = defineProps({
    user: { type: Object, default: () => ({}) },
});

const emit = defineEmits(['navigate']);

const data = ref(null);
const loading = ref(true);
const error = ref('');

const filterMode = ref('today'); // 'today' | 'month' | 'all'
const selectedDate = ref(new Date().toISOString().split('T')[0]);
const todayIso = new Date().toISOString().split('T')[0];
const selectedFilterYear = ref(new Date().getFullYear());
const selectedFilterMonth = ref(new Date().getMonth() + 1);
const calendarMonth = ref(new Date().getMonth());
const calendarYear = ref(new Date().getFullYear());
const activeStage = ref('all');
const hoverStage = ref(null);

const isAdmin = computed(() => props.user?.role === 'admin');

const STAGES = [
    { key: 'new', label: 'Baru', dot: 'bg-slate-400', hex: '#94a3b8' },
    { key: 'contacted', label: 'Dikontak', dot: 'bg-blue-400', hex: '#60a5fa' },
    { key: 'active', label: 'Aktif', dot: 'bg-cyan-500', hex: '#06b6d4' },
    { key: 'negotiation', label: 'Negosiasi', dot: 'bg-amber-400', hex: '#fbbf24' },
    { key: 'won', label: 'Berhasil', dot: 'bg-emerald-500', hex: '#10b981' },
    { key: 'lost', label: 'Ditolak', dot: 'bg-red-400', hex: '#f87171' },
    { key: 'inactive', label: 'Tidak Aktif', dot: 'bg-gray-300', hex: '#cbd5e1' },
];

const MONTH_NAMES = [
    'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
];
const MONTH_NAMES_SHORT = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];

const allLeads = computed(() => data.value?.pipelineLeadsData || data.value?.data?.pipelineLeadsData || []);
const sixMonthLeads = computed(() => data.value?.sixMonthLeads || []);
const maxSixMonthLeads = computed(() => Math.max(1, ...sixMonthLeads.value.map(month => Number(month.total) || 0)));

const calendarDays = computed(() => {
    const firstDay = new Date(calendarYear.value, calendarMonth.value, 1).getDay();
    const daysInMonth = new Date(calendarYear.value, calendarMonth.value + 1, 0).getDate();
    const days = Array.from({ length: firstDay }, () => null);

    for (let day = 1; day <= daysInMonth; day++) {
        const iso = `${calendarYear.value}-${String(calendarMonth.value + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
        days.push({ number: day, iso });
    }

    return days;
});

const calendarTitle = computed(() => `${MONTH_NAMES[calendarMonth.value]} ${calendarYear.value}`);

function isCalendarToday(iso) {
    return iso === new Date().toISOString().split('T')[0];
}

function isSelectedDate(iso) {
    return iso === selectedDate.value;
}

function changeCalendarMonth(offset) {
    const next = new Date(calendarYear.value, calendarMonth.value + offset, 1);
    calendarMonth.value = next.getMonth();
    calendarYear.value = next.getFullYear();
}

async function selectCalendarDay(day) {
    if (!day) return;
    selectedDate.value = day.iso;
    filterMode.value = 'today';
    await loadDashboard();
}

// Leads filtered ONLY by period (for Donut slices, stageCounts, and period metrics)
const periodLeads = computed(() => {
    return allLeads.value.filter(lead => {
        if (filterMode.value === 'today') {
            return lead.date_iso === selectedDate.value;
        } else if (filterMode.value === 'month') {
            return Number(lead.year) === Number(selectedFilterYear.value) && Number(lead.month) === Number(selectedFilterMonth.value);
        }
        return true;
    });
});

const periodTotal = computed(() => periodLeads.value.length);
const periodWon = computed(() => periodLeads.value.filter(l => l.status === 'won').length);
const periodConvRate = computed(() => periodTotal.value > 0 ? ((periodWon.value / periodTotal.value) * 100).toFixed(1) : '0.0');

// ── KPI Cards Metrics (Reaktif Mengikuti Pilihan Periode: Hari Ini / Pilih Bulan / Semua Data) ──
const kpiLeadsIn = computed(() => {
    return periodLeads.value.length;
});

const kpiLeadsUnanswered = computed(() => {
    return periodLeads.value.filter(l => {
        if (l.is_unanswered !== undefined) {
            return Boolean(l.is_unanswered);
        }
        return l.status === 'new';
    }).length;
});

const kpiLeadsAnswered = computed(() => {
    return Math.max(0, kpiLeadsIn.value - kpiLeadsUnanswered.value);
});

const kpiLeadsResponded = computed(() => {
    return periodLeads.value.filter(l => ['active', 'negotiation', 'won'].includes(l.status)).length;
});

const kpiSubtextLeadIn = computed(() => {
    if (filterMode.value === 'today') {
        const parts = selectedDate.value.split('-');
        const day = parseInt(parts[2] || '1', 10);
        const mon = MONTH_NAMES_SHORT[parseInt(parts[1] || '1', 10) - 1];
        return `Total masuk ${day} ${mon} ${parts[0]}`;
    } else if (filterMode.value === 'month') {
        return `Total masuk ${MONTH_NAMES[selectedFilterMonth.value - 1]} ${selectedFilterYear.value}`;
    }
    return 'Total sepanjang waktu';
});

const kpiSubtextAnswered = computed(() => {
    if (filterMode.value === 'today') return 'Sudah dikontak sales';
    if (filterMode.value === 'month') return 'Sudah dikontak bulan ini';
    return 'Sudah dikontak (total)';
});

const kpiSubtextUnanswered = computed(() => {
    if (filterMode.value === 'today') return 'Perlu segera dikontak';
    if (filterMode.value === 'month') return 'Belum dikontak bulan ini';
    return 'Belum dikontak (total)';
});

const kpiSubtextResponded = computed(() => {
    if (filterMode.value === 'today') return 'Aktif / negosiasi';
    if (filterMode.value === 'month') return 'Aktif / negosiasi bulan ini';
    return 'Aktif / negosiasi (total)';
});

const recentActivities = computed(() => {
    return data.value?.recent_activities || data.value?.recentActivities || [];
});

const upcomingFollowUps = computed(() => {
    return data.value?.upcoming_followups || data.value?.upcomingFollowUps || [];
});

// Counts per stage on the whole period (always accurate, never wiped out by clicking)
const stageCounts = computed(() => {
    const map = {};
    STAGES.forEach(s => {
        map[s.key] = periodLeads.value.filter(l => l.status === s.key).length;
    });
    return map;
});

// SVG Donut slices - bentuk bunderan tetap (stroke-width 20 selalu, tidak membesar saat hover)
const circumference = 339.292;
const donutCircles = computed(() => {
    const total = periodTotal.value;
    let currentOffset = 0;
    return STAGES.map(s => {
        const cnt = stageCounts.value[s.key] || 0;
        if (cnt === 0 || total === 0) return null;
        const slice = (cnt / total) * circumference;
        const dashArray = `${slice} ${circumference}`;
        const dashOffset = -currentOffset;
        currentOffset += slice;

        const isHovered = hoverStage.value === s.key;
        const opacity = hoverStage.value ? (isHovered ? 1 : 0.45) : 1;

        return {
            ...s,
            cnt,
            dashArray,
            dashOffset,
            opacity,
        };
    }).filter(Boolean);
});

// Center display: directly displays total lead count and TOTAL LEAD by default
// When hovered, instantly displays that status's count and name
const centerCount = computed(() => {
    if (hoverStage.value) {
        return stageCounts.value[hoverStage.value] || 0;
    }
    if (activeStage.value && activeStage.value !== 'all') {
        return stageCounts.value[activeStage.value] || 0;
    }
    return periodTotal.value;
});

const centerLabel = computed(() => {
    const targetKey = hoverStage.value || (activeStage.value !== 'all' ? activeStage.value : null);
    if (targetKey) {
        const st = STAGES.find(s => s.key === targetKey);
        return st ? st.label.toUpperCase() : 'LEAD';
    }
    return 'TOTAL LEAD';
});

const periodLabel = computed(() => {
    if (filterMode.value === 'today') {
        const parts = selectedDate.value.split('-');
        const day = parseInt(parts[2] || '1', 10);
        const mon = MONTH_NAMES_SHORT[parseInt(parts[1] || '1', 10) - 1];
        return `Hari Ini (${String(day).padStart(2, '0')} ${mon} ${parts[0]})`;
    } else if (filterMode.value === 'month') {
        return `${MONTH_NAMES[selectedFilterMonth.value - 1]} ${selectedFilterYear.value}`;
    }
    return 'Sepanjang Waktu (Semua Data)';
});

// Leads filtered by period AND activeStage (for lead list on right panel)
const filteredLeads = computed(() => {
    if (activeStage.value && activeStage.value !== 'all') {
        return periodLeads.value.filter(lead => lead.status === activeStage.value);
    }
    return periodLeads.value;
});

const activeStageLabel = computed(() => {
    const st = STAGES.find(s => s.key === activeStage.value);
    return st ? st.label : 'Semua Status';
});

function selectStage(key) {
    activeStage.value = activeStage.value === key ? 'all' : key;
    hoverStage.value = null;
}

function setFilterMode(mode) {
    filterMode.value = mode;
    activeStage.value = 'all';
    hoverStage.value = null;
}

function prevMonth() {
    if (selectedFilterMonth.value === 1) {
        selectedFilterMonth.value = 12;
        selectedFilterYear.value--;
    } else {
        selectedFilterMonth.value--;
    }
    activeStage.value = 'all';
}

function nextMonth() {
    if (selectedFilterMonth.value === 12) {
        selectedFilterMonth.value = 1;
        selectedFilterYear.value++;
    } else {
        selectedFilterMonth.value++;
    }
    activeStage.value = 'all';
}

function getStatusPillClass(status) {
    switch (status) {
        case 'won': return 'bg-emerald-50 text-emerald-700';
        case 'negotiation': return 'bg-amber-50 text-amber-700';
        case 'active': return 'bg-cyan-50 text-cyan-700';
        case 'contacted': return 'bg-blue-50 text-blue-700';
        case 'new': return 'bg-slate-100 text-slate-700';
        case 'lost': return 'bg-red-50 text-red-700';
        default: return 'bg-gray-100 text-gray-500';
    }
}

async function loadDashboard() {
    loading.value = true;
    error.value = '';
    try {
        const res = await api.request(`/dashboard?date=${selectedDate.value}`);
        data.value = res.data || res;
    } catch (err) {
        error.value = err.message || 'Gagal memuat dashboard.';
    } finally {
        loading.value = false;
    }
}

onMounted(() => {
    loadDashboard();
});
</script>

<template>
    <div class="space-y-6">
        <!-- Header -->
        <div>
            <h1 class="text-xl font-bold text-gray-900">
                {{ isAdmin ? 'Dashboard Manajemen' : 'Dashboard Sales CRM' }}
            </h1>
            <p class="text-xs text-gray-500 mt-0.5">
                {{ isAdmin ? 'Ringkasan performa seluruh tim sales, verifikasi transaksi, & katalog akademi' : 'Pantau lead, komisi, dan target follow-up Anda hari ini' }}
            </p>
        </div>

        <div v-if="loading" class="flex items-center justify-center py-20 text-gray-400">
            <svg class="w-5 h-5 animate-spin text-orange-500 mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
            <span class="text-xs">Memuat data dashboard...</span>
        </div>

        <div v-else-if="error" class="p-4 bg-red-50 border border-red-200 text-red-700 text-xs">
            {{ error }}
        </div>

        <template v-else>
            <!-- KPI Cards & Calendar -->
            <div class="grid grid-cols-1 lg:grid-cols-[minmax(0,1fr)_280px] gap-3 sm:gap-4 items-stretch">
                <div class="grid grid-cols-2 gap-2.5 sm:gap-3 auto-rows-fr">
                    <StatCard title="Lead Masuk" :value="kpiLeadsIn" :subtext="kpiSubtextLeadIn" />
                    <StatCard title="Sudah Dijawab" :value="kpiLeadsAnswered" :subtext="kpiSubtextAnswered" subtext-color="text-emerald-600" />
                    <StatCard title="Belum Dijawab" :value="kpiLeadsUnanswered" :subtext="kpiSubtextUnanswered" :subtext-color="kpiLeadsUnanswered > 0 ? 'text-amber-600' : 'text-gray-400'" />
                    <StatCard title="Sudah Merespon" :value="kpiLeadsResponded" :subtext="kpiSubtextResponded" subtext-color="text-blue-600" />
                </div>

                <div class="bg-white border border-gray-100 p-3 sm:p-4 shadow-2xs">
                    <div class="flex items-center justify-between mb-2.5">
                        <button type="button" @click="changeCalendarMonth(-1)" class="w-8 h-8 rounded-lg text-gray-400 hover:bg-orange-50 hover:text-orange-600 transition-colors flex items-center justify-center" aria-label="Bulan sebelumnya">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                        </button>
                        <div class="flex items-center gap-1.5">
                            <p class="text-xs sm:text-sm font-semibold text-gray-900">{{ calendarTitle }}</p>
                            <button v-if="selectedDate !== todayIso || filterMode !== 'today'" @click="selectedDate = todayIso; filterMode = 'today'; loadDashboard()" type="button" class="text-[10px] text-orange-600 font-bold hover:underline bg-orange-50 px-1.5 py-0.5 rounded">
                                Hari Ini
                            </button>
                        </div>
                        <button type="button" @click="changeCalendarMonth(1)" class="w-8 h-8 rounded-lg text-gray-400 hover:bg-orange-50 hover:text-orange-600 transition-colors flex items-center justify-center" aria-label="Bulan berikutnya">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                        </button>
                    </div>
                    <div class="grid grid-cols-7 gap-1 text-center">
                        <span v-for="weekday in ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab']" :key="weekday" class="text-[10px] font-medium text-gray-400 py-1">{{ weekday }}</span>
                        <span v-for="(day, index) in calendarDays" :key="index">
                            <button v-if="day" type="button" @click="selectCalendarDay(day)"
                                class="w-7 h-7 sm:w-8 sm:h-8 rounded-full text-[11px] sm:text-xs transition-all flex items-center justify-center mx-auto"
                                :class="isSelectedDate(day.iso) ? 'bg-orange-500 text-white font-bold' : (isCalendarToday(day.iso) ? 'ring-1 ring-orange-400 text-orange-600 font-semibold' : 'text-gray-600 hover:bg-orange-50 hover:text-orange-600')">
                                {{ day.number }}
                            </button>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Distribusi Status Lead & Filter Prospek Periode -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
                <!-- Panel Kiri: Donut Chart & 7 Status Cards -->
                <div class="lg:col-span-2 bg-white p-5 border border-gray-100 shadow-xs flex flex-col justify-between">
                    <div>
                        <!-- Header Panel -->
                        <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-3">
                            <div>
                                <h2 class="text-xs font-bold text-gray-800 uppercase tracking-wide">Distribusi Status Lead</h2>
                                <p class="text-[11px] text-gray-400 mt-0.5">{{ periodLabel }}</p>
                            </div>
                            <span class="text-xs text-gray-500">Total: <strong class="text-gray-900 font-bold">{{ periodTotal }}</strong></span>
                        </div>

                        <!-- Donut + 7 Status Buttons -->
                        <div class="flex flex-col sm:flex-row items-center gap-6 pt-2">
                            <!-- SVG Donut Chart -->
                            <div class="relative shrink-0 mx-auto sm:mx-0">
                                <svg viewBox="0 0 160 160" class="w-44 h-44 sm:w-52 sm:h-52 -rotate-90 transform origin-center">
                                    <circle cx="80" cy="80" r="54" fill="none" stroke="#f1f5f9" stroke-width="20" />
                                    <circle v-for="c in donutCircles" :key="c.key"
                                        cx="80" cy="80" r="54" fill="none"
                                        :stroke="c.hex"
                                        stroke-width="20"
                                        :stroke-dasharray="c.dashArray"
                                        :stroke-dashoffset="c.dashOffset"
                                        :style="{ opacity: c.opacity, transition: 'opacity 0.15s ease' }"
                                        class="cursor-pointer"
                                        @click="selectStage(c.key)"
                                        @mouseenter="hoverStage = c.key"
                                        @mouseleave="hoverStage = null">
                                    </circle>
                                </svg>
                                <!-- Center Count & Label (Tanpa Tooltip & Tanpa Resize) -->
                                <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none">
                                    <span class="text-2xl sm:text-3xl font-extrabold text-gray-900 leading-none">{{ centerCount }}</span>
                                    <span class="text-[10px] text-gray-400 font-semibold tracking-wider uppercase mt-1 leading-none">{{ centerLabel }}</span>
                                </div>
                            </div>

                            <!-- 7 Interactive Status Buttons (Hover Mengubah Teks di Tengah, Tanpa Tooltip/Resize) -->
                            <div class="flex-1 w-full">
                                <div class="grid grid-cols-4 lg:grid-cols-7 gap-2">
                                    <button v-for="s in STAGES" :key="s.key"
                                        type="button"
                                        @click="selectStage(s.key)"
                                        @mouseenter="hoverStage = s.key"
                                        @mouseleave="hoverStage = null"
                                        class="text-center p-1.5 transition-colors rounded-lg cursor-pointer"
                                        :class="[
                                            activeStage === s.key
                                                ? 'bg-orange-50 border border-orange-300 ring-1 ring-orange-200'
                                                : (hoverStage === s.key ? 'bg-gray-100' : 'border border-transparent hover:bg-gray-50')
                                        ]">
                                        <span class="inline-block w-1.5 h-1.5 rounded-full mb-1" :class="s.dot"></span>
                                        <p class="text-base sm:text-lg font-bold text-gray-900 leading-tight">{{ stageCounts[s.key] ?? 0 }}</p>
                                        <p class="text-[10px] text-gray-500 mt-0.5 leading-tight truncate">{{ s.label }}</p>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Bar -->
                    <div class="pt-3 border-t border-gray-100 mt-2 flex items-center justify-between text-xs text-gray-500">
                        <span>Conversion rate: <strong class="text-gray-900 font-bold">{{ periodConvRate }}%</strong></span>
                        <button v-if="activeStage !== 'all'" @click="activeStage = 'all'"
                            class="text-[11px] text-orange-600 hover:text-orange-700 font-semibold underline">
                            Tampilkan Semua Status
                        </button>
                    </div>
                </div>

                <!-- Panel Kanan: Pilihan Periode Prospek -->
                <div class="bg-white p-5 border border-gray-100 shadow-xs flex flex-col justify-between">
                    <div>
                        <!-- Header -->
                        <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-3">
                            <div>
                                <h2 class="text-xs font-bold text-gray-800 uppercase tracking-wide">Pilihan Periode Prospek</h2>
                                <p class="text-[11px] text-gray-400 mt-0.5">{{ periodLabel }}</p>
                            </div>
                            <button v-if="activeStage !== 'all'" @click="activeStage = 'all'" class="text-[11px] text-orange-600 hover:text-orange-700 font-semibold flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                                Semua Status
                            </button>
                        </div>

                        <!-- 3 Segmented Tabs -->
                        <div class="grid grid-cols-3 gap-1 p-1 bg-gray-100 mb-2.5">
                            <button type="button" @click="setFilterMode('today')"
                                class="py-1.5 px-2 text-center text-xs rounded-md transition-all"
                                :class="filterMode === 'today' ? 'bg-orange-500 text-white font-bold shadow-xs' : 'text-gray-600 hover:text-gray-900'">
                                Hari Ini
                            </button>
                            <button type="button" @click="setFilterMode('month')"
                                class="py-1.5 px-2 text-center text-xs rounded-md transition-all"
                                :class="filterMode === 'month' ? 'bg-orange-500 text-white font-bold shadow-xs' : 'text-gray-600 hover:text-gray-900'">
                                Pilih Bulan
                            </button>
                            <button type="button" @click="setFilterMode('all')"
                                class="py-1.5 px-2 text-center text-xs rounded-md transition-all"
                                :class="filterMode === 'all' ? 'bg-orange-500 text-white font-bold shadow-xs' : 'text-gray-600 hover:text-gray-900'">
                                Semua Data
                            </button>
                        </div>

                        <!-- Mode Controls -->
                        <div v-if="filterMode === 'today'" class="flex items-center justify-between p-2 px-2.5 bg-orange-50/60 border border-orange-100 mb-2.5 text-xs">
                            <span class="text-gray-600 flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-orange-500 animate-pulse"></span>
                                Target Hari Ini:
                            </span>
                            <span class="font-bold text-gray-900">{{ selectedDate }}</span>
                        </div>

                        <div v-else-if="filterMode === 'month'" class="flex items-center gap-1.5 p-1 bg-gray-50 border border-gray-200 mb-2.5">
                            <button type="button" @click="prevMonth" class="p-1 rounded hover:bg-gray-200 text-gray-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                            </button>
                            <select v-model.number="selectedFilterMonth" class="flex-1 text-xs font-semibold text-gray-800 bg-white border border-gray-300 py-1 px-2 outline-none">
                                <option v-for="(name, idx) in MONTH_NAMES" :key="idx" :value="idx + 1">{{ name }}</option>
                            </select>
                            <select v-model.number="selectedFilterYear" class="w-20 text-xs font-semibold text-gray-800 bg-white border border-gray-300 py-1 px-2 outline-none">
                                <option v-for="y in [2024, 2025, 2026, 2027, 2028, 2029, 2030]" :key="y" :value="y">{{ y }}</option>
                            </select>
                            <button type="button" @click="nextMonth" class="p-1 rounded hover:bg-gray-200 text-gray-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </button>
                        </div>

                        <div v-else class="flex items-center justify-between p-2 px-2.5 bg-blue-50/60 border border-blue-100 mb-2.5 text-xs">
                            <span class="text-gray-600 flex items-center gap-1.5">
                                <svg class="w-3.5 h-3.5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                                Akumulasi Historis:
                            </span>
                            <span class="font-bold text-blue-900">Sepanjang Waktu</span>
                        </div>

                        <!-- 3 Stats Summary -->
                        <div class="grid grid-cols-3 gap-2 p-2 bg-gray-50 border border-gray-100 text-center mb-2.5">
                            <div>
                                <p class="text-[10px] text-gray-500 uppercase tracking-wide">Total Lead</p>
                                <p class="text-base sm:text-lg font-bold text-gray-900">{{ periodTotal }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] text-gray-500 uppercase tracking-wide">Closing</p>
                                <p class="text-base sm:text-lg font-bold text-emerald-600">{{ periodWon }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] text-gray-500 uppercase tracking-wide">Konversi</p>
                                <p class="text-base sm:text-lg font-bold text-gray-900">{{ periodConvRate }}%</p>
                            </div>
                        </div>

                        <!-- Filter Status Active Pill -->
                        <div v-if="activeStage !== 'all'" class="mb-2 flex items-center justify-between text-xs bg-amber-50 border border-amber-200 px-2.5 py-1 text-amber-800">
                            <span>Filter status: <strong>{{ activeStageLabel }}</strong></span>
                            <button type="button" @click="activeStage = 'all'" class="text-[11px] underline font-medium hover:text-amber-900">✕ Tampilkan Semua</button>
                        </div>

                        <!-- Lead List -->
                        <div class="overflow-y-auto max-h-[175px] space-y-2 pr-1 scrollbar-thin">
                            <div v-if="filteredLeads.length === 0" class="py-6 text-center text-gray-400">
                                <svg class="w-7 h-7 mx-auto mb-1.5 opacity-40 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                                <p class="text-xs text-gray-400">Tidak ada prospek pada periode & status ini</p>
                            </div>
                            <div v-else v-for="lead in filteredLeads.slice(0, 5)" :key="lead.id"
                                class="p-2.5 border border-gray-100 bg-white hover:border-orange-200 transition-all">
                                <div class="flex items-start justify-between gap-2 mb-1">
                                    <div class="min-w-0 flex-1">
                                        <button @click="emit('navigate', `/leads/${lead.id}`)" class="text-xs font-bold text-gray-900 hover:text-orange-600 truncate block text-left">
                                            {{ lead.name }}
                                        </button>
                                        <p class="text-[10px] text-gray-400 truncate">{{ lead.product || '-' }}</p>
                                    </div>
                                    <Badge :status="lead.status" class="shrink-0" />
                                </div>
                                <div class="flex items-center justify-between pt-1 text-[11px]">
                                    <span class="text-gray-400 text-[10px]">{{ lead.phone || '-' }}</span>
                                    <div class="flex items-center gap-1.5">
                                        <a v-if="lead.wa_link" :href="lead.wa_link" target="_blank" class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-600 hover:bg-emerald-700 text-white transition-colors">
                                            Chat WA
                                        </a>
                                        <button @click="emit('navigate', `/leads/${lead.id}`)" class="px-2 py-0.5 rounded text-[10px] font-semibold bg-gray-100 hover:bg-gray-200 text-gray-700 transition-colors">
                                            Detail
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Action -->
                    <div class="pt-3 border-t border-gray-100 mt-2">
                        <button @click="emit('navigate', activeStage === 'all' ? '/leads' : `/leads?status=${activeStage}`)"
                            class="w-full inline-flex items-center justify-center gap-2 px-3 py-2 text-xs font-semibold rounded-xl bg-orange-500 hover:bg-orange-600 text-white shadow-xs transition-colors">
                            <span>{{ activeStage === 'all' ? 'Buka Daftar Lead di CRM' : `Lihat Semua Prospek ${activeStageLabel}` }}</span>
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Modul Operasional & Pelatihan (Khusus Admin) -->
            <div v-if="isAdmin" class="mb-5">
                <div class="mb-3">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Modul Operasional & Pelatihan (Khusus Admin)</p>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-2.5 sm:gap-3">
                    <button @click="emit('navigate', '/registrations')" class="group bg-white border border-blue-100 p-3 sm:p-4 text-left hover:bg-orange-500 hover:border-orange-500 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 flex flex-col justify-between min-h-[130px] sm:min-h-[150px]">
                        <div class="flex items-start justify-between text-left">
                            <p class="text-sm sm:text-base font-bold leading-tight text-gray-900 group-hover:text-white 2xl:whitespace-nowrap">
                                <span>Pendaftaran</span><br class="card-title-br 2xl:hidden"><span class="card-title-space hidden 2xl:inline">&nbsp;</span><span>Peserta</span>
                            </p>
                            <span class="w-6 h-6 sm:w-7 sm:h-7 rounded-full bg-gray-100 text-gray-500 flex items-center justify-center group-hover:bg-white/20 group-hover:text-white transition-colors shrink-0">
                                <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 17L17 7m0 0H9m8 0v8"/></svg>
                            </span>
                        </div>
                        <div class="mt-auto">
                            <p class="text-[11px] sm:text-xs text-gray-400 group-hover:text-white/80 mt-1">Daftarkan ke jadwal aktif</p>
                        </div>
                    </button>
                    <button @click="emit('navigate', '/training-schedules')" class="group bg-white border border-blue-100 p-3 sm:p-4 text-left hover:bg-orange-500 hover:border-orange-500 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 flex flex-col justify-between min-h-[130px] sm:min-h-[150px]">
                        <div class="flex items-start justify-between text-left">
                            <p class="text-sm sm:text-base font-bold leading-tight text-gray-900 group-hover:text-white 2xl:whitespace-nowrap">
                                <span>Jadwal</span><br class="card-title-br 2xl:hidden"><span class="card-title-space hidden 2xl:inline">&nbsp;</span><span>Pelatihan</span>
                            </p>
                            <span class="w-6 h-6 sm:w-7 sm:h-7 rounded-full bg-gray-100 text-gray-500 flex items-center justify-center group-hover:bg-white/20 group-hover:text-white transition-colors shrink-0">
                                <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 17L17 7m0 0H9m8 0v8"/></svg>
                            </span>
                        </div>
                        <div class="mt-auto">
                            <p class="text-[11px] sm:text-xs text-gray-400 group-hover:text-white/80 mt-1">Cek kuota & instruktur</p>
                        </div>
                    </button>
                    <button @click="emit('navigate', '/customers')" class="group bg-white border border-blue-100 p-3 sm:p-4 text-left hover:bg-orange-500 hover:border-orange-500 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 flex flex-col justify-between min-h-[130px] sm:min-h-[150px]">
                        <div class="flex items-start justify-between text-left">
                            <p class="text-sm sm:text-base font-bold leading-tight text-gray-900 group-hover:text-white 2xl:whitespace-nowrap">
                                <span>Daftar</span><br class="card-title-br 2xl:hidden"><span class="card-title-space hidden 2xl:inline">&nbsp;</span><span>Customer</span>
                            </p>
                            <span class="w-6 h-6 sm:w-7 sm:h-7 rounded-full bg-gray-100 text-gray-500 flex items-center justify-center group-hover:bg-white/20 group-hover:text-white transition-colors shrink-0">
                                <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 17L17 7m0 0H9m8 0v8"/></svg>
                            </span>
                        </div>
                        <div class="mt-auto">
                            <p class="text-[11px] sm:text-xs text-gray-400 group-hover:text-white/80 mt-1">Export CSV & Data Peserta</p>
                        </div>
                    </button>
                    <button @click="emit('navigate', '/institutions')" class="group bg-white border border-blue-100 p-3 sm:p-4 text-left hover:bg-orange-500 hover:border-orange-500 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 flex flex-col justify-between min-h-[130px] sm:min-h-[150px]">
                        <div class="flex items-start justify-between text-left">
                            <p class="text-sm sm:text-base font-bold leading-tight text-gray-900 group-hover:text-white 2xl:whitespace-nowrap">
                                <span>Daftar</span><br class="card-title-br 2xl:hidden"><span class="card-title-space hidden 2xl:inline">&nbsp;</span><span>Instansi</span>
                            </p>
                            <span class="w-6 h-6 sm:w-7 sm:h-7 rounded-full bg-gray-100 text-gray-500 flex items-center justify-center group-hover:bg-white/20 group-hover:text-white transition-colors shrink-0">
                                <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 17L17 7m0 0H9m8 0v8"/></svg>
                            </span>
                        </div>
                        <div class="mt-auto">
                            <p class="text-[11px] sm:text-xs text-gray-400 group-hover:text-white/80 mt-1">Mitra kampus & kantor</p>
                        </div>
                    </button>
                    <button @click="emit('navigate', '/trainers')" class="group bg-white border border-blue-100 p-3 sm:p-4 text-left hover:bg-orange-500 hover:border-orange-500 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 flex flex-col justify-between min-h-[130px] sm:min-h-[150px]">
                        <div class="flex items-start justify-between text-left">
                            <p class="text-sm sm:text-base font-bold leading-tight text-gray-900 group-hover:text-white 2xl:whitespace-nowrap">
                                <span>Daftar</span><br class="card-title-br 2xl:hidden"><span class="card-title-space hidden 2xl:inline">&nbsp;</span><span>Trainer</span>
                            </p>
                            <span class="w-6 h-6 sm:w-7 sm:h-7 rounded-full bg-gray-100 text-gray-500 flex items-center justify-center group-hover:bg-white/20 group-hover:text-white transition-colors shrink-0">
                                <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 17L17 7m0 0H9m8 0v8"/></svg>
                            </span>
                        </div>
                        <div class="mt-auto">
                            <p class="text-[11px] sm:text-xs text-gray-400 group-hover:text-white/80 mt-1">Instruktur & expert</p>
                        </div>
                    </button>
                </div>
            </div>
            <div v-else class="mb-5">
                <div class="flex items-center justify-between mb-3">
                    <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Aktivitas Affiliate & Penjualan</p>
                    <span class="text-[11px] text-emerald-600 font-semibold">Komisi 10% dari setiap deal closing</span>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-2.5 sm:gap-3">
                    <button @click="emit('navigate', '/leads/create')" class="group bg-white border border-emerald-100 p-3 sm:p-4 text-left hover:bg-orange-500 hover:border-orange-500 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 flex flex-col justify-between min-h-[130px] sm:min-h-[150px]">
                        <div class="flex items-start justify-between text-left">
                            <p class="text-sm sm:text-base font-bold leading-tight text-gray-900 group-hover:text-white 2xl:whitespace-nowrap">
                                <span>Input</span><br class="card-title-br 2xl:hidden"><span class="card-title-space hidden 2xl:inline">&nbsp;</span><span>Prospek Baru</span>
                            </p>
                            <span class="w-6 h-6 sm:w-7 sm:h-7 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center group-hover:bg-white/20 group-hover:text-white transition-colors shrink-0">
                                <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 17L17 7m0 0H9m8 0v8"/></svg>
                            </span>
                        </div>
                        <div class="mt-auto">
                            <p class="text-[11px] sm:text-xs text-gray-400 group-hover:text-white/80 mt-1">Kenalan sales / calon deal</p>
                        </div>
                    </button>
                    <button @click="emit('navigate', '/payments/create')" class="group bg-white border border-emerald-100 p-3 sm:p-4 text-left hover:bg-orange-500 hover:border-orange-500 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 flex flex-col justify-between min-h-[130px] sm:min-h-[150px]">
                        <div class="flex items-start justify-between text-left">
                            <p class="text-sm sm:text-base font-bold leading-tight text-gray-900 group-hover:text-white 2xl:whitespace-nowrap">
                                <span>Kirim</span><br class="card-title-br 2xl:hidden"><span class="card-title-space hidden 2xl:inline">&nbsp;</span><span>Bukti Bayar</span>
                            </p>
                            <span class="w-6 h-6 sm:w-7 sm:h-7 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center group-hover:bg-white/20 group-hover:text-white transition-colors shrink-0">
                                <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 17L17 7m0 0H9m8 0v8"/></svg>
                            </span>
                        </div>
                        <div class="mt-auto">
                            <p class="text-[11px] sm:text-xs text-gray-400 group-hover:text-white/80 mt-1">Upload transfer DP / lunas</p>
                        </div>
                    </button>
                    <button @click="emit('navigate', '/commissions')" class="group bg-white border border-emerald-100 p-3 sm:p-4 text-left hover:bg-orange-500 hover:border-orange-500 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 flex flex-col justify-between min-h-[130px] sm:min-h-[150px]">
                        <div class="flex items-start justify-between text-left">
                            <p class="text-sm sm:text-base font-bold leading-tight text-gray-900 group-hover:text-white 2xl:whitespace-nowrap">
                                <span>Komisi 10%</span><br class="card-title-br 2xl:hidden"><span class="card-title-space hidden 2xl:inline">&nbsp;</span><span>Saya</span>
                            </p>
                            <span class="w-6 h-6 sm:w-7 sm:h-7 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center group-hover:bg-white/20 group-hover:text-white transition-colors shrink-0">
                                <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 17L17 7m0 0H9m8 0v8"/></svg>
                            </span>
                        </div>
                        <div class="mt-auto">
                            <p class="text-[11px] sm:text-xs text-gray-400 group-hover:text-white/80 mt-1">Cek insentif & pencairan</p>
                        </div>
                    </button>
                    <button @click="emit('navigate', '/products')" class="group bg-white border border-emerald-100 p-3 sm:p-4 text-left hover:bg-orange-500 hover:border-orange-500 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 flex flex-col justify-between min-h-[130px] sm:min-h-[150px]">
                        <div class="flex items-start justify-between text-left">
                            <p class="text-sm sm:text-base font-bold leading-tight text-gray-900 group-hover:text-white 2xl:whitespace-nowrap">
                                <span>Katalog &</span><br class="card-title-br 2xl:hidden"><span class="card-title-space hidden 2xl:inline">&nbsp;</span><span>Harga Patokan</span>
                            </p>
                            <span class="w-6 h-6 sm:w-7 sm:h-7 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center group-hover:bg-white/20 group-hover:text-white transition-colors shrink-0">
                                <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 17L17 7m0 0H9m8 0v8"/></svg>
                            </span>
                        </div>
                        <div class="mt-auto">
                            <p class="text-[11px] sm:text-xs text-gray-400 group-hover:text-white/80 mt-1">Harga resmi sertifikasi</p>
                        </div>
                    </button>
                    <button @click="emit('navigate', '/follow-ups')" class="group bg-white border border-emerald-100 p-3 sm:p-4 text-left hover:bg-orange-500 hover:border-orange-500 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 flex flex-col justify-between min-h-[130px] sm:min-h-[150px]">
                        <div class="flex items-start justify-between text-left">
                            <p class="text-sm sm:text-base font-bold leading-tight text-gray-900 group-hover:text-white 2xl:whitespace-nowrap">
                                <span>Follow-up</span><br class="card-title-br 2xl:hidden"><span class="card-title-space hidden 2xl:inline">&nbsp;</span><span>Prospek</span>
                            </p>
                            <span class="w-6 h-6 sm:w-7 sm:h-7 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center group-hover:bg-white/20 group-hover:text-white transition-colors shrink-0">
                                <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 17L17 7m0 0H9m8 0v8"/></svg>
                            </span>
                        </div>
                        <div class="mt-auto">
                            <p class="text-[11px] sm:text-xs text-gray-400 group-hover:text-white/80 mt-1">Jadwal tindak lanjut</p>
                        </div>
                    </button>
                </div>
            </div>

            <!-- Lead 6 Bulan -->
            <div class="bg-white p-5 border border-gray-100 shadow-2xs">
                <div class="flex items-center justify-between mb-3">
                    <h2 class="text-xs font-bold text-gray-900">Lead 6 Bulan sampai {{ calendarTitle }}</h2>
                    <span class="inline-flex items-center gap-1 text-[10px] text-gray-500">
                        <span class="w-2 h-2 bg-emerald-400"></span>
                        Won
                    </span>
                </div>
                <div v-if="sixMonthLeads.length" class="grid grid-cols-6 gap-2 sm:gap-4 items-end h-28">
                    <div v-for="month in sixMonthLeads" :key="month.label" class="h-full flex flex-col items-center justify-end gap-1">
                        <span class="text-[9px] text-gray-400">{{ month.total }}</span>
                        <div class="w-full max-w-10 h-20 flex items-end justify-center">
                            <div class="w-full rounded-t-sm bg-emerald-400 transition-all" :style="{ height: `${Math.max(4, (Number(month.total) / maxSixMonthLeads) * 100)}%` }"></div>
                        </div>
                        <span class="text-[9px] text-gray-400 truncate max-w-full">{{ month.label }}</span>
                    </div>
                </div>
                <div v-else class="h-28 flex items-center justify-center text-xs text-gray-400">
                    Belum ada data lead.
                </div>
            </div>

            <!-- Aktivitas Terbaru & Follow-up Mendatang -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
                <div class="bg-white p-5 border border-gray-100 shadow-xs">
                    <div class="flex items-center justify-between pb-2.5 border-b border-gray-100 mb-3">
                        <h2 class="text-xs font-bold text-gray-900 uppercase tracking-wider">Aktivitas Terbaru</h2>
                        <button @click="emit('navigate', '/activities')" class="text-[11px] text-orange-600 hover:text-orange-700 font-semibold flex items-center gap-1">
                            <span>Lihat Semua</span>
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </button>
                    </div>
                    <div v-if="!recentActivities.length" class="text-center py-8 text-xs text-gray-400">
                        Belum ada aktivitas tercatat.
                    </div>
                    <div v-else>
                        <div class="divide-y divide-gray-100">
                            <div v-for="act in recentActivities.slice(0, 3)" :key="act.id" class="py-2.5 flex items-center justify-between text-xs">
                                <div class="min-w-0 pr-2">
                                    <p class="font-bold text-gray-800 truncate">{{ act.lead?.name || 'Prospek' }}</p>
                                    <p class="text-[11px] text-gray-500 truncate">{{ act.description || act.subject || act.type }}</p>
                                </div>
                                <span class="text-[10px] text-gray-400 whitespace-nowrap shrink-0">{{ act.formatted_time || (act.created_at ? new Date(act.created_at).toLocaleDateString('id-ID') : '-') }}</span>
                            </div>
                        </div>
                        <div v-if="recentActivities.length > 3" class="mt-2.5 pt-2 text-center border-t border-gray-50">
                            <button @click="emit('navigate', '/activities')" class="text-[11px] text-orange-600 hover:text-orange-700 font-semibold inline-flex items-center gap-1">
                                <span>Lihat selengkapnya di menu aktivitas</span>
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="bg-white p-5 border border-gray-100 shadow-xs">
                    <div class="flex items-center justify-between pb-2.5 border-b border-gray-100 mb-3">
                        <h2 class="text-xs font-bold text-gray-900 uppercase tracking-wider">Follow-up Mendatang</h2>
                        <button @click="emit('navigate', '/follow-ups')" class="text-[11px] text-orange-600 hover:text-orange-700 font-semibold flex items-center gap-1">
                            <span>Lihat Agenda</span>
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        </button>
                    </div>
                    <div v-if="!upcomingFollowUps.length" class="text-center py-8 text-xs text-gray-400">
                        Tidak ada follow-up mendatang.
                    </div>
                    <div v-else>
                        <div class="divide-y divide-gray-100">
                            <div v-for="fu in upcomingFollowUps.slice(0, 3)" :key="fu.id" class="py-2.5 flex items-center justify-between text-xs">
                                <div class="min-w-0 pr-2">
                                    <p class="font-bold text-gray-800 truncate">{{ fu.lead?.name || fu.title || 'Prospek' }}</p>
                                    <p class="text-[11px] text-gray-500 truncate">{{ fu.notes || fu.description || fu.title || 'Rencana kontak' }}</p>
                                </div>
                                <div class="text-right shrink-0">
                                    <span class="text-[10px] text-orange-600 font-bold block">{{ fu.scheduled_at || fu.follow_up_at || '-' }}</span>
                                    <span v-if="fu.method" class="text-[9px] uppercase px-1.5 py-0.5 rounded font-semibold bg-gray-100 text-gray-600">
                                        {{ fu.method }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div v-if="upcomingFollowUps.length > 3" class="mt-2.5 pt-2 text-center border-t border-gray-50">
                            <button @click="emit('navigate', '/follow-ups')" class="text-[11px] text-orange-600 hover:text-orange-700 font-semibold inline-flex items-center gap-1">
                                <span>Lihat selengkapnya di agenda follow-up</span>
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </template>
    </div>
</template>
