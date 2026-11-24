<script setup>
import { computed, onMounted, ref } from 'vue';
import { api } from '../../services/api';

const props = defineProps({
    id: { type: [String, Number], required: true },
    user: { type: Object, default: () => ({}) },
});

const emit = defineEmits(['navigate']);

const isAdmin = computed(() => props.user?.role === 'admin');

const form = ref({
    product_id: '',
    trainer_id: '',
    training_date: '',
    start_time: '09:00',
    end_time: '16:00',
    capacity: 25,
    location: '',
    status: 'scheduled',
    notes: '',
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
            training_date: s.training_date ? s.training_date.substring(0, 10) : '',
            start_time: s.start_time ? s.start_time.substring(0, 5) : '09:00',
            end_time: s.end_time ? s.end_time.substring(0, 5) : '16:00',
            capacity: s.capacity || 25,
            location: s.location || '',
            status: s.status || 'scheduled',
            notes: s.notes || '',
        };
    } catch (err) {
        error.value = err.message || 'Gagal memuat jadwal.';
    } finally {
        loading.value = false;
    }
}

async function save() {
    if (!isAdmin.value) return;
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
    if (!isAdmin.value) {
        emit('navigate', '/training-schedules');
        return;
    }
    loadData();
});
</script>

<template>
    <div v-if="!isAdmin" class="max-w-xl mx-auto p-6 bg-white border border-red-100 text-center space-y-3">
        <p class="text-sm font-semibold text-red-600">Akses Ditolak</p>
        <p class="text-xs text-gray-500">Hanya Admin yang dapat mengubah jadwal pelatihan.</p>
        <button type="button" @click="emit('navigate', '/training-schedules')"
            class="px-4 py-2 text-xs font-semibold text-white bg-orange-500 rounded-lg hover:bg-orange-600">
            Kembali ke Jadwal Pelatihan
        </button>
    </div>
    <div v-else class="max-w-2xl mx-auto space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Edit Jadwal Pelatihan #{{ id }}</h1>
                <p class="text-xs text-gray-500 mt-0.5">Perbarui tanggal pelaksanaan, instruktur, & kuota</p>
            </div>
            <button @click="emit('navigate', '/training-schedules')" class="self-start sm:self-auto whitespace-nowrap shrink-0 text-xs font-semibold text-gray-500 hover:text-gray-900">
                &larr; Batal & Kembali
            </button>
        </div>

        <div v-if="loading" class="text-center py-12 text-xs text-gray-400">Memuat data...</div>
        <div v-else-if="error" class="p-3 bg-red-50 border border-red-200 text-red-600 text-xs">{{ error }}</div>

        <form v-else @submit.prevent="save" class="bg-white p-4 sm:p-6 border border-gray-100 shadow-xs space-y-4">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Program Pelatihan *</label>
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

            <div class="pt-2 flex flex-wrap justify-end gap-2">
                <button type="button" @click="emit('navigate', '/training-schedules')" class="px-4 py-2.5 rounded-xl border border-gray-200 font-semibold text-xs text-gray-600 hover:bg-gray-50">Batal</button>
                <button type="submit" :disabled="saving" class="px-5 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-bold text-xs shadow-sm disabled:opacity-50">
                    <span v-if="saving">Menyimpan...</span>
                    <span v-else>Simpan Perubahan</span>
                </button>
            </div>
        </form>
    </div>
</template>
