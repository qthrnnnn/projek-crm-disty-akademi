<script setup>
import { computed, onMounted, ref } from 'vue';
import { api } from '../../services/api';
import Pagination from '../../Components/Pagination.vue';
import Badge from '../../Components/Badge.vue';
import Modal from '../../Components/Modal.vue';

const props = defineProps({
    user: { type: Object, default: () => ({}) },
});

const emit = defineEmits(['navigate']);

const payments = ref([]);
const pagination = ref({ current_page: 1, last_page: 1, total: 0 });
const loading = ref(true);
const previewImage = ref('');
const showModal = ref(false);

const isAdmin = computed(() => props.user?.role === 'admin');

async function loadPayments(page = 1) {
    loading.value = true;
    try {
        const res = await api.list('payments', { page: String(page), per_page: '20' });
        payments.value = res.data || [];
        pagination.value = {
            current_page: res.current_page || page,
            last_page: res.last_page || 1,
            total: res.total || payments.value.length,
            from: res.from || 1,
            to: res.to || payments.value.length,
        };
    } catch (err) {
        console.error(err);
    } finally {
        loading.value = false;
    }
}

async function verifyPayment(id, status) {
    if (!confirm(`Yakin ingin menandai pembayaran ini sebagai ${status === 'valid' ? 'VALID' : 'DITOLAK'}?`)) return;
    try {
        await api.update('payments', id, { status });
        loadPayments(pagination.value.current_page);
    } catch (err) {
        alert(err.message || 'Gagal mengubah status pembayaran.');
    }
}

function viewProof(url) {
    previewImage.value = url;
    showModal.value = true;
}

function formatCurrency(val) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(val || 0);
}

onMounted(() => {
    loadPayments();
});
</script>

<template>
    <div class="space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Verifikasi Pembayaran & Bukti Transfer</h1>
                <p class="text-xs text-gray-500 mt-0.5">Daftar transaksi masuk calon peserta untuk pelatihan & sertifikasi</p>
            </div>
            <button @click="emit('navigate', '/payments/create')"
                class="px-4 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-bold text-xs shadow-sm flex items-center justify-center gap-1.5">
                <span>+</span> Kirim Bukti Bayar
            </button>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-gray-50/80 text-gray-400 font-bold uppercase tracking-wider text-[10px] border-b border-gray-100">
                        <tr>
                            <th class="p-4">Kode Bayar</th>
                            <th class="p-4">Peserta / Program</th>
                            <th class="p-4">Nominal Bayar</th>
                            <th class="p-4">Metode & Rekening</th>
                            <th class="p-4">Status</th>
                            <th class="p-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 text-gray-700">
                        <tr v-if="loading">
                            <td colspan="6" class="p-8 text-center text-gray-400 text-xs">Memuat data pembayaran...</td>
                        </tr>
                        <tr v-else-if="payments.length === 0">
                            <td colspan="6" class="p-8 text-center text-gray-400 text-xs">Belum ada transaksi pembayaran.</td>
                        </tr>
                        <tr v-for="p in payments" :key="p.id" class="hover:bg-gray-50/50 transition-colors">
                            <td class="p-4">
                                <span class="font-bold text-gray-900 block">#{{ p.id }}</span>
                                <span class="text-[10px] text-gray-400">{{ p.created_at ? new Date(p.created_at).toLocaleDateString('id-ID') : '-' }}</span>
                            </td>
                            <td class="p-4">
                                <span class="font-bold text-gray-900 block">{{ p.lead?.name || 'Peserta' }}</span>
                                <span class="text-[10px] text-gray-500">{{ p.lead?.product?.name || 'Program Akademi' }}</span>
                            </td>
                            <td class="p-4 font-black text-emerald-600">
                                {{ formatCurrency(p.amount) }}
                            </td>
                            <td class="p-4 text-gray-600">
                                <span class="font-bold">{{ p.bank_name || 'BCA' }}</span>
                                <span class="text-[10px] text-gray-400 block">a.n. {{ p.sender_name || '-' }}</span>
                            </td>
                            <td class="p-4">
                                <Badge :status="p.status || 'valid'" />
                            </td>
                            <td class="p-4 text-right space-x-2">
                                <button v-if="p.proof_url || p.proof_image" @click="viewProof(p.proof_url || p.proof_image)" class="font-bold text-gray-500 hover:text-gray-900">
                                    Bukti
                                </button>
                                <template v-if="isAdmin && p.status === 'pending'">
                                    <button @click="verifyPayment(p.id, 'valid')" class="font-bold text-emerald-600 hover:text-emerald-700">Setujui</button>
                                    <button @click="verifyPayment(p.id, 'rejected')" class="font-bold text-red-600 hover:text-red-700">Tolak</button>
                                </template>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="p-4">
                <Pagination :meta="pagination" :loading="loading" @change-page="loadPayments" />
            </div>
        </div>

        <Modal :show="showModal" title="Bukti Transfer Pembayaran" @close="showModal = false">
            <div class="text-center">
                <img :src="previewImage" alt="Bukti Transfer" class="max-h-[70vh] mx-auto rounded-xl object-contain border border-gray-100" />
            </div>
        </Modal>
    </div>
</template>
