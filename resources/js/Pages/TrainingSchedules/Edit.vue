<script setup>
import { onMounted, ref } from 'vue';
import { api } from '../../services/api';

const props = defineProps({
    id: { type: [String, Number], required: true },
});

const emit = defineEmits(['navigate']);

const form = ref({
    product_id: '',
    trainer_id: '',
    start_date: '',
    end_date: '',
    quota: 25,
    location: '',
    status: 'open',
});

const products = ref([]);
const trainers = ref([]);
const loading = ref(true);
const saving = ref(false);
const error = ref('');

async function loadData() {
    loading.value = true;
    try {
        const [pRes, tRes, sRes] = await Promise.all([
            api.list('products', { per_page: 100 }),
            api.list('trainers', { per_page: 100 }),
            api.get('training-schedules', props.id),
        ]);
        products.value = pRes.data || [];
        trainers.value = tRes.data || [];
        const s = sRes.data || sRes;
        form.value = {
            product_id: s.product_id || '',
            trainer_id: s.trainer_id || '',
            start_date: s.start_date || '',
            end_date: s.end_date || '',
            quota: s.quota || 25,
            location: s.location || '',
            status: s.status || 'open',
        };
    } catch (err) {
        error.value = err.message || 'Gagal memuat jadwal.';
    } finally {
        loading.value = false;
    }
}

async function save() {
    saving.value = true;
    error.value = '';
    try {
        await api.update('training-schedules', props.id, form.value);
        emit('navigate', '/training-schedules');
    } catch (err) {
        error.value = err.message || 'Gagal memperbarui jadwal.';
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
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Edit Jadwal Pelatihan #{{ id }}</h1>
                <p class="text-xs text-gray-500 mt-0.5">Perbarui tanggal pelaksanaan & kuota</p>
            </div>
            <button @click="emit('navigate', '/training-schedules')" class="text-xs font-semibold text-gray-500 hover:text-gray-900">
                &larr; Batal & Kembali
            </button>
        </div>

        <div v-if="loading" class="text-center py-12 text-xs text-gray-400">Memuat data...</div>
        <div v-else-if="error" class="p-3 rounded-xl bg-red-50 border border-red-200 text-red-600 text-xs">{{ error }}</div>

        <form v-else @submit.prevent="save" class="bg-white p-6 rounded-2xl border border-gray-100 shadow-xs space-y-4">
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Program Pelatihan</label>
                <select v-model="form.product_id" required
                    class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500/20 bg-white">
                    <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }}</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Trainer Bertugas</label>
                <select v-model="form.trainer_id"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500/20 bg-white">
                    <option value="">Pilih Trainer</option>
                    <option v-for="t in trainers" :key="t.id" :value="t.id">{{ t.name }}</option>
                </select>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Tanggal Mulai</label>
                    <input v-model="form.start_date" type="date" required
                        class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500/20 bg-white" />
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Tanggal Selesai</label>
                    <input v-model="form.end_date" type="date" required
                        class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500/20 bg-white" />
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Kuota Maksimal</label>
                    <input v-model="form.quota" type="number" required min="1"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500/20 outline-none" />
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Status Kelas</label>
                    <select v-model="form.status"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500/20 bg-white">
                        <option value="open">Buka Pendaftaran (Open)</option>
                        <option value="ongoing">Sedang Berjalan (Ongoing)</option>
                        <option value="completed">Selesai (Completed)</option>
                        <option value="cancelled">Dibatalkan</option>
                    </select>
                </div>
            </div>

            <div class="pt-2 flex justify-end gap-2">
                <button type="button" @click="emit('navigate', '/training-schedules')" class="px-4 py-2.5 rounded-xl border border-gray-200 font-semibold text-xs text-gray-600 hover:bg-gray-50">Batal</button>
                <button type="submit" :disabled="saving" class="px-5 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-bold text-xs shadow-sm disabled:opacity-50">
                    <span v-if="saving">Menyimpan...</span>
                    <span v-else>Simpan Perubahan</span>
                </button>
            </div>
        </form>
    </div>
</template>
