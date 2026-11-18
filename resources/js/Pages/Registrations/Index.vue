<script setup>
import { computed, onMounted, ref } from 'vue';
import { api } from '../../services/api';
import Pagination from '../../Components/Pagination.vue';

const props = defineProps({
    user: { type: Object, default: () => ({}) },
});

const emit = defineEmits(['navigate']);

const registrations = ref([]);
const pagination = ref({ current_page: 1, last_page: 1, total: 0 });
const search = ref('');
const status = ref('');
const salesId = ref('');
const salesUsers = ref([]);
const perPage = ref('50');
const loading = ref(true);

const isAdmin = computed(() => props.user?.role === 'admin');

async function loadSalesUsers() {
    try {
        const res = await api.list('users', { role: 'sales', per_page: '100' });
        salesUsers.value = res.data || [];
    } catch (e) {
        console.error(e);
    }
}

function formatCurrency(val) {
    if (!val) return 'Rp 0';
    return 'Rp ' + Number(val).toLocaleString('id-ID');
}

function formatDate(val) {
    if (!val) return '-';
    try {
        const d = new Date(val);
        if (isNaN(d.getTime())) return val;
        return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
    } catch {
        return val;
    }
}

function formatTime(val) {
    if (!val) return '';
    try {
        const d = new Date(val);
        if (isNaN(d.getTime())) return '';
        return d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', hour12: false }).replace('.', ':');
    } catch {
        return '';
    }
}

function statusBadgeClass(st) {
    const s = (st || '').toLowerCase();
    switch (s) {
        case 'confirmed': return 'bg-emerald-50 text-emerald-700 border-emerald-200';
        case 'registered': return 'bg-blue-50 text-blue-700 border-blue-200';
        case 'attended': return 'bg-purple-50 text-purple-700 border-purple-200';
        case 'completed': return 'bg-teal-50 text-teal-700 border-teal-200';
        case 'cancelled': return 'bg-gray-100 text-gray-600 border-gray-200';
        default: return 'bg-gray-50 text-gray-600 border-gray-200';
    }
}

async function loadRegistrations(page = 1) {
    loading.value = true;
    try {
        const params = { page: String(page), per_page: perPage.value };
        if (search.value) params.search = search.value;
        if (status.value) params.status = status.value;
        if (isAdmin.value && salesId.value) params.sales_id = salesId.value;

        const res = await api.list('registrations', params);
        registrations.value = res.data || [];
        pagination.value = {
            current_page: res.current_page || page,
            last_page: res.last_page || 1,
            total: res.total || registrations.value.length,
            from: res.from || 1,
            to: res.to || registrations.value.length,
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
        loadRegistrations(1);
    }, 250);
}

function handleFilter() {
    clearTimeout(searchTimeout);
    loadRegistrations(1);
}

function resetFilter() {
    clearTimeout(searchTimeout);
    search.value = '';
    status.value = '';
    salesId.value = '';
    loadRegistrations(1);
}

onMounted(() => {
    loadRegistrations();
    if (isAdmin.value) {
        loadSalesUsers();
    }
});
</script>

<template>
    <div class="space-y-4">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Pendaftaran Peserta</h1>
                <p class="text-xs text-gray-500 mt-0.5">Customer didaftarkan ke jadwal yang telah dibuat, bukan menentukan tanggal sendiri.</p>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" @click="emit('navigate', '/training-schedules')"
                    class="px-3.5 py-2 text-xs font-medium text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 shadow-sm transition-colors">
                    Lihat Jadwal
                </button>
                <button type="button" @click="emit('navigate', '/registrations/create')"
                    class="px-4 py-2 text-xs font-semibold text-white bg-orange-500 rounded-full hover:bg-orange-600 shadow-sm transition-colors">
                    + Daftarkan Peserta
                </button>
            </div>
        </div>

        <!-- Filter Card -->
        <div class="bg-white border border-blue-100/70 p-3.5 shadow-sm">
            <form @submit.prevent="handleFilter" class="flex flex-col sm:flex-row items-center gap-2.5">
                <div class="relative flex-1 w-full">
                    <input v-model="search" @input="onSearchInput" type="text"
                        placeholder="Cari nomor, customer, training, trainer, sales..."
                        class="w-full text-xs border border-gray-200 px-3 py-2 text-gray-800 placeholder-gray-400 focus:outline-none focus:border-orange-500" />
                </div>
                <div v-if="isAdmin" class="w-full sm:w-48">
                    <select v-model="salesId" @change="handleFilter"
                        class="w-full text-xs border border-gray-200 px-3 py-2 text-gray-700 focus:outline-none focus:border-orange-500">
                        <option value="">Semua Sales PIC</option>
                        <option v-for="s in salesUsers" :key="s.id" :value="s.id">{{ s.name }}</option>
                    </select>
                </div>
                <div class="w-full sm:w-48">
                    <select v-model="status" @change="handleFilter"
                        class="w-full text-xs border border-gray-200 px-3 py-2 text-gray-700 focus:outline-none focus:border-orange-500">
                        <option value="">Semua status</option>
                        <option value="registered">Registered</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="attended">Attended</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <button type="submit"
                    class="w-full sm:w-auto px-5 py-2 text-xs font-semibold text-white bg-orange-500 rounded-lg hover:bg-orange-600 shadow-sm transition-colors">
                    Filter
                </button>
                <button v-if="search || status || salesId" type="button" @click="resetFilter"
                    class="px-3 py-2 text-xs text-gray-500 hover:text-gray-700 border border-gray-200 rounded-lg">
                    Reset
                </button>
            </form>
        </div>

        <!-- Table Card -->
        <div class="bg-white border border-blue-100/70 overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[680px] text-left text-xs text-gray-600">
                    <thead class="bg-gray-50/70 border-b border-gray-100 text-gray-400 font-semibold uppercase tracking-wider text-[10.5px]">
                        <tr>
                            <th class="py-3.5 px-4">NO. REGISTRASI</th>
                            <th class="py-3.5 px-4">CUSTOMER</th>
                            <th v-if="isAdmin" class="py-3.5 px-4">SALES PIC</th>
                            <th class="py-3.5 px-4">PELATIHAN</th>
                            <th class="py-3.5 px-4">JADWAL</th>
                            <th class="py-3.5 px-4">STATUS</th>
                            <th class="py-3.5 px-4">AMOUNT</th>
                            <th class="py-3.5 px-4 text-right">AKSI</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-if="loading">
                            <td :colspan="isAdmin ? 8 : 7" class="py-8 text-center text-gray-400">Memuat pendaftaran...</td>
                        </tr>
                        <tr v-else-if="registrations.length === 0">
                            <td :colspan="isAdmin ? 8 : 7" class="py-8 text-center text-gray-400">
                                <span v-if="search">Tidak ditemukan pendaftaran dengan kata kunci "<strong>{{ search }}</strong>".</span>
                                <span v-else>Belum ada data pendaftaran.</span>
                            </td>
                        </tr>
                        <tr v-for="r in registrations" :key="r.id" class="hover:bg-blue-50/20 transition-colors">
                            <td class="py-3.5 px-4 font-mono font-bold text-gray-900">
                                <div>{{ r.registration_number || ('#REG-' + r.id) }}</div>
                                <div class="text-[10px] font-sans font-normal text-gray-400 mt-0.5 flex items-center gap-1 flex-wrap">
                                    <span>{{ formatDate(r.created_at) }}</span>
                                    <template v-if="formatTime(r.created_at)">
                                        <span>&bull;</span>
                                        <span class="font-mono text-gray-500 font-medium">{{ formatTime(r.created_at) }} WIB</span>
                                    </template>
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                <button @click="emit('navigate', `/customers/${r.customer_id}`)" class="font-medium text-blue-600 hover:underline text-left block">
                                    {{ r.customer?.name || ('Customer #' + r.customer_id) }}
                                </button>
                                <div class="text-[11px] text-gray-400">{{ r.customer?.customer_code || '-' }}</div>
                            </td>
                            <td v-if="isAdmin" class="py-3.5 px-4">
                                <div class="flex items-center gap-1.5">
                                    <span class="inline-flex items-center justify-center w-5 h-5 rounded-full bg-orange-100 text-orange-700 font-bold text-[10px]">
                                        {{ (r.sales_name || r.sales_pic || r.customer?.lead?.assigned_user?.name || 'A').charAt(0).toUpperCase() }}
                                    </span>
                                    <div>
                                        <div class="font-medium text-gray-800">{{ r.sales_name || r.sales_pic || r.customer?.lead?.assigned_user?.name || '-' }}</div>
                                        <div v-if="r.customer?.lead?.assigned_user?.email" class="text-[10px] text-gray-400">{{ r.customer?.lead?.assigned_user?.email }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="py-3.5 px-4 font-medium text-gray-900">
                                {{ r.product?.name || r.product_name || r.schedule?.product?.name || '-' }}
                            </td>
                            <td class="py-3.5 px-4">
                                <template v-if="r.schedule || r.training_schedule">
                                    <div class="font-medium text-gray-800">{{ formatDate((r.schedule || r.training_schedule).training_date) }}</div>
                                    <div class="text-[11px] text-gray-400">Trainer: {{ (r.schedule || r.training_schedule).trainer?.name || '-' }}</div>
                                </template>
                                <span v-else class="text-gray-400">-</span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="inline-block px-2 py-0.5 border text-[10px] font-semibold uppercase tracking-wider" :class="statusBadgeClass(r.status)">
                                    {{ r.status }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 font-bold text-gray-900">
                                {{ formatCurrency(r.amount) }}
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <button @click="emit('navigate', `/registrations/${r.id}/edit`)" class="text-xs text-blue-600 hover:underline font-medium">
                                    Edit
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="p-4 border-t border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-2 text-xs text-gray-500">
                    <span>Tampilkan</span>
                    <select v-model="perPage" @change="loadRegistrations(1)" class="border border-gray-200 rounded px-2 py-1 text-xs bg-white">
                        <option value="10">10</option>
                        <option value="25">25</option>
                        <option value="50">50</option>
                        <option value="100">100</option>
                    </select>
                </div>
                <div v-if="pagination.total > 0">
                    <Pagination :pagination="pagination" @page="loadRegistrations" />
                </div>
            </div>
        </div>
    </div>
</template>