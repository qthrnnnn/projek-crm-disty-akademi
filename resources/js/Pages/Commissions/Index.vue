<script setup>
import { computed, onMounted, ref } from 'vue';
import { api } from '../../services/api';
import Pagination from '../../Components/Pagination.vue';

const props = defineProps({
    user: { type: Object, default: () => ({}) },
});

const emit = defineEmits(['navigate']);

const commissions = ref([]);
const salesUsers = ref([]);
const pagination = ref({ current_page: 1, last_page: 1, total: 0 });
const statusFilter = ref('');
const userFilter = ref('');
const loading = ref(true);

const totalPending = ref(0);
const totalPaid = ref(0);

const isAdmin = computed(() => props.user?.role === 'admin');

function formatCurrency(val) {
    if (!val) return 'Rp 0';
    return 'Rp ' + Number(val).toLocaleString('id-ID');
}

async function loadSalesUsers() {
    try {
        const res = await api.list('users', { per_page: '100' });
        salesUsers.value = (res.data || []).filter(u => u.role === 'sales');
    } catch (e) {
        console.error(e);
    }
}

async function loadCommissions(page = 1) {
    loading.value = true;
    try {
        const params = { page: String(page), per_page: '20' };
        if (statusFilter.value) params.status = statusFilter.value;
        if (userFilter.value) params.user_id = userFilter.value;

        const res = await api.list('commissions', params);
        commissions.value = res.data || [];
        totalPending.value = res.total_pending || commissions.value.filter(c => c.status === 'pending' || c.status === 'approved').reduce((acc, c) => acc + (Number(c.amount) || 0), 0);
        totalPaid.value = res.total_paid || commissions.value.filter(c => c.status === 'paid').reduce((acc, c) => acc + (Number(c.amount) || 0), 0);

        pagination.value = {
            current_page: res.current_page || page,
            last_page: res.last_page || 1,
            total: res.total || commissions.value.length,
            from: res.from || 1,
            to: res.to || commissions.value.length,
        };
    } catch (err) {
        console.error(err);
    } finally {
        loading.value = false;
    }
}

async function approveCommission(id) {
    if (!confirm('Setujui komisi ini?')) return;
    try {
        await api.approveCommission(id);
        loadCommissions(pagination.value.current_page);
    } catch (e) {
        alert(e.message || 'Gagal menyetujui komisi.');
    }
}

async function payCommission(id) {
    if (!confirm('Tandai komisi ini sebagai SUDAH DIBAYAR?')) return;
    try {
        await api.payCommission(id);
        loadCommissions(pagination.value.current_page);
    } catch (e) {
        alert(e.message || 'Gagal mengubah status bayar komisi.');
    }
}

function resetFilter() {
    statusFilter.value = '';
    userFilter.value = '';
    loadCommissions(1);
}

onMounted(() => {
    loadSalesUsers();
    loadCommissions();
});
</script>

<template>
    <div class="space-y-4">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-xl font-semibold text-gray-900">Manajemen Komisi Sales</h1>
                <p class="text-xs text-gray-500 mt-0.5">Penetapan dan pembayaran insentif atas setiap peserta (pelanggan) yang berhasil mendaftar</p>
            </div>
        </div>

        <!-- Cards Summary (2 Cards) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div class="bg-white border border-blue-100 p-4 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500">Komisi Menunggu / Disetujui (Pending)</p>
                    <p class="text-2xl font-bold text-amber-600 mt-1">{{ formatCurrency(totalPending) }}</p>
                </div>
                <div class="w-10 h-10 bg-amber-50 text-amber-600 flex items-center justify-center rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
            <div class="bg-white border border-blue-100 p-4 shadow-sm flex items-center justify-between">
                <div>
                    <p class="text-xs text-gray-500">Total Komisi Sudah Dibayarkan</p>
                    <p class="text-2xl font-bold text-emerald-600 mt-1">{{ formatCurrency(totalPaid) }}</p>
                </div>
                <div class="w-10 h-10 bg-emerald-50 text-emerald-600 flex items-center justify-center rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
            </div>
        </div>

        <!-- Filter Card -->
        <div class="bg-white border border-blue-100 p-3.5 shadow-sm">
            <form @submit.prevent="loadCommissions(1)" class="flex flex-wrap items-center gap-2.5">
                <select v-model="statusFilter" class="text-xs border border-gray-200 px-3 py-2 text-gray-700 focus:outline-none focus:border-orange-500">
                    <option value="">Semua Status Komisi</option>
                    <option value="pending">Pending</option>
                    <option value="approved">Disetujui (Approved)</option>
                    <option value="paid">Sudah Dibayar (Paid)</option>
                </select>
                <select v-if="isAdmin" v-model="userFilter" class="text-xs border border-gray-200 px-3 py-2 text-gray-700 focus:outline-none focus:border-orange-500">
                    <option value="">Semua Sales</option>
                    <option v-for="s in salesUsers" :key="s.id" :value="s.id">{{ s.name }}</option>
                </select>
                <button type="submit" class="text-xs bg-orange-500 text-white font-medium py-2 px-3 rounded-lg hover:bg-orange-600 transition-colors">
                    Filter
                </button>
                <button v-if="statusFilter || userFilter" @click="resetFilter" type="button" class="text-xs text-gray-500 hover:text-gray-700 px-2 py-1">
                    Reset
                </button>
            </form>
        </div>

        <!-- Table Card -->
        <div class="bg-white border border-blue-100 overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[650px] text-left text-xs text-gray-600">
                    <thead class="bg-gray-50/70 border-b border-gray-100 text-gray-500 font-medium uppercase tracking-wider text-[11px]">
                        <tr>
                            <th class="py-3 px-4">Sales Penerima</th>
                            <th class="py-3 px-4">Peserta / Lead</th>
                            <th class="py-3 px-4">Nilai Deal</th>
                            <th class="py-3 px-4">Rate (%)</th>
                            <th class="py-3 px-4">Nominal Komisi</th>
                            <th class="py-3 px-4">Status</th>
                            <th v-if="isAdmin" class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-if="loading">
                            <td :colspan="isAdmin ? 7 : 6" class="py-8 text-center text-gray-400">Memuat komisi...</td>
                        </tr>
                        <tr v-else-if="commissions.length === 0">
                            <td :colspan="isAdmin ? 7 : 6" class="py-8 text-center text-gray-400">Belum ada catatan komisi.</td>
                        </tr>
                        <tr v-for="c in commissions" :key="c.id" class="hover:bg-blue-50/30 transition-colors">
                            <td class="py-3 px-4">
                                <div class="inline-flex items-center gap-1.5">
                                    <span class="w-5 h-5 rounded-full bg-gray-200 text-gray-700 flex items-center justify-center text-[10px] font-bold">
                                        {{ ((c.user?.name || '?').charAt(0)).toUpperCase() }}
                                    </span>
                                    <span class="font-medium text-gray-900">{{ c.user?.name || '-' }}</span>
                                </div>
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-semibold text-gray-900">{{ c.lead?.name || 'Peserta' }}</div>
                                <div class="text-[11px] text-gray-400">{{ c.lead?.product?.name || '-' }}</div>
                            </td>
                            <td class="py-3 px-4 text-gray-800">
                                {{ formatCurrency(c.lead?.deal_price || (c.amount ? c.amount * 10 : 0)) }}
                            </td>
                            <td class="py-3 px-4 text-gray-500 font-semibold">10%</td>
                            <td class="py-3 px-4 font-bold text-emerald-600">
                                {{ formatCurrency(c.amount) }}
                            </td>
                            <td class="py-3 px-4">
                                <span class="px-2 py-0.5 text-[10px] font-medium border"
                                    :class="c.status === 'paid' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : (c.status === 'approved' ? 'bg-blue-50 text-blue-700 border-blue-200' : 'bg-amber-50 text-amber-700 border-amber-200')">
                                    {{ c.status === 'paid' ? 'Paid' : (c.status === 'approved' ? 'Approved' : 'Pending') }}
                                </span>
                            </td>
                            <td v-if="isAdmin" class="py-3 px-4 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-1.5">
                                    <button v-if="c.status === 'pending'" @click="approveCommission(c.id)"
                                        class="px-2 py-1 text-[11px] font-medium text-blue-700 bg-blue-50 hover:bg-blue-100 rounded border border-blue-200">
                                        Setujui
                                    </button>
                                    <button v-if="c.status === 'approved'" @click="payCommission(c.id)"
                                        class="px-2 py-1 text-[11px] font-medium text-emerald-700 bg-emerald-50 hover:bg-emerald-100 rounded border border-emerald-200">
                                        Bayar
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div v-if="pagination.total > 0" class="p-3 border-t border-gray-100 flex items-center justify-between">
                <Pagination :pagination="pagination" @page="loadCommissions" />
            </div>
        </div>
    </div>
</template>