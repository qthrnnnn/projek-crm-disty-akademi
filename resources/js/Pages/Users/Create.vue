<script setup>
import { ref } from 'vue';
import { api } from '../../services/api';

const emit = defineEmits(['navigate']);

const form = ref({
    name: '',
    email: '',
    password: '',
    role: 'sales',
    phone: '',
    bank_name: 'BCA',
    bank_account_number: '',
    bank_account_name: '',
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

        <div v-if="error" class="p-3 bg-red-50 border border-red-200 text-red-600 text-xs">{{ error }}</div>

        <form @submit.prevent="save" class="bg-white p-6 border border-gray-100 shadow-xs space-y-4">
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Nama Pengguna *</label>
                <input v-model="form.name" type="text" required
                    class="w-full px-3.5 py-2.5 border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500/20 outline-none" placeholder="Misal: Andi Wijaya" />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Email Login *</label>
                    <input v-model="form.email" type="email" required
                        class="w-full px-3.5 py-2.5 border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500/20 outline-none" placeholder="andi@distyakademi.com" />
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Password Awal *</label>
                    <input v-model="form.password" type="password" required minlength="6"
                        class="w-full px-3.5 py-2.5 border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500/20 outline-none" placeholder="Minimal 6 karakter" />
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Role Hak Akses *</label>
                    <select v-model="form.role" required
                        class="w-full px-3.5 py-2.5 border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500/20 bg-white">
                        <option value="sales">Sales & Affiliate (Kelola Lead Pribadi & Komisi)</option>
                        <option value="admin">Administrator CRM (Akses Seluruh Modul & Manajemen)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">No. WhatsApp / HP</label>
                    <input v-model="form.phone" type="text"
                        class="w-full px-3.5 py-2.5 border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500/20 outline-none" placeholder="08123456789" />
                </div>
            </div>

            <div class="pt-3 border-t border-gray-100">
                <h3 class="text-xs font-bold text-gray-800 mb-2">Rekening Bank Pencairan Komisi (Khusus Sales)</h3>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-600 mb-1">Nama Bank</label>
                        <select v-model="form.bank_name" class="w-full px-3 py-2 border border-gray-200 text-xs bg-white">
                            <option value="BCA">BCA</option>
                            <option value="Bank Mandiri">Bank Mandiri</option>
                            <option value="BNI">BNI</option>
                            <option value="BRI">BRI</option>
                            <option value="BSI">BSI</option>
                            <option value="CIMB Niaga">CIMB Niaga</option>
                            <option value="Bank Permata">Bank Permata</option>
                            <option value="Bank Danamon">Bank Danamon</option>
                            <option value="Bank Jatim">Bank Jatim</option>
                            <option value="Bank Jago">Bank Jago</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-600 mb-1">Nomor Rekening</label>
                        <input v-model="form.bank_account_number" type="text" placeholder="Contoh: 883019281"
                            class="w-full px-3 py-2 border border-gray-200 text-xs font-mono" />
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-600 mb-1">Atas Nama Rekening</label>
                        <input v-model="form.bank_account_name" type="text" placeholder="Sesuai buku tabungan"
                            class="w-full px-3 py-2 border border-gray-200 text-xs" />
                    </div>
                </div>
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
