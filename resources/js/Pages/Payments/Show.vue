<script setup>
import { computed, onMounted, ref } from 'vue';
import { api } from '../../services/api';
import Badge from '../../Components/Badge.vue';

const props = defineProps({
    id: { type: [String, Number], required: true },
    user: { type: Object, default: () => ({}) },
});

const emit = defineEmits(['navigate']);

const payment = ref(null);
const loading = ref(true);
const saving = ref(false);
const error = ref('');
const proofAttachment = ref(null);
const proofError = ref('');
const uploadingProof = ref(false);
const proofPreviewFailed = ref(false);
const statusForm = ref({
    status: 'valid',
    verification_notes: '',
});

const isAdmin = computed(() => props.user?.role === 'admin');
const proofSrc = computed(() => getProofSrc(payment.value));
const showProof = computed(() => Boolean(proofSrc.value) && !proofPreviewFailed.value);

function formatCurrency(val) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(val || 0);
}

function formatDate(val) {
    if (!val) return '-';
    try {
        const d = new Date(val);
        if (isNaN(d.getTime())) return val;
        return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
    } catch {
        return val;
    }
}

function formatTime(val) {
    if (!val) return '';
    try {
        const d = new Date(val);
        if (isNaN(d.getTime())) return '';
        return d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', hour12: false }).replace('.', ':');
    } catch {
        return '';
    }
}

function formatDateTime(val) {
    if (!val) return '-';
    try {
        const d = new Date(val);
        if (isNaN(d.getTime())) return val;
        const datePart = d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
        const timePart = d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', hour12: false }).replace('.', ':');
        return `${datePart}, ${timePart} WIB`;
    } catch {
        return val;
    }
}

function getProofSrc(p) {
    if (!p) return '';
    if (p.proof_url) {
        try {
            const urlObj = new URL(p.proof_url);
            return urlObj.pathname;
        } catch {
            return p.proof_url;
        }
    }
    if (p.proof_file) {
        return p.proof_file.startsWith('http') ? p.proof_file : `/storage/${p.proof_file}`;
    }
    if (p.proof_image) {
        return p.proof_image.startsWith('http') ? p.proof_image : `/storage/${p.proof_image}`;
    }
    return '';
}

async function loadPayment() {
    loading.value = true;
    error.value = '';
    try {
        const res = await api.get('payments', props.id);
        const data = res.data || res;
        payment.value = data;
        proofPreviewFailed.value = false;
        statusForm.value.status = data.status || 'valid';
        statusForm.value.verification_notes = data.verification_notes || '';
    } catch (err) {
        error.value = err.message || 'Gagal memuat detail pembayaran.';
    } finally {
        loading.value = false;
    }
}

async function attachProof() {
    if (!proofAttachment.value || uploadingProof.value) return;

    uploadingProof.value = true;
    proofError.value = '';
    try {
        const data = new FormData();
        data.append('proof_file', proofAttachment.value);
        const res = await api.create(`payments/${props.id}/proof`, data);
        payment.value.proof_file = res.data.proof_file;
        payment.value.proof_url = res.data.proof_url;
        proofAttachment.value = null;
        proofPreviewFailed.value = false;
    } catch (err) {
        proofError.value = err.message || 'Gagal melampirkan struk.';
    } finally {
        uploadingProof.value = false;
    }
}

async function submitVerification() {
    if (statusForm.value.status === 'invalid' && !statusForm.value.verification_notes.trim()) {
        alert('Mohon cantumkan keterangan/alasan penolakan agar sales mengetahui alasan bukti tidak valid.');
        return;
    }
    saving.value = true;
    try {
        await api.update('payments', props.id, {
            status: statusForm.value.status,
            verification_notes: statusForm.value.verification_notes,
        });
        await loadPayment();
        if (statusForm.value.status === 'valid') {
            alert('Status verifikasi berhasil disimpan! Pembayaran valid dan pendaftaran peserta terkonfirmasi.');
        } else if (statusForm.value.status === 'invalid') {
            alert('Bukti pembayaran ditolak (tidak valid). Notifikasi dan keterangan alasan telah dikirimkan ke akun sales penanggung jawab.');
        } else {
            alert('Hasil verifikasi berhasil disimpan.');
        }
    } catch (err) {
        alert(err.message || 'Gagal menyimpan status verifikasi.');
    } finally {
        saving.value = false;
    }
}

onMounted(() => {
    loadPayment();
});
</script>

<template>
    <div class="max-w-3xl mx-auto space-y-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <h1 class="text-xl font-semibold text-gray-900">Verifikasi Bukti Transfer</h1>
                <p class="text-xs text-gray-500 mt-0.5">Validasi pembayaran peserta untuk penerbitan akses kelas & komisi sales</p>
            </div>
            <button @click="emit('navigate', '/payments')" class="self-start sm:self-auto whitespace-nowrap shrink-0 text-xs text-gray-600 px-3 py-1.5 border border-gray-200 rounded-lg hover:bg-gray-50">
                &larr; Kembali
            </button>
        </div>

        <div v-if="loading" class="text-center py-16 text-gray-400 text-xs">Memuat detail pembayaran...</div>
        <div v-else-if="error" class="p-4 bg-red-50 border border-red-200 text-red-700 text-xs">{{ error }}</div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Detail transaksi -->
            <div class="bg-white border border-blue-100 p-5 shadow-xs space-y-3 text-xs">
                <h3 class="font-semibold text-gray-900 text-sm border-b border-gray-100 pb-2">Detail Transaksi</h3>
                <div class="space-y-2.5">
                    <div>
                        <span class="text-gray-400 block text-[11px]">Nama Peserta</span>
                        <button v-if="payment.lead?.id" @click="emit('navigate', `/leads/${payment.lead.id}`)" class="font-bold text-blue-600 hover:underline text-left">
                            {{ payment.lead?.name }} ({{ payment.lead?.lead_code || `#${payment.lead?.id}` }})
                        </button>
                        <span v-else class="font-bold text-gray-800">{{ payment.lead?.name || 'Peserta' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400 block text-[11px]">Program Pelatihan</span>
                        <span class="font-semibold text-gray-800">{{ payment.product?.name || (payment.lead?.product?.name || '-') }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400 block text-[11px]">Nominal Transfer Customer</span>
                        <span class="text-base font-bold text-emerald-600">{{ formatCurrency(payment.amount) }}</span>
                        <div v-if="payment.product?.price || payment.lead?.product?.price" class="text-[11px] text-gray-500 mt-1.5 bg-gray-50 p-2.5 border border-gray-100 space-y-1">
                            <div class="flex justify-between">
                                <span>Harga Resmi Katalog (Disty):</span>
                                <span class="font-medium text-gray-700">{{ formatCurrency(payment.product?.price || payment.lead?.product?.price) }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span>Hak Komisi Sales (10%):</span>
                                <span class="font-medium text-emerald-600">{{ formatCurrency(Math.round((payment.product?.price || payment.lead?.product?.price || 0) * 0.10)) }}</span>
                            </div>
                            <div class="flex justify-between border-t border-gray-200 pt-1 font-semibold text-gray-900">
                                <span>Total Ditransfer Customer:</span>
                                <span>{{ formatCurrency(payment.amount) }}</span>
                            </div>
                        </div>
                    </div>
                    <div>
                        <span class="text-gray-400 block text-[11px]">Bank Tujuan & Rekening Pengirim</span>
                        <div class="mt-0.5 space-y-0.5 text-xs">
                            <div>
                                <span class="text-gray-500">Bank Tujuan (Disty):</span>
                                <span class="font-bold text-gray-900 ml-1">{{ payment.bank_name || payment.payment_method || 'BCA' }}</span>
                            </div>
                            <div v-if="payment.account_name || payment.sender_name">
                                <span class="text-gray-500">Rekening Pengirim:</span>
                                <span class="font-semibold text-gray-800 ml-1">a.n {{ payment.account_name || payment.sender_name }}</span>
                            </div>
                        </div>
                    </div>
                    <div>
                        <span class="text-gray-400 block text-[11px]">Waktu Pengunggahan Bukti Bayar</span>
                        <span class="font-medium text-gray-800">
                            {{ formatDate(payment.payment_date || payment.created_at) }}
                            <span v-if="formatTime(payment.payment_date || payment.created_at)" class="font-mono text-gray-600 font-semibold text-[11px]">
                                &bull; {{ formatTime(payment.payment_date || payment.created_at) }} WIB
                            </span>
                        </span>
                    </div>
                    <div>
                        <span class="text-gray-400 block text-[11px]">Sales yang Menangani (PIC)</span>
                        <span class="font-medium text-gray-800">{{ payment.lead?.assigned_user?.name || payment.lead?.assignedUser?.name || 'Belum ada PIC' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400 block text-[11px]">Status Saat Ini</span>
                        <Badge :status="payment.status || 'valid'" />
                    </div>
                    <div v-if="payment.verified_by_user || payment.verifiedByUser" class="pt-2 border-t border-gray-100 text-[11px] text-gray-500">
                        Diverifikasi oleh: <strong class="text-gray-700">{{ payment.verified_by_user?.name || payment.verifiedByUser?.name }}</strong>
                        <span v-if="payment.verified_at"> pada {{ formatDateTime(payment.verified_at) }}</span>
                    </div>
                </div>
            </div>

            <!-- Bukti Transfer & Form Verifikasi -->
            <div class="space-y-4">
                <div class="bg-white border border-blue-100 p-5 shadow-xs space-y-3 text-xs">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-2">
                        <h3 class="font-semibold text-gray-900 text-sm">Bukti Struk Transfer</h3>
                        <a v-if="showProof" :href="proofSrc" target="_blank" rel="noopener noreferrer" class="text-xs font-semibold text-orange-600 hover:text-orange-700 hover:underline">
                            Buka Gambar Penuh
                        </a>
                    </div>
                    <div v-if="showProof" class="border border-gray-200 overflow-hidden bg-gray-50 p-2 flex flex-col items-center">
                        <a :href="proofSrc" target="_blank" rel="noopener noreferrer" class="block w-full text-center group cursor-zoom-in">
                            <img :src="proofSrc" alt="Bukti Transfer" @error="proofPreviewFailed = true"
                                class="w-full max-h-72 object-contain mx-auto transition-transform group-hover:scale-[1.01]">
                        </a>
                        <p class="text-[11px] text-gray-400 mt-2">Klik gambar untuk melihat dalam ukuran penuh di tab baru</p>
                    </div>
                    <div v-else class="h-32 bg-gray-50 flex items-center justify-center text-gray-400 text-xs border border-dashed border-gray-200">
                        {{ proofPreviewFailed ? 'File struk tidak dapat dibuka' : 'Belum ada struk untuk transaksi ini' }}
                    </div>
                    <form v-if="isAdmin && !showProof" @submit.prevent="attachProof" class="space-y-2">
                        <label for="payment-proof" class="block font-semibold text-gray-700">Unggah struk asli transaksi ini</label>
                        <input id="payment-proof" type="file" accept="image/jpeg,image/png,image/webp,image/avif,image/gif,image/bmp"
                            required :disabled="uploadingProof" @change="proofAttachment = $event.target.files[0] || null"
                            class="w-full border border-gray-200 px-3 py-2">
                        <p v-if="proofError" role="alert" class="text-red-600">{{ proofError }}</p>
                        <button type="submit" :disabled="uploadingProof || !proofAttachment"
                            class="px-3 py-2 bg-orange-50 text-orange-700 font-semibold rounded-lg disabled:opacity-50">
                            {{ uploadingProof ? 'Mengunggah…' : 'Lampirkan Struk' }}
                        </button>
                    </form>
                </div>

                <!-- Form Verifikasi -->
                <div class="bg-white border border-blue-100 p-5 shadow-xs space-y-3 text-xs">
                    <h3 class="font-semibold text-gray-900 text-sm">Status Verifikasi</h3>
                    <form v-if="isAdmin" @submit.prevent="submitVerification" class="space-y-3">
                        <div>
                            <label class="block font-semibold text-gray-700 mb-1">Keputusan Verifikasi</label>
                            <select v-model="statusForm.status" required class="w-full border border-gray-200 px-3 py-2 text-gray-800 focus:outline-none focus:border-blue-500">
                                <option value="valid">Valid (Dana Masuk - Terbitkan Komisi Sales & Deal)</option>
                                <option value="recheck">Perlu Cek Ulang (Minta sales cross-check)</option>
                                <option value="invalid">Tidak Valid (Tolak Pembayaran)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-gray-700 mb-1">
                                {{ statusForm.status === 'invalid' ? 'Alasan Bukti Tidak Valid' : 'Catatan Verifikasi' }}
                                <span v-if="statusForm.status === 'invalid'" class="text-red-500">* (Wajib)</span>
                            </label>
                            <textarea v-model="statusForm.verification_notes" rows="2"
                                :required="statusForm.status === 'invalid'"
                                class="w-full border px-3 py-2 text-gray-800"
                                :class="statusForm.status === 'invalid' ? 'border-red-300 focus:border-red-500' : 'border-gray-200 focus:border-blue-500'"
                                :placeholder="statusForm.status === 'invalid' ? 'Tuliskan alasan penolakan (misal: bukti palsu, mutasi tidak ditemukan, dll). Keterangan ini akan langsung dikirimkan sebagai notifikasi ke akun Sales.' : 'Catatan audit...'"></textarea>
                            <p v-if="statusForm.status === 'invalid'" class="text-[11px] text-red-500 mt-0.5">
                                Keterangan ini otomatis dikirimkan sebagai notifikasi ke akun Sales yang menangani prospek ini.
                            </p>
                        </div>
                        <button type="submit" :disabled="saving" class="w-full py-2 bg-orange-500 hover:bg-orange-600 text-white font-medium rounded-xl shadow-xs transition-colors disabled:opacity-50">
                            {{ saving ? 'Menyimpan...' : 'Simpan Hasil Verifikasi' }}
                        </button>
                    </form>
                    <div v-else class="p-3 bg-gray-50 text-gray-600 space-y-2">
                        <p>Status: <strong class="uppercase text-gray-900">{{ payment.status }}</strong></p>
                        <div v-if="payment.status === 'invalid'" class="p-3 bg-red-50 border border-red-200 text-red-800 space-y-1">
                            <p class="font-bold text-xs">Bukti Pembayaran Dinyatakan Tidak Valid</p>
                            <p class="text-xs">Alasan Penolakan: "<strong>{{ payment.verification_notes || 'Bukti transfer tidak sesuai/tidak valid.' }}</strong>"</p>
                            <p class="text-[11px] text-red-600 pt-1 border-t border-red-200">
                                Silakan hubungi calon peserta untuk meminta bukti struk transfer yang sah atau transfer ulang.
                            </p>
                        </div>
                        <p v-else-if="payment.verification_notes" class="text-[11px] text-gray-500">Catatan Admin: "{{ payment.verification_notes }}"</p>
                        <p class="text-[10.5px] text-gray-400 border-t border-gray-200 pt-1.5">
                            Verifikasi pembayaran dilakukan secara terpusat oleh Admin/Keuangan. Begitu diverifikasi valid, komisi affiliate 10% otomatis diterbitkan untuk Anda.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
