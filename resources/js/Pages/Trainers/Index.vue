<script setup>
import { onMounted, ref } from 'vue';
import { api } from '../../services/api';
import Pagination from '../../Components/Pagination.vue';

const props = defineProps({
    user: { type: Object, default: () => ({}) },
});

const emit = defineEmits(['navigate']);

const trainers = ref([]);
const pagination = ref({ current_page: 1, last_page: 1, total: 0 });
const search = ref('');
const loading = ref(true);

async function loadTrainers(page = 1) {
    loading.value = true;
    try {
        const params = { page: String(page), per_page: '20' };
        if (search.value) params.search = search.value;
        const res = await api.list('trainers', params);
        trainers.value = res.data || [];
        pagination.value = {
            current_page: res.current_page || page,
            last_page: res.last_page || 1,
            total: res.total || trainers.value.length,
            from: res.from || 1,
            to: res.to || trainers.value.length,
        };
    } catch (err) {
        console.error(err);
    } finally {
        loading.value = false;
    }
}

function handleFilter() {
    loadTrainers(1);
}

function resetFilter() {
    search.value = '';
    loadTrainers(1);
}

onMounted(() => {
    loadTrainers();
});
</script>

<template>
    <div class="space-y-4">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Daftar Trainer</h1>
                <p class="text-xs text-gray-500 mt-0.5">Trainer digunakan saat membuat jadwal pelatihan.</p>
            </div>
            <div>
                <button type="button" @click="emit('navigate', '/trainers/create')"
                    class="px-4 py-2 text-xs font-semibold text-white bg-orange-500 rounded-full hover:bg-orange-600 shadow-sm transition-colors">
                    + Tambah Trainer
                </button>
            </div>
        </div>

        <!-- Filter Card -->
        <div class="bg-white border border-blue-100/70 p-3.5 shadow-sm">
            <form @submit.prevent="handleFilter" class="flex flex-col sm:flex-row items-center gap-2.5">
                <div class="relative flex-1 w-full">
                    <input v-model="search" type="text"
                        placeholder="Cari trainer, email, atau telepon..."
                        class="w-full text-xs border border-gray-200 px-3 py-2 text-gray-800 placeholder-gray-400 focus:outline-none focus:border-orange-500" />
                </div>
                <button type="submit" class="w-full sm:w-auto px-5 py-2 text-xs font-semibold text-white bg-orange-500 rounded-lg hover:bg-orange-600 shadow-sm transition-colors">
                    Cari
                </button>
                <button v-if="search" @click="resetFilter" type="button" class="px-3 py-2 text-xs text-gray-500 hover:text-gray-700 border border-gray-200 rounded-lg">
                    Reset
                </button>
            </form>
        </div>

        <!-- Table Card -->
        <div class="bg-white border border-blue-100/70 overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[550px] text-left text-xs text-gray-600">
                    <thead class="bg-gray-50/70 border-b border-gray-100 text-gray-400 font-semibold uppercase tracking-wider text-[10.5px]">
                        <tr>
                            <th class="py-3.5 px-4">NAMA ▲</th>
                            <th class="py-3.5 px-4">EMAIL</th>
                            <th class="py-3.5 px-4">TELEPON</th>
                            <th class="py-3.5 px-4">STATUS</th>
                            <th class="py-3.5 px-4 text-right">AKSI</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-if="loading">
                            <td colspan="5" class="p-8 text-center text-gray-400">Memuat data trainer...</td>
                        </tr>
                        <tr v-else-if="trainers.length === 0">
                            <td colspan="5" class="p-8 text-center text-gray-400">Belum ada trainer.</td>
                        </tr>
                        <tr v-for="t in trainers" :key="t.id" class="hover:bg-blue-50/20 transition-colors">
                            <td class="py-3.5 px-4 font-semibold text-gray-900">
                                {{ t.name }}
                            </td>
                            <td class="py-3.5 px-4 text-gray-600">
                                {{ t.email || '-' }}
                            </td>
                            <td class="py-3.5 px-4 text-gray-600">
                                {{ t.phone || '-' }}
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="inline-block px-2 py-0.5 border text-[10px] font-semibold uppercase tracking-wider"
                                    :class="t.is_active ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-gray-100 text-gray-600 border-gray-200'">
                                    {{ t.is_active ? 'Aktif' : 'Non-aktif' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <button @click="emit('navigate', `/trainers/${t.id}/edit`)"
                                    class="p-1.5 text-gray-400 hover:text-orange-600 rounded-lg hover:bg-orange-50 transition-colors"
                                    title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="p-4 border-t border-gray-100 flex items-center justify-between">
                <div class="flex items-center gap-2 text-xs text-gray-500">
                    <span>Tampilkan</span>
                    <span class="border border-gray-200 px-2.5 py-1 text-xs text-gray-700 bg-white">50</span>
                </div>
                <div v-if="pagination.total > 0">
                    <Pagination :pagination="pagination" @page="loadTrainers" />
                </div>
            </div>
        </div>
    </div>
</template>