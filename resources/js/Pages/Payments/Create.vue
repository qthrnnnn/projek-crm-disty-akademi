<script setup>
import { onMounted, ref } from 'vue';
import { api } from '../../services/api';

const emit = defineEmits(['navigate']);

const leadId = new URLSearchParams(window.location.search).get('lead_id') || '';

const form = ref({
    lead_id: leadId,
    amount: '',
    bank_name: 'BCA',
    sender_name: '',
    proof: null,
});

const leads = ref([]);
const loading = ref(false);
const error = ref('');

async function loadLeads() {
    try {
        const res = await api.list('leads', { per_page: 100 });
        leads.value = res.data || [];
    } catch (e) {
        console.error(e);
    }
}

function handleFile(e) {
    form.value.proof = e.target.files[0] || null;
}

async function submit() {
    loading.value = true;
    error.value = '';
    try {
        const fd = new FormData();
        fd.append('lead_id', form.value.lead_id);
        fd.append('amount', form.value.amount);
        fd.append('bank_name', form.value.bank_name);
        fd.append('sender_name', form.value.sender_name);
        if (form.value.proof) fd.append('proof', form.value.proof);

        await api.create('payments', fd);
        emit('navigate', '/payments');
    } catch (err) {
        error.value = err.message || 'Gagal mengirim bukti pembayaran.';
    } finally {
        loading.value = false;
    }
}

onMounted(() => {
    loadLeads();
});
</script>

<template>
    <div class="max-w-2xl mx-auto space-y-5">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Input Bukti Pembayaran</h1>
                <p class="text-xs text-gray-500 mt-0.5">Kirim bukti transfer calon peserta untuk diverifikasi admin</p>
            </div>
            <button @click="emit('navigate', '/payments')" class="text-xs font-semibold text-gray-500 hover:text-gray-900">
                &larr; Batal & Kembali
            </button>
        </div>

        <div v-if="error" class="p-3 rounded-xl bg-red-50 border border-red-200 text-red-600 text-xs">{{ error }}</div>

        <form @submit.prevent="submit" class="bg-white p-6 rounded-2xl border border-gray-100 shadow-xs space-y-4">
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Pilih Prospek / Peserta *</label>
                <select v-model="form.lead_id" required
                    class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500/20 bg-white">
                    <option value="">Pilih Prospek</option>
                    <option v-for="l in leads" :key="l.id" :value="l.id">{{ l.name }} ({{ l.phone || l.email || '-' }})</option>
                </select>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Nominal Transfer (Rp) *</label>
                    <input v-model="form.amount" type="number" required min="1000"
                        class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500/20 outline-none" placeholder="1500000" />
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Bank Tujuan *</label>
                    <select v-model="form.bank_name" required
                        class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500/20 bg-white">
                        <option value="BCA">BCA (Disty Akademi)</option>
                        <option value="Mandiri">Mandiri</option>
                        <option value="BNI">BNI</option>
                        <option value="BRI">BRI</option>
                        <option value="BSI">BSI</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Nama Pengirim (Sesuai Rekening) *</label>
                <input v-model="form.sender_name" type="text" required
                    class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500/20 outline-none" placeholder="Misal: Dimas Pratama" />
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Unggah Struk / Bukti Transfer</label>
                <input type="file" accept="image/*" @change="handleFile"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500/20 bg-white" />
            </div>

            <div class="pt-2 flex justify-end gap-2">
                <button type="button" @click="emit('navigate', '/payments')" class="px-4 py-2.5 rounded-xl border border-gray-200 font-semibold text-xs text-gray-600 hover:bg-gray-50">Batal</button>
                <button type="submit" :disabled="loading" class="px-5 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-bold text-xs shadow-sm disabled:opacity-50">
                    <span v-if="loading">Mengirim...</span>
                    <span v-else>Kirim Bukti Bayar</span>
                </button>
            </div>
        </form>
    </div>
</template>
