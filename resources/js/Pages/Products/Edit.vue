<script setup>
import { onMounted, ref } from 'vue';
import { api } from '../../services/api';

const props = defineProps({
    id: { type: [String, Number], required: true },
});

const emit = defineEmits(['navigate']);

const form = ref({
    name: '',
    code: '',
    category: '',
    price: '',
    description: '',
    is_active: 1,
});
const loading = ref(true);
const saving = ref(false);
const error = ref('');

async function loadProduct() {
    loading.value = true;
    try {
        const res = await api.get('products', props.id);
        const p = res.data || res;
        form.value = {
            name: p.name || '',
            code: p.code || '',
            category: p.category || 'Reguler',
            price: p.price || 0,
            description: p.description || '',
            is_active: p.is_active ? 1 : 0,
        };
    } catch (err) {
        error.value = err.message || 'Gagal memuat program.';
    } finally {
        loading.value = false;
    }
}

async function save() {
    saving.value = true;
    error.value = '';
    try {
        await api.update('products', props.id, form.value);
        emit('navigate', '/products');
    } catch (err) {
        error.value = err.message || 'Gagal memperbarui program.';
    } finally {
        saving.value = false;
    }
}

onMounted(() => {
    loadProduct();
});
</script>

<template>
    <div class="max-w-2xl mx-auto space-y-5">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Edit Program #{{ id }}</h1>
                <p class="text-xs text-gray-500 mt-0.5">Perbarui harga dan materi program</p>
            </div>
            <button @click="emit('navigate', '/products')" class="text-xs font-semibold text-gray-500 hover:text-gray-900">
                &larr; Batal & Kembali
            </button>
        </div>

        <div v-if="loading" class="text-center py-12 text-xs text-gray-400">Memuat data...</div>
        <div v-else-if="error" class="p-3 rounded-xl bg-red-50 border border-red-200 text-red-600 text-xs">{{ error }}</div>

        <form v-else @submit.prevent="save" class="bg-white p-6 rounded-2xl border border-gray-100 shadow-xs space-y-4">
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Nama Program Pelatihan *</label>
                <input v-model="form.name" type="text" required
                    class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500/20 outline-none" />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Kode Program</label>
                    <input v-model="form.code" type="text"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500/20 outline-none" />
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Kategori</label>
                    <select v-model="form.category"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500/20 bg-white">
                        <option value="Reguler">Reguler / Bootcamp</option>
                        <option value="Sertifikasi">Sertifikasi BNSP / Industri</option>
                        <option value="Corporate">Corporate Training</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Biaya Pelatihan (Rp) *</label>
                <input v-model="form.price" type="number" required min="0"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500/20 outline-none" />
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Status Keaktifan</label>
                <select v-model="form.is_active"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500/20 bg-white">
                    <option :value="1">Aktif / Buka Pendaftaran</option>
                    <option :value="0">Nonaktif</option>
                </select>
            </div>

            <div class="pt-2 flex justify-end gap-2">
                <button type="button" @click="emit('navigate', '/products')" class="px-4 py-2.5 rounded-xl border border-gray-200 font-semibold text-xs text-gray-600 hover:bg-gray-50">Batal</button>
                <button type="submit" :disabled="saving" class="px-5 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-bold text-xs shadow-sm disabled:opacity-50">
                    <span v-if="saving">Menyimpan...</span>
                    <span v-else>Simpan Perubahan</span>
                </button>
            </div>
        </form>
    </div>
</template>
