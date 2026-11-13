<script setup>
import { onMounted, ref } from 'vue';
import { api } from '../../services/api';

const emit = defineEmits(['navigate']);

const form = ref({
    lead_id: '',
    type: 'whatsapp',
    scheduled_at: '',
    notes: '',
});
const leads = ref([]);
const loading = ref(false);
const error = ref('');

async function loadLeads() {
    try {
        const res = await api.list('leads', { per_page: 100 });
        leads.value = res.data || [];
    } catch (e) {
        console.error(e);
    }
}

async function save() {
    loading.value = true;
    error.value = '';
    try {
        await api.create('follow-ups', form.value);
        emit('navigate', '/follow-ups');
    } catch (err) {
        error.value = err.message || 'Gagal menyimpan agenda follow-up.';
    } finally {
        loading.value = false;
    }
}

onMounted(() => {
    loadLeads();
});
</script>

<template>
    <div class="max-w-2xl mx-auto space-y-5">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Buat Agenda Follow-Up</h1>
                <p class="text-xs text-gray-500 mt-0.5">Jadwalkan kontak lanjutan dengan calon peserta</p>
            </div>
            <button @click="emit('navigate', '/follow-ups')" class="text-xs font-semibold text-gray-500 hover:text-gray-900">
                &larr; Batal & Kembali
            </button>
        </div>

        <div v-if="error" class="p-3 rounded-xl bg-red-50 border border-red-200 text-red-600 text-xs">{{ error }}</div>

        <form @submit.prevent="save" class="bg-white p-6 rounded-2xl border border-gray-100 shadow-xs space-y-4">
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Pilih Prospek *</label>
                <select v-model="form.lead_id" required
                    class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500/20 bg-white">
                    <option value="">Pilih Prospek</option>
                    <option v-for="l in leads" :key="l.id" :value="l.id">{{ l.name }} ({{ l.phone || l.email || '-' }})</option>
                </select>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Metode Kontak *</label>
                    <select v-model="form.type" required
                        class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500/20 bg-white">
                        <option value="whatsapp">WhatsApp</option>
                        <option value="call">Telepon Langsung</option>
                        <option value="email">Email</option>
                        <option value="meeting">Pertemuan / Demo</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Rencana Tanggal & Jam *</label>
                    <input v-model="form.scheduled_at" type="datetime-local" required
                        class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500/20 bg-white" />
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Catatan Follow-Up</label>
                <textarea v-model="form.notes" rows="3"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500/20 outline-none" placeholder="Target pembahasan atau penawaran..."></textarea>
            </div>

            <div class="pt-2 flex justify-end gap-2">
                <button type="button" @click="emit('navigate', '/follow-ups')" class="px-4 py-2.5 rounded-xl border border-gray-200 font-semibold text-xs text-gray-600 hover:bg-gray-50">Batal</button>
                <button type="submit" :disabled="loading" class="px-5 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-bold text-xs shadow-sm disabled:opacity-50">
                    <span v-if="loading">Menyimpan...</span>
                    <span v-else>Simpan Agenda</span>
                </button>
            </div>
        </form>
    </div>
</template>
