<script setup>
import { ref } from 'vue';
import { api } from '../../services/api';

const emit = defineEmits(['authenticated']);

const email = ref('admin@distyakademi.com');
const password = ref('password');
const remember = ref(false);
const loading = ref(false);
const error = ref('');
const currentYear = new Date().getFullYear();

async function submit() {
    loading.value = true;
    error.value = '';
    try {
        const res = await api.login(email.value, password.value, remember.value);
        emit('authenticated', res.user);
    } catch (err) {
        error.value = err.message || 'Login gagal. Periksa kembali email dan password.';
    } finally {
        loading.value = false;
    }
}
</script>

<template>
    <div class="min-h-screen bg-slate-50 flex items-center justify-center p-4 w-full">
        <div class="w-full max-w-md">
            <div class="bg-white border border-blue-100 p-8 shadow-sm">
                <!-- Logo Disty Akademi -->
                <div class="flex justify-center mb-6">
                    <img src="/images/disty-logo.png" alt="Disty Akademi" class="h-12 w-auto object-contain"
                         @error="(e) => { e.target.src = '/images/disty-icon.png'; }" />
                </div>

                <div class="text-center mb-6">
                    <h1 class="text-xl font-bold text-gray-900">Selamat Datang</h1>
                    <p class="text-xs text-gray-500 mt-1">Masuk ke akun Anda untuk melanjutkan</p>
                </div>

                <div v-if="error" class="mb-5 bg-red-50 border border-red-200 px-4 py-3 text-xs text-red-700">
                    <p>{{ error }}</p>
                </div>

                <form @submit.prevent="submit" class="space-y-4 text-xs">
                    <div>
                        <label for="email" class="block font-semibold text-gray-700 mb-1.5">Email</label>
                        <input id="email" v-model="email" type="email" required autofocus
                            class="w-full border border-gray-200 px-3.5 py-2.5 text-gray-800 placeholder-gray-400 focus:outline-none focus:border-blue-500 transition-colors"
                            placeholder="nama@distyakademi.com" />
                    </div>

                    <div>
                        <label for="password" class="block font-semibold text-gray-700 mb-1.5">Password</label>
                        <input id="password" v-model="password" type="password" required
                            class="w-full border border-gray-200 px-3.5 py-2.5 text-gray-800 placeholder-gray-400 focus:outline-none focus:border-blue-500 transition-colors"
                            placeholder="••••••••" />
                    </div>

                    <div class="flex items-center justify-between pt-1">
                        <label class="flex items-center gap-2 cursor-pointer text-gray-600">
                            <input type="checkbox" v-model="remember" class="h-4 w-4 border-gray-300 text-blue-600 focus:ring-blue-500" />
                            <span>Ingat saya</span>
                        </label>
                    </div>

                    <button type="submit" :disabled="loading"
                        class="w-full rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-semibold py-2.5 shadow-sm transition-colors text-xs mt-2 disabled:opacity-50">
                        <span>{{ loading ? 'Memverifikasi...' : 'Masuk' }}</span>
                    </button>
                </form>

                <div class="mt-6 pt-6 border-t border-gray-100 text-center">
                    <p class="text-[11px] text-gray-400">
                        &copy; {{ currentYear }} Disty Akademi. All rights reserved.
                    </p>
                </div>
            </div>
        </div>
    </div>
</template>
