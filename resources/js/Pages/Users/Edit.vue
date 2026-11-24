<script setup>
import { onMounted, ref } from 'vue';
import { api } from '../../services/api';

const props = defineProps({
    id: { type: [String, Number], required: true },
});

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
const loading = ref(true);
const saving = ref(false);
const error = ref('');

async function loadData() {
    loading.value = true;
    try {
        const res = await api.get('users', props.id);
        const u = res.data || res;
        form.value = {
            name: u.name || '',
            email: u.email || '',
            password: '',
            role: u.role || 'sales',
            phone: u.phone || '',
            bank_name: u.bank_name || 'BCA',
            bank_account_number: u.bank_account_number || '',
            bank_account_name: u.bank_account_name || u.name || '',
        };
    } catch (err) {
        error.value = err.message || 'Gagal memuat pengguna.';
    } finally {
        loading.value = false;
    }
}

async function save() {
    saving.value = true;
    error.value = '';
    try {
        const payload = {
            name: form.value.name,
            email: form.value.email,
            role: form.value.role,
            phone: form.value.phone,
            bank_name: form.value.bank_name,
            bank_account_number: form.value.bank_account_number,
            bank_account_name: form.value.bank_account_name,
        };
        if (form.value.password) payload.password = form.value.password;
        await api.update('users', props.id, payload);
        emit('navigate', '/users');
    } catch (err) {
        error.value = err.message || 'Gagal memperbarui pengguna.';
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
                <h1 class="text-xl font-bold text-gray-900">Edit Akun Pengguna</h1>
                <p class="text-xs text-gray-500 mt-0.5">Perbarui profil, akses peran, dan nomor rekening pencairan komisi</p>
            </div>
            <button @click="emit('navigate', '/users')" class="self-start sm:self-auto whitespace-nowrap shrink-0 text-xs font-semibold text-gray-500 hover:text-gray-900">
                &larr; Batal & Kembali
            </button>
        </div>

        <div v-if="loading" class="text-center py-12 text-xs text-gray-400">Memuat data pengguna...</div>
        <div v-else>
            <div v-if="error" class="p-3 mb-4 bg-red-50 border border-red-200 text-red-600 text-xs">{{ error }}</div>

            <form @submit.prevent="save" class="bg-white p-4 sm:p-6 border border-gray-100 shadow-xs space-y-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Nama Pengguna *</label>
                    <input v-model="form.name" type="text" required
                        class="w-full px-3.5 py-2.5 border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500/20 outline-none" />
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Email Login *</label>
                        <input v-model="form.email" type="email" required
                            class="w-full px-3.5 py-2.5 border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500/20 outline-none" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Password Baru (Opsional)</label>
                        <input v-model="form.password" type="password" minlength="6"
                            class="w-full px-3.5 py-2.5 border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500/20 outline-none" placeholder="Kosongkan jika tidak diubah" />
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

                <!-- Bank Info Section -->
                <div class="pt-4 border-t border-gray-100">
                    <div class="mb-3">
                        <h3 class="text-xs font-bold text-gray-900">Rekening Bank Pencairan Komisi (Sales)</h3>
                        <p class="text-[11px] text-gray-500">Nomor rekening tujuan transfer komisi 10% setiap closing</p>
                    </div>

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
                                <option value="Seabank">Seabank</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-gray-600 mb-1">Nomor Rekening</label>
                            <input v-model="form.bank_account_number" type="text" placeholder="Contoh: 883019281"
                                class="w-full px-3 py-2 border border-gray-200 text-xs font-mono font-medium" />
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-gray-600 mb-1">Atas Nama Rekening</label>
                            <input v-model="form.bank_account_name" type="text" placeholder="Sesuai buku tabungan"
                                class="w-full px-3 py-2 border border-gray-200 text-xs" />
                        </div>
                    </div>
                </div>

                <div class="pt-2 flex flex-wrap justify-end gap-2">
                    <button type="button" @click="emit('navigate', '/users')" class="px-4 py-2.5 rounded-xl border border-gray-200 font-semibold text-xs text-gray-600 hover:bg-gray-50">Batal</button>
                    <button type="submit" :disabled="saving" class="px-5 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-bold text-xs shadow-sm disabled:opacity-50">
                        <span v-if="saving">Menyimpan...</span>
                        <span v-else>Simpan Perubahan</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</template>
