<script setup>
import { onMounted, ref } from 'vue';
import { api } from '../../services/api';
import Pagination from '../../Components/Pagination.vue';
import Badge from '../../Components/Badge.vue';

const emit = defineEmits(['navigate']);

const batches = ref([]);
const pagination = ref({ current_page: 1, last_page: 1, total: 0 });
const loading = ref(true);

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
    <div class="space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Riwayat Import Batch CRM</h1>
                <p class="text-xs text-gray-500 mt-0.5">Daftar unggahan data lead massal melalui file CSV / Excel</p>
            </div>
            <button @click="emit('navigate', '/lead-imports/create')"
                class="px-4 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-bold text-xs shadow-sm flex items-center justify-center gap-1.5">
                <span>+</span> Import Data Lead
            </button>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-gray-50/80 text-gray-400 font-bold uppercase tracking-wider text-[10px] border-b border-gray-100">
                        <tr>
                            <th class="p-4">Nama File / Batch</th>
                            <th class="p-4">Tanggal Unggah</th>
                            <th class="p-4">Total Baris</th>
                            <th class="p-4">Berhasil Masuk</th>
                            <th class="p-4">Gagal / Duplikat</th>
                            <th class="p-4">Status</th>
                            <th class="p-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 text-gray-700">
                        <tr v-if="loading">
                            <td colspan="7" class="p-8 text-center text-gray-400 text-xs">Memuat batch import...</td>
                        </tr>
                        <tr v-else-if="batches.length === 0">
                            <td colspan="7" class="p-8 text-center text-gray-400 text-xs">Belum ada riwayat batch import.</td>
                        </tr>
                        <tr v-for="b in batches" :key="b.id" class="hover:bg-gray-50/50 transition-colors">
                            <td class="p-4 font-bold text-gray-900">
                                <div>{{ b.original_filename || b.filename || 'batch_import.xlsx' }}</div>
                                <span class="text-[10px] text-gray-400">ID: #{{ b.id }}</span>
                            </td>
                            <td class="p-4 text-gray-500">{{ b.created_at ? new Date(b.created_at).toLocaleString('id-ID') : '-' }}</td>
                            <td class="p-4 font-black text-gray-900">{{ b.total_rows || 0 }}</td>
                            <td class="p-4 font-bold text-emerald-600">{{ b.successful_rows || 0 }}</td>
                            <td class="p-4 font-bold text-red-600">{{ b.failed_rows || 0 }}</td>
                            <td class="p-4"><Badge :status="b.status || 'completed'" /></td>
                            <td class="p-4 text-right">
                                <button @click="emit('navigate', `/lead-imports/${b.id}`)" class="font-bold text-orange-600 hover:text-orange-700">Detail</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="p-4">
                <Pagination :meta="pagination" :loading="loading" @change-page="loadBatches" />
            </div>
        </div>
    </div>
</template>
