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

function resetFilter() {
    search.value = '';
    statusFilter.value = '';
    assignedFilter.value = '';
    loadLeads(1);
}

function downloadTemplate() {
    window.location.href = '/api/leads/template';
}

function formatRupiah(num) {
    if (!num) return '0';
    return Number(num).toLocaleString('id-ID');
}

onMounted(() => {
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
                <!-- Tombol Import CSV / Excel -->
                <button type="button" @click="emit('navigate', '/lead-imports')"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-medium text-gray-700 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 hover:border-gray-300 shadow-2xs transition-all">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    Import CSV / Excel
                </button>

                <!-- Tombol Template CSV -->
                <button type="button" @click="downloadTemplate"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-medium text-gray-600 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 shadow-2xs transition-all"
                    title="Unduh Format Template CSV">
                    <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Template
                </button>

                <!-- Tombol Tambah Lead -->
                <button type="button" @click="emit('navigate', '/leads/create')"
                    class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-semibold text-white bg-orange-500 rounded-xl hover:bg-orange-600 shadow-sm transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Tambah Lead
                </button>
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

        <!-- Filter bar -->
        <div class="bg-white border border-gray-200 p-3.5 shadow-xs">
            <form @submit.prevent="handleFilter" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2.5">
                <div>
                    <input v-model="search" type="text" placeholder="Cari nama, email, hp, instansi..."
                        class="w-full text-xs border border-gray-200 px-3.5 py-2 text-gray-800 placeholder-gray-400 focus:outline-none focus:border-orange-500" />
                </div>
                <div>
                    <select v-model="statusFilter" class="w-full text-xs border border-gray-200 px-3 py-2 text-gray-700 focus:outline-none focus:border-orange-500">
                        <option value="">Semua Status</option>
                        <option value="new">Baru</option>
                        <option value="contacted">Dikontak</option>
                        <option value="qualified">Aktif (Merespon)</option>
                        <option value="proposal">Negosiasi</option>
                        <option value="won">Berhasil (Closing)</option>
                        <option value="lost">Ditolak</option>
                        <option value="invalid">Tidak Valid</option>
                    </select>
                </div>
                <div v-if="isAdmin">
                    <select v-model="assignedFilter" class="w-full text-xs border border-gray-200 px-3 py-2 text-gray-700 focus:outline-none focus:border-orange-500">
                        <option value="">Semua Sales PIC</option>
                        <option v-for="s in salesUsers" :key="s.id" :value="s.id">{{ s.name }}</option>
                    </select>
                </div>
                <div v-else>
                    <input type="text" readonly :value="`PIC: ${user?.name || ''} (Sales Aktif)`" class="w-full text-xs border border-gray-100 bg-gray-50 px-3 py-2 text-gray-500" />
                </div>
                <div class="flex items-center gap-1.5">
                    <button type="submit" class="w-full text-xs bg-orange-500 hover:bg-orange-600 text-white font-medium py-2 px-3 rounded-xl transition-colors">
                        Filter
                    </button>
                    <button v-if="search || statusFilter || assignedFilter" @click="resetFilter" type="button" class="p-2 text-xs text-gray-500 hover:text-gray-700 border border-gray-200 rounded-xl hover:bg-gray-50" title="Reset">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
            </form>
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
                            <td colspan="6" class="p-8 text-center text-gray-400">Tidak ada calon pelanggan ditemukan.</td>
                        </tr>
                        <tr v-for="lead in leads" :key="lead.id" class="hover:bg-orange-50/20 transition-colors">
                            <!-- Calon Pelanggan / Lead -->
                            <td class="py-3.5 px-4">
                                <div class="flex items-center gap-2">
                                    <button @click="emit('navigate', `/leads/${lead.id}`)" class="font-semibold text-gray-900 hover:text-orange-600 transition-colors text-left">
                                        {{ lead.name }}
                                    </button>
                                </div>
                                <div class="flex items-center gap-1.5 text-[11px] text-gray-400 mt-0.5 font-mono">
                                    <span>{{ lead.lead_code }}</span>
                                    <template v-if="lead.company || lead.institution">
                                        <span class="text-gray-300">•</span>
                                        <span class="text-gray-500 font-sans truncate max-w-[180px]">{{ lead.company || lead.institution }}</span>
                                    </template>
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
                                    Deal: Rp {{ formatRupiah(lead.deal_price) }}
                                    <span v-if="lead.product?.price" class="text-[10px] text-gray-400 font-normal line-through ml-1">
                                        Rp {{ formatRupiah(lead.product.price) }}
                                    </span>
                                </div>
                                <div v-else-if="lead.product?.price" class="text-[11px] text-gray-400 block mt-0.5">
                                    Harga: Rp {{ formatRupiah(lead.product.price) }}
                                </div>
                                <div class="text-[10px] text-emerald-600 font-medium">
                                    Komisi 10%: Rp {{ formatRupiah(lead.deal_price ? lead.deal_price * 0.1 : (lead.product?.price ? lead.product.price * 0.1 : 0)) }}
                                </div>
                            </td>

                            <!-- Sales Penanggung Jawab -->
                            <td class="py-3.5 px-4">
                                <div v-if="!lead.assigned_to" class="flex items-center gap-2">
                                    <span class="inline-flex items-center px-2 py-0.5 text-[10px] font-medium bg-amber-50 text-amber-700 border border-amber-200">
                                        Belum Ada PIC
                                    </span>
                                </div>
                                <div v-else class="inline-flex items-center gap-1.5">
                                    <span class="w-5 h-5 rounded-full bg-gray-200 text-gray-700 flex items-center justify-center text-[10px] font-bold">
                                        {{ ((lead.assigned_user?.name || lead.sales?.name || '?').charAt(0)).toUpperCase() }}
                                    </span>
                                    <span class="font-medium text-gray-700 text-xs">{{ lead.assigned_user?.name || lead.sales?.name || '-' }}</span>
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
                    </tbody>
                </table>
            </div>
            <div v-if="pagination.total > 0" class="p-3 border-t border-gray-100 flex items-center justify-between">
                <Pagination :pagination="pagination" @page="loadLeads" />
            </div>
        </div>
    </div>
</template>