<script setup>
import { ref } from 'vue';
import { api } from '../../services/api';

const emit = defineEmits(['navigate']);

const form = ref({
    name: '',
    email: '',
    password: '',
    role: 'sales',
});
const loading = ref(false);
const error = ref('');

async function save() {
    loading.value = true;
    error.value = '';
    try {
        await api.create('users', form.value);
        emit('navigate', '/users');
    } catch (err) {
        error.value = err.message || 'Gagal membuat pengguna baru.';
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <div class="max-w-2xl mx-auto space-y-5">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Tambah Akun Pengguna</h1>
                <p class="text-xs text-gray-500 mt-0.5">Buat kredensial login untuk tim sales atau admin</p>
            </div>
            <button @click="emit('navigate', '/users')" class="text-xs font-semibold text-gray-500 hover:text-gray-900">
                &larr; Batal & Kembali
            </button>
        </div>

        <div v-if="error" class="p-3 rounded-xl bg-red-50 border border-red-200 text-red-600 text-xs">{{ error }}</div>

        <form @submit.prevent="save" class="bg-white p-6 rounded-2xl border border-gray-100 shadow-xs space-y-4">
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Nama Pengguna *</label>
                <input v-model="form.name" type="text" required
                    class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500/20 outline-none" placeholder="Misal: Andi Wijaya" />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Email Login *</label>
                    <input v-model="form.email" type="email" required
                        class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500/20 outline-none" placeholder="andi@distyakademi.com" />
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Password Awal *</label>
                    <input v-model="form.password" type="password" required minlength="6"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500/20 outline-none" placeholder="Minimal 6 karakter" />
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Role Hak Akses *</label>
                <select v-model="form.role" required
                    class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500/20 bg-white">
                    <option value="sales">Sales & Affiliate (Kelola Lead Pribadi & Komisi)</option>
                    <option value="admin">Administrator CRM (Akses Seluruh Modul & Manajemen)</option>
                </select>
            </div>

            <div class="pt-2 flex justify-end gap-2">
                <button type="button" @click="emit('navigate', '/users')" class="px-4 py-2.5 rounded-xl border border-gray-200 font-semibold text-xs text-gray-600 hover:bg-gray-50">Batal</button>
                <button type="submit" :disabled="loading" class="px-5 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-bold text-xs shadow-sm disabled:opacity-50">
                    <span v-if="loading">Menyimpan...</span>
                    <span v-else>Simpan Akun</span>
                </button>
            </div>
        </form>
    </div>
</template>
