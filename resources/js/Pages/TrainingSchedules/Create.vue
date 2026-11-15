<script setup>
import { onMounted, ref } from 'vue';
import { api } from '../../services/api';

const emit = defineEmits(['navigate']);

const form = ref({
    product_id: '',
    trainer_id: '',
    training_date: '',
    start_time: '09:00',
    end_time: '16:00',
    capacity: 25,
    status: 'scheduled',
    location: 'Disty Academy Hall / Zoom Meeting',
    notes: '',
});

const products = ref([]);
const trainers = ref([]);
const loading = ref(false);
const error = ref('');

async function loadDependencies() {
    try {
        const [pRes, tRes] = await Promise.all([
            api.list('products', { per_page: 100 }),
            api.list('trainers', { per_page: 100 }),
        ]);
        products.value = pRes.data || [];
        trainers.value = tRes.data || [];
    } catch (e) {
        console.error(e);
    }
}

async function save() {
    loading.value = true;
    error.value = '';
    try {
        await api.create('training-schedules', form.value);
        emit('navigate', '/training-schedules');
    } catch (err) {
        error.value = err.message || 'Gagal menyimpan jadwal pelatihan.';
    } finally {
        loading.value = false;
    }
}

onMounted(() => {
    loadDependencies();
});
</script>

<template>
    <div class="max-w-2xl mx-auto space-y-5">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Buat Jadwal Pelatihan Baru</h1>
                <p class="text-xs text-gray-500 mt-0.5">Atur materi program, instruktur, kuota, & waktu</p>
            </div>
            <button @click="emit('navigate', '/training-schedules')" class="text-xs font-semibold text-gray-500 hover:text-gray-900">
                &larr; Batal & Kembali
            </button>
        </div>

        <div v-if="error" class="p-3 bg-red-50 border border-red-200 text-red-600 text-xs">{{ error }}</div>

        <form @submit.prevent="save" class="bg-white p-6 border border-gray-100 shadow-xs space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Pilih Program Pelatihan *</label>
                    <select v-model="form.product_id" required
                        class="w-full px-3.5 py-2.5 border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500/20 bg-white">
                        <option value="">-- Pilih Program Pelatihan --</option>
                        <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }}</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Trainer Pengampu *</label>
                    <select v-model="form.trainer_id" required
                        class="w-full px-3.5 py-2.5 border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500/20 bg-white">
                        <option value="">-- Pilih Trainer --</option>
                        <option v-for="t in trainers" :key="t.id" :value="t.id">{{ t.name }}</option>
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Tanggal Pelatihan *</label>
                    <input v-model="form.training_date" type="date" required
                        class="w-full px-3.5 py-2.5 border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500/20 bg-white" />
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Jam Mulai *</label>
                    <input v-model="form.start_time" type="time" required
                        class="w-full px-3.5 py-2.5 border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500/20 bg-white" />
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Jam Selesai *</label>
                    <input v-model="form.end_time" type="time" required
                        class="w-full px-3.5 py-2.5 border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500/20 bg-white" />
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Kapasitas Kuota *</label>
                    <input v-model="form.capacity" type="number" required min="1"
                        class="w-full px-3.5 py-2.5 border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500/20 outline-none" />
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Status Jadwal</label>
                    <select v-model="form.status"
                        class="w-full px-3.5 py-2.5 border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500/20 bg-white">
                        <option value="scheduled">Scheduled (Terjadwal)</option>
                        <option value="ongoing">Ongoing (Sedang Berjalan)</option>
                        <option value="completed">Completed (Selesai)</option>
                        <option value="cancelled">Cancelled (Dibatalkan)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Lokasi / Media</label>
                    <input v-model="form.location" type="text"
                        class="w-full px-3.5 py-2.5 border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500/20 outline-none" placeholder="Zoom / Offline Kampus" />
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Catatan Tambahan</label>
                <textarea v-model="form.notes" rows="2"
                    class="w-full px-3.5 py-2.5 border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500/20 outline-none" placeholder="Kebutuhan software, persiapan kelas, dll..."></textarea>
            </div>

            <div class="pt-2 flex justify-end gap-2">
                <button type="button" @click="emit('navigate', '/training-schedules')" class="px-4 py-2.5 rounded-xl border border-gray-200 font-semibold text-xs text-gray-600 hover:bg-gray-50">Batal</button>
                <button type="submit" :disabled="loading" class="px-5 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-bold text-xs shadow-sm disabled:opacity-50">
                    <span v-if="loading">Menyimpan...</span>
                    <span v-else>Simpan Jadwal</span>
                </button>
            </div>
        </form>
    </div>
</template>
