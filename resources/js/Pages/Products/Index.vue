<script setup>
import { computed, onMounted, ref } from 'vue';
import { api } from '../../services/api';
import Pagination from '../../Components/Pagination.vue';

const props = defineProps({
    user: { type: Object, default: () => ({}) },
});

const emit = defineEmits(['navigate']);

const products = ref([]);
const pagination = ref({ current_page: 1, last_page: 1, total: 0 });
const search = ref('');
const loading = ref(true);
const viewMode = ref('grid'); // 'grid' | 'table'

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

let searchTimeout = null;
function onSearchInput() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        loadProducts(1);
    }, 250);
}

function handleFilter() {
    clearTimeout(searchTimeout);
    loadProducts(1);
}

function resetFilter() {
    clearTimeout(searchTimeout);
    search.value = '';
    loadProducts(1);
}

async function deleteProduct(id) {
    if (!confirm('Hapus program ini dari katalog?')) return;
    try {
        await api.remove('products', id);
        loadProducts(pagination.value.current_page);
    } catch (err) {
        alert(err.message || 'Gagal menghapus program.');
    }
}

onMounted(() => {
    loadProducts();
});
</script>

<template>
    <div class="space-y-4">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Katalog Program</h1>
                <p class="text-xs text-gray-500 mt-0.5">Daftar kode, nama program, dan harga</p>
            </div>
            <div class="flex items-center gap-2">
                <div class="flex items-center bg-gray-100 p-1 text-xs font-semibold">
                    <button type="button" @click="viewMode = 'grid'"
                        class="px-3 py-1.5 rounded-lg transition"
                        :class="viewMode === 'grid' ? 'bg-white text-orange-600 shadow-2xs font-bold' : 'text-gray-500 hover:text-gray-900'">
                        Kartu
                    </button>
                    <button type="button" @click="viewMode = 'table'"
                        class="px-3 py-1.5 rounded-lg transition"
                        :class="viewMode === 'table' ? 'bg-white text-orange-600 shadow-2xs font-bold' : 'text-gray-500 hover:text-gray-900'">
                        Tabel
                    </button>
                </div>

                <button v-if="isAdmin" @click="emit('navigate', '/products/create')"
                    class="px-4 py-2 text-xs font-semibold text-white bg-orange-500 rounded-xl hover:bg-orange-600 shadow-xs transition-colors">
                    + Tambah Program
                </button>
            </div>
        </div>

        <!-- Pencarian Sederhana -->
        <div class="bg-white border border-gray-100 p-3 shadow-xs">
            <form @submit.prevent="handleFilter" class="flex items-center gap-2">
                <div class="relative flex-1">
                    <input v-model="search" @input="onSearchInput" type="text"
                        placeholder="Cari berdasarkan nama atau kode program..."
                        class="w-full text-xs border border-gray-200 px-3.5 py-2 text-gray-800 placeholder-gray-400 focus:outline-none focus:border-orange-500" />
                </div>
                <button type="submit"
                    class="px-4 py-2 text-xs font-semibold text-white bg-orange-500 rounded-xl hover:bg-orange-600 shadow-2xs transition-colors">
                    Cari
                </button>
                <button v-if="search" type="button" @click="resetFilter"
                    class="px-3 py-2 text-xs text-gray-500 hover:text-gray-700 border border-gray-200 rounded-xl">
                    Reset
                </button>
            </form>
        </div>

        <!-- Loading State -->
        <div v-if="loading" class="py-12 text-center text-gray-400 text-xs">
            Memuat katalog program...
        </div>

        <!-- Empty State -->
        <div v-else-if="products.length === 0" class="p-8 text-center text-gray-400 text-xs bg-white border border-gray-100">
            <span v-if="search">Tidak ditemukan program dengan kata kunci "<strong>{{ search }}</strong>".</span>
            <span v-else>Tidak ada program ditemukan.</span>
        </div>

        <!-- Tampilan Kartu Minimalis (Hanya Kode, Nama, dan Harga) -->
        <div v-else-if="viewMode === 'grid'" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <div v-for="p in products" :key="p.id"
                class="bg-white p-5 border border-gray-200 shadow-xs hover:border-orange-200 transition flex flex-col justify-between">
                <div>
                    <!-- Kode Program -->
                    <div class="flex items-center justify-between mb-3">
                        <span class="font-mono text-xs font-bold text-orange-600 bg-orange-50 px-2.5 py-1 border border-orange-100">
                            {{ p.code }}
                        </span>
                    </div>

                    <!-- Nama Program -->
                    <h2 class="font-bold text-gray-900 text-sm leading-snug">
                        {{ p.name }}
                    </h2>
                </div>

                <!-- Rincian Biaya Transfer & Komisi Sales -->
                <div class="mt-4 pt-3.5 border-t border-gray-100 space-y-2">
                    <div class="bg-orange-50/70 p-2.5 border border-orange-100 flex items-center justify-between">
                        <div>
                            <span class="text-[10px] text-orange-800 font-bold uppercase tracking-wider block">Customer Wajib Transfer</span>
                            <span class="text-base font-black text-orange-600 leading-tight">
                                {{ formatCurrency(Math.round((p.price || 0) * 1.10)) }}
                            </span>
                        </div>
                        <div class="text-right">
                            <span class="text-[10px] text-emerald-800 font-bold uppercase tracking-wider block">Komisi Sales (10%)</span>
                            <span class="text-xs font-black text-emerald-600">
                                + {{ formatCurrency(Math.round((p.price || 0) * 0.10)) }}
                            </span>
                        </div>
                    </div>
                    <div class="flex items-center justify-between text-[11px] text-gray-500 px-0.5">
                        <span>Harga Resmi Katalog: <strong>{{ formatCurrency(p.price) }}</strong></span>
                        <div v-if="isAdmin" class="flex items-center gap-1.5">
                            <button @click="emit('navigate', `/products/${p.id}/edit`)"
                                class="px-2 py-0.5 text-xs font-semibold text-gray-600 hover:text-gray-900 border border-gray-200 rounded-lg hover:bg-gray-50 transition">
                                Edit
                            </button>
                            <button @click="deleteProduct(p.id)"
                                class="px-2 py-0.5 text-xs font-semibold text-red-600 hover:text-red-700 border border-red-200 rounded-lg hover:bg-red-50 transition">
                                Hapus
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tampilan Tabel Minimalis (Hanya Kode, Nama, dan Harga) -->
        <div v-else class="bg-white border border-gray-200 overflow-hidden shadow-xs">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[650px] text-left text-xs text-gray-600">
                    <thead class="bg-gray-50/80 border-b border-gray-100 text-gray-400 font-bold uppercase tracking-wider text-[10px]">
                        <tr>
                            <th class="py-3.5 px-4 w-28">KODE</th>
                            <th class="py-3.5 px-4">NAMA PROGRAM</th>
                            <th class="py-3.5 px-4">HARGA KATALOG</th>
                            <th class="py-3.5 px-4">KOMISI SALES (10%)</th>
                            <th class="py-3.5 px-4">CUSTOMER HARUS TRANSFER</th>
                            <th v-if="isAdmin" class="py-3.5 px-4 text-right w-24">AKSI</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-for="p in products" :key="p.id" class="hover:bg-gray-50/50 transition-colors">
                            <td class="py-3.5 px-4 font-mono font-bold text-orange-600">
                                {{ p.code }}
                            </td>
                            <td class="py-3.5 px-4 font-bold text-gray-900 text-xs">
                                {{ p.name }}
                            </td>
                            <td class="py-3.5 px-4 font-semibold text-gray-600 text-xs">
                                {{ formatCurrency(p.price) }}
                            </td>
                            <td class="py-3.5 px-4 font-bold text-emerald-600 text-xs">
                                + {{ formatCurrency(Math.round((p.price || 0) * 0.10)) }}
                            </td>
                            <td class="py-3.5 px-4 font-black text-orange-600 text-xs">
                                {{ formatCurrency(Math.round((p.price || 0) * 1.10)) }}
                            </td>
                            <td v-if="isAdmin" class="py-3.5 px-4 text-right whitespace-nowrap">
                                <div class="inline-flex items-center gap-1">
                                    <button @click="emit('navigate', `/products/${p.id}/edit`)" class="p-1.5 text-gray-400 hover:text-orange-600 rounded hover:bg-orange-50" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </button>
                                    <button @click="deleteProduct(p.id)" class="p-1.5 text-gray-400 hover:text-red-600 rounded hover:bg-red-50" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-gray-100 bg-gray-50/50">
                <Pagination :meta="pagination" :loading="loading" @change-page="loadProducts" />
            </div>
        </div>
    </div>
</template>
