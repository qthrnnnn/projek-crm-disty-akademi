<script setup>
import { computed, onMounted, ref } from 'vue';
import { api } from '../../services/api';
import Pagination from '../../Components/Pagination.vue';
import Badge from '../../Components/Badge.vue';

const props = defineProps({
    user: { type: Object, default: () => ({}) },
});

const emit = defineEmits(['navigate']);

const products = ref([]);
const pagination = ref({ current_page: 1, last_page: 1, total: 0 });
const search = ref('');
const loading = ref(true);

const isAdmin = computed(() => props.user?.role === 'admin');

function formatCurrency(val) {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(val || 0);
}

async function loadProducts(page = 1) {
    loading.value = true;
    try {
        const params = { page: String(page), per_page: '20' };
        if (search.value) params.search = search.value;
        const res = await api.list('products', params);
        products.value = res.data || [];
        pagination.value = {
            current_page: res.current_page || page,
            last_page: res.last_page || 1,
            total: res.total || products.value.length,
            from: res.from || 1,
            to: res.to || products.value.length,
        };
    } catch (err) {
        console.error(err);
    } finally {
        loading.value = false;
    }
}

onMounted(() => {
    loadProducts();
});
</script>

<template>
    <div class="space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Katalog Program & Harga</h1>
                <p class="text-xs text-gray-500 mt-0.5">Daftar silabus, sertifikasi, dan biaya pelatihan resmi Disty Akademi</p>
            </div>
            <div class="flex items-center gap-2">
                <input v-model="search" @input="loadProducts(1)" type="text" placeholder="Cari program..."
                    class="px-3.5 py-2 rounded-xl border border-gray-200 text-xs bg-white focus:outline-none" />
                <button v-if="isAdmin" @click="emit('navigate', '/products/create')"
                    class="px-4 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-bold text-xs shadow-sm">
                    + Tambah Program
                </button>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-gray-50/80 text-gray-400 font-bold uppercase tracking-wider text-[10px] border-b border-gray-100">
                        <tr>
                            <th class="p-4">Kode & Nama Program</th>
                            <th class="p-4">Kategori</th>
                            <th class="p-4">Biaya / Harga</th>
                            <th class="p-4">Komisi Sales (10%)</th>
                            <th class="p-4">Status</th>
                            <th v-if="isAdmin" class="p-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 text-gray-700">
                        <tr v-if="loading">
                            <td :colspan="isAdmin ? 6 : 5" class="p-8 text-center text-gray-400 text-xs">Memuat katalog...</td>
                        </tr>
                        <tr v-else-if="products.length === 0">
                            <td :colspan="isAdmin ? 6 : 5" class="p-8 text-center text-gray-400 text-xs">Belum ada program terdaftar.</td>
                        </tr>
                        <tr v-for="p in products" :key="p.id" class="hover:bg-gray-50/50 transition-colors">
                            <td class="p-4 font-bold text-gray-900">
                                <div>{{ p.name }}</div>
                                <span class="text-[10px] font-mono text-gray-400">{{ p.code || `#PRD-${p.id}` }}</span>
                            </td>
                            <td class="p-4 text-gray-600">{{ p.category || 'Akademi' }}</td>
                            <td class="p-4 font-black text-gray-900">{{ formatCurrency(p.price) }}</td>
                            <td class="p-4 font-bold text-orange-600">{{ formatCurrency(p.price * 0.1) }}</td>
                            <td class="p-4"><Badge :status="p.is_active ? 'aktif' : 'inactive'" /></td>
                            <td v-if="isAdmin" class="p-4 text-right">
                                <button @click="emit('navigate', `/products/${p.id}/edit`)" class="font-bold text-orange-600 hover:text-orange-700">Edit</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="p-4">
                <Pagination :meta="pagination" :loading="loading" @change-page="loadProducts" />
            </div>
        </div>
    </div>
</template>
