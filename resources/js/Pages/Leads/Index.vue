<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import { api } from '../../services/api';
import Pagination from '../../Components/Pagination.vue';
import Badge from '../../Components/Badge.vue';

const props = defineProps({
    user: { type: Object, default: () => ({}) },
});

const emit = defineEmits(['navigate']);

const leads = ref([]);
const salesUsers = ref([]);
const pagination = ref({ current_page: 1, last_page: 1, total: 0 });
const perPage = ref('50');
const search = ref('');
const dateFilter = ref('');
const statusFilter = ref('');
const assignedFilter = ref('');
const scopeTab = ref('all');
const loading = ref(true);

const myLeadsCount = ref(0);
const unassignedCount = ref(0);
const lostPoolCount = ref(0);
const totalLeadsCount = ref(0);

const isSales = computed(() => props.user?.role === 'sales');
const isAdmin = computed(() => props.user?.role === 'admin');

async function loadCounts() {
    try {
        const res = await api.list('leads', { per_page: '100' });
        const all = res.data || [];
        totalLeadsCount.value = all.length;
        unassignedCount.value = all.filter(l => !l.assigned_to).length;
        lostPoolCount.value = all.filter(l => l.status === 'lost').length;
        if (props.user?.id) {
            myLeadsCount.value = all.filter(l => l.assigned_to === props.user.id).length;
        }
    } catch (e) {
        console.error(e);
    }
}

async function loadSalesUsers() {
    try {
        const res = await api.list('users', { per_page: '100' });
        salesUsers.value = (res.data || []).filter(u => u.role === 'sales');
    } catch (e) {
        console.error(e);
    }
}

async function loadLeads(page = 1) {
    loading.value = true;
    try {
        const params = { page: String(page), per_page: String(perPage.value) };
        if (search.value) params.search = search.value;
        if (dateFilter.value) params.date = dateFilter.value;
        if (statusFilter.value) params.status = statusFilter.value;
        if (assignedFilter.value) params.assigned = assignedFilter.value;
        
        const res = await api.list('leads', params);
        let items = res.data || [];

        // Apply scope filters locally if needed
        if (scopeTab.value === 'my_leads' && props.user?.id) {
            items = items.filter(l => l.assigned_to === props.user.id);
        } else if (scopeTab.value === 'unassigned') {
            items = items.filter(l => !l.assigned_to);
        } else if (scopeTab.value === 'lost_pool') {
            items = items.filter(l => l.status === 'lost');
        }

        leads.value = items;
        pagination.value = {
            current_page: res.current_page || page,
            last_page: res.last_page || 1,
            total: res.total || items.length,
            from: res.from || 1,
            to: res.to || items.length,
        };
    } catch (err) {
        console.error(err);
    } finally {
        loading.value = false;
    }
}

function setScope(tab) {
    scopeTab.value = tab;
    loadLeads(1);
}

function handleFilter() {
    loadLeads(1);
}

let searchTimeout = null;
function debounceSearch() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        handleFilter();
    }, 300);
}

function resetFilter() {
    search.value = '';
    dateFilter.value = '';
    statusFilter.value = '';
    assignedFilter.value = '';
    loadLeads(1);
}

function formatRupiah(num) {
    if (!num) return '0';
    return Number(num).toLocaleString('id-ID');
}

// Modal Penugasan Sales oleh Admin
const showAssignModal = ref(false);
const assigningLead = ref(null);
const selectedSalesId = ref('');
const assigning = ref(false);
const assignError = ref('');

function openAssignModal(lead) {
    assigningLead.value = lead;
    selectedSalesId.value = lead.assigned_to || (salesUsers.value[0]?.id || '');
    assignError.value = '';
    showAssignModal.value = true;
}

async function saveAssignment() {
    if (!assigningLead.value || !selectedSalesId.value) return;
    assigning.value = true;
    assignError.value = '';
    try {
        await api.update('leads', assigningLead.value.id, {
            assigned_to: selectedSalesId.value,
            assigned_at: new Date().toISOString(),
        });
        showAssignModal.value = false;
        loadCounts();
        loadLeads(pagination.value.current_page);
    } catch (err) {
        assignError.value = err.message || 'Gagal menugaskan lead ke sales.';
    } finally {
        assigning.value = false;
    }
}

function formatDate(dtStr) {
    if (!dtStr) return '-';
    const d = new Date(dtStr);
    if (isNaN(d.getTime())) return dtStr;
    return d.toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    });
}

function getDateKey(dtStr) {
    if (!dtStr) return 'tanpa-tanggal';
    const d = new Date(dtStr);
    if (isNaN(d.getTime())) return dtStr;
    const y = d.getFullYear();
    const m = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    return `${y}-${m}-${day}`;
}

function isDifferentDate(index) {
    if (index === 0) return true;
    const prevDate = getDateKey(leads.value[index - 1]?.created_at);
    const currDate = getDateKey(leads.value[index]?.created_at);
    return prevDate !== currDate;
}

function formatDateHeader(dtStr) {
    if (!dtStr) return 'Tanpa Tanggal';
    const d = new Date(dtStr);
    if (isNaN(d.getTime())) return dtStr;
    return d.toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });
}

function formatTime(dtStr) {
    if (!dtStr) return '-';
    const d = new Date(dtStr);
    if (isNaN(d.getTime())) return '-';
    return d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
}

function countLeadsOnDate(dtStr) {
    const targetKey = getDateKey(dtStr);
    return leads.value.filter(l => getDateKey(l.created_at) === targetKey).length;
}

// ── Print Modal State & Handlers ──
const showPrintModal = ref(false);
const printFilterDate = ref('');
const printFilterStatus = ref('');
const printFilterSales = ref('');
const printOrientation = ref('landscape');
const allLeadsForPrint = ref([]);
const loadingPrintData = ref(false);

async function openPrintModal() {
    printFilterDate.value = dateFilter.value || '';
    printFilterStatus.value = statusFilter.value || '';
    printFilterSales.value = assignedFilter.value || (isSales.value ? String(props.user?.id || '') : '');
    showPrintModal.value = true;
    await fetchPrintLeads();
}

function closePrintModal() {
    showPrintModal.value = false;
}

async function fetchPrintLeads() {
    loadingPrintData.value = true;
    try {
        const params = { per_page: '500' };
        if (isSales.value && props.user?.id) {
            params.assigned = String(props.user.id);
        } else if (printFilterSales.value) {
            params.assigned = printFilterSales.value;
        }
        if (printFilterDate.value) {
            params.date = printFilterDate.value;
        }
        if (printFilterStatus.value) {
            params.status = printFilterStatus.value;
        }
        const res = await api.list('leads', params);
        allLeadsForPrint.value = res.data || [];
    } catch (e) {
        console.error('Gagal mengambil data cetak lead:', e);
    } finally {
        loadingPrintData.value = false;
    }
}

const printLeadsList = computed(() => {
    let list = allLeadsForPrint.value;
    if (printFilterDate.value) {
        list = list.filter(l => getDateKey(l.created_at) === printFilterDate.value);
    }
    if (printFilterStatus.value) {
        list = list.filter(l => l.status === printFilterStatus.value);
    }
    if (printFilterSales.value) {
        list = list.filter(l => String(l.assigned_to) === String(printFilterSales.value));
    }
    return list;
});

const printFilterDateFormatted = computed(() => {
    if (!printFilterDate.value) return 'Semua Tanggal';
    const parts = printFilterDate.value.split('-');
    if (parts.length === 3) {
        const d = new Date(printFilterDate.value);
        return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
    }
    return printFilterDate.value;
});

const todayFormatted = computed(() => {
    return new Date().toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
    });
});

const reportDocNumber = computed(() => {
    const now = new Date();
    const yr = now.getFullYear();
    const mo = String(now.getMonth() + 1).padStart(2, '0');
    return `RDL-DISTY/${yr}${mo}/001`;
});

function printDocument() {
    const printArea = document.getElementById('print-area');
    if (!printArea) {
        window.print();
        return;
    }

    // Clean up any previously created print iframe
    const prevFrame = document.getElementById('print-iframe-isolated');
    if (prevFrame) {
        prevFrame.remove();
    }

    const isLandscape = printOrientation.value === 'landscape';

    const iframe = document.createElement('iframe');
    iframe.id = 'print-iframe-isolated';
    iframe.style.position = 'fixed';
    iframe.style.top = '-9999px';
    iframe.style.left = '-9999px';
    iframe.style.width = isLandscape ? '297mm' : '210mm';
    iframe.style.height = isLandscape ? '210mm' : '297mm';
    iframe.style.border = 'none';
    iframe.style.opacity = '0';
    iframe.style.pointerEvents = 'none';
    document.body.appendChild(iframe);

    // Extract all styles and links from the parent document
    const headNodes = Array.from(document.querySelectorAll('link[rel="stylesheet"], style')).map(n => n.outerHTML).join('\n');

    const pageRule = isLandscape
        ? '@page { size: A4 landscape; size: landscape; margin: 8mm 10mm 8mm 10mm; }'
        : '@page { size: A4 portrait; size: portrait; margin: 8mm 8mm 8mm 8mm; }';

    const iframeDoc = iframe.contentWindow.document;
    iframeDoc.open();
    iframeDoc.write(`
        <!DOCTYPE html>
        <html lang="id">
        <head>
            <meta charset="utf-8">
            <base href="${window.location.origin}/">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Rekapitulasi Data Lead / Prospek - Disty Akademi</title>
            <link rel="preconnect" href="https://fonts.googleapis.com">
            <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
            <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
            <script src="https://cdn.tailwindcss.com"><\/script>
            ${headNodes}
            <style>
                ${pageRule}
                *, *::before, *::after {
                    box-sizing: border-box !important;
                    -webkit-print-color-adjust: exact !important;
                    print-color-adjust: exact !important;
                }
                html, body {
                    width: 100% !important;
                    height: auto !important;
                    min-height: auto !important;
                    max-height: none !important;
                    overflow: visible !important;
                    margin: 0 !important;
                    padding: 0 !important;
                    background: #ffffff !important;
                    color: #111827 !important;
                    font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
                    font-size: 10px !important;
                }
                #print-sheet-root {
                    width: 100% !important;
                    max-width: 100% !important;
                    margin: 0 !important;
                    padding: 0 !important;
                    background: #ffffff !important;
                }
                table {
                    width: 100% !important;
                    max-width: 100% !important;
                    border-collapse: collapse !important;
                    table-layout: fixed !important;
                }
                thead {
                    display: table-header-group !important;
                }
                tfoot {
                    display: table-footer-group !important;
                }
                tr {
                    page-break-inside: avoid !important;
                    break-inside: avoid !important;
                }
                th, td {
                    box-sizing: border-box !important;
                    vertical-align: middle !important;
                }
                .whitespace-nowrap,
                th.whitespace-nowrap,
                td.whitespace-nowrap,
                .no-break {
                    white-space: nowrap !important;
                    word-break: keep-all !important;
                    overflow-wrap: normal !important;
                    word-wrap: normal !important;
                }
                .signature-section {
                    page-break-inside: avoid !important;
                    break-inside: avoid !important;
                    margin-top: 24px !important;
                }
            </style>
        </head>
        <body class="bg-white text-gray-900 p-0 m-0">
            <div id="print-sheet-root">
                ${printArea.innerHTML}
            </div>
        </body>
        </html>
    `);
    iframeDoc.close();

    const triggerPrint = () => {
        try {
            iframe.contentWindow.focus();
            iframe.contentWindow.print();
        } catch (err) {
            console.error('Iframe print failed, falling back to window.print', err);
            window.print();
        } finally {
            setTimeout(() => {
                iframe.remove();
            }, 3000);
        }
    };

    // Give iframe document time to settle and images to load
    const imgs = Array.from(iframeDoc.images || []);
    if (imgs.length > 0) {
        let loaded = 0;
        const total = imgs.length;
        const checkDone = () => {
            loaded++;
            if (loaded >= total) {
                setTimeout(triggerPrint, 120);
            }
        };
        imgs.forEach(img => {
            if (img.complete) {
                checkDone();
            } else {
                img.onload = checkDone;
                img.onerror = checkDone;
            }
        });
        setTimeout(triggerPrint, 700);
    } else {
        setTimeout(triggerPrint, 250);
    }
}

function getStatusLabel(status) {
    const map = {
        new: 'Baru',
        contacted: 'Dikontak',
        active: 'Aktif',
        negotiation: 'Negosiasi',
        won: 'Closing',
        lost: 'Ditolak',
        inactive: 'Nonaktif',
    };
    return map[status] || status || '-';
}

function getStatusBadgeClass(status) {
    const map = {
        new: 'bg-blue-50 text-blue-700 border-blue-300',
        contacted: 'bg-purple-50 text-purple-700 border-purple-300',
        active: 'bg-sky-50 text-sky-700 border-sky-300',
        negotiation: 'bg-amber-50 text-amber-800 border-amber-300',
        won: 'bg-emerald-50 text-emerald-800 border-emerald-400 font-bold',
        lost: 'bg-red-50 text-red-700 border-red-300',
        inactive: 'bg-gray-50 text-gray-700 border-gray-300',
    };
    return map[status] || 'bg-gray-50 text-gray-700 border-gray-300';
}

onMounted(() => {
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('status')) {
        statusFilter.value = urlParams.get('status');
    }
    if (urlParams.get('date')) {
        dateFilter.value = urlParams.get('date');
    }
    if (urlParams.get('search')) {
        search.value = urlParams.get('search');
    }
    loadCounts();
    loadSalesUsers();
    loadLeads();
});
</script>

<template>
    <div>
        <!-- Main Page Content (Hidden during Print) -->
        <div class="no-print space-y-4">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Manajemen Leads</h1>
                <p class="text-xs text-gray-500 mt-0.5">Kelola prospek dan calon peserta pelatihan (Aturan: 1 Lead = 1 Sales Eksklusif)</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <!-- Tombol Cetak Rekapitulasi Data Lead (Sales & Admin) -->
                <button type="button" @click="openPrintModal"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-white bg-orange-500 rounded-xl hover:bg-orange-600 shadow-sm transition-all"
                    title="Cetak Rekapitulasi Data Lead / Prospek">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    Cetak Daftar Lead
                </button>

                <!-- Tombol Khusus Sales: Import Excel Leads & Tambah Lead -->
                <template v-if="isSales">
                    <button type="button" @click="emit('navigate', '/lead-imports/create')"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-white bg-emerald-600 rounded-xl hover:bg-emerald-700 shadow-2xs transition-all"
                        title="Upload file Excel data lead Anda">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                        </svg>
                        Import Excel Leads
                    </button>

                    <button type="button" @click="emit('navigate', '/leads/create')"
                        class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-semibold text-white bg-orange-500 rounded-xl hover:bg-orange-600 shadow-sm transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Tambah Lead
                    </button>
                </template>
            </div>
        </div>

        <!-- Tabs Kepemilikan Lead -->
        <div class="flex items-center gap-2 overflow-x-auto pb-1 border-b border-gray-200 text-xs">
            <button v-if="isSales" @click="setScope('my_leads')"
                class="px-3.5 py-2 font-medium rounded-lg transition-colors whitespace-nowrap flex items-center gap-1.5"
                :class="scopeTab === 'my_leads' ? 'bg-orange-500 text-white shadow-xs' : 'text-gray-600 hover:bg-gray-100'">
                <span>Lead Saya</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px]"
                    :class="scopeTab === 'my_leads' ? 'bg-white/25 text-white' : 'bg-gray-200 text-gray-700'">
                    {{ myLeadsCount }}
                </span>
            </button>

            <button @click="setScope('unassigned')"
                class="px-3.5 py-2 font-medium rounded-lg transition-colors whitespace-nowrap flex items-center gap-1.5"
                :class="scopeTab === 'unassigned' ? 'bg-orange-500 text-white shadow-xs' : 'text-gray-600 hover:bg-gray-100'">
                <span>Belum Ditugaskan (Pool Lead)</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px]"
                    :class="scopeTab === 'unassigned' ? 'bg-white/25 text-white' : 'bg-amber-100 text-amber-800'">
                    {{ unassignedCount }}
                </span>
            </button>

            <button @click="setScope('lost_pool')"
                class="px-3.5 py-2 font-medium rounded-lg transition-colors whitespace-nowrap flex items-center gap-1.5"
                :class="scopeTab === 'lost_pool' ? 'bg-orange-500 text-white shadow-xs' : 'text-gray-600 hover:bg-gray-100'">
                <span>Lost / Siap Follow-up Ulang</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px]"
                    :class="scopeTab === 'lost_pool' ? 'bg-white/25 text-white' : 'bg-red-100 text-red-800'">
                    {{ lostPoolCount }}
                </span>
            </button>

            <button @click="setScope('all')"
                class="px-3.5 py-2 font-medium rounded-lg transition-colors whitespace-nowrap flex items-center gap-1.5"
                :class="scopeTab === 'all' ? 'bg-orange-500 text-white shadow-xs' : 'text-gray-600 hover:bg-gray-100'">
                <span>Semua Lead</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px]"
                    :class="scopeTab === 'all' ? 'bg-white/25 text-white' : 'bg-gray-200 text-gray-700'">
                    {{ totalLeadsCount }}
                </span>
            </button>
        </div>

        <!-- Filter Otomatis (Tanpa Tombol Bold Oranye) -->
        <div class="bg-white border border-gray-200 p-3 shadow-xs">
            <div class="grid grid-cols-1 sm:grid-cols-2" :class="isAdmin ? 'lg:grid-cols-4' : 'lg:grid-cols-3'" style="gap: 10px;">
                <!-- 1. Filter Tanggal Input -->
                <div>
                    <label class="block text-[10.5px] font-semibold text-gray-500 mb-1">Tanggal Input</label>
                    <input v-model="dateFilter" type="date" @change="handleFilter"
                        class="w-full text-xs border border-gray-200 px-3 py-2 text-gray-700 bg-white focus:outline-none focus:border-orange-500" />
                </div>

                <!-- 2. Cari Data Prospek -->
                <div>
                    <label class="block text-[10.5px] font-semibold text-gray-500 mb-1">Cari Prospek</label>
                    <input v-model="search" @input="debounceSearch" type="text" placeholder="Nama, email, HP, instansi..."
                        class="w-full text-xs border border-gray-200 px-3 py-2 text-gray-800 placeholder-gray-400 focus:outline-none focus:border-orange-500" />
                </div>

                <!-- 3. Status Prospek -->
                <div>
                    <label class="block text-[10.5px] font-semibold text-gray-500 mb-1">Status Prospek</label>
                    <select v-model="statusFilter" @change="handleFilter" class="w-full text-xs border border-gray-200 px-3 py-2 text-gray-700 focus:outline-none focus:border-orange-500 bg-white">
                        <option value="">Semua Status</option>
                        <option value="new">Calon Pelanggan (Baru)</option>
                        <option value="contacted">Sudah Ada Respon</option>
                        <option value="active">Lead Aktif</option>
                        <option value="negotiation">Negosiasi</option>
                        <option value="won">Closing (Berhasil)</option>
                        <option value="lost">Lost / Ditolak</option>
                        <option value="inactive">Tidak Aktif</option>
                    </select>
                </div>

                <!-- 4. Sales PIC (Khusus Admin) -->
                <div v-if="isAdmin">
                    <div class="flex items-center justify-between mb-1">
                        <label class="block text-[10.5px] font-semibold text-gray-500">Sales PIC</label>
                        <button v-if="dateFilter || search || statusFilter || assignedFilter" @click="resetFilter" type="button" class="text-[10.5px] text-gray-400 hover:text-red-600 underline">
                            Reset
                        </button>
                    </div>
                    <select v-model="assignedFilter" @change="handleFilter" class="w-full text-xs border border-gray-200 px-3 py-2 text-gray-700 focus:outline-none focus:border-orange-500 bg-white">
                        <option value="">Semua Sales PIC</option>
                        <option v-for="s in salesUsers" :key="s.id" :value="s.id">{{ s.name }}</option>
                    </select>
                </div>
            </div>

            <!-- Tombol Reset Cepat jika Sales dan Filter Sedang Aktif -->
            <div v-if="!isAdmin && (dateFilter || search || statusFilter)" class="pt-2 mt-2 border-t border-gray-100 flex items-center justify-between text-xs">
                <span class="text-[11px] text-gray-400">Filter sedang aktif</span>
                <button @click="resetFilter" type="button" class="text-[11px] text-red-600 hover:underline">
                    Reset Filter
                </button>
            </div>
        </div>

        <!-- Leads Table -->
        <div class="bg-white border border-gray-200 overflow-hidden shadow-xs">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs text-gray-600 min-w-[700px]">
                    <thead class="bg-gray-50/70 border-b border-gray-100 text-gray-500 font-semibold uppercase tracking-wider text-[10.5px]">
                        <tr>
                            <th class="py-3.5 px-4">Calon Pelanggan / Lead</th>
                            <th class="py-3.5 px-4">Kontak</th>
                            <th class="py-3.5 px-4">Program Minat</th>
                            <th class="py-3.5 px-4">Sales Penanggung Jawab</th>
                            <th class="py-3.5 px-4">Status Prospek</th>
                            <th class="py-3.5 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-if="loading">
                            <td colspan="6" class="p-8 text-center text-gray-400">Memuat data lead...</td>
                        </tr>
                        <tr v-else-if="leads.length === 0">
                            <td colspan="6" class="p-8 text-center text-gray-400">
                                <span v-if="search">Tidak ditemukan calon pelanggan dengan kata kunci "<strong>{{ search }}</strong>".</span>
                                <span v-else>Tidak ada calon pelanggan ditemukan.</span>
                            </td>
                        </tr>
                        <template v-for="(lead, index) in leads" :key="lead.id">
                            <!-- Pembatas Tanggal Minimalis -->
                            <tr v-if="isDifferentDate(index)" class="bg-gray-50/75 border-t border-b border-gray-200">
                                <td colspan="6" class="py-2 px-4">
                                    <div class="flex items-center justify-between text-xs">
                                        <span class="font-semibold text-gray-700 tracking-tight">
                                            {{ formatDateHeader(lead.created_at) }}
                                        </span>
                                        <span class="text-[11px] text-gray-400 font-normal">
                                            {{ countLeadsOnDate(lead.created_at) }} prospek
                                        </span>
                                    </div>
                                </td>
                            </tr>

                            <!-- Baris Data Prospek -->
                            <tr class="hover:bg-orange-50/20 transition-colors">
                                <!-- Calon Pelanggan / Lead -->
                                <td class="py-3 px-4">
                                    <div class="flex items-center gap-2">
                                        <button @click="emit('navigate', `/leads/${lead.id}`)" class="font-semibold text-gray-900 hover:text-orange-600 transition-colors text-left">
                                            {{ lead.name }}
                                        </button>
                                    </div>
                                    <div class="flex items-center gap-1.5 text-[11px] text-gray-400 mt-0.5">
                                        <span class="font-mono text-gray-500">{{ lead.lead_code }}</span>
                                        <template v-if="lead.company || lead.institution">
                                            <span class="text-gray-300">•</span>
                                            <span class="font-sans truncate max-w-[180px]">{{ lead.company || lead.institution }}</span>
                                        </template>
                                        <span class="text-gray-300">•</span>
                                        <span>{{ formatTime(lead.created_at) }} WIB</span>
                                    </div>
                                </td>

                            <!-- Kontak -->
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-1 font-medium text-gray-800">
                                    <template v-if="lead.whatsapp || lead.phone">
                                        <span class="text-emerald-600 font-bold text-[10px]">WA</span>
                                        <span>{{ lead.whatsapp || lead.phone }}</span>
                                    </template>
                                    <span v-else class="text-gray-400">-</span>
                                </div>
                                <div class="text-[11px] text-gray-400 truncate max-w-[160px]">{{ lead.email || '-' }}</div>
                            </td>

                            <!-- Program Minat -->
                            <td class="py-3.5 px-4">
                                <span class="font-medium text-gray-900 block">{{ lead.product?.name || lead.target_program || 'Belum memilih' }}</span>
                                <div v-if="lead.deal_price" class="text-[11px] text-orange-600 font-semibold mt-0.5">
                                    Total Transfer: Rp {{ formatRupiah(lead.deal_price) }}
                                    <span v-if="lead.product?.price" class="text-[10px] text-gray-400 font-normal ml-1">
                                        (Katalog Rp {{ formatRupiah(lead.product.price) }})
                                    </span>
                                </div>
                                <div v-else-if="lead.product?.price" class="text-[11px] text-gray-500 block mt-0.5">
                                    Total Transfer: Rp {{ formatRupiah(Math.round(lead.product.price * 1.10)) }}
                                    <span class="text-[10px] text-gray-400 block">(Katalog Rp {{ formatRupiah(lead.product.price) }})</span>
                                </div>
                                <div class="text-[10px] text-emerald-600 font-medium">
                                    Komisi 10%: Rp {{ formatRupiah(lead.product?.price ? Math.round(lead.product.price * 0.10) : (lead.deal_price ? Math.round(lead.deal_price * 0.10) : 0)) }}
                                </div>
                            </td>

                            <!-- Sales Penanggung Jawab -->
                            <td class="py-3.5 px-4">
                                <div v-if="!lead.assigned_to" class="flex items-center gap-1.5">
                                    <span class="inline-flex items-center px-2 py-0.5 text-[10px] font-medium bg-amber-50 text-amber-700 border border-amber-200 rounded">
                                        Belum Ada PIC
                                    </span>
                                    <button v-if="isAdmin" @click="openAssignModal(lead)"
                                        class="px-2 py-0.5 text-[10px] font-semibold text-orange-700 bg-orange-50 hover:bg-orange-100 border border-orange-200 rounded transition"
                                        title="Tugaskan lead ini ke salah satu sales">
                                        + Tugaskan
                                    </button>
                                </div>
                                <div v-else class="inline-flex items-center gap-1.5">
                                    <span class="w-5 h-5 rounded-full bg-gray-200 text-gray-700 flex items-center justify-center text-[10px] font-bold">
                                        {{ ((lead.assigned_user?.name || lead.sales?.name || '?').charAt(0)).toUpperCase() }}
                                    </span>
                                    <span class="font-medium text-gray-700 text-xs">{{ lead.assigned_user?.name || lead.sales?.name || '-' }}</span>
                                    <button v-if="isAdmin" @click="openAssignModal(lead)"
                                        class="text-[10px] text-gray-400 hover:text-orange-600 ml-0.5"
                                        title="Ganti Sales PIC">
                                        ✎
                                    </button>
                                </div>
                            </td>

                            <!-- Status Prospek -->
                            <td class="py-3.5 px-4">
                                <Badge :status="lead.status" />
                            </td>

                            <!-- Aksi -->
                            <td class="py-3.5 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button @click="emit('navigate', `/leads/${lead.id}`)"
                                        class="p-1.5 text-gray-400 hover:text-orange-600 rounded-lg hover:bg-orange-50 transition-colors"
                                        title="Detail">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </button>
                                    <button @click="emit('navigate', `/leads/${lead.id}/edit`)"
                                        class="p-1.5 text-gray-400 hover:text-orange-600 rounded-lg hover:bg-orange-50 transition-colors"
                                        title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        </template>
                    </tbody>
                </table>
            </div>
            <div v-if="pagination.total > 0" class="p-3 border-t border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-2 text-xs text-gray-500">
                    <span>Tampilkan</span>
                    <select v-model="perPage" @change="loadLeads(1)" class="border border-gray-200 px-2.5 py-1 text-xs text-gray-700 bg-white focus:outline-none focus:border-orange-500">
                        <option value="20">20</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
                <Pagination :pagination="pagination" @page="loadLeads" />
            </div>
        </div>
        </div>

        <!-- Modal Penugasan Sales PIC (Admin Only) -->
        <div v-if="showAssignModal" class="no-print fixed inset-0 z-50 bg-black/40 flex items-center justify-center p-4">
            <div class="bg-white max-w-sm w-full p-5 shadow-xl border border-gray-100 space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <h3 class="text-sm font-bold text-gray-900">Tugaskan Prospek ke Sales PIC</h3>
                    <button @click="showAssignModal = false" class="text-gray-400 hover:text-gray-600 text-lg">&times;</button>
                </div>

                <div v-if="assignError" class="p-2.5 bg-red-50 text-red-600 text-xs border border-red-200">
                    {{ assignError }}
                </div>

                <div v-if="assigningLead" class="p-3 bg-gray-50 border border-gray-100 text-xs space-y-1">
                    <span class="text-gray-400 block text-[11px]">Prospek</span>
                    <div class="font-bold text-gray-900">{{ assigningLead.name }}</div>
                    <div class="text-gray-500 text-[11px]">{{ assigningLead.company || assigningLead.institution || 'Individu' }} &bull; {{ assigningLead.phone || '-' }}</div>
                </div>

                <form @submit.prevent="saveAssignment" class="space-y-3.5 text-xs">
                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">Pilih Sales Penanggung Jawab</label>
                        <select v-model="selectedSalesId" required class="w-full px-3 py-2 border border-gray-200 bg-white">
                            <option value="" disabled>Pilih Sales</option>
                            <option v-for="s in salesUsers" :key="s.id" :value="s.id">{{ s.name }} ({{ s.email }})</option>
                        </select>
                    </div>

                    <div class="pt-2 flex justify-end gap-2 border-t border-gray-100">
                        <button type="button" @click="showAssignModal = false"
                            class="px-3.5 py-2 border border-gray-200 rounded-xl font-semibold text-gray-600 hover:bg-gray-50">
                            Batal
                        </button>
                        <button type="submit" :disabled="assigning"
                            class="px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white font-bold rounded-xl disabled:opacity-50 transition">
                            <span v-if="assigning">Menugaskan...</span>
                            <span v-else>Simpan Penugasan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ── MODAL CETAK REKAPITULASI DATA LEAD ── -->
        <div v-if="showPrintModal" class="print-modal-wrapper fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-6 print:static print:block print:bg-white print:overflow-visible print:inset-auto print:p-0">
            <div class="print-modal-container bg-white w-full max-h-[92vh] overflow-y-auto shadow-2xl flex flex-col border border-gray-200 print:static print:block print:max-h-none print:h-auto print:my-0 print:border-none print:shadow-none print:overflow-visible print:max-w-none transition-all"
                :class="printOrientation === 'landscape' ? 'max-w-6xl' : 'max-w-4xl'">
                <!-- Header Modal (Hidden saat print) -->
                <div class="p-3.5 sm:p-4 border-b border-gray-200 flex flex-wrap items-center justify-between gap-3 bg-white no-print">
                    <div>
                        <h3 class="text-sm font-bold text-gray-900">Cetak Rekapitulasi Data Lead / Prospek</h3>
                        <p class="text-[11px] text-gray-500">Pratinjau lembar data resmi prospek Disty Akademi</p>
                    </div>

                    <!-- Filter Bar & Orientasi di Header Modal -->
                    <div class="flex flex-wrap items-center gap-2">
                        <!-- Pilihan Orientasi Cetak -->
                        <div class="flex items-center gap-1 bg-gray-100 p-0.5 border border-gray-300">
                            <button type="button" @click="printOrientation = 'landscape'"
                                :class="printOrientation === 'landscape' ? 'bg-orange-500 text-white font-bold shadow-xs' : 'text-gray-600 hover:text-gray-900'"
                                class="px-2.5 py-1 text-[11px] transition-colors">
                                Lanskap (Rekomendasi)
                            </button>
                            <button type="button" @click="printOrientation = 'portrait'"
                                :class="printOrientation === 'portrait' ? 'bg-orange-500 text-white font-bold shadow-xs' : 'text-gray-600 hover:text-gray-900'"
                                class="px-2.5 py-1 text-[11px] transition-colors">
                                Potret
                            </button>
                        </div>

                        <div class="flex items-center gap-1.5">
                            <span class="text-xs text-gray-500 font-medium">Tanggal:</span>
                            <input type="date" v-model="printFilterDate" @change="fetchPrintLeads"
                                class="text-xs border border-gray-300 py-1 px-2 text-gray-800 focus:outline-none focus:border-orange-500 bg-white" />
                            <button v-if="printFilterDate" @click="printFilterDate = ''; fetchPrintLeads()" type="button"
                                class="text-[10px] text-gray-500 hover:text-gray-800 underline">Semua</button>
                        </div>

                        <select v-model="printFilterStatus" @change="fetchPrintLeads"
                            class="text-xs border border-gray-300 py-1 px-2 text-gray-800 focus:outline-none focus:border-orange-500 bg-white">
                            <option value="">Semua Status</option>
                            <option value="new">Baru</option>
                            <option value="contacted">Dikontak</option>
                            <option value="active">Aktif</option>
                            <option value="negotiation">Negosiasi</option>
                            <option value="won">Berhasil (Closing)</option>
                            <option value="lost">Ditolak</option>
                            <option value="inactive">Tidak Aktif</option>
                        </select>

                        <select v-if="isAdmin" v-model="printFilterSales" @change="fetchPrintLeads"
                            class="text-xs border border-gray-300 py-1 px-2 text-gray-800 focus:outline-none focus:border-orange-500 bg-white">
                            <option value="">Semua Sales</option>
                            <option v-for="s in salesUsers" :key="s.id" :value="s.id">{{ s.name }}</option>
                        </select>

                        <button @click="printDocument" class="px-3.5 py-1.5 text-xs font-semibold text-white bg-orange-500 hover:bg-orange-600 transition-colors shadow-xs">
                            Cetak (PDF)
                        </button>

                        <button @click="closePrintModal" class="px-3 py-1.5 text-xs font-medium text-gray-600 bg-white border border-gray-300 hover:bg-gray-100 transition-colors">
                            Tutup
                        </button>
                    </div>
                </div>

                <!-- Lembar Dokumen Cetak (Print Area) -->
                <div class="flex-1 overflow-y-auto p-4 sm:p-8 bg-gray-50 print:bg-white print:p-0">
                    <div id="print-area" class="bg-white p-6 sm:p-8 border border-gray-200 shadow-sm mx-auto text-gray-900 print:border-none print:shadow-none print:p-0 print:max-w-none"
                        :class="printOrientation === 'landscape' ? 'w-full max-w-5xl' : 'w-full max-w-4xl'">
                        <!-- KOP SURAT RESMI DISTY AKADEMI -->
                        <div class="border-b-4 border-double border-gray-800 pb-4 mb-5">
                            <div class="flex items-center justify-between gap-4">
                                <div class="flex items-center gap-3.5">
                                    <img src="/images/disty-logo.png" alt="Disty Akademi" class="h-12 w-auto max-w-[130px] object-contain shrink-0" onerror="this.style.display='none'; if (this.nextElementSibling) this.nextElementSibling.style.display='flex';" />
                                    <div class="hidden w-12 h-12 bg-orange-500 text-white font-black text-2xl items-center justify-center rounded-sm shrink-0">D</div>
                                    <div>
                                        <h1 class="text-xl font-black tracking-wider text-gray-900 uppercase leading-none mb-1">DISTY AKADEMI</h1>
                                        <p class="text-[11px] font-semibold text-gray-700 tracking-wide leading-tight mb-0.5">LEMBAGA PELATIHAN KERJA & PENGEMBANGAN SDM PROFESIONAL</p>
                                        <p class="text-[10px] text-gray-500 leading-tight">Jl. Raya Rungkut Madya No. 1, Gunung Anyar, Surabaya, Jawa Timur 60294</p>
                                    </div>
                                </div>
                                <div class="text-right text-[10px] text-gray-600 leading-tight border-l border-gray-300 pl-4 block shrink-0">
                                    <p><span class="font-semibold text-gray-800">Email:</span> info@distyakademi.com</p>
                                    <p><span class="font-semibold text-gray-800">Website:</span> www.distyakademi.com</p>
                                    <p><span class="font-semibold text-gray-800">Telepon:</span> (031) 878-2233</p>
                                </div>
                            </div>
                        </div>

                        <!-- JUDUL DOKUMEN -->
                        <div class="text-center mb-4">
                            <h2 class="text-base font-black uppercase tracking-wider text-gray-900 underline underline-offset-4">
                                REKAPITULASI DATA PROSPEK & CALON PESERTA (LEADS CRM)
                            </h2>
                            <p class="text-[11px] text-gray-600 mt-1">
                                Lampiran Resmi Pengelolaan Prospek dan Penugasan Tim Sales Disty Akademi
                            </p>
                        </div>

                        <!-- METADATA DOKUMEN -->
                        <div class="border border-gray-300 bg-gray-50/70 p-3 mb-4 text-[11px] grid grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <div><span class="text-gray-500 font-medium">Nomor Dokumen:</span> <span class="font-bold text-gray-900 ml-1">{{ reportDocNumber }}</span></div>
                                <div><span class="text-gray-500 font-medium">Tanggal Cetak:</span> <span class="font-semibold text-gray-900 ml-1">{{ todayFormatted }}</span></div>
                                <div><span class="text-gray-500 font-medium">Periode / Tanggal Masuk:</span> <span class="font-bold text-gray-900 ml-1">{{ printFilterDateFormatted }}</span></div>
                                <div><span class="text-gray-500 font-medium">Dicetak Oleh:</span> <span class="font-semibold text-gray-900 ml-1">{{ props.user?.name || 'Administrator CRM' }} ({{ isSales ? 'Sales' : 'Admin' }})</span></div>
                            </div>
                            <div class="space-y-1 text-right">
                                <div>
                                    <span class="text-gray-500 font-medium">Status Data:</span>
                                    <span class="font-bold uppercase ml-1 px-2 py-0.5 text-[10px] bg-gray-200 text-gray-800 border border-gray-300">
                                        {{ printFilterStatus ? getStatusLabel(printFilterStatus) : 'SEMUA STATUS' }}
                                    </span>
                                </div>
                                <div><span class="text-gray-500 font-medium">Jumlah Prospek:</span> <span class="font-bold text-gray-900 ml-1">{{ printLeadsList.length }} Prospek</span></div>
                                <div v-if="printFilterSales"><span class="text-gray-500 font-medium">Sales PIC:</span> <span class="font-bold text-gray-900 ml-1">{{ salesUsers.find(s => String(s.id) === String(printFilterSales))?.name || '-' }}</span></div>
                                <div><span class="text-gray-500 font-medium">Format Kertas:</span> <span class="font-bold text-gray-800 ml-1 uppercase">{{ printOrientation === 'landscape' ? 'A4 Lanskap' : 'A4 Potret' }}</span></div>
                            </div>
                        </div>

                        <!-- TABEL DATA PROSPEK -->
                        <div class="border border-gray-300 mb-5 overflow-hidden">
                            <table class="w-full text-left text-xs border-collapse table-fixed">
                                <colgroup v-if="printOrientation === 'landscape'">
                                    <col style="width: 4%;" />
                                    <col style="width: 10%;" />
                                    <col style="width: 14%;" />
                                    <col style="width: 23%;" />
                                    <col style="width: 15%;" />
                                    <col style="width: 16%;" />
                                    <col style="width: 11%;" />
                                    <col style="width: 7%;" />
                                </colgroup>
                                <colgroup v-else>
                                    <col style="width: 5%;" />
                                    <col style="width: 11%;" />
                                    <col style="width: 16%;" />
                                    <col style="width: 20%;" />
                                    <col style="width: 15%;" />
                                    <col style="width: 15%;" />
                                    <col style="width: 10%;" />
                                    <col style="width: 8%;" />
                                </colgroup>
                                <thead class="bg-gray-100 border-b-2 border-gray-400 text-gray-800 font-bold uppercase text-[9px] sm:text-[9.5px]">
                                    <tr>
                                        <th class="py-2.5 px-1 border-r border-gray-300 text-center whitespace-nowrap" style="white-space: nowrap !important;">No.</th>
                                        <th class="py-2.5 px-1.5 border-r border-gray-300 text-center whitespace-nowrap" style="white-space: nowrap !important;">Tgl Masuk</th>
                                        <th class="py-2.5 px-1.5 border-r border-gray-300 text-center whitespace-nowrap" style="white-space: nowrap !important;">Kode Lead</th>
                                        <th class="py-2.5 px-2.5 border-r border-gray-300">Nama Prospek / Instansi</th>
                                        <th class="py-2.5 px-2 border-r border-gray-300">Kontak</th>
                                        <th class="py-2.5 px-2 border-r border-gray-300">Program Minat</th>
                                        <th class="py-2.5 px-2 border-r border-gray-300 whitespace-nowrap" style="white-space: nowrap !important;">Sales PIC</th>
                                        <th class="py-2.5 px-1 text-center whitespace-nowrap" style="white-space: nowrap !important;">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="text-[10px]">
                                    <tr v-if="loadingPrintData">
                                        <td colspan="8" class="py-8 text-center text-gray-500 italic border-b border-gray-300">Memuat data prospek...</td>
                                    </tr>
                                    <tr v-else-if="printLeadsList.length === 0">
                                        <td colspan="8" class="py-8 text-center text-gray-500 italic border-b border-gray-300">Tidak ada data lead pada kriteria filter ini.</td>
                                    </tr>
                                    <tr v-for="(lead, idx) in printLeadsList" :key="lead.id" class="hover:bg-gray-50/50">
                                        <td class="py-2 px-1 text-center border-r border-b border-gray-300 font-semibold text-gray-700 whitespace-nowrap text-[10px]" style="white-space: nowrap !important;">
                                            {{ idx + 1 }}
                                        </td>
                                        <td class="py-2 px-1 text-center border-r border-b border-gray-300 text-gray-800 whitespace-nowrap" style="white-space: nowrap !important;">
                                            <span class="font-medium block leading-tight text-gray-900 text-[9.5px] whitespace-nowrap" style="white-space: nowrap !important;">{{ formatDate(lead.created_at) }}</span>
                                            <span class="text-[8.5px] text-gray-500 font-mono block leading-tight mt-0.5 whitespace-nowrap" style="white-space: nowrap !important;">{{ formatTime(lead.created_at) }} WIB</span>
                                        </td>
                                        <td class="py-2 px-1.5 text-center border-r border-b border-gray-300 whitespace-nowrap" style="white-space: nowrap !important;">
                                            <span class="font-mono text-[9px] font-bold text-gray-800 tracking-tight whitespace-nowrap block" style="white-space: nowrap !important; word-break: keep-all !important;">{{ lead.lead_code || '-' }}</span>
                                        </td>
                                        <td class="py-2 px-2.5 border-r border-b border-gray-300">
                                            <p class="font-bold text-gray-900 leading-snug text-[10px] sm:text-[10.5px]">{{ lead.name }}</p>
                                            <p class="text-[9px] text-gray-600 font-medium leading-tight mt-0.5">
                                                {{ lead.company || lead.institution || 'Individu / Umum' }}
                                            </p>
                                        </td>
                                        <td class="py-2 px-2 border-r border-b border-gray-300">
                                            <p class="font-mono font-bold text-gray-900 whitespace-nowrap text-[9.5px] leading-tight" style="white-space: nowrap !important; word-break: keep-all !important;">
                                                {{ lead.whatsapp || lead.phone || '-' }}
                                            </p>
                                            <p v-if="lead.email" class="text-[8.5px] text-gray-500 leading-tight mt-0.5 break-all font-sans">
                                                {{ lead.email }}
                                            </p>
                                        </td>
                                        <td class="py-2 px-2 border-r border-b border-gray-300">
                                            <p class="font-semibold text-gray-800 leading-snug text-[9.5px] sm:text-[10px]">
                                                {{ lead.product?.name || lead.product_name || 'Program Pelatihan' }}
                                            </p>
                                            <p v-if="lead.deal_price" class="text-[9px] text-emerald-700 font-bold leading-tight mt-0.5 whitespace-nowrap">
                                                Rp {{ formatRupiah(lead.deal_price) }}
                                            </p>
                                            <p v-else-if="lead.product?.price" class="text-[9px] text-gray-500 leading-tight mt-0.5 whitespace-nowrap">
                                                Rp {{ formatRupiah(lead.product.price) }}
                                            </p>
                                        </td>
                                        <td class="py-2 px-2 border-r border-b border-gray-300 text-gray-800 whitespace-nowrap font-medium text-[9.5px]" style="white-space: nowrap !important;">
                                            <span class="whitespace-nowrap block" style="white-space: nowrap !important;">{{ lead.assigned_user?.name || lead.sales?.name || 'Belum ada PIC' }}</span>
                                        </td>
                                        <td class="py-2 px-1 border-b border-gray-300 text-center whitespace-nowrap" style="white-space: nowrap !important;">
                                            <span class="inline-block px-1.5 py-0.5 text-[8.5px] font-bold uppercase rounded border whitespace-nowrap"
                                                :class="getStatusBadgeClass(lead.status)" style="white-space: nowrap !important;">
                                                {{ getStatusLabel(lead.status) }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- RINGKASAN DATA -->
                        <div class="border border-gray-300 p-2.5 mb-5 bg-gray-50 flex items-center justify-between text-[11px]">
                            <span class="font-bold text-gray-800">Total Prospek Tercatat: {{ printLeadsList.length }} Orang</span>
                            <div class="flex items-center gap-3 text-gray-600 text-[10px]">
                                <span>Baru: <strong>{{ printLeadsList.filter(l => l.status === 'new').length }}</strong></span>
                                <span>Negosiasi: <strong>{{ printLeadsList.filter(l => l.status === 'negotiation').length }}</strong></span>
                                <span>Closing (Berhasil): <strong class="text-emerald-700 font-bold">{{ printLeadsList.filter(l => l.status === 'won').length }}</strong></span>
                            </div>
                        </div>

                        <!-- TANDA TANGAN / LEGALITAS -->
                        <div class="signature-section pt-4 text-xs">
                            <div class="text-right text-gray-600 text-[11px] mb-4">
                                Surabaya, {{ todayFormatted }}
                            </div>
                            <div class="grid grid-cols-3 gap-6 text-center text-[11px]">
                                <div class="space-y-12">
                                    <p class="font-medium text-gray-600">Dibuat Oleh (Petugas/Sales),</p>
                                    <div>
                                        <p class="font-bold text-gray-900 underline">{{ props.user?.name || 'Tim Sales' }}</p>
                                        <p class="text-[10px] text-gray-500">{{ isSales ? 'Sales Officer' : 'Staff CRM & Administrasi' }}</p>
                                    </div>
                                </div>
                                <div class="space-y-12">
                                    <p class="font-medium text-gray-600">Diverifikasi (Supervisor),</p>
                                    <div>
                                        <p class="font-bold text-gray-900 underline">Supervisor Sales & Marketing</p>
                                        <p class="text-[10px] text-gray-500">Disty Akademi</p>
                                    </div>
                                </div>
                                <div class="space-y-12">
                                    <p class="font-medium text-gray-600">Mengetahui & Menyetujui,</p>
                                    <div>
                                        <p class="font-bold text-gray-900 underline">Direktur Operasional</p>
                                        <p class="text-[10px] text-gray-500">PT Sentra Vidya Utama</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Footer Modal (Hidden saat print) -->
                <div class="p-3.5 sm:p-4 border-t border-gray-200 flex items-center justify-between bg-gray-50/80 no-print">
                    <span class="text-xs text-gray-500">
                        Format cetak dioptimalkan untuk kertas A4 potret atau landscape saat dicetak ke PDF/Printer.
                    </span>
                    <div class="flex items-center gap-2">
                        <button @click="closePrintModal" class="px-4 py-2 text-xs font-medium text-gray-600 bg-white border border-gray-300 hover:bg-gray-100 transition-colors">
                            Tutup
                        </button>
                        <button @click="printDocument" class="px-4 py-2 text-xs font-semibold text-white bg-orange-500 hover:bg-orange-600 transition-colors shadow-xs">
                            Cetak Laporan Sekarang
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style>
@media print {
    @page {
        size: A4 landscape;
        margin: 8mm 10mm 8mm 10mm;
    }

    html,
    body,
    #app,
    [class*="h-screen"],
    [class*="max-h-"],
    [class*="overflow-"],
    #main-content,
    main {
        width: 100% !important;
        height: auto !important;
        min-height: auto !important;
        max-height: none !important;
        overflow: visible !important;
        position: static !important;
        margin: 0 !important;
        padding: 0 !important;
        background: #ffffff !important;
        color: #111827 !important;
        box-shadow: none !important;
        border: none !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    aside,
    header,
    nav,
    .no-print,
    .no-print * {
        display: none !important;
        visibility: hidden !important;
        height: 0 !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    .print-modal-wrapper,
    .print-modal-container {
        display: block !important;
        position: static !important;
        width: 100% !important;
        max-width: 100% !important;
        height: auto !important;
        max-height: none !important;
        overflow: visible !important;
        background: transparent !important;
        box-shadow: none !important;
        border: none !important;
        padding: 0 !important;
        margin: 0 !important;
    }

    #print-area {
        display: block !important;
        position: static !important;
        width: 100% !important;
        max-width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
        background: #ffffff !important;
        color: #111827 !important;
        box-sizing: border-box !important;
    }

    table {
        width: 100% !important;
        max-width: 100% !important;
        border-collapse: collapse !important;
        table-layout: fixed !important;
        page-break-inside: auto !important;
    }

    thead {
        display: table-header-group !important;
    }

    tfoot {
        display: table-footer-group !important;
    }

    tr {
        page-break-inside: avoid !important;
        break-inside: avoid !important;
    }

    th, td {
        box-sizing: border-box !important;
        vertical-align: middle !important;
    }

    .whitespace-nowrap,
    th.whitespace-nowrap,
    td.whitespace-nowrap,
    .no-break {
        white-space: nowrap !important;
        word-break: keep-all !important;
        overflow-wrap: normal !important;
        word-wrap: normal !important;
    }

    .signature-section {
        page-break-inside: avoid !important;
        break-inside: avoid !important;
        margin-top: 1.5rem !important;
    }
}
</style>