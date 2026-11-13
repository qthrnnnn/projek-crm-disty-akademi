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
const statusForm = ref({
    status: 'valid',
    verification_notes: '',
});

const isAdmin = computed(() => props.user?.role === 'admin');

function formatCurrency(val) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(val || 0);
}

async function loadPayment() {
    loading.value = true;
    error.value = '';
    try {
        const res = await api.get('payments', props.id);
        const data = res.data || res;
        payment.value = data;
        statusForm.value.status = data.status || 'valid';
        statusForm.value.verification_notes = data.verification_notes || '';
    } catch (err) {
        error.value = err.message || 'Gagal memuat detail pembayaran.';
    } finally {
        loading.value = false;
    }
}

async function submitVerification() {
    saving.value = true;
    try {
        await api.update('payments', props.id, {
            status: statusForm.value.status,
            verification_notes: statusForm.value.verification_notes,
        });
        await loadPayment();
        alert('Status verifikasi berhasil disimpan!');
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
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-semibold text-gray-900">Verifikasi Bukti Transfer</h1>
                <p class="text-xs text-gray-500 mt-0.5">Validasi pembayaran peserta untuk penerbitan akses kelas & komisi sales</p>
            </div>
            <button @click="emit('navigate', '/payments')" class="text-xs text-gray-600 px-3 py-1.5 border border-gray-200 rounded-lg hover:bg-gray-50">
                &larr; Kembali
            </button>
        </div>

        <div v-if="loading" class="text-center py-16 text-gray-400 text-xs">Memuat detail pembayaran...</div>
        <div v-else-if="error" class="p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs">{{ error }}</div>

        <div v-else class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <!-- Detail transaksi -->
            <div class="bg-white border border-blue-100 p-5 shadow-xs space-y-3 text-xs rounded-xl">
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
                        <span class="text-gray-400 block text-[11px]">Nominal Transfer</span>
                        <span class="text-base font-bold text-emerald-600">{{ formatCurrency(payment.amount) }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400 block text-[11px]">Metode / Bank / Rekening</span>
                        <span class="font-medium text-gray-800">
                            {{ payment.bank_name || payment.payment_method || '-' }}
                            <span v-if="payment.account_name || payment.sender_name"> — a.n {{ payment.account_name || payment.sender_name }}</span>
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
                        <span v-if="payment.verified_at"> pada {{ payment.verified_at }}</span>
                    </div>
                </div>
            </div>

            <!-- Bukti Transfer & Form Verifikasi -->
            <div class="space-y-4">
                <div class="bg-white border border-blue-100 p-5 shadow-xs space-y-3 text-xs rounded-xl">
                    <h3 class="font-semibold text-gray-900 text-sm border-b border-gray-100 pb-2">Bukti Struk Transfer</h3>
                    <div v-if="payment.proof_file || payment.proof_url || payment.proof_image" class="border border-gray-100 overflow-hidden bg-gray-50 rounded-lg">
                        <img :src="payment.proof_url || (payment.proof_file ? `/storage/${payment.proof_file}` : payment.proof_image)" alt="Bukti Transfer" class="w-full h-48 object-contain">
                    </div>
                    <div v-else class="h-32 bg-gray-50 flex items-center justify-center text-gray-400 text-xs rounded-lg">
                        Tidak ada lampiran file struk
                    </div>
                </div>

                <!-- Form Verifikasi -->
                <div class="bg-white border border-blue-100 p-5 shadow-xs space-y-3 text-xs rounded-xl">
                    <h3 class="font-semibold text-gray-900 text-sm">Status Verifikasi</h3>
                    <form v-if="isAdmin" @submit.prevent="submitVerification" class="space-y-3">
                        <div>
                            <label class="block font-semibold text-gray-700 mb-1">Keputusan Verifikasi</label>
                            <select v-model="statusForm.status" required class="w-full border border-gray-200 px-3 py-2 text-gray-800 rounded-lg focus:outline-none focus:border-blue-500">
                                <option value="valid">Valid (Dana Masuk - Terbitkan Komisi Sales & Deal)</option>
                                <option value="recheck">Perlu Cek Ulang (Minta sales cross-check)</option>
                                <option value="invalid">Tidak Valid (Tolak Pembayaran)</option>
                            </select>
                        </div>
                        <div>
                            <label class="block font-semibold text-gray-700 mb-1">Catatan Verifikasi</label>
                            <textarea v-model="statusForm.verification_notes" rows="2" class="w-full border border-gray-200 px-3 py-2 text-gray-800 rounded-lg" placeholder="Catatan audit..."></textarea>
                        </div>
                        <button type="submit" :disabled="saving" class="w-full py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow-xs transition-colors disabled:opacity-50">
                            {{ saving ? 'Menyimpan...' : 'Simpan Hasil Verifikasi' }}
                        </button>
                    </form>
                    <div v-else class="p-3 bg-gray-50 text-gray-600 space-y-2 rounded-lg">
                        <p>Status: <strong class="uppercase text-gray-900">{{ payment.status }}</strong></p>
                        <p v-if="payment.verification_notes" class="text-[11px] text-gray-500">Catatan Admin: "{{ payment.verification_notes }}"</p>
                        <p class="text-[10.5px] text-gray-400 border-t border-gray-200 pt-1.5">
                            Verifikasi pembayaran dilakukan secara terpusat oleh Admin/Keuangan. Begitu diverifikasi valid, komisi affiliate 10% otomatis diterbitkan untuk Anda.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
