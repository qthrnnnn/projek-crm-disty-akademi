<script setup>
import { computed, onMounted, ref, watch } from 'vue';
import { api } from '../../services/api';
import Pagination from '../../Components/Pagination.vue';

const props = defineProps({
    user: { type: Object, default: () => ({}) },
    query: { type: Object, default: () => ({}) },
});

const emit = defineEmits(['navigate']);

const isAdmin = computed(() => props.user?.role === 'admin');
const isSales = computed(() => props.user?.role === 'sales');

const customers = ref([]);
const institutions = ref([]);
const pagination = ref({ current_page: 1, last_page: 1, total: 0 });
const search = ref('');
const statusFilter = ref('');
const institutionFilter = ref('');
const loading = ref(true);

async function loadInstitutions() {
    try {
        const res = await api.list('institutions', { per_page: '100' });
        institutions.value = res.data || [];
    } catch (e) {
        console.error(e);
    }
}

async function loadCustomers(page = 1) {
    loading.value = true;
    try {
        const params = { page: String(page), per_page: '20' };
        if (search.value) params.search = search.value;
        if (statusFilter.value) params.status = statusFilter.value;
        if (institutionFilter.value) params.institution_id = institutionFilter.value;

        const res = await api.list('customers', params);
        customers.value = res.data || [];
        pagination.value = {
            current_page: res.current_page || page,
            last_page: res.last_page || 1,
            total: res.total || customers.value.length,
            from: res.from || 1,
            to: res.to || customers.value.length,
        };
    } catch (err) {
        console.error(err);
    } finally {
        loading.value = false;
    }
}

let searchTimeout = null;
function onSearchInput() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        loadCustomers(1);
    }, 250);
}

function handleFilter() {
    clearTimeout(searchTimeout);
    loadCustomers(1);
}

function resetFilter() {
    clearTimeout(searchTimeout);
    search.value = '';
    statusFilter.value = '';
    institutionFilter.value = '';
    loadCustomers(1);
}

function exportCsv() {
    window.location.href = '/api/customers/export';
}

function downloadTemplate() {
    window.location.href = '/api/customers/template';
}

async function deleteCustomer(id) {
    if (!confirm('Hapus customer ini?')) return;
    try {
        await api.delete('customers', id);
        loadCustomers(pagination.value.current_page);
    } catch (err) {
        alert(err.message || 'Gagal menghapus customer.');
    }
}

// ── Print Modal State & Handlers ──
const showPrintModal = ref(false);
const printFilterDate = ref('');
const printFilterStatus = ref('');
const printFilterInstitution = ref('');
const printOrientation = ref('landscape');
const allCustomersForPrint = ref([]);
const loadingPrintData = ref(false);

async function openPrintModal() {
    printFilterDate.value = '';
    printFilterStatus.value = statusFilter.value || '';
    printFilterInstitution.value = institutionFilter.value || '';
    showPrintModal.value = true;
    await fetchPrintCustomers();
}

function closePrintModal() {
    showPrintModal.value = false;
}

async function fetchPrintCustomers() {
    loadingPrintData.value = true;
    try {
        const params = { per_page: '500' };
        if (printFilterDate.value) {
            params.date = printFilterDate.value;
        }
        if (printFilterStatus.value) {
            params.status = printFilterStatus.value;
        }
        if (printFilterInstitution.value) {
            params.institution_id = printFilterInstitution.value;
        }
        const res = await api.list('customers', params);
        allCustomersForPrint.value = res.data || [];
    } catch (e) {
        console.error('Gagal mengambil data cetak customer:', e);
    } finally {
        loadingPrintData.value = false;
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

function formatTime(dtStr) {
    if (!dtStr) return '';
    const d = new Date(dtStr);
    if (isNaN(d.getTime())) return '';
    return d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', hour12: false }).replace('.', ':');
}

const printCustomersList = computed(() => {
    let list = allCustomersForPrint.value;
    if (printFilterDate.value) {
        list = list.filter(c => {
            if (!c.created_at) return false;
            return c.created_at.startsWith(printFilterDate.value);
        });
    }
    if (printFilterStatus.value) {
        list = list.filter(c => c.status === printFilterStatus.value);
    }
    if (printFilterInstitution.value) {
        list = list.filter(c => String(c.institution_id) === String(printFilterInstitution.value));
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
    return `RDC-DISTY/${yr}${mo}/001`;
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
        ? '@page { size: A4 landscape; margin: 8mm 10mm 8mm 10mm; }'
        : '@page { size: A4 portrait; margin: 8mm 8mm 8mm 8mm; }';

    const iframeDoc = iframe.contentWindow.document;
    iframeDoc.open();
    iframeDoc.write(`
        <!DOCTYPE html>
        <html lang="id">
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Rekapitulasi Data Customer - Disty Akademi</title>
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
                    font-size: 10.5px !important;
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
                    margin-top: 20px !important;
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

    // Trigger print dialog once loaded
    setTimeout(() => {
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
    }, 450);
}

onMounted(() => {
    const urlParams = new URLSearchParams(window.location.search);
    const initialInst = props.query?.institution_id || urlParams.get('institution_id');
    if (initialInst) {
        institutionFilter.value = String(initialInst);
    }
    loadInstitutions();
    loadCustomers();
});

watch(() => props.query?.institution_id, (newInstId) => {
    if (newInstId !== undefined) {
        institutionFilter.value = newInstId ? String(newInstId) : '';
        loadCustomers(1);
    }
});
</script>

<template>
    <div>
        <!-- Main Page Content (Hidden during Print) -->
        <div class="no-print space-y-4">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Daftar Customer</h1>
                <p class="text-xs text-gray-500 mt-0.5">Data pelanggan, peserta pelatihan, dan instansi mitra Disty Akademi.</p>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" @click="openPrintModal"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-white bg-orange-500 rounded-xl hover:bg-orange-600 shadow-sm transition-all"
                    title="Cetak Rekapitulasi Data Customer">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    Cetak Daftar Customer
                </button>
            </div>
        </div>

        <!-- Filter Card -->
        <div class="bg-white border border-blue-100/70 p-3.5 shadow-sm">
            <form @submit.prevent="handleFilter" class="flex flex-col sm:flex-row items-center gap-2.5">
                <div class="relative flex-1 w-full">
                    <input v-model="search" @input="onSearchInput" type="text"
                        placeholder="Cari customer, email, telepon, instansi..."
                        class="w-full text-xs border border-gray-200 px-3 py-2 text-gray-800 placeholder-gray-400 focus:outline-none focus:border-orange-500" />
                </div>
                <div class="w-full sm:w-44">
                    <select v-model="statusFilter" @change="handleFilter" class="w-full text-xs border border-gray-200 px-3 py-2 text-gray-700 focus:outline-none focus:border-orange-500">
                        <option value="">Semua Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div class="w-full sm:w-44">
                    <select v-model="institutionFilter" @change="handleFilter" class="w-full text-xs border border-gray-200 px-3 py-2 text-gray-700 focus:outline-none focus:border-orange-500">
                        <option value="">Semua Instansi</option>
                        <option v-for="inst in institutions" :key="inst.id" :value="inst.id">{{ inst.name }}</option>
                    </select>
                </div>
                <button type="submit" class="w-full sm:w-auto px-4 py-2 text-xs bg-orange-500 text-white font-medium rounded-lg hover:bg-orange-600 transition-colors whitespace-nowrap">
                    Terapkan Filter
                </button>
                <button v-if="search || statusFilter || institutionFilter" @click="resetFilter" type="button" class="px-3 py-2 text-xs text-gray-500 hover:text-gray-700 border border-gray-200 rounded-lg">
                    Reset
                </button>
            </form>
        </div>

        <!-- Table Card -->
        <div class="bg-white border border-blue-100/70 overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[700px] text-left text-xs text-gray-600">
                    <thead class="bg-gray-50/70 border-b border-gray-100 text-gray-400 font-semibold uppercase tracking-wider text-[10.5px]">
                        <tr>
                            <th class="py-3.5 px-4">CUSTOMER</th>
                            <th class="py-3.5 px-4">CUSTOMER CODE</th>
                            <th class="py-3.5 px-4">INSTANSI</th>
                            <th class="py-3.5 px-4">EMAIL</th>
                            <th class="py-3.5 px-4">TELEPON</th>
                            <th class="py-3.5 px-4">STATUS</th>
                            <th class="py-3.5 px-4 text-right">AKSI</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-if="loading">
                            <td colspan="7" class="p-8 text-center text-gray-400">Memuat data customer...</td>
                        </tr>
                        <tr v-else-if="customers.length === 0">
                            <td colspan="7" class="p-8 text-center text-gray-400">
                                <span v-if="search">Tidak ditemukan customer dengan kata kunci "<strong>{{ search }}</strong>".</span>
                                <span v-else>Tidak ada customer ditemukan.</span>
                            </td>
                        </tr>
                        <tr v-for="c in customers" :key="c.id" class="hover:bg-blue-50/30 transition-colors">
                            <td class="py-3.5 px-4 font-semibold text-gray-900">
                                <button @click="emit('navigate', `/customers/${c.id}`)" class="hover:text-orange-600 text-left">
                                    {{ c.name }}
                                </button>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-gray-500 text-[11px]">
                                {{ c.customer_code || '-' }}
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-medium text-gray-900">{{ c.institution?.name || c.company || '-' }}</div>
                                <div v-if="c.institution?.type" class="text-[10.5px] text-gray-400">{{ c.institution.type }}</div>
                            </td>
                            <td class="py-3.5 px-4 text-gray-600">{{ c.email || '-' }}</td>
                            <td class="py-3.5 px-4 text-gray-600">{{ c.phone || '-' }}</td>
                            <td class="py-3.5 px-4">
                                <span class="px-2 py-0.5 text-[10px] font-medium border"
                                    :class="(c.status === 'active' || c.status === 'aktif' || !c.status) ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-gray-100 text-gray-500 border-gray-200'">
                                    {{ (c.status === 'active' || c.status === 'aktif' || !c.status) ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button @click="emit('navigate', `/customers/${c.id}`)" class="p-1.5 text-gray-400 hover:text-orange-600 rounded-lg hover:bg-orange-50 transition-colors" title="Detail">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </button>
                                    <button @click="emit('navigate', `/customers/${c.id}/edit`)" class="p-1.5 text-gray-400 hover:text-orange-600 rounded-lg hover:bg-orange-50 transition-colors" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                    <button v-if="isAdmin" @click="deleteCustomer(c.id)" class="p-1.5 text-gray-400 hover:text-red-600 rounded-lg hover:bg-red-50 transition-colors" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div v-if="pagination.total > 0" class="p-3 border-t border-gray-100 flex items-center justify-between">
                <Pagination :pagination="pagination" @page="loadCustomers" />
            </div>
        </div>
        </div>

        <!-- ── MODAL CETAK REKAPITULASI DAFTAR CUSTOMER ── -->
        <div v-if="showPrintModal" class="print-modal-wrapper fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-xs flex items-center justify-center p-3 sm:p-6 print:static print:block print:bg-white print:overflow-visible print:inset-auto print:p-0">
            <div class="print-modal-container bg-white w-full max-h-[92vh] overflow-y-auto shadow-2xl flex flex-col border border-gray-200 print:static print:block print:max-h-none print:h-auto print:my-0 print:border-none print:shadow-none print:overflow-visible print:max-w-none transition-all"
                :class="printOrientation === 'landscape' ? 'max-w-6xl' : 'max-w-4xl'">
                <!-- Header Modal (Hidden saat print) -->
                <div class="p-3.5 sm:p-4 border-b border-gray-200 flex flex-wrap items-center justify-between gap-3 bg-white no-print">
                    <div>
                        <h3 class="text-sm font-bold text-gray-900">Cetak Rekapitulasi Daftar Customer</h3>
                        <p class="text-[11px] text-gray-500">Pratinjau lembar data resmi pelanggan & peserta Disty Akademi</p>
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
                            <input type="date" v-model="printFilterDate" @change="fetchPrintCustomers"
                                class="text-xs border border-gray-300 py-1 px-2 text-gray-800 focus:outline-none focus:border-orange-500 bg-white" />
                            <button v-if="printFilterDate" @click="printFilterDate = ''; fetchPrintCustomers()" type="button"
                                class="text-[10px] text-gray-500 hover:text-gray-800 underline">Semua</button>
                        </div>

                        <select v-model="printFilterStatus" @change="fetchPrintCustomers"
                            class="text-xs border border-gray-300 py-1 px-2 text-gray-800 focus:outline-none focus:border-orange-500 bg-white">
                            <option value="">Semua Status</option>
                            <option value="active">Active</option>
                            <option value="inactive">Inactive</option>
                        </select>

                        <select v-model="printFilterInstitution" @change="fetchPrintCustomers"
                            class="text-xs border border-gray-300 py-1 px-2 text-gray-800 focus:outline-none focus:border-orange-500 bg-white max-w-[160px]">
                            <option value="">Semua Instansi</option>
                            <option v-for="inst in institutions" :key="inst.id" :value="inst.id">{{ inst.name }}</option>
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
                                REKAPITULASI DAFTAR PELANGGAN & PESERTA PELATIHAN (CUSTOMER CRM)
                            </h2>
                            <p class="text-[11px] text-gray-600 mt-1">
                                Lampiran Resmi Data Peserta dan Instansi Mitra Terdaftar Disty Akademi
                            </p>
                        </div>

                        <!-- METADATA DOKUMEN -->
                        <div class="border border-gray-300 bg-gray-50/70 p-3 mb-4 text-[11px] grid grid-cols-2 gap-4">
                            <div class="space-y-1">
                                <div><span class="text-gray-500 font-medium">Nomor Dokumen:</span> <span class="font-bold text-gray-900 ml-1">{{ reportDocNumber }}</span></div>
                                <div><span class="text-gray-500 font-medium">Tanggal Cetak:</span> <span class="font-semibold text-gray-900 ml-1">{{ todayFormatted }}</span></div>
                                <div><span class="text-gray-500 font-medium">Periode / Tanggal Terdaftar:</span> <span class="font-bold text-gray-900 ml-1">{{ printFilterDateFormatted }}</span></div>
                                <div><span class="text-gray-500 font-medium">Dicetak Oleh:</span> <span class="font-semibold text-gray-900 ml-1">{{ props.user?.name || 'Administrator CRM' }}</span></div>
                            </div>
                            <div class="space-y-1 text-right">
                                <div>
                                    <span class="text-gray-500 font-medium">Status Data:</span>
                                    <span class="font-bold uppercase ml-1 px-2 py-0.5 text-[10px] bg-gray-200 text-gray-800 border border-gray-300">
                                        {{ printFilterStatus ? (printFilterStatus === 'active' ? 'ACTIVE' : 'INACTIVE') : 'SEMUA STATUS' }}
                                    </span>
                                </div>
                                <div><span class="text-gray-500 font-medium">Jumlah Customer:</span> <span class="font-bold text-gray-900 ml-1">{{ printCustomersList.length }} Pelanggan</span></div>
                                <div v-if="printFilterInstitution"><span class="text-gray-500 font-medium">Instansi:</span> <span class="font-bold text-gray-900 ml-1">{{ institutions.find(i => String(i.id) === String(printFilterInstitution))?.name || '-' }}</span></div>
                                <div><span class="text-gray-500 font-medium">Format Kertas:</span> <span class="font-bold text-gray-800 ml-1 uppercase">{{ printOrientation === 'landscape' ? 'A4 Lanskap' : 'A4 Potret' }}</span></div>
                            </div>
                        </div>

                        <!-- TABEL DATA CUSTOMER -->
                        <div class="border border-gray-300 mb-5 overflow-hidden">
                            <table class="w-full text-left text-xs border-collapse table-fixed">
                                <thead class="bg-gray-100 border-b-2 border-gray-400 text-gray-800 font-bold uppercase text-[10px]">
                                    <tr>
                                        <th class="py-2.5 px-1.5 border-r border-gray-300 text-center w-[4%] whitespace-nowrap">No</th>
                                        <th class="py-2.5 px-2 border-r border-gray-300 text-center w-[11%] whitespace-nowrap">Tgl Terdaftar</th>
                                        <th class="py-2.5 px-2 border-r border-gray-300 w-[13%] whitespace-nowrap">Kode Customer</th>
                                        <th class="py-2.5 px-2.5 border-r border-gray-300 w-[26%]">Nama Pelanggan</th>
                                        <th class="py-2.5 px-2.5 border-r border-gray-300 w-[21%]">Instansi / Perusahaan</th>
                                        <th class="py-2.5 px-2 border-r border-gray-300 w-[15%]">Kontak</th>
                                        <th class="py-2.5 px-1.5 text-center w-[10%] border-r border-gray-300 whitespace-nowrap">Status</th>
                                    </tr>
                                </thead>
                                <tbody class="text-[10.5px]">
                                    <tr v-if="loadingPrintData">
                                        <td colspan="7" class="py-8 text-center text-gray-500 italic border-b border-gray-300">Memuat data customer...</td>
                                    </tr>
                                    <tr v-else-if="printCustomersList.length === 0">
                                        <td colspan="7" class="py-8 text-center text-gray-500 italic border-b border-gray-300">Tidak ada data customer pada kriteria filter ini.</td>
                                    </tr>
                                    <tr v-for="(c, idx) in printCustomersList" :key="c.id" class="hover:bg-gray-50/50">
                                        <td class="py-2 px-1.5 text-center border-r border-b border-gray-300 font-semibold text-gray-700 whitespace-nowrap">
                                            {{ idx + 1 }}
                                        </td>
                                        <td class="py-2 px-2 text-center border-r border-b border-gray-300 text-gray-800 whitespace-nowrap font-medium">
                                            <span class="font-medium block leading-tight text-gray-900 text-[9.5px] whitespace-nowrap">{{ formatDate(c.created_at) }}</span>
                                            <span v-if="formatTime(c.created_at)" class="text-[8.5px] text-gray-500 font-mono block leading-tight mt-0.5 whitespace-nowrap">{{ formatTime(c.created_at) }} WIB</span>
                                        </td>
                                        <td class="py-2 px-2 border-r border-b border-gray-300 font-mono text-[9.5px] font-semibold text-gray-700 whitespace-nowrap">
                                            {{ c.customer_code || '-' }}
                                        </td>
                                        <td class="py-2 px-2.5 border-r border-b border-gray-300">
                                            <p class="font-bold text-gray-900 leading-snug">{{ c.name }}</p>
                                            <p v-if="c.nik" class="text-[9px] text-gray-500 font-mono leading-tight mt-0.5">NIK: {{ c.nik }}</p>
                                        </td>
                                        <td class="py-2 px-2.5 border-r border-b border-gray-300 text-gray-800">
                                            <span class="font-medium leading-snug">{{ c.institution?.name || c.company || 'Individu / Umum' }}</span>
                                        </td>
                                        <td class="py-2 px-2 border-r border-b border-gray-300">
                                            <p class="font-mono font-bold text-gray-900 whitespace-nowrap text-[10px] leading-tight">
                                                {{ c.phone || '-' }}
                                            </p>
                                            <p v-if="c.email" class="text-[9px] text-gray-500 truncate block max-w-[130px] leading-tight mt-0.5">
                                                {{ c.email }}
                                            </p>
                                        </td>
                                        <td class="py-2 px-1.5 border-r border-b border-gray-300 text-center whitespace-nowrap">
                                            <span class="inline-block px-2 py-0.5 rounded text-[9px] font-bold border uppercase"
                                                :class="(c.status === 'active' || c.status === 'aktif' || !c.status) ? 'bg-emerald-50 text-emerald-800 border-emerald-400 font-bold' : 'bg-gray-50 text-gray-700 border-gray-300'">
                                                {{ (c.status === 'active' || c.status === 'aktif' || !c.status) ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- RINGKASAN DATA -->
                        <div class="border border-gray-300 p-2.5 mb-5 bg-gray-50 flex items-center justify-between text-[11px]">
                            <span class="font-bold text-gray-800">Total Customer: {{ printCustomersList.length }} Pelanggan</span>
                            <div class="flex items-center gap-3 text-gray-600 text-[10px]">
                                <span>Status Aktif: <strong class="text-emerald-700 font-bold">{{ printCustomersList.filter(c => c.status === 'active' || c.status === 'aktif' || !c.status).length }}</strong></span>
                                <span>Status Non-Aktif: <strong>{{ printCustomersList.filter(c => c.status === 'inactive').length }}</strong></span>
                            </div>
                        </div>

                        <!-- TANDA TANGAN / LEGALITAS -->
                        <div class="signature-section pt-4 text-xs">
                            <div class="text-right text-gray-600 text-[11px] mb-4">
                                Surabaya, {{ todayFormatted }}
                            </div>
                            <div class="grid grid-cols-3 gap-6 text-center text-[11px]">
                                <div class="space-y-12">
                                    <p class="font-medium text-gray-600">Dibuat Oleh (Admin Customer),</p>
                                    <div>
                                        <p class="font-bold text-gray-900 underline">{{ props.user?.name || 'Administrator CRM' }}</p>
                                        <p class="text-[10px] text-gray-500">Staff Administrasi Pelanggan</p>
                                    </div>
                                </div>
                                <div class="space-y-12">
                                    <p class="font-medium text-gray-600">Diverifikasi (Head of Operations),</p>
                                    <div>
                                        <p class="font-bold text-gray-900 underline">Head of Operations</p>
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