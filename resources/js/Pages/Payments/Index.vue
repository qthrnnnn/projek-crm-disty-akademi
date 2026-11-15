<script setup>
import { computed, onMounted, ref } from 'vue';
import { api } from '../../services/api';
import Pagination from '../../Components/Pagination.vue';

const props = defineProps({
    user: { type: Object, default: () => ({}) },
});

const emit = defineEmits(['navigate']);

const payments = ref([]);
const pagination = ref({ current_page: 1, last_page: 1, total: 0 });
const status = ref('');
const loading = ref(true);

const isAdmin = computed(() => props.user?.role === 'admin');

function formatDate(val) {
    if (!val) return '-';
    try {
        const d = new Date(val);
        return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
    } catch {
        return val;
    }
}

function formatCurrency(val) {
    if (!val) return 'Rp 0';
    return 'Rp ' + Number(val).toLocaleString('id-ID');
}

function statusBadgeClass(st) {
    const s = (st || '').toLowerCase();
    switch (s) {
        case 'valid': return 'bg-emerald-50 text-emerald-700 border-emerald-200';
        case 'invalid': return 'bg-red-50 text-red-700 border-red-200';
        case 'recheck': return 'bg-orange-50 text-orange-700 border-orange-200';
        case 'pending': return 'bg-amber-50 text-amber-700 border-amber-200';
        default: return 'bg-gray-50 border-gray-200 text-gray-600';
    }
}

async function loadPayments(page = 1) {
    loading.value = true;
    try {
        const params = { page: String(page), per_page: '20' };
        if (status.value) params.status = status.value;

        const res = await api.list('payments', params);
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

function resetFilter() {
    status.value = '';
    loadPayments(1);
}

onMounted(() => {
    loadPayments();
});
</script>

<template>
    <div class="space-y-4">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-xl font-semibold text-gray-900">Pembayaran & Bukti Transfer</h1>
                <p class="text-xs text-gray-500 mt-0.5">Kelola verifikasi transfer peserta dan pencatatan deal akademi</p>
            </div>
            <button @click="emit('navigate', '/payments/create')"
                class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-medium text-white bg-orange-500 rounded-full hover:bg-orange-600 transition-colors shadow-sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Input Pembayaran
            </button>
        </div>

        <!-- Filter Card -->
        <div class="bg-white border border-blue-100 p-3.5 shadow-sm">
            <form @submit.prevent="loadPayments(1)" class="flex flex-wrap items-center gap-2.5">
                <select v-model="status" @change="loadPayments(1)" class="text-xs border border-gray-200 px-3 py-2 text-gray-700 focus:outline-none focus:border-orange-500">
                    <option value="">Semua Status Pembayaran</option>
                    <option value="pending">Menunggu Verifikasi</option>
                    <option value="valid">Valid / Diterima</option>
                    <option value="invalid">Tidak Valid</option>
                    <option value="recheck">Perlu Cek Ulang</option>
                </select>
                <button type="submit" class="text-xs bg-orange-500 text-white font-medium py-2 px-3 rounded-lg hover:bg-orange-600 transition-colors">
                    Filter
                </button>
                <button v-if="status" @click="resetFilter" type="button" class="text-xs text-gray-500 hover:text-gray-700 px-2 py-1">
                    Reset
                </button>
            </form>
        </div>

        <!-- Table Card -->
        <div class="bg-white border border-blue-100 overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[640px] text-left text-xs text-gray-600">
                    <thead class="bg-gray-50/70 border-b border-gray-100 text-gray-500 font-medium uppercase tracking-wider text-[11px]">
                        <tr>
                            <th class="py-3 px-4">Tanggal & Peserta</th>
                            <th class="py-3 px-4">Program Pelatihan</th>
                            <th class="py-3 px-4">Nominal</th>
                            <th class="py-3 px-4">Metode / Bank</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-if="loading">
                            <td colspan="6" class="py-8 text-center text-gray-400">Memuat pembayaran...</td>
                        </tr>
                        <tr v-else-if="payments.length === 0">
                            <td colspan="6" class="py-8 text-center text-gray-400">Belum ada riwayat pembayaran</td>
                        </tr>
                        <tr v-for="p in payments" :key="p.id" class="hover:bg-blue-50/30 transition-colors">
                            <td class="py-3 px-4">
                                <div class="font-semibold text-gray-900">{{ p.lead?.name || 'Peserta' }}</div>
                                <div class="text-[11px] text-gray-400">{{ formatDate(p.payment_date || p.created_at) }}</div>
                            </td>
                            <td class="py-3 px-4">
                                {{ p.product?.name || (p.lead?.product?.name || '-') }}
                            </td>
                            <td class="py-3 px-4 font-bold text-gray-900">
                                {{ formatCurrency(p.amount) }}
                            </td>
                            <td class="py-3 px-4">
                                <div>{{ p.bank_name || (p.payment_method ? (p.payment_method.charAt(0).toUpperCase() + p.payment_method.slice(1)) : 'Transfer') }}</div>
                                <div v-if="p.account_name" class="text-[11px] text-gray-400">a.n {{ p.account_name }}</div>
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 text-[10px] font-medium border" :class="statusBadgeClass(p.status)">
                                    {{ p.status ? (p.status.charAt(0).toUpperCase() + p.status.slice(1)) : '-' }}
                                </span>
                                <div v-if="p.status === 'invalid' && p.verification_notes" class="text-[10.5px] text-red-600 mt-1 max-w-[190px] leading-tight">
                                    <span class="font-semibold">Alasan:</span> {{ p.verification_notes }}
                                </div>
                                <div v-else-if="p.status === 'recheck' && p.verification_notes" class="text-[10.5px] text-orange-600 mt-1 max-w-[190px] leading-tight">
                                    <span class="font-semibold">Catatan:</span> {{ p.verification_notes }}
                                </div>
                            </td>
                            <td class="py-3 px-4 text-right">
                                <button v-if="isAdmin" @click="emit('navigate', `/payments/${p.id}`)"
                                    class="inline-flex items-center gap-1 text-xs font-medium text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 px-2.5 py-1 rounded">
                                    Verifikasi
                                </button>
                                <button v-else @click="emit('navigate', `/payments/${p.id}`)"
                                    class="inline-flex items-center gap-1 text-xs font-medium text-gray-700 hover:text-gray-900 bg-gray-100 hover:bg-gray-200 px-2.5 py-1 rounded">
                                    Detail
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div v-if="pagination.total > 0" class="p-3 border-t border-gray-100 flex items-center justify-between">
                <Pagination :pagination="pagination" @page="loadPayments" />
            </div>
        </div>
    </div>
</template>