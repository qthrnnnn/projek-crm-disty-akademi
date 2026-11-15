<script setup>
import { ref } from 'vue';
import { api } from '../../services/api';

const emit = defineEmits(['navigate']);

const form = ref({
    name: '',
    type: 'Kampus',
    phone: '',
    email: '',
    address: '',
    city: '',
});
const loading = ref(false);
const error = ref('');

async function save() {
    loading.value = true;
    error.value = '';
    try {
        await api.create('institutions', form.value);
        emit('navigate', '/institutions');
    } catch (err) {
        error.value = err.message || 'Gagal menyimpan instansi.';
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <div class="max-w-2xl mx-auto space-y-5">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Tambah Mitra Instansi</h1>
                <p class="text-xs text-gray-500 mt-0.5">Input kampus, sekolah, perusahaan, atau organisasi mitra</p>
            </div>
            <button @click="emit('navigate', '/institutions')" class="text-xs font-semibold text-gray-500 hover:text-gray-900">
                &larr; Batal & Kembali
            </button>
        </div>

        <div v-if="error" class="p-3 bg-red-50 border border-red-200 text-red-600 text-xs">{{ error }}</div>

        <form @submit.prevent="save" class="bg-white p-6 border border-gray-100 shadow-xs space-y-4">
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Nama Instansi *</label>
                <input v-model="form.name" type="text" required
                    class="w-full px-3.5 py-2.5 border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500/20 outline-none" placeholder="Misal: Universitas Brawijaya / PT Maju Bersama" />
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Kategori Instansi</label>
                <select v-model="form.type"
                    class="w-full px-3.5 py-2.5 border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500/20 bg-white">
                    <option value="Kampus">Perguruan Tinggi / Kampus</option>
                    <option value="Sekolah">SMK / SMA</option>
                    <option value="Perusahaan">Perusahaan Swasta / BUMN</option>
                    <option value="Pemerintah">Instansi Pemerintah</option>
                    <option value="Yayasan">Yayasan / Organisasi</option>
                </select>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Kontak Instansi (Telepon / WhatsApp)</label>
                    <input v-model="form.phone" type="text"
                        class="w-full px-3.5 py-2.5 border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500/20 outline-none" placeholder="08xxxxxxxxxx / (031) xxxxxx" />
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Email Instansi</label>
                    <input v-model="form.email" type="email"
                        class="w-full px-3.5 py-2.5 border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500/20 outline-none" placeholder="kontak@instansi.ac.id" />
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Alamat Instansi</label>
                <textarea v-model="form.address" rows="3"
                    class="w-full px-3.5 py-2.5 border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500/20 outline-none" placeholder="Alamat lengkap instansi..."></textarea>
            </div>

            <div class="pt-2 flex justify-end gap-2">
                <button type="button" @click="emit('navigate', '/institutions')" class="px-4 py-2.5 rounded-xl border border-gray-200 font-semibold text-xs text-gray-600 hover:bg-gray-50">Batal</button>
                <button type="submit" :disabled="loading" class="px-5 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-bold text-xs shadow-sm disabled:opacity-50">
                    <span v-if="loading">Menyimpan...</span>
                    <span v-else>Simpan Instansi</span>
                </button>
            </div>
        </form>
    </div>
</template>
