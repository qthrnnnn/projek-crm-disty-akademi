<script setup>
import { onMounted, ref } from 'vue';
import { api } from '../../services/api';

const props = defineProps({
    id: { type: [String, Number], required: true },
});

const emit = defineEmits(['navigate']);

const form = ref({
    name: '',
    expertise: '',
    phone: '',
    email: '',
    bio: '',
    is_active: 1,
});
const loading = ref(true);
const saving = ref(false);
const error = ref('');

async function loadData() {
    loading.value = true;
    try {
        const res = await api.get('trainers', props.id);
        const t = res.data || res;
        form.value = {
            name: t.name || '',
            expertise: t.expertise || '',
            phone: t.phone || '',
            email: t.email || '',
            bio: t.bio || '',
            is_active: t.is_active ? 1 : 0,
        };
    } catch (err) {
        error.value = err.message || 'Gagal memuat trainer.';
    } finally {
        loading.value = false;
    }
}

async function save() {
    saving.value = true;
    error.value = '';
    try {
        await api.update('trainers', props.id, form.value);
        emit('navigate', '/trainers');
    } catch (err) {
        error.value = err.message || 'Gagal memperbarui trainer.';
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
                <h1 class="text-xl font-bold text-gray-900">Edit Trainer #{{ id }}</h1>
                <p class="text-xs text-gray-500 mt-0.5">Perbarui biodata dan status instruktur</p>
            </div>
            <button @click="emit('navigate', '/trainers')" class="self-start sm:self-auto whitespace-nowrap shrink-0 text-xs font-semibold text-gray-500 hover:text-gray-900">
                &larr; Batal & Kembali
            </button>
        </div>

        <div v-if="loading" class="text-center py-12 text-xs text-gray-400">Memuat data...</div>
        <div v-else-if="error" class="p-3 bg-red-50 border border-red-200 text-red-600 text-xs">{{ error }}</div>

        <form v-else @submit.prevent="save" class="bg-white p-4 sm:p-6 border border-gray-100 shadow-xs space-y-4">
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Nama Trainer / Instruktur *</label>
                <input v-model="form.name" type="text" required
                    class="w-full px-3.5 py-2.5 border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500/20 outline-none" />
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Bidang Keahlian *</label>
                <input v-model="form.expertise" type="text" required
                    class="w-full px-3.5 py-2.5 border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500/20 outline-none" />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">WhatsApp *</label>
                    <input v-model="form.phone" type="text" required
                        class="w-full px-3.5 py-2.5 border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500/20 outline-none" />
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Email</label>
                    <input v-model="form.email" type="email"
                        class="w-full px-3.5 py-2.5 border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500/20 outline-none" />
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Status Keaktifan</label>
                <select v-model="form.is_active"
                    class="w-full px-3.5 py-2.5 border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500/20 bg-white">
                    <option :value="1">Aktif Mengajar</option>
                    <option :value="0">Cuti / Nonaktif</option>
                </select>
            </div>

            <div class="pt-2 flex flex-wrap justify-end gap-2">
                <button type="button" @click="emit('navigate', '/trainers')" class="px-4 py-2.5 rounded-xl border border-gray-200 font-semibold text-xs text-gray-600 hover:bg-gray-50">Batal</button>
                <button type="submit" :disabled="saving" class="px-5 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-bold text-xs shadow-sm disabled:opacity-50">
                    <span v-if="saving">Menyimpan...</span>
                    <span v-else>Simpan Perubahan</span>
                </button>
            </div>
        </form>
    </div>
</template>
