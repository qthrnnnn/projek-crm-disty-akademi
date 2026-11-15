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
        const params = { page: String(page), per_page: '20' };
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
    <div class="space-y-4">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Manajemen Leads</h1>
                <p class="text-xs text-gray-500 mt-0.5">Kelola prospek dan calon peserta pelatihan (Aturan: 1 Lead = 1 Sales Eksklusif)</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
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
                <Pagination :pagination="pagination" @page="loadLeads" />
            </div>
        </div>

        <!-- Modal Penugasan Sales PIC (Admin Only) -->
        <div v-if="showAssignModal" class="fixed inset-0 z-50 bg-black/40 flex items-center justify-center p-4">
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
    </div>
</template>