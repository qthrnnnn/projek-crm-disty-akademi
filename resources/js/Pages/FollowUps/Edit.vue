<script setup>
import { onMounted, ref } from 'vue';
import { api } from '../../services/api';

const props = defineProps({
    id: { type: [String, Number], required: true },
});

const emit = defineEmits(['navigate']);

const form = ref({
    status: 'completed',
    result_notes: '',
});
const followUp = ref(null);
const loading = ref(true);
const saving = ref(false);
const error = ref('');

async function loadData() {
    loading.value = true;
    try {
        const res = await api.get('follow-ups', props.id);
        const f = res.data || res;
        followUp.value = f;
        form.value = {
            status: f.status || 'pending',
            result_notes: f.result_notes || '',
        };
    } catch (err) {
        error.value = err.message || 'Gagal memuat agenda.';
    } finally {
        loading.value = false;
    }
}

async function save() {
    saving.value = true;
    error.value = '';
    try {
        await api.update('follow-ups', props.id, form.value);
        emit('navigate', '/follow-ups');
    } catch (err) {
        error.value = err.message || 'Gagal memperbarui agenda.';
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
                <h1 class="text-xl font-bold text-gray-900">Perbarui Hasil Follow-Up #{{ id }}</h1>
                <p class="text-xs text-gray-500 mt-0.5">Catat respon prospek setelah dihubungi</p>
            </div>
            <button @click="emit('navigate', '/follow-ups')" class="text-xs font-semibold text-gray-500 hover:text-gray-900">
                &larr; Batal & Kembali
            </button>
        </div>

        <div v-if="loading" class="text-center py-12 text-xs text-gray-400">Memuat data...</div>
        <div v-else-if="error" class="p-3 rounded-xl bg-red-50 border border-red-200 text-red-600 text-xs">{{ error }}</div>

        <form v-else @submit.prevent="save" class="bg-white p-6 rounded-2xl border border-gray-100 shadow-xs space-y-4">
            <div v-if="followUp?.lead" class="p-3 bg-gray-50 rounded-xl border border-gray-100 text-xs">
                <span class="text-gray-400 block text-[11px]">Prospek</span>
                <span class="font-bold text-gray-900">{{ followUp.lead.name }}</span> ({{ followUp.lead.phone || '-' }})
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Status Follow-Up</label>
                <select v-model="form.status"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500/20 bg-white">
                    <option value="pending">Menunggu / Terjadwal</option>
                    <option value="completed">Selesai / Sudah Dihubungi</option>
                    <option value="cancelled">Dibatalkan</option>
                </select>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Catatan Respon Prospek</label>
                <textarea v-model="form.result_notes" rows="4"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500/20 outline-none" placeholder="Hasil percakapan, minat, atau keberatan prospek..."></textarea>
            </div>

            <div class="pt-2 flex justify-end gap-2">
                <button type="button" @click="emit('navigate', '/follow-ups')" class="px-4 py-2.5 rounded-xl border border-gray-200 font-semibold text-xs text-gray-600 hover:bg-gray-50">Batal</button>
                <button type="submit" :disabled="saving" class="px-5 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-bold text-xs shadow-sm disabled:opacity-50">
                    <span v-if="saving">Menyimpan...</span>
                    <span v-else>Simpan Respon</span>
                </button>
            </div>
        </form>
    </div>
</template>
