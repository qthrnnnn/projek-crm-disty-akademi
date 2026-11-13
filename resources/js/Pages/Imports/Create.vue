<script setup>
import { onMounted, ref } from 'vue';
import { api } from '../../services/api';

const emit = defineEmits(['navigate']);

const file = ref(null);
const sourceId = ref('');
const sources = ref([]);
const loading = ref(false);
const error = ref('');

async function loadSources() {
    try {
        const res = await api.list('lead-sources', { per_page: 100 });
        sources.value = res.data || [];
    } catch (e) {
        console.error(e);
    }
}

function handleFile(e) {
    file.value = e.target.files[0] || null;
}

async function upload() {
    if (!file.value) {
        error.value = 'Silakan pilih file CSV atau Excel.';
        return;
    }
    loading.value = true;
    error.value = '';
    try {
        const fd = new FormData();
        fd.append('file', file.value);
        if (sourceId.value) fd.append('source_id', sourceId.value);
        await api.create('lead-imports', fd);
        emit('navigate', '/lead-imports');
    } catch (err) {
        error.value = err.message || 'Gagal mengunggah file import.';
    } finally {
        loading.value = false;
    }
}

onMounted(() => {
    loadSources();
});
</script>

<template>
    <div class="max-w-2xl mx-auto space-y-5">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Upload Batch Import Lead</h1>
                <p class="text-xs text-gray-500 mt-0.5">Unggah berkas spreadsheet prospek (CSV / XLSX)</p>
            </div>
            <button @click="emit('navigate', '/lead-imports')" class="text-xs font-semibold text-gray-500 hover:text-gray-900">
                &larr; Batal & Kembali
            </button>
        </div>

        <div v-if="error" class="p-3 rounded-xl bg-red-50 border border-red-200 text-red-600 text-xs">{{ error }}</div>

        <form @submit.prevent="upload" class="bg-white p-6 rounded-2xl border border-gray-100 shadow-xs space-y-4">
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Pilih Berkas Spreadsheet *</label>
                <input type="file" required accept=".csv,.xlsx,.xls" @change="handleFile"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500/20 bg-white" />
                <p class="text-[11px] text-gray-400 mt-1.5">Format kolom yang didukung: Nama, No_WA / Phone, Email, Instansi, Program</p>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Sumber Prospek (Opsional)</label>
                <select v-model="sourceId"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500/20 bg-white">
                    <option value="">Deteksi dari File / Default</option>
                    <option v-for="s in sources" :key="s.id" :value="s.id">{{ s.name }}</option>
                </select>
            </div>

            <div class="pt-2 flex justify-end gap-2">
                <button type="button" @click="emit('navigate', '/lead-imports')" class="px-4 py-2.5 rounded-xl border border-gray-200 font-semibold text-xs text-gray-600 hover:bg-gray-50">Batal</button>
                <button type="submit" :disabled="loading" class="px-5 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-bold text-xs shadow-sm disabled:opacity-50">
                    <span v-if="loading">Memproses File...</span>
                    <span v-else>Mulai Import Lead</span>
                </button>
            </div>
        </form>
    </div>
</template>
