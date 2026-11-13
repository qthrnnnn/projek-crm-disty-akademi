<script setup>
import { computed, onMounted, ref } from 'vue';
import { api } from '../../services/api';
import Pagination from '../../Components/Pagination.vue';
import Badge from '../../Components/Badge.vue';

const props = defineProps({
    user: { type: Object, default: () => ({}) },
});

const emit = defineEmits(['navigate']);

const followups = ref([]);
const salesUsers = ref([]);
const pagination = ref({ current_page: 1, last_page: 1, total: 0 });
const statusFilter = ref('');
const assignedFilter = ref('');
const loading = ref(true);

const isAdmin = computed(() => props.user?.role === 'admin');

async function loadSalesUsers() {
    try {
        const res = await api.list('users', { per_page: '100' });
        salesUsers.value = (res.data || []).filter(u => u.role === 'sales');
    } catch (e) {
        console.error(e);
    }
}

async function loadFollowUps(page = 1) {
    loading.value = true;
    try {
        const params = { page: String(page), per_page: '20' };
        if (statusFilter.value) params.status = statusFilter.value;
        if (assignedFilter.value) params.assigned = assignedFilter.value;

        const res = await api.list('follow-ups', params);
        followups.value = res.data || [];
        pagination.value = {
            current_page: res.current_page || page,
            last_page: res.last_page || 1,
            total: res.total || followups.value.length,
            from: res.from || 1,
            to: res.to || followups.value.length,
        };
    } catch (err) {
        console.error(err);
    } finally {
        loading.value = false;
    }
}

function handleFilter() {
    loadFollowUps(1);
}

function resetFilter() {
    statusFilter.value = '';
    assignedFilter.value = '';
    loadFollowUps(1);
}

function formatDateTime(dtStr) {
    if (!dtStr) return '-';
    const d = new Date(dtStr);
    if (isNaN(d.getTime())) return dtStr;
    return d.toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

onMounted(() => {
    loadSalesUsers();
    loadFollowUps();
});
</script>

<template>
    <div class="space-y-4">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-xl font-semibold text-gray-900">Jadwal Follow-up Prospek</h1>
                <p class="text-xs text-gray-500 mt-0.5">Kelola janji tindak lanjut dan respon calon peserta</p>
            </div>
            <button @click="emit('navigate', '/follow-ups/create')"
                class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-medium text-white bg-orange-600 rounded-full hover:bg-orange-700 transition-colors shadow-sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Jadwal
            </button>
        </div>

        <!-- Filter Card -->
        <div class="bg-white border border-blue-100 p-3.5 shadow-sm">
            <form @submit.prevent="handleFilter" class="flex flex-wrap items-center gap-2.5">
                <select v-model="statusFilter" class="text-xs border border-gray-200 px-3 py-2 text-gray-700 focus:outline-none focus:border-orange-500">
                    <option value="">Semua Status</option>
                    <option value="pending">Pending</option>
                    <option value="completed">Selesai</option>
                    <option value="cancelled">Dibatalkan</option>
                </select>
                <select v-if="isAdmin" v-model="assignedFilter" class="text-xs border border-gray-200 px-3 py-2 text-gray-700 focus:outline-none focus:border-orange-500">
                    <option value="">Semua Sales</option>
                    <option v-for="s in salesUsers" :key="s.id" :value="s.id">{{ s.name }}</option>
                </select>
                <button type="submit" class="text-xs bg-orange-600 text-white font-medium py-2 px-3 rounded-lg hover:bg-orange-700 transition-colors">
                    Filter
                </button>
                <button v-if="statusFilter || assignedFilter" @click="resetFilter" type="button" class="text-xs text-gray-500 hover:text-gray-700 px-2 py-1">
                    Reset
                </button>
            </form>
        </div>

        <!-- Table Card -->
        <div class="bg-white border border-blue-100 overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[620px] text-left text-xs text-gray-600">
                    <thead class="bg-gray-50/70 border-b border-gray-100 text-gray-500 font-medium uppercase tracking-wider text-[11px]">
                        <tr>
                            <th class="py-3 px-4">Agenda & Lead</th>
                            <th class="py-3 px-4">Waktu</th>
                            <th class="py-3 px-4">Metode</th>
                            <th class="py-3 px-4">Sales PIC</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-if="loading">
                            <td colspan="6" class="p-8 text-center text-gray-400">Memuat data follow-up...</td>
                        </tr>
                        <tr v-else-if="followups.length === 0">
                            <td colspan="6" class="p-8 text-center text-gray-400">Belum ada agenda follow-up.</td>
                        </tr>
                        <tr v-for="fu in followups" :key="fu.id" class="hover:bg-blue-50/30 transition-colors">
                            <td class="py-3 px-4">
                                <div class="font-semibold text-gray-900">{{ fu.title || '-' }}</div>
                                <button v-if="fu.lead" @click="emit('navigate', `/leads/${fu.lead.id}`)" class="text-[11px] text-blue-600 hover:underline text-left block">
                                    {{ fu.lead.name }} ({{ fu.lead.lead_code }})
                                </button>
                            </td>
                            <td class="py-3 px-4">
                                <span class="text-gray-700">
                                    {{ formatDateTime(fu.follow_up_at || fu.scheduled_at) }}
                                </span>
                            </td>
                            <td class="py-3 px-4 uppercase text-[10px] font-semibold text-gray-500">
                                {{ fu.method || fu.type || 'WHATSAPP' }}
                            </td>
                            <td class="py-3 px-4">
                                {{ fu.assigned_user?.name || fu.assignedUser?.name || '-' }}
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 text-[10px] font-medium border"
                                    :class="fu.status === 'completed' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : (fu.status === 'cancelled' ? 'bg-gray-100 text-gray-500 border-gray-200' : 'bg-amber-50 text-amber-700 border-amber-200')">
                                    {{ fu.status === 'completed' ? 'Completed' : (fu.status === 'cancelled' ? 'Cancelled' : 'Pending') }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right">
                                <button @click="emit('navigate', `/follow-ups/${fu.id}/edit`)" class="text-xs text-blue-600 hover:underline">
                                    Edit
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div v-if="pagination.total > 0" class="p-3 border-t border-gray-100 flex items-center justify-between">
                <Pagination :pagination="pagination" @page="loadFollowUps" />
            </div>
        </div>
    </div>
</template>