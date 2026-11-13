<script setup>
import { onMounted, ref } from 'vue';
import { api } from '../../services/api';
import Pagination from '../../Components/Pagination.vue';
import Badge from '../../Components/Badge.vue';

const props = defineProps({
    user: { type: Object, default: () => ({}) },
});

const emit = defineEmits(['navigate']);

const activities = ref([]);
const pagination = ref({ current_page: 1, last_page: 1, total: 0 });
const activityType = ref(new URLSearchParams(window.location.search).get('type') || '');
const loading = ref(true);
const error = ref('');

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
    error.value = '';
    try {
        const params = { page: String(page) };
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
        error.value = err.message || 'Gagal memuat aktivitas.';
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

onMounted(() => {
    loadActivities();
});
</script>

<template>
    <div class="space-y-5">
        <!-- Header & Action -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Riwayat Aktivitas</h1>
                <p class="text-xs text-gray-500 mt-0.5">Catatan seluruh interaksi tim dengan prospek & pelanggan</p>
            </div>
            <div class="flex items-center gap-2">
                <select v-model="activityType" @change="handleFilter"
                    class="px-3 py-2 rounded-xl border border-gray-200 text-xs text-gray-700 bg-white focus:outline-none focus:ring-2 focus:ring-orange-500/20">
                    <option value="">Semua Jenis</option>
                    <option v-for="(lbl, val) in typeLabels" :key="val" :value="val">{{ lbl }}</option>
                </select>
                <button v-if="activityType" @click="handleReset" class="p-2 rounded-xl border border-gray-200 text-gray-400 hover:text-gray-600 bg-white text-xs">
                    Reset
                </button>
            </div>
        </div>

        <div v-if="loading" class="flex items-center justify-center py-20 text-gray-400">
            <svg class="w-5 h-5 animate-spin text-orange-500 mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
            <span class="text-xs">Memuat aktivitas...</span>
        </div>

        <div v-else-if="error" class="p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs">
            {{ error }}
        </div>

        <!-- Table Card -->
        <div v-else class="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-gray-50/80 text-gray-400 font-bold uppercase tracking-wider text-[10px] border-b border-gray-100">
                        <tr>
                            <th class="p-4">Tanggal & Waktu</th>
                            <th class="p-4">Nama Prospek / Lead</th>
                            <th class="p-4">Jenis Aktivitas</th>
                            <th class="p-4">Catatan / Ringkasan</th>
                            <th class="p-4">Petugas / Sales</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 text-gray-700">
                        <tr v-if="activities.length === 0">
                            <td colspan="5" class="p-8 text-center text-gray-400 text-xs">
                                Belum ada riwayat aktivitas yang tercatat.
                            </td>
                        </tr>
                        <tr v-for="act in activities" :key="act.id" class="hover:bg-gray-50/50 transition-colors">
                            <td class="p-4 font-medium text-gray-500 whitespace-nowrap">
                                {{ act.created_at ? new Date(act.created_at).toLocaleString('id-ID') : '-' }}
                            </td>
                            <td class="p-4 font-bold text-gray-900">
                                <a v-if="act.lead" href="#" @click.prevent="emit('navigate', `/leads/${act.lead.id}`)" class="hover:text-orange-600">
                                    {{ act.lead.name }}
                                </a>
                                <span v-else class="text-gray-400">-</span>
                            </td>
                            <td class="p-4">
                                <Badge :status="act.type" :text="typeLabels[act.type] || act.type" />
                            </td>
                            <td class="p-4 max-w-xs truncate text-gray-600">
                                {{ act.notes || act.description || '-' }}
                            </td>
                            <td class="p-4 text-gray-500 whitespace-nowrap">
                                {{ act.user?.name || '-' }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="p-4">
                <Pagination :meta="pagination" :loading="loading" @change-page="loadActivities" />
            </div>
        </div>
    </div>
</template>
