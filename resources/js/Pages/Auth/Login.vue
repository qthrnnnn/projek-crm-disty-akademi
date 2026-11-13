<script setup>
import { ref } from 'vue';
import { api } from '../../services/api';

const emit = defineEmits(['authenticated']);

const email = ref('admin@distyakademi.com');
const password = ref('password');
const loading = ref(false);
const error = ref('');

async function submit() {
    loading.value = true;
    error.value = '';
    try {
        const res = await api.login(email.value, password.value);
        emit('authenticated', res.user);
    } catch (err) {
        error.value = err.message || 'Login gagal. Periksa kembali email dan password.';
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <main class="min-h-screen bg-gray-50 flex items-center justify-center p-4">
        <div class="w-full max-w-sm bg-white rounded-3xl p-8 shadow-xl shadow-gray-200/50 border border-gray-100">
            <div class="text-center mb-8">
                <div class="w-14 h-14 rounded-2xl bg-orange-100 text-orange-600 flex items-center justify-center mx-auto mb-3 shadow-xs">
                    <img src="/images/disty-icon.png" alt="Logo" class="w-9 h-9 rounded-xl object-contain" />
                </div>
                <h1 class="text-xl font-bold text-gray-900">Disty Akademi CRM</h1>
                <p class="text-xs text-gray-400 mt-1">Masuk ke sistem manajemen & penjualan</p>
            </div>

            <div v-if="error" class="mb-4 p-3 rounded-xl bg-red-50 border border-red-200 text-red-600 text-xs">
                {{ error }}
            </div>

            <form @submit.prevent="submit" class="space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Email</label>
                    <input v-model="email" type="email" required
                        class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 outline-none transition"
                        placeholder="admin@distyakademi.com" />
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Password</label>
                    <input v-model="password" type="password" required
                        class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 outline-none transition"
                        placeholder="••••••••" />
                </div>
                <button type="submit" :disabled="loading"
                    class="w-full py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-bold text-xs shadow-md shadow-orange-500/20 transition-all flex items-center justify-center gap-2 disabled:opacity-50">
                    <span v-if="loading">Memproses...</span>
                    <span v-else>Masuk ke CRM</span>
                </button>
            </form>
        </div>
    </main>
</template>
