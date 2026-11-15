<script setup>
import { computed, onMounted, ref } from 'vue';
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

const selectedLead = computed(() => {
    return leads.value.find(l => String(l.id) === String(form.value.lead_id)) || null;
});

function onLeadChange() {
    const lead = selectedLead.value;
    if (lead) {
        if (!form.value.sender_name) {
            form.value.sender_name = lead.name;
        }
        // Rumus: Total Transfer = Harga Asli Katalog + 10% Komisi Sales
        const basePrice = Number(lead.product?.price || 0);
        if (basePrice > 0) {
            form.value.amount = Math.round(basePrice * 1.10);
        } else if (lead.deal_price && Number(lead.deal_price) > 0) {
            form.value.amount = Math.round(Number(lead.deal_price));
        }
    }
}

function formatRupiahInput(val) {
    if (val === null || val === undefined || val === '') return '';
    const num = Number(String(val).replace(/\D/g, ''));
    if (isNaN(num)) return '';
    return num.toLocaleString('id-ID');
}

function handleAmountInput(e) {
    const raw = e.target.value.replace(/\D/g, '');
    form.value.amount = raw ? Number(raw) : 0;
    e.target.value = raw ? Number(raw).toLocaleString('id-ID') : '';
}

async function loadLeads() {
    try {
        const res = await api.list('leads', { per_page: 100, for_payment: 1 });
        const all = res.data || [];
        // Filter ketat: Hanya calon peserta yang sudah didaftarkan sales ke produk dan belum lunas diverifikasi
        leads.value = all.filter(l => {
            const hasProduct = Boolean(l.product_id && (l.product || l.product_name));
            const hasValidPayment = l.payments && l.payments.some(p => p.status === 'valid');
            return hasProduct && !hasValidPayment && l.status !== 'lost' && l.status !== 'inactive';
        });

        // Jika URL membuka spesifik lead_id (misal dari halaman prospek) dan belum ada di list
        if (form.value.lead_id && !leads.value.some(l => String(l.id) === String(form.value.lead_id))) {
            try {
                const singleRes = await api.get('leads', form.value.lead_id);
                const singleLead = singleRes.data || singleRes;
                if (singleLead && singleLead.id) {
                    leads.value.unshift(singleLead);
                }
            } catch (err) {
                console.error(err);
            }
        }

        if (form.value.lead_id) {
            onLeadChange();
        }
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
        fd.append('account_name', form.value.sender_name);
        if (selectedLead.value?.product_id) {
            fd.append('product_id', selectedLead.value.product_id);
        }
        if (form.value.proof) {
            fd.append('proof_file', form.value.proof);
            fd.append('proof', form.value.proof);
        }

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

        <div v-if="error" class="p-3 bg-red-50 border border-red-200 text-red-600 text-xs">{{ error }}</div>

        <form @submit.prevent="submit" class="bg-white p-6 border border-gray-100 shadow-xs space-y-4">
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Pilih Prospek / Peserta *</label>
                <select v-model="form.lead_id" @change="onLeadChange" required
                    class="w-full px-3.5 py-2.5 border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500/20 bg-white">
                    <option value="" disabled>-- Pilih Calon Peserta yang Menunggu Pembayaran --</option>
                    <option v-for="l in leads" :key="l.id" :value="l.id">
                        {{ l.name }} ({{ l.phone || l.email || '-' }}) &bull; {{ l.product?.name }} &bull; Customer Transfer: Rp {{ Number(Math.round((l.product?.price || l.deal_price || 0) * 1.10)).toLocaleString('id-ID') }} (Komisi Anda: Rp {{ Number(Math.round((l.product?.price || l.deal_price || 0) * 0.10)).toLocaleString('id-ID') }})
                    </option>
                </select>
                <p v-if="leads.length === 0" class="text-[11px] text-amber-600 mt-1.5">
                    Belum ada calon peserta yang didaftarkan ke program pelatihan atau semua pendaftaran telah lunas diverifikasi. Daftarkan calon peserta terlebih dahulu di menu <strong>Pendaftaran Peserta</strong>.
                </p>
                <p v-else class="text-[11px] text-gray-500 mt-1">
                    Daftar di atas hanya menampilkan calon peserta yang sudah didaftarkan ke program pelatihan oleh sales dan sedang menunggu konfirmasi pembayaran.
                </p>
            </div>

            <!-- Preview Rincian Tagihan & Komisi -->
            <div v-if="selectedLead" class="p-4 bg-orange-50/70 border border-orange-200/90 text-xs space-y-2.5">
                <div class="flex items-center justify-between">
                    <span class="font-bold text-gray-900">
                        Rincian Transaksi: {{ selectedLead.name }}
                    </span>
                    <span class="text-[11px] font-mono px-2 py-0.5 bg-orange-100 text-orange-800 font-semibold">
                        {{ selectedLead.lead_code || `#${selectedLead.id}` }}
                    </span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 text-[11.5px]">
                    <div class="bg-white p-3 border border-orange-100 shadow-2xs">
                        <span class="text-gray-400 block text-[10px] font-semibold uppercase tracking-wider">Harga Resmi Katalog</span>
                        <span class="font-bold text-gray-800 text-xs">Rp {{ Number(selectedLead.product?.price || 0).toLocaleString('id-ID') }}</span>
                    </div>
                    <div class="bg-white p-3 border border-emerald-200 shadow-2xs">
                        <span class="text-emerald-700 block text-[10px] font-bold uppercase tracking-wider">Komisi Sales Anda (10%)</span>
                        <span class="font-black text-emerald-600 text-sm">Rp {{ Number(Math.round((selectedLead.product?.price || 0) * 0.10)).toLocaleString('id-ID') }}</span>
                    </div>
                    <div class="bg-white p-3 border border-orange-200 shadow-2xs">
                        <span class="text-orange-700 block text-[10px] font-bold uppercase tracking-wider">Customer Wajib Transfer</span>
                        <span class="font-black text-orange-600 text-sm">Rp {{ Number(Math.round((selectedLead.product?.price || 0) * 1.10) || form.amount || 0).toLocaleString('id-ID') }}</span>
                    </div>
                </div>
                <div class="pt-2 border-t border-orange-200/70 text-[11px] text-gray-600">
                    Nominal yang harus dibayarkan customer adalah total tagihan resmi: <strong>Rp {{ Number(Math.round((selectedLead.product?.price || 0) * 1.10) || form.amount || 0).toLocaleString('id-ID') }}</strong>. Komisi Anda 10% sebesar <strong>Rp {{ Number(Math.round((selectedLead.product?.price || 0) * 0.10)).toLocaleString('id-ID') }}</strong> otomatis dicairkan setelah admin memverifikasi bukti struk ini.
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Nominal Transfer Customer (Rp) *</label>
                    <div class="flex items-center border border-gray-200 bg-white overflow-hidden shadow-2xs focus-within:border-orange-500 focus-within:ring-2 focus-within:ring-orange-500/20 transition-all">
                        <span class="inline-flex items-center self-stretch px-3.5 bg-gray-50 text-xs font-bold text-gray-500 border-r border-gray-200 select-none">
                            Rp
                        </span>
                        <input
                            type="text"
                            :value="formatRupiahInput(form.amount)"
                            @input="handleAmountInput"
                            required
                            class="flex-1 w-full px-3.5 py-2.5 text-xs font-bold text-gray-900 outline-none border-0 focus:ring-0 bg-transparent"
                            placeholder="0"
                        />
                    </div>
                    <p class="text-[11px] text-gray-500 mt-1">
                        Nominal transfer customer adalah harga katalog resmi ditambah komisi sales 10%.
                    </p>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Bank Tujuan *</label>
                    <select v-model="form.bank_name" required
                        class="w-full px-3.5 py-2.5 border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500/20 bg-white">
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
                    class="w-full px-3.5 py-2.5 border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500/20 outline-none" placeholder="Misal: Hendra Gunawan" />
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Unggah Struk / Bukti Transfer</label>
                <input type="file" accept="image/*" @change="handleFile"
                    class="w-full px-3.5 py-2.5 border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500/20 bg-white" />
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
