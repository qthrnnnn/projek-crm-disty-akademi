<script setup>
import { computed, onMounted, ref } from 'vue';
import { api } from '../../services/api';
import Pagination from '../../Components/Pagination.vue';
import Badge from '../../Components/Badge.vue';
import StatCard from '../../Components/StatCard.vue';

const props = defineProps({
    user: { type: Object, default: () => ({}) },
});

const commissions = ref([]);
const pagination = ref({ current_page: 1, last_page: 1, total: 0 });
const summary = ref({ total_earned: 0, total_pending: 0, total_paid: 0 });
const loading = ref(true);

const isAdmin = computed(() => props.user?.role === 'admin');

function formatCurrency(val) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(val || 0);
}

async function loadCommissions(page = 1) {
    loading.value = true;
    try {
        const res = await api.list('commissions', { page: String(page), per_page: '20' });
        commissions.value = res.data || [];
        summary.value = {
            total_earned: res.total_earned || commissions.value.reduce((acc, c) => acc + (Number(c.amount) || 0), 0),
            total_pending: res.total_pending || commissions.value.filter(c => c.status === 'pending').reduce((acc, c) => acc + (Number(c.amount) || 0), 0),
            total_paid: res.total_paid || commissions.value.filter(c => c.status === 'paid').reduce((acc, c) => acc + (Number(c.amount) || 0), 0),
        };
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

onMounted(() => {
    loadCommissions();
});
</script>

<template>
    <div class="space-y-5">
        <div>
            <h1 class="text-xl font-bold text-gray-900">{{ isAdmin ? 'Manajemen Komisi Sales (10%)' : 'Komisi Saya (10%)' }}</h1>
            <p class="text-xs text-gray-500 mt-0.5">Penghitungan otomatis komisi dari setiap pembayaran tervalidasi</p>
        </div>

        <!-- 3 KPI Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <StatCard title="Total Komisi" :value="formatCurrency(summary.total_earned)" subtext="Akumulasi seluruh komisi" />
            <StatCard title="Menunggu Pencairan" :value="formatCurrency(summary.total_pending)" subtext="Status pending verifikasi" subtext-color="text-amber-600" />
            <StatCard title="Sudah Dicairkan" :value="formatCurrency(summary.total_paid)" subtext="Ditransfer ke rekening sales" subtext-color="text-emerald-600" />
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-gray-50/80 text-gray-400 font-bold uppercase tracking-wider text-[10px] border-b border-gray-100">
                        <tr>
                            <th class="p-4">Tanggal</th>
                            <th class="p-4" v-if="isAdmin">Sales</th>
                            <th class="p-4">Prospek / Lead</th>
                            <th class="p-4">Program</th>
                            <th class="p-4">Komisi (10%)</th>
                            <th class="p-4">Status Pencairan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 text-gray-700">
                        <tr v-if="loading">
                            <td :colspan="isAdmin ? 6 : 5" class="p-8 text-center text-gray-400 text-xs">Memuat data komisi...</td>
                        </tr>
                        <tr v-else-if="commissions.length === 0">
                            <td :colspan="isAdmin ? 6 : 5" class="p-8 text-center text-gray-400 text-xs">Belum ada catatan komisi.</td>
                        </tr>
                        <tr v-for="c in commissions" :key="c.id" class="hover:bg-gray-50/50 transition-colors">
                            <td class="p-4 text-gray-500 whitespace-nowrap">{{ c.created_at ? new Date(c.created_at).toLocaleDateString('id-ID') : '-' }}</td>
                            <td v-if="isAdmin" class="p-4 font-bold text-gray-900">{{ c.user?.name || '-' }}</td>
                            <td class="p-4 font-semibold text-gray-900">{{ c.lead?.name || '-' }}</td>
                            <td class="p-4 text-gray-600">{{ c.lead?.product?.name || 'Program Pelatihan' }}</td>
                            <td class="p-4 font-black text-emerald-600">{{ formatCurrency(c.amount) }}</td>
                            <td class="p-4"><Badge :status="c.status || 'paid'" /></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="p-4">
                <Pagination :meta="pagination" :loading="loading" @change-page="loadCommissions" />
            </div>
        </div>
    </div>
</template>
