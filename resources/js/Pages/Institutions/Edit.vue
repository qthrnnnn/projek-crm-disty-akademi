<script setup>
import { onMounted, ref } from 'vue';
import { api } from '../../services/api';

const props = defineProps({
    id: { type: [String, Number], required: true },
});

const emit = defineEmits(['navigate']);

const form = ref({
    name: '',
    type: 'Kampus',
    address: '',
    pic_name: '',
    pic_phone: '',
});
const loading = ref(true);
const saving = ref(false);
const error = ref('');

async function loadData() {
    loading.value = true;
    try {
        const res = await api.get('institutions', props.id);
        const inst = res.data || res;
        form.value = {
            name: inst.name || '',
            type: inst.type || 'Kampus',
            address: inst.address || '',
            pic_name: inst.pic_name || '',
            pic_phone: inst.pic_phone || '',
        };
    } catch (err) {
        error.value = err.message || 'Gagal memuat instansi.';
    } finally {
        loading.value = false;
    }
}

async function save() {
    saving.value = true;
    error.value = '';
    try {
        await api.update('institutions', props.id, form.value);
        emit('navigate', '/institutions');
    } catch (err) {
        error.value = err.message || 'Gagal memperbarui instansi.';
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
                <h1 class="text-xl font-bold text-gray-900">Edit Mitra Instansi #{{ id }}</h1>
                <p class="text-xs text-gray-500 mt-0.5">Perbarui profil dan kontak PIC</p>
            </div>
            <button @click="emit('navigate', '/institutions')" class="text-xs font-semibold text-gray-500 hover:text-gray-900">
                &larr; Batal & Kembali
            </button>
        </div>

        <div v-if="loading" class="text-center py-12 text-xs text-gray-400">Memuat data...</div>
        <div v-else-if="error" class="p-3 rounded-xl bg-red-50 border border-red-200 text-red-600 text-xs">{{ error }}</div>

        <form v-else @submit.prevent="save" class="bg-white p-6 rounded-2xl border border-gray-100 shadow-xs space-y-4">
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Nama Instansi *</label>
                <input v-model="form.name" type="text" required
                    class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500/20 outline-none" />
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Kategori Instansi</label>
                <select v-model="form.type"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500/20 bg-white">
                    <option value="Kampus">Perguruan Tinggi / Kampus</option>
                    <option value="Sekolah">SMK / SMA</option>
                    <option value="Perusahaan">Perusahaan Swasta / BUMN</option>
                    <option value="Pemerintah">Instansi Pemerintah</option>
                </select>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Nama PIC *</label>
                    <input v-model="form.pic_name" type="text" required
                        class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500/20 outline-none" />
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Kontak WhatsApp PIC *</label>
                    <input v-model="form.pic_phone" type="text" required
                        class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500/20 outline-none" />
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Alamat Instansi</label>
                <textarea v-model="form.address" rows="2"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500/20 outline-none"></textarea>
            </div>

            <div class="pt-2 flex justify-end gap-2">
                <button type="button" @click="emit('navigate', '/institutions')" class="px-4 py-2.5 rounded-xl border border-gray-200 font-semibold text-xs text-gray-600 hover:bg-gray-50">Batal</button>
                <button type="submit" :disabled="saving" class="px-5 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-bold text-xs shadow-sm disabled:opacity-50">
                    <span v-if="saving">Menyimpan...</span>
                    <span v-else>Simpan Perubahan</span>
                </button>
            </div>
        </form>
    </div>
</template>
