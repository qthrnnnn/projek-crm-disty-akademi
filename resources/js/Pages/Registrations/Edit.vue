<script setup>
import { onMounted, ref } from 'vue';
import { api } from '../../services/api';

const props = defineProps({
    id: { type: [String, Number], required: true },
});

const emit = defineEmits(['navigate']);

const form = ref({
    status: 'registered',
    notes: '',
});
const reg = ref(null);
const loading = ref(true);
const saving = ref(false);
const error = ref('');

async function loadData() {
    loading.value = true;
    try {
        const res = await api.get('registrations', props.id);
        const r = res.data || res;
        reg.value = r;
        form.value = {
            status: r.status || 'registered',
            notes: r.notes || '',
        };
    } catch (err) {
        error.value = err.message || 'Gagal memuat pendaftaran.';
    } finally {
        loading.value = false;
    }
}

async function save() {
    saving.value = true;
    error.value = '';
    try {
        await api.update('registrations', props.id, form.value);
        emit('navigate', '/registrations');
    } catch (err) {
        error.value = err.message || 'Gagal memperbarui status pendaftaran.';
    } finally {
        saving.value = false;
    }
}

onMounted(() => {
    loadData();
});
</script>

<template>
    <div class="max-w-2xl mx-auto space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Perbarui Pendaftaran #{{ id }}</h1>
                <p class="text-xs text-gray-500 mt-0.5">Update kehadiran dan status sertifikasi peserta</p>
            </div>
            <button @click="emit('navigate', '/registrations')" class="self-start sm:self-auto whitespace-nowrap shrink-0 text-xs font-semibold text-gray-500 hover:text-gray-900">
                &larr; Batal & Kembali
            </button>
        </div>

        <div v-if="loading" class="text-center py-12 text-xs text-gray-400">Memuat data...</div>
        <div v-else-if="error" class="p-3 bg-red-50 border border-red-200 text-red-600 text-xs">{{ error }}</div>

        <form v-else @submit.prevent="save" class="bg-white p-4 sm:p-6 border border-gray-100 shadow-xs space-y-4">
            <div class="p-4 bg-gray-50 border border-gray-100 text-xs space-y-1">
                <p class="font-bold text-gray-900">{{ reg?.customer?.name || reg?.lead?.name }}</p>
                <p class="text-gray-500">{{ reg?.product?.name || reg?.schedule?.product?.name || reg?.training_schedule?.product?.name || reg?.product_name || 'Program Pelatihan' }}</p>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Status Kehadiran / Sertifikasi</label>
                <select v-model="form.status"
                    class="w-full px-3.5 py-2.5 border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500/20 bg-white">
                    <option value="registered">Registered (Terdaftar)</option>
                    <option value="confirmed">Confirmed (Terkonfirmasi)</option>
                    <option value="attended">Attended (Hadir / Pelatihan)</option>
                    <option value="completed">Completed (Selesai Pelatihan)</option>
                    <option value="cancelled">Cancelled (Batal)</option>
                </select>
            </div>

            <div class="pt-2 flex flex-wrap justify-end gap-2">
                <button type="button" @click="emit('navigate', '/registrations')" class="px-4 py-2.5 rounded-xl border border-gray-200 font-semibold text-xs text-gray-600 hover:bg-gray-50">Batal</button>
                <button type="submit" :disabled="saving" class="px-5 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-bold text-xs shadow-sm disabled:opacity-50">
                    <span v-if="saving">Menyimpan...</span>
                    <span v-else>Simpan Perubahan</span>
                </button>
            </div>
        </form>
    </div>
</template>
