<script setup>
import { computed, onMounted, ref } from 'vue';
import { api } from '../../services/api';

const emit = defineEmits(['navigate']);

const qLeadId = new URLSearchParams(window.location.search).get('lead_id') || '';
const qRegId = new URLSearchParams(window.location.search).get('registration_id') || '';

const selectedKey = ref('');

const form = ref({
    lead_id: '',
    registration_id: '',
    product_id: '',
    amount: '',
    bank_name: 'BCA',
    sender_name: '',
    proof: null,
});

const leads = ref([]);
const registrations = ref([]);
const loading = ref(false);
const error = ref('');

const registrationCandidates = computed(() => {
    return registrations.value
        .filter(r => r.status === 'registered' && r.customer_id)
        .map(r => {
            const product = r.product || r.schedule?.product || null;
            const price = Number(product?.price || (r.amount ? r.amount / 1.10 : 0));
            const amount = Number(r.amount || Math.round(price * 1.10));
            return {
                key: `reg:${r.id}`,
                type: 'registration',
                registration_id: r.id,
                lead_id: r.customer?.lead_id || r.customer?.lead?.id || null,
                product_id: r.product_id || product?.id || null,
                name: r.customer?.name || ('Customer #' + r.customer_id),
                phone: r.customer?.phone || '-',
                company: r.customer?.company || r.customer?.institution?.name || '-',
                code: r.registration_number || ('#REG-' + r.id),
                product_name: product?.name || r.product_name || '-',
                product_price: price,
                amount: amount,
                commission: Math.round(price * 0.10),
                is_repeat_order: r.is_repeat_order === true,
            };
        });
});

const customerCandidates = computed(() => registrationCandidates.value.filter(candidate => candidate.is_repeat_order));

const leadCandidates = computed(() => {
    const registeredPrograms = new Set(registrationCandidates.value.map(candidate => `${candidate.lead_id}:${candidate.product_id}`));
    const unregisteredLeads = leads.value
        .filter(l => {
            const hasProduct = Boolean(l.product_id && (l.product || l.product_name));
            const hasValidPayment = l.payments && l.payments.some(p => p.status === 'valid');
            const alreadyRegistered = registeredPrograms.has(`${l.id}:${l.product_id}`);
            return hasProduct && !hasValidPayment && !alreadyRegistered && l.status !== 'lost' && l.status !== 'inactive';
        })
        .map(l => {
            const price = Number(l.product?.price || l.deal_price || 0);
            const amount = Math.round(price * 1.10);
            return {
                key: `lead:${l.id}`,
                type: 'lead',
                registration_id: null,
                lead_id: l.id,
                product_id: l.product_id,
                name: l.name,
                phone: l.phone || l.whatsapp || '-',
                company: l.company || l.institution || '-',
                code: l.lead_code || ('#LDA-' + l.id),
                product_name: l.product?.name || l.product_name || '-',
                product_price: price,
                amount: amount,
                commission: Math.round(price * 0.10),
                is_repeat_order: false,
            };
        });

    return [
        ...registrationCandidates.value.filter(candidate => !candidate.is_repeat_order),
        ...unregisteredLeads,
    ];
});

const allCandidates = computed(() => {
    return [...customerCandidates.value, ...leadCandidates.value];
});

const selectedCandidate = computed(() => {
    if (!selectedKey.value) return null;
    return allCandidates.value.find(c => c.key === selectedKey.value) || null;
});

function onCandidateChange() {
    const item = selectedCandidate.value;
    if (item) {
        if (!form.value.sender_name) {
            form.value.sender_name = item.name;
        }
        form.value.amount = item.amount;
        form.value.lead_id = item.lead_id || '';
        form.value.product_id = item.product_id || '';
        form.value.registration_id = item.registration_id || '';
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

async function loadData() {
    try {
        const [lRes, rRes] = await Promise.all([
            api.list('leads', { per_page: 100, for_payment: 1 }),
            api.list('registrations', { per_page: 100, status: 'registered', for_payment: 1 }),
        ]);

        leads.value = lRes.data || [];
        registrations.value = rRes.data || [];

        let found;
        if (qRegId) {
            found = registrationCandidates.value.find(candidate => String(candidate.registration_id) === qRegId);
        } else if (qLeadId) {
            const productId = leads.value.find(lead => String(lead.id) === qLeadId)?.product_id;
            const matchingCandidates = leadCandidates.value.filter(candidate => String(candidate.lead_id) === qLeadId);
            found = matchingCandidates.find(candidate => candidate.product_id === productId) ?? matchingCandidates[0];
        }

        if (found) {
            selectedKey.value = found.key;
            onCandidateChange();
        }
    } catch (e) {
        console.error(e);
    }
}

function handleFile(e) {
    form.value.proof = e.target.files[0] || null;
}

async function submit() {
    const item = selectedCandidate.value;
    if (!item) {
        error.value = 'Silakan pilih peserta / customer yang menunggu pembayaran.';
        return;
    }
    if (!form.value.proof) {
        error.value = 'Silakan pilih gambar struk sebelum mengirim bukti pembayaran.';
        return;
    }

    loading.value = true;
    error.value = '';
    try {
        const fd = new FormData();
        if (item.lead_id) {
            fd.append('lead_id', item.lead_id);
        }
        if (item.registration_id) {
            fd.append('registration_id', item.registration_id);
        }
        if (item.product_id) {
            fd.append('product_id', item.product_id);
        }
        fd.append('amount', form.value.amount);
        fd.append('bank_name', form.value.bank_name);
        fd.append('sender_name', form.value.sender_name);
        fd.append('account_name', form.value.sender_name);
        fd.append('proof_file', form.value.proof);

        await api.create('payments', fd);
        emit('navigate', '/payments');
    } catch (err) {
        error.value = err.message || 'Gagal mengirim bukti pembayaran.';
    } finally {
        loading.value = false;
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
                <h1 class="text-xl font-bold text-gray-900">Input Bukti Pembayaran</h1>
                <p class="text-xs text-gray-500 mt-0.5">Kirim bukti transfer peserta atau customer terdaftar untuk diverifikasi admin</p>
            </div>
            <button @click="emit('navigate', '/payments')" class="self-start sm:self-auto whitespace-nowrap shrink-0 text-xs font-semibold text-gray-500 hover:text-gray-900">
                &larr; Batal & Kembali
            </button>
        </div>

        <div v-if="error" class="p-3 bg-red-50 border border-red-200 text-red-600 text-xs">{{ error }}</div>

        <form @submit.prevent="submit" class="bg-white p-4 sm:p-6 border border-gray-100 shadow-xs space-y-4">
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Pilih Peserta / Customer Menunggu Pembayaran *</label>
                <select v-model="selectedKey" @change="onCandidateChange" required
                    class="w-full px-3.5 py-2.5 border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500/20 bg-white">
                    <option value="" disabled>-- Pilih Peserta yang Menunggu Pembayaran --</option>
                    <optgroup v-if="customerCandidates.length > 0" label="Customer Terdaftar (Repeat Order)">
                        <option v-for="c in customerCandidates" :key="c.key" :value="c.key">
                            [Customer] {{ c.name }} ({{ c.phone }}) &bull; {{ c.product_name }} &bull; Total Transfer: Rp {{ Number(c.amount).toLocaleString('id-ID') }} (Komisi Anda: Rp {{ Number(c.commission).toLocaleString('id-ID') }})
                        </option>
                    </optgroup>
                    <optgroup v-if="leadCandidates.length > 0" label="Calon Peserta Baru (Leads & Prospek)">
                        <option v-for="l in leadCandidates" :key="l.key" :value="l.key">
                            [Prospek] {{ l.name }} ({{ l.phone }}) &bull; {{ l.product_name }} &bull; Total Transfer: Rp {{ Number(l.amount).toLocaleString('id-ID') }} (Komisi Anda: Rp {{ Number(l.commission).toLocaleString('id-ID') }})
                        </option>
                    </optgroup>
                </select>
                <p v-if="allCandidates.length === 0" class="text-[11px] text-amber-600 mt-1.5">
                    Belum ada calon peserta atau customer yang menunggu pembayaran. Daftarkan terlebih dahulu di menu <strong>Pendaftaran Peserta</strong>.
                </p>
                <p v-else class="text-[11px] text-gray-500 mt-1">
                    Daftar di atas menampilkan calon peserta (leads) maupun customer terdaftar (repeat order) yang sudah didaftarkan ke program pelatihan dan menunggu bukti transfer.
                </p>
            </div>

            <!-- Preview Rincian Tagihan & Komisi -->
            <div v-if="selectedCandidate" class="p-3.5 sm:p-4 bg-orange-50/70 border border-orange-200/90 text-xs space-y-2.5">
                <div class="flex flex-wrap items-center justify-between gap-2">
                    <span class="font-bold text-gray-900 flex flex-wrap items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full" :class="selectedCandidate.is_repeat_order ? 'bg-emerald-500' : 'bg-blue-500'"></span>
                        Rincian Transaksi: {{ selectedCandidate.name }}
                        <span class="text-[10px] px-1.5 py-0.2 rounded font-medium"
                            :class="selectedCandidate.is_repeat_order ? 'bg-emerald-100 text-emerald-800' : 'bg-blue-100 text-blue-800'">
                            {{ selectedCandidate.is_repeat_order ? 'Customer Terdaftar' : 'Calon Peserta (Lead)' }}
                        </span>
                    </span>
                    <span class="text-[11px] font-mono px-2 py-0.5 bg-orange-100 text-orange-800 font-semibold">
                        {{ selectedCandidate.code }}
                    </span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 text-[11.5px]">
                    <div class="bg-white p-3 border border-orange-100 shadow-2xs">
                        <span class="text-gray-400 block text-[10px] font-semibold uppercase tracking-wider">Harga Resmi Katalog</span>
                        <span class="font-bold text-gray-800 text-xs">Rp {{ Number(selectedCandidate.product_price).toLocaleString('id-ID') }}</span>
                    </div>
                    <div class="bg-white p-3 border border-emerald-200 shadow-2xs">
                        <span class="text-emerald-700 block text-[10px] font-bold uppercase tracking-wider">Komisi Sales Anda (10%)</span>
                        <span class="font-black text-emerald-600 text-sm">Rp {{ Number(selectedCandidate.commission).toLocaleString('id-ID') }}</span>
                    </div>
                    <div class="bg-white p-3 border border-orange-200 shadow-2xs">
                        <span class="text-orange-700 block text-[10px] font-bold uppercase tracking-wider">Customer Wajib Transfer</span>
                        <span class="font-black text-orange-600 text-sm">Rp {{ Number(selectedCandidate.amount).toLocaleString('id-ID') }}</span>
                    </div>
                </div>
                <div class="pt-2 border-t border-orange-200/70 text-[11px] text-gray-600">
                    Nominal yang harus dibayarkan customer adalah total tagihan resmi: <strong>Rp {{ Number(selectedCandidate.amount).toLocaleString('id-ID') }}</strong>. Komisi Anda 10% sebesar <strong>Rp {{ Number(selectedCandidate.commission).toLocaleString('id-ID') }}</strong> otomatis dicairkan setelah admin memverifikasi bukti struk ini.
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
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Bank Tujuan (Rekening Disty) *</label>
                    <select v-model="form.bank_name" required
                        class="w-full px-3.5 py-2.5 border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500/20 bg-white">
                        <option value="BCA">BCA (Disty Akademi)</option>
                        <option value="Mandiri">Mandiri (Disty Akademi)</option>
                        <option value="BNI">BNI (Disty Akademi)</option>
                        <option value="BRI">BRI (Disty Akademi)</option>
                        <option value="BSI">BSI (Disty Akademi)</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Nama Pengirim (Sesuai Rekening Customer) *</label>
                <input v-model="form.sender_name" type="text" required
                    class="w-full px-3.5 py-2.5 border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500/20 outline-none" placeholder="Nama pemilik rekening customer yang transfer, misal: Maya Anggraini" />
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Unggah Struk / Bukti Transfer *</label>
                <input type="file" accept="image/jpeg,image/png,image/webp,image/avif,image/gif,image/bmp" required @change="handleFile"
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
