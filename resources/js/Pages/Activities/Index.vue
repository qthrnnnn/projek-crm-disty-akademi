<script setup>
import { onMounted, ref } from 'vue';
import { api } from '../../services/api';
import Pagination from '../../Components/Pagination.vue';

const props = defineProps({
    user: { type: Object, default: () => ({}) },
});

const emit = defineEmits(['navigate']);

const activities = ref([]);
const pagination = ref({ current_page: 1, last_page: 1, total: 0 });
const activityType = ref('');
const loading = ref(true);

const typeLabels = {
    call: 'Telepon',
    email: 'Email',
    whatsapp: 'WhatsApp',
    meeting: 'Meeting',
    note: 'Catatan',
    status_change: 'Ubah Status',
    admin_reminder: 'Instruksi Admin',
};

async function loadActivities(page = 1) {
    loading.value = true;
    try {
        const params = { page: String(page), per_page: '20' };
        if (activityType.value) params.type = activityType.value;
        const res = await api.list('activities', params);
        activities.value = res.data || [];
        pagination.value = {
            current_page: res.current_page || page,
            last_page: res.last_page || 1,
            total: res.total || activities.value.length,
            from: res.from || 1,
            to: res.to || activities.value.length,
        };
    } catch (err) {
        console.error(err);
    } finally {
        loading.value = false;
    }
}

function handleFilter() {
    loadActivities(1);
}

function handleReset() {
    activityType.value = '';
    loadActivities(1);
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
    loadActivities();
});
</script>

<template>
    <div class="space-y-4">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-xl font-semibold text-gray-900">Log Aktivitas & Riwayat Interaksi</h1>
                <p class="text-xs text-gray-500 mt-0.5">Semua jejak kontak prospek, telepon, whatsapp, & status change</p>
            </div>
        </div>

        <!-- Filter Card -->
        <div class="bg-white border border-blue-100 p-3.5 shadow-sm">
            <form @submit.prevent="handleFilter" class="flex flex-wrap items-center gap-2.5">
                <select v-model="activityType" class="text-xs border border-gray-200 px-3 py-2 text-gray-700 focus:outline-none focus:border-orange-500">
                    <option value="">Semua Jenis Aktivitas</option>
                    <option v-for="(lbl, val) in typeLabels" :key="val" :value="val">{{ lbl }}</option>
                </select>
                <button type="submit" class="text-xs bg-orange-500 text-white font-medium py-2 px-3 rounded-lg hover:bg-orange-600 transition-colors">
                    Filter
                </button>
                <button v-if="activityType" @click="handleReset" type="button" class="text-xs text-gray-500 hover:text-gray-700 px-2 py-1">
                    Reset
                </button>
            </form>
        </div>

        <!-- Activities Table Card -->
        <div class="bg-white border border-blue-100 overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[620px] text-left text-xs text-gray-600">
                    <thead class="bg-gray-50/70 border-b border-gray-100 text-gray-500 font-medium uppercase tracking-wider text-[11px]">
                        <tr>
                            <th class="py-3 px-4">WAKTU</th>
                            <th class="py-3 px-4">TIPE</th>
                            <th class="py-3 px-4">PROSPEK</th>
                            <th class="py-3 px-4">SUBJEK / DETAIL</th>
                            <th class="py-3 px-4">SALES USER</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-if="loading">
                            <td colspan="5" class="p-8 text-center text-gray-400">Memuat log aktivitas...</td>
                        </tr>
                        <tr v-else-if="activities.length === 0">
                            <td colspan="5" class="p-8 text-center text-gray-400">Belum ada riwayat aktivitas yang tercatat.</td>
                        </tr>
                        <tr v-for="act in activities" :key="act.id" class="hover:bg-blue-50/30 transition-colors">
                            <td class="py-3 px-4 whitespace-nowrap text-gray-500">
                                {{ formatDateTime(act.activity_at || act.created_at) }}
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 text-[10px] font-medium border bg-gray-50 border-gray-200">
                                    {{ typeLabels[act.type] || act.type }}
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                <button v-if="act.lead" @click="emit('navigate', `/leads/${act.lead.id}`)" class="font-semibold text-blue-600 hover:underline text-left">
                                    {{ act.lead.name }}
                                </button>
                                <span v-else class="text-gray-400">-</span>
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-medium text-gray-900">{{ act.subject || act.notes || act.description || '-' }}</div>
                            </td>
                            <td class="py-3 px-4">
                                {{ act.user?.name || '-' }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div v-if="pagination.total > 0" class="p-3 border-t border-gray-100 flex items-center justify-between">
                <Pagination :pagination="pagination" @page="loadActivities" />
            </div>
        </div>
    </div>
</template>