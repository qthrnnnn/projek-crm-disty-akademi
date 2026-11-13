<script setup>
import { onMounted, ref } from 'vue';
import { api } from '../../services/api';

const emit = defineEmits(['navigate']);

const form = ref({
    customer_id: '',
    training_schedule_id: '',
    status: 'registered',
    notes: '',
});

const customers = ref([]);
const schedules = ref([]);
const loading = ref(false);
const error = ref('');

async function loadDependencies() {
    try {
        const [cRes, sRes] = await Promise.all([
            api.list('customers', { per_page: 100 }),
            api.list('training-schedules', { per_page: 100 }),
        ]);
        customers.value = cRes.data || [];
        schedules.value = sRes.data || [];
    } catch (e) {
        console.error(e);
    }
}

async function save() {
    loading.value = true;
    error.value = '';
    try {
        await api.create('registrations', form.value);
        emit('navigate', '/registrations');
    } catch (err) {
        error.value = err.message || 'Gagal mendaftarkan peserta.';
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
                <h1 class="text-xl font-bold text-gray-900">Pendaftaran Peserta ke Kelas</h1>
                <p class="text-xs text-gray-500 mt-0.5">Hubungkan customer dengan jadwal pelatihan aktif</p>
            </div>
            <button @click="emit('navigate', '/registrations')" class="text-xs font-semibold text-gray-500 hover:text-gray-900">
                &larr; Batal & Kembali
            </button>
        </div>

        <div v-if="error" class="p-3 rounded-xl bg-red-50 border border-red-200 text-red-600 text-xs">{{ error }}</div>

        <form @submit.prevent="save" class="bg-white p-6 rounded-2xl border border-gray-100 shadow-xs space-y-4">
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Pilih Customer / Peserta *</label>
                <select v-model="form.customer_id" required
                    class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500/20 bg-white">
                    <option value="">Pilih Customer</option>
                    <option v-for="c in customers" :key="c.id" :value="c.id">{{ c.name }} ({{ c.phone || '-' }})</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Pilih Jadwal Pelatihan Aktif *</label>
                <select v-model="form.training_schedule_id" required
                    class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500/20 bg-white">
                    <option value="">Pilih Jadwal</option>
                    <option v-for="s in schedules" :key="s.id" :value="s.id">
                        {{ s.product?.name || 'Pelatihan' }} &bull; Mulai: {{ s.start_date || '-' }}
                    </option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Status Pendaftaran</label>
                <select v-model="form.status"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500/20 bg-white">
                    <option value="registered">Terdaftar</option>
                    <option value="attended">Hadir / Sedang Berjalan</option>
                    <option value="completed">Lulus / Sertifikat Terbit</option>
                </select>
            </div>

            <div class="pt-2 flex justify-end gap-2">
                <button type="button" @click="emit('navigate', '/registrations')" class="px-4 py-2.5 rounded-xl border border-gray-200 font-semibold text-xs text-gray-600 hover:bg-gray-50">Batal</button>
                <button type="submit" :disabled="loading" class="px-5 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-bold text-xs shadow-sm disabled:opacity-50">
                    <span v-if="loading">Menyimpan...</span>
                    <span v-else>Simpan Pendaftaran</span>
                </button>
            </div>
        </form>
    </div>
</template>
