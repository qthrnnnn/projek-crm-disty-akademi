<script setup>
import { computed, onMounted, ref } from 'vue';
import { api } from '../../services/api';
import Pagination from '../../Components/Pagination.vue';

const props = defineProps({
    user: { type: Object, default: () => ({}) },
});

const emit = defineEmits(['navigate']);

const schedules = ref([]);
const pagination = ref({ current_page: 1, last_page: 1, total: 0 });
const search = ref('');
const status = ref('');
const loading = ref(true);

const isAdmin = computed(() => props.user?.role === 'admin');

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
        case 'scheduled': return 'bg-blue-50 text-blue-700 border-blue-200';
        case 'ongoing': return 'bg-amber-50 text-amber-700 border-amber-200';
        case 'completed': return 'bg-emerald-50 text-emerald-700 border-emerald-200';
        case 'cancelled': return 'bg-gray-100 text-gray-600 border-gray-200';
        default: return 'bg-gray-50 border-gray-200';
    }
}

async function loadSchedules(page = 1) {
    loading.value = true;
    try {
        const params = { page: String(page), per_page: '20' };
        if (search.value) params.search = search.value;
        if (status.value) params.status = status.value;

        const res = await api.list('training-schedules', params);
        schedules.value = res.data || [];
        pagination.value = {
            current_page: res.current_page || page,
            last_page: res.last_page || 1,
            total: res.total || schedules.value.length,
            from: res.from || 1,
            to: res.to || schedules.value.length,
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
        loadSchedules(1);
    }, 250);
}

function handleFilter() {
    clearTimeout(searchTimeout);
    loadSchedules(1);
}

function resetFilter() {
    clearTimeout(searchTimeout);
    search.value = '';
    status.value = '';
    loadSchedules(1);
}

onMounted(() => {
    loadSchedules();
});
</script>

<template>
    <div class="space-y-4">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Jadwal yang Tersedia</h1>
                <p class="text-xs text-gray-500 mt-0.5">Setiap jadwal memiliki trainer, waktu, lokasi, dan kapasitas.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <button v-if="isAdmin" type="button" @click="emit('navigate', '/trainers')"
                    class="px-3.5 py-2 text-xs font-medium text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 shadow-sm transition-colors">
                    Kelola Trainer
                </button>
                <button v-if="isAdmin" type="button" @click="emit('navigate', '/training-schedules/create')"
                    class="px-4 py-2 text-xs font-semibold text-white bg-orange-500 rounded-full hover:bg-orange-600 shadow-sm transition-colors">
                    + Buat Jadwal
                </button>
            </div>
        </div>

        <!-- Filter Card -->
        <div class="bg-white border border-blue-100/70 p-3.5 shadow-sm">
            <form @submit.prevent="handleFilter" class="flex flex-col sm:flex-row items-center gap-2.5">
                <div class="relative flex-1 w-full">
                    <input v-model="search" @input="onSearchInput" type="text"
                        placeholder="Cari pelatihan, trainer, atau lokasi..."
                        class="w-full text-xs border border-gray-200 px-3 py-2 text-gray-800 placeholder-gray-400 focus:outline-none focus:border-orange-500" />
                </div>
                <div class="w-full sm:w-48">
                    <select v-model="status" @change="handleFilter"
                        class="w-full text-xs border border-gray-200 px-3 py-2 text-gray-700 focus:outline-none focus:border-orange-500">
                        <option value="">Semua status</option>
                        <option value="scheduled">Scheduled</option>
                        <option value="ongoing">Ongoing</option>
                        <option value="completed">Completed</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                </div>
                <button type="submit"
                    class="w-full sm:w-auto px-5 py-2 text-xs font-semibold text-white bg-orange-500 rounded-lg hover:bg-orange-600 shadow-sm transition-colors">
                    Cari
                </button>
                <button v-if="search || status" type="button" @click="resetFilter"
                    class="w-full sm:w-auto px-3 py-2 text-xs text-gray-500 hover:text-gray-700 border border-gray-200 rounded-lg">
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
                            <th class="py-3.5 px-4">PELATIHAN</th>
                            <th class="py-3.5 px-4">TRAINER</th>
                            <th class="py-3.5 px-4">TANGGAL</th>
                            <th class="py-3.5 px-4">JAM MULAI</th>
                            <th class="py-3.5 px-4">JAM SELESAI</th>
                            <th class="py-3.5 px-4">STATUS</th>
                            <th class="py-3.5 px-4">PESERTA</th>
                            <th class="py-3.5 px-4 text-center">AKSI</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-if="loading">
                            <td colspan="8" class="py-8 text-center text-gray-400 text-xs">Memuat jadwal...</td>
                        </tr>
                        <tr v-else-if="schedules.length === 0">
                            <td colspan="8" class="py-8 text-center text-gray-400 text-xs">
                                <span v-if="search">Tidak ditemukan jadwal pelatihan dengan kata kunci "<strong>{{ search }}</strong>".</span>
                                <span v-else>Belum ada jadwal pelatihan.</span>
                            </td>
                        </tr>
                        <tr v-for="s in schedules" :key="s.id" class="hover:bg-blue-50/20 transition-colors">
                            <td class="py-3.5 px-4 font-semibold text-gray-900">
                                <div>{{ s.product?.name || s.product_name || ('Program #' + s.product_id) }}</div>
                                <div v-if="s.location" class="text-[11px] text-gray-400 font-normal">{{ s.location }}</div>
                            </td>
                            <td class="py-3.5 px-4 text-gray-800 font-medium">
                                {{ s.trainer?.name || s.trainer_name || '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-gray-900 font-medium whitespace-nowrap">
                                {{ formatDate(s.training_date) }}
                            </td>
                            <td class="py-3.5 px-4 text-gray-600 whitespace-nowrap">
                                {{ s.start_time ? s.start_time.substring(0, 5) : '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-gray-600 whitespace-nowrap">
                                {{ s.end_time ? s.end_time.substring(0, 5) : '-' }}
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="inline-block px-2 py-0.5 border text-[10px] font-semibold uppercase tracking-wider" :class="statusBadgeClass(s.status)">
                                    {{ s.status }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 font-medium">
                                <span class="text-blue-600 font-bold">{{ s.registrations_count ?? 0 }}</span> / {{ s.capacity || 0 }}
                            </td>
                            <td class="py-3.5 px-4 text-center whitespace-nowrap">
                                <div class="inline-flex items-center justify-center gap-1.5">
                                    <button v-if="user?.role === 'sales'" @click="emit('navigate', `/registrations/create?schedule_id=${s.id}`)"
                                        class="p-1.5 text-gray-400 hover:text-emerald-600 rounded-lg hover:bg-emerald-50 transition-colors"
                                        title="Daftarkan customer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v14m-7-7h14"/>
                                        </svg>
                                    </button>
                                    <button v-if="isAdmin" @click="emit('navigate', `/training-schedules/${s.id}/edit`)"
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
            <div class="p-3 sm:p-4 border-t border-gray-100 flex flex-wrap items-center justify-between gap-2">
                <div class="flex items-center gap-2 text-xs text-gray-500">
                    <span>Tampilkan</span>
                    <span class="border border-gray-200 px-2.5 py-1 text-xs text-gray-700 bg-white">50</span>
                </div>
                <div v-if="pagination.total > 0">
                    <Pagination :pagination="pagination" @page="loadSchedules" />
                </div>
            </div>
        </div>
    </div>
</template>
