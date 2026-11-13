<script setup>
import { onMounted, ref } from 'vue';
import { api } from '../../services/api';
import Pagination from '../../Components/Pagination.vue';

const props = defineProps({
    user: { type: Object, default: () => ({}) },
});

const emit = defineEmits(['navigate']);

const institutions = ref([]);
const pagination = ref({ current_page: 1, last_page: 1, total: 0 });
const search = ref('');
const loading = ref(true);

async function loadInstitutions(page = 1) {
    loading.value = true;
    try {
        const params = { page: String(page), per_page: '20' };
        if (search.value) params.search = search.value;
        const res = await api.list('institutions', params);
        institutions.value = res.data || [];
        pagination.value = {
            current_page: res.current_page || page,
            last_page: res.last_page || 1,
            total: res.total || institutions.value.length,
            from: res.from || 1,
            to: res.to || institutions.value.length,
        };
    } catch (err) {
        console.error(err);
    } finally {
        loading.value = false;
    }
}

function handleFilter() {
    loadInstitutions(1);
}

function resetFilter() {
    search.value = '';
    loadInstitutions(1);
}

async function deleteInstitution(id) {
    if (!confirm('Hapus instansi ini?')) return;
    try {
        await api.delete('institutions', id);
        loadInstitutions(pagination.value.current_page);
    } catch (err) {
        alert(err.message || 'Gagal menghapus instansi.');
    }
}

onMounted(() => {
    loadInstitutions();
});
</script>

<template>
    <div class="space-y-4">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Daftar Instansi</h1>
                <p class="text-xs text-gray-500 mt-0.5">Kelola data instansi, perusahaan, kampus, atau organisasi mitra.</p>
            </div>
            <div class="flex items-center gap-2">
                <button @click="emit('navigate', '/institutions/create')"
                    class="px-4 py-2 text-xs font-semibold text-white bg-orange-500 rounded-full hover:bg-orange-600 shadow-sm transition-colors">
                    + Tambah Instansi
                </button>
            </div>
        </div>

        <!-- Filter Card -->
        <div class="bg-white border border-blue-100/70 p-3.5 shadow-sm">
            <form @submit.prevent="handleFilter" class="flex flex-col sm:flex-row items-center gap-2.5">
                <div class="relative flex-1 w-full">
                    <input v-model="search" type="text"
                        placeholder="Cari instansi, kota, atau jenis instansi..."
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
                            <th class="py-3.5 px-4">INSTANSI</th>
                            <th class="py-3.5 px-4">JENIS</th>
                            <th class="py-3.5 px-4">KOTA</th>
                            <th class="py-3.5 px-4 text-center">CUSTOMER</th>
                            <th class="py-3.5 px-4 text-right">AKSI</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-if="loading">
                            <td colspan="5" class="p-8 text-center text-gray-400">Memuat data instansi...</td>
                        </tr>
                        <tr v-else-if="institutions.length === 0">
                            <td colspan="5" class="p-8 text-center text-gray-400">Belum ada mitra instansi terdaftar.</td>
                        </tr>
                        <tr v-for="inst in institutions" :key="inst.id" class="hover:bg-blue-50/30 transition-colors">
                            <td class="py-3.5 px-4">
                                <div class="font-semibold text-gray-900">{{ inst.name }}</div>
                                <div v-if="inst.email || inst.phone" class="text-[11px] text-gray-400 mt-0.5">
                                    <span v-if="inst.email">{{ inst.email }}</span>
                                    <span v-if="inst.email && inst.phone"> • </span>
                                    <span v-if="inst.phone">{{ inst.phone }}</span>
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2 py-0.5 text-[10px] font-medium border bg-gray-50 text-gray-600 border-gray-200">
                                    {{ inst.type || 'Perguruan Tinggi' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-gray-700">
                                {{ inst.city ? (inst.province ? `${inst.city} , ${inst.province}` : inst.city) : (inst.address || '-') }}
                            </td>
                            <td class="py-3.5 px-4 text-center">
                                <button @click="emit('navigate', `/customers?institution_id=${inst.id}`)" class="text-blue-600 hover:underline font-semibold">
                                    {{ inst.customers_count ?? (inst.customers?.length ?? 0) }} customer
                                </button>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button @click="emit('navigate', `/institutions/${inst.id}/edit`)" class="p-1.5 text-gray-400 hover:text-orange-600 rounded-lg hover:bg-orange-50 transition-colors" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                    <button @click="deleteInstitution(inst.id)" class="p-1.5 text-gray-400 hover:text-red-600 rounded-lg hover:bg-red-50 transition-colors" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div v-if="pagination.total > 0" class="p-3 border-t border-gray-100 flex items-center justify-between">
                <Pagination :pagination="pagination" @page="loadInstitutions" />
            </div>
        </div>
    </div>
</template>