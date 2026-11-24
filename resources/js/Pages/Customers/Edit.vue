<script setup>
import { onMounted, ref } from 'vue';
import { api } from '../../services/api';

const props = defineProps({
    id: { type: [String, Number], required: true },
});

const emit = defineEmits(['navigate']);

const form = ref({
    name: '',
    phone: '',
    email: '',
    company: '',
    address: '',
});
const loading = ref(true);
const saving = ref(false);
const error = ref('');

async function loadCustomer() {
    loading.value = true;
    try {
        const res = await api.get('customers', props.id);
        const c = res.data || res;
        form.value = {
            name: c.name || '',
            phone: c.phone || '',
            email: c.email || '',
            company: c.company || '',
            address: c.address || '',
        };
    } catch (err) {
        error.value = err.message || 'Gagal memuat customer.';
    } finally {
        loading.value = false;
    }
}

async function save() {
    saving.value = true;
    error.value = '';
    try {
        await api.update('customers', props.id, form.value);
        emit('navigate', `/customers/${props.id}`);
    } catch (err) {
        error.value = err.message || 'Gagal memperbarui customer.';
    } finally {
        saving.value = false;
    }
}

onMounted(() => {
    loadCustomer();
});
</script>

<template>
    <div class="max-w-2xl mx-auto space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Edit Customer #{{ id }}</h1>
                <p class="text-xs text-gray-500 mt-0.5">Perbarui profil dan kontak customer</p>
            </div>
            <button @click="emit('navigate', '/customers')" class="self-start sm:self-auto whitespace-nowrap shrink-0 text-xs font-semibold text-gray-500 hover:text-gray-900">
                &larr; Batal & Kembali
            </button>
        </div>

        <div v-if="loading" class="text-center py-12 text-xs text-gray-400">Memuat data...</div>
        <div v-else-if="error" class="p-3 bg-red-50 border border-red-200 text-red-600 text-xs">{{ error }}</div>

        <form v-else @submit.prevent="save" class="bg-white p-4 sm:p-6 border border-gray-100 shadow-xs space-y-4">
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Nama Customer *</label>
                <input v-model="form.name" type="text" required
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
                <label class="block text-xs font-semibold text-gray-700 mb-1">Perusahaan / Instansi</label>
                <input v-model="form.company" type="text"
                    class="w-full px-3.5 py-2.5 border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500/20 outline-none" />
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Alamat Domisili</label>
                <textarea v-model="form.address" rows="2"
                    class="w-full px-3.5 py-2.5 border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500/20 outline-none"></textarea>
            </div>

            <div class="pt-2 flex flex-wrap justify-end gap-2">
                <button type="button" @click="emit('navigate', `/customers/${id}`)" class="px-4 py-2.5 rounded-xl border border-gray-200 font-semibold text-xs text-gray-600 hover:bg-gray-50">Batal</button>
                <button type="submit" :disabled="saving" class="px-5 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-bold text-xs shadow-sm disabled:opacity-50">
                    <span v-if="saving">Menyimpan...</span>
                    <span v-else>Simpan Perubahan</span>
                </button>
            </div>
        </form>
    </div>
</template>
