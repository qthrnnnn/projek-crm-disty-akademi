<script setup>
import { onMounted, ref } from 'vue';
import { api } from '../../services/api';
import Badge from '../../Components/Badge.vue';

const props = defineProps({
    id: { type: [String, Number], required: true },
});

const emit = defineEmits(['navigate']);

const batch = ref(null);
const loading = ref(true);
const error = ref('');

async function loadBatch() {
    loading.value = true;
    try {
        const res = await api.get('lead-imports', props.id);
        batch.value = res.data || res;
    } catch (err) {
        error.value = err.message || 'Gagal memuat detail batch.';
    } finally {
        loading.value = false;
    }
}

onMounted(() => {
    loadBatch();
});
</script>

<template>
    <div class="space-y-5">
        <div v-if="loading" class="text-center py-20 text-gray-400 text-xs">Memuat detail batch...</div>
        <div v-else-if="error" class="p-4 bg-red-50 border border-red-200 text-red-700 text-xs">{{ error }}</div>
        <div v-else class="space-y-5">
            <div class="bg-white p-5 border border-gray-100 shadow-xs flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-mono font-bold text-gray-400">#BATCH-{{ batch.id }}</span>
                        <Badge :status="batch.status || 'completed'" />
                    </div>
                    <h1 class="text-xl font-black text-gray-900 mt-2">{{ batch.original_filename || batch.filename }}</h1>
                    <p class="text-xs text-gray-500 mt-0.5">Diupload {{ batch.created_at ? new Date(batch.created_at).toLocaleString('id-ID') : '-' }}</p>
                </div>
                <button @click="emit('navigate', '/lead-imports')" class="self-start sm:self-auto whitespace-nowrap shrink-0 px-3.5 py-2 rounded-xl border border-gray-200 text-xs font-semibold text-gray-600 hover:bg-gray-50">
                    &larr; Kembali ke Daftar Batch
                </button>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white p-4 border border-gray-100 text-center">
                    <p class="text-xs text-gray-400 font-semibold">Total Baris</p>
                    <p class="text-2xl font-black text-gray-900 mt-1">{{ batch.total_rows || 0 }}</p>
                </div>
                <div class="bg-white p-4 border border-gray-100 text-center">
                    <p class="text-xs text-gray-400 font-semibold">Berhasil Masuk</p>
                    <p class="text-2xl font-black text-emerald-600 mt-1">{{ batch.successful_rows || 0 }}</p>
                </div>
                <div class="bg-white p-4 border border-gray-100 text-center">
                    <p class="text-xs text-gray-400 font-semibold">Gagal / Dilewati</p>
                    <p class="text-2xl font-black text-red-600 mt-1">{{ batch.failed_rows || 0 }}</p>
                </div>
            </div>
        </div>
    </div>
</template>
