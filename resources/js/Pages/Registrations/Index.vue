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
const loading = ref(true);

const isAdmin = computed(() => props.user?.role === 'admin');

function formatCurrency(val) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(val || 0);
}

function formatDate(val) {
    if (!val) return '-';
    try {
        const d = new Date(val);
        return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
    } catch {
        return val;
    }
}

function statusBadgeClass(st) {
    switch (st) {
        case 'registered': return 'bg-blue-50 text-blue-700 border-blue-200';
        case 'confirmed': return 'bg-emerald-50 text-emerald-700 border-emerald-200';
        case 'attended': return 'bg-purple-50 text-purple-700 border-purple-200';
        case 'cancelled': return 'bg-gray-100 text-gray-600 border-gray-200';
        default: return 'bg-gray-50 border-gray-200';
    }
}

async function loadRegistrations(page = 1) {
    loading.value = true;
    try {
        const params = { page: String(page), per_page: '20' };
        if (search.value) params.search = search.value;
        if (status.value) params.status = status.value;

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

function resetFilter() {
    search.value = '';
    status.value = '';
    loadRegistrations(1);
}

async function deleteReg(id) {
    if (!confirm('Hapus pendaftaran peserta ini?')) return;
    try {
        await api.remove('registrations', id);
        loadRegistrations(pagination.value.current_page);
    } catch (err) {
        alert(err.message || 'Gagal menghapus pendaftaran.');
    }
}

onMounted(() => {
    loadRegistrations();
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
                <button @click="emit('navigate', '/training-schedules')"
                    class="px-3.5 py-2 text-xs font-medium text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 shadow-sm transition-colors">
                    Lihat Jadwal
                </button>
                <button v-if="isAdmin" @click="emit('navigate', '/registrations/create')"
                    class="px-4 py-2 text-xs font-semibold text-white bg-gray-900 rounded-full hover:bg-black shadow-sm transition-colors">
                    + Daftarkan Customer
                </button>
            </div>
        </div>

        <!-- Filter Card -->
        <div class="bg-white border border-blue-100/70 p-3.5 shadow-sm">
            <form @submit.prevent="loadRegistrations(1)" class="flex flex-col sm:flex-row items-center gap-2.5">
                <div class="relative flex-1 w-full">
                    <input v-model="search" type="text"
                        placeholder="Cari nomor, customer, training, trainer..."
                        class="w-full text-xs border border-gray-200 px-3 py-2 text-gray-800 placeholder-gray-400 focus:outline-none focus:border-blue-500 rounded" />
                </div>
                <div class="w-full sm:w-48">
                    <select v-model="status" @change="loadRegistrations(1)"
                        class="w-full text-xs border border-gray-200 px-3 py-2 text-gray-700 focus:outline-none focus:border-blue-500 rounded">
                        <option value="">Semua status</option>
                        <option value="registered">Registered</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="attended">Attended</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <button type="submit"
                    class="w-full sm:w-auto px-5 py-2 text-xs font-semibold text-white bg-slate-900 rounded-lg hover:bg-slate-800 shadow-sm transition-colors">
                    Filter
                </button>
                <button v-if="search || status" type="button" @click="resetFilter"
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
                            <th class="py-3.5 px-4">PELATIHAN</th>
                            <th class="py-3.5 px-4">JADWAL</th>
                            <th class="py-3.5 px-4">STATUS</th>
                            <th class="py-3.5 px-4">AMOUNT</th>
                            <th class="py-3.5 px-4 text-right">AKSI</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-if="loading">
                            <td colspan="7" class="py-8 text-center text-gray-400 text-xs">Memuat pendaftaran...</td>
                        </tr>
                        <tr v-else-if="registrations.length === 0">
                            <td colspan="7" class="py-8 text-center text-gray-400 text-xs">Belum ada pendaftaran peserta.</td>
                        </tr>
                        <tr v-for="r in registrations" :key="r.id" class="hover:bg-blue-50/20 transition-colors">
                            <td class="py-3.5 px-4 font-mono font-semibold text-gray-900">
                                {{ r.registration_number || ('#REG-' + r.id) }}
                            </td>
                            <td class="py-3.5 px-4">
                                <a href="javascript:void(0)" @click="emit('navigate', `/customers/${r.customer_id}`)" class="font-medium text-blue-600 hover:underline">
                                    {{ r.customer?.name || ('Customer #' + r.customer_id) }}
                                </a>
                                <div class="text-[11px] text-gray-400">{{ r.customer?.customer_code || '-' }}</div>
                            </td>
                            <td class="py-3.5 px-4 font-medium text-gray-800">
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
                            <td class="py-3.5 px-4 text-right space-x-1 whitespace-nowrap">
                                <div class="inline-flex items-center gap-1">
                                    <button @click="emit('navigate', `/registrations/${r.id}/edit`)" class="p-1.5 text-gray-400 hover:text-blue-600 rounded hover:bg-blue-50" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </button>
                                    <button v-if="isAdmin" @click="deleteReg(r.id)" class="p-1.5 text-gray-400 hover:text-red-600 rounded hover:bg-red-50" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-gray-100 bg-gray-50/50">
                <Pagination :meta="pagination" :loading="loading" @change-page="loadRegistrations" />
            </div>
        </div>
    </div>
</template>
