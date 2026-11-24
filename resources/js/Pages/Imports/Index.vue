<script setup>
import { computed, onMounted, ref } from 'vue';
import { api } from '../../services/api';
import Pagination from '../../Components/Pagination.vue';

const props = defineProps({
    user: { type: Object, default: () => ({}) },
});

const isSales = computed(() => props.user?.role === 'sales');
const isAdmin = computed(() => props.user?.role === 'admin');

const emit = defineEmits(['navigate']);

const batches = ref([]);
const pagination = ref({ current_page: 1, last_page: 1, total: 0 });
const loading = ref(true);

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

async function loadBatches(page = 1) {
    loading.value = true;
    try {
        const res = await api.list('lead-imports', { page: String(page), per_page: '20' });
        batches.value = res.data || [];
        pagination.value = {
            current_page: res.current_page || page,
            last_page: res.last_page || 1,
            total: res.total || batches.value.length,
            from: res.from || 1,
            to: res.to || batches.value.length,
        };
    } catch (err) {
        console.error(err);
    } finally {
        loading.value = false;
    }
}

onMounted(() => {
    loadBatches();
});
</script>

<template>
    <div class="space-y-4">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-xl font-semibold text-gray-900">Riwayat Import Lead (CSV / Excel)</h1>
                <p class="text-xs text-gray-500 mt-0.5">Daftar batch upload prospek masal dari CSV atau Excel</p>
            </div>
            <button v-if="isSales" @click="emit('navigate', '/lead-imports/create')"
                class="self-start sm:self-auto inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-medium text-white bg-orange-500 rounded-xl hover:bg-orange-600 transition-colors shadow-sm shrink-0">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
                Upload File Baru (Sales)
            </button>
            <div v-else-if="isAdmin" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-gray-600 bg-gray-100 border border-gray-200">
                <svg class="w-3.5 h-3.5 text-gray-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                <span>Mode Pemantauan Admin (Hanya Sales yang Unggah File)</span>
            </div>
        </div>

        <!-- Table Card -->
        <div class="bg-white border border-blue-100 overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[680px] text-left text-xs text-gray-600">
                    <thead class="bg-gray-50/70 border-b border-gray-100 text-gray-500 font-medium uppercase tracking-wider text-[11px]">
                        <tr>
                            <th class="py-3 px-4">Waktu & File</th>
                            <th class="py-3 px-4">Pengunggah</th>
                            <th class="py-3 px-4">Total Baris</th>
                            <th class="py-3 px-4">Berhasil</th>
                            <th class="py-3 px-4">Gagal</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-if="loading">
                            <td colspan="7" class="py-8 text-center text-gray-400">Memuat riwayat import...</td>
                        </tr>
                        <tr v-else-if="batches.length === 0">
                            <td colspan="7" class="py-8 text-center text-gray-400">Belum ada riwayat import data</td>
                        </tr>
                        <tr v-for="b in batches" :key="b.id" class="hover:bg-blue-50/30 transition-colors">
                            <td class="py-3 px-4">
                                <div class="font-semibold text-gray-900">{{ b.file_name || b.original_filename || 'batch_import.xlsx' }}</div>
                                <div class="text-[11px] text-gray-400">{{ formatDateTime(b.created_at) }}</div>
                            </td>
                            <td class="py-3 px-4">{{ b.user?.name || '-' }}</td>
                            <td class="py-3 px-4 font-medium">{{ b.total_rows || 0 }}</td>
                            <td class="py-3 px-4 text-emerald-600 font-bold">{{ b.success_rows ?? b.successful_rows ?? 0 }}</td>
                            <td class="py-3 px-4 text-red-600 font-medium">{{ b.failed_rows || 0 }}</td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                <span class="inline-flex items-center whitespace-nowrap px-2 py-0.5 text-[10px] font-medium border bg-blue-50 text-blue-700 border-blue-200">
                                    {{ b.status ? (b.status.charAt(0).toUpperCase() + b.status.slice(1)) : 'Completed' }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right">
                                <button @click="emit('navigate', `/lead-imports/${b.id}`)" class="text-xs text-blue-600 hover:underline font-medium">
                                    Detail Baris
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div v-if="pagination.total > 0" class="p-3 border-t border-gray-100 flex items-center justify-between">
                <Pagination :pagination="pagination" @page="loadBatches" />
            </div>
        </div>
    </div>
</template>