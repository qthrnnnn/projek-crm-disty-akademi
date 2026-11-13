<script setup>
import { onMounted, ref } from 'vue';
import { api } from '../../services/api';
import Pagination from '../../Components/Pagination.vue';

const props = defineProps({
    user: { type: Object, default: () => ({}) },
});

const emit = defineEmits(['navigate']);

const customers = ref([]);
const institutions = ref([]);
const pagination = ref({ current_page: 1, last_page: 1, total: 0 });
const search = ref('');
const statusFilter = ref('');
const institutionFilter = ref('');
const loading = ref(true);

async function loadInstitutions() {
    try {
        const res = await api.list('institutions', { per_page: '100' });
        institutions.value = res.data || [];
    } catch (e) {
        console.error(e);
    }
}

async function loadCustomers(page = 1) {
    loading.value = true;
    try {
        const params = { page: String(page), per_page: '20' };
        if (search.value) params.search = search.value;
        if (statusFilter.value) params.status = statusFilter.value;
        if (institutionFilter.value) params.institution_id = institutionFilter.value;

        const res = await api.list('customers', params);
        customers.value = res.data || [];
        pagination.value = {
            current_page: res.current_page || page,
            last_page: res.last_page || 1,
            total: res.total || customers.value.length,
            from: res.from || 1,
            to: res.to || customers.value.length,
        };
    } catch (err) {
        console.error(err);
    } finally {
        loading.value = false;
    }
}

function handleFilter() {
    loadCustomers(1);
}

function resetFilter() {
    search.value = '';
    statusFilter.value = '';
    institutionFilter.value = '';
    loadCustomers(1);
}

function exportCsv() {
    window.location.href = '/api/customers/export';
}

function downloadTemplate() {
    window.location.href = '/api/customers/template';
}

async function deleteCustomer(id) {
    if (!confirm('Hapus customer ini?')) return;
    try {
        await api.delete('customers', id);
        loadCustomers(pagination.value.current_page);
    } catch (err) {
        alert(err.message || 'Gagal menghapus customer.');
    }
}

onMounted(() => {
    loadInstitutions();
    loadCustomers();
});
</script>

<template>
    <div class="space-y-4">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Daftar Customer</h1>
                <p class="text-xs text-gray-500 mt-0.5">Data pelanggan, peserta pelatihan, dan instansi mitra Disty Akademi.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <!-- Tombol Import -->
                <button type="button" @click="emit('navigate', '/customers/import')"
                    class="px-3.5 py-2 text-xs font-medium text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 shadow-sm transition-colors flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                    </svg>
                    Import Data
                </button>

                <!-- Tombol Export -->
                <button type="button" @click="exportCsv"
                    class="px-3.5 py-2 text-xs font-medium text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 shadow-sm transition-colors flex items-center gap-1.5"
                    title="Export Customer ke CSV">
                    <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Export Excel/CSV
                </button>

                <!-- Tombol Template -->
                <button type="button" @click="downloadTemplate"
                    class="px-3 py-2 text-xs text-gray-500 hover:text-gray-700 border border-gray-200 rounded-lg bg-white"
                    title="Unduh Format Template CSV">
                    Template CSV
                </button>

                <!-- Tombol Tambah -->
                <button type="button" @click="emit('navigate', '/customers/create')"
                    class="px-4 py-2 text-xs font-semibold text-white bg-orange-500 rounded-full hover:bg-orange-600 shadow-sm transition-colors">
                    + Tambah Customer
                </button>
            </div>
        </div>

        <!-- Filter Card -->
        <div class="bg-white border border-blue-100/70 p-3.5 shadow-sm">
            <form @submit.prevent="handleFilter" class="flex flex-col sm:flex-row items-center gap-2.5">
                <div class="relative flex-1 w-full">
                    <input v-model="search" type="text"
                        placeholder="Cari customer, email, telepon, instansi..."
                        class="w-full text-xs border border-gray-200 px-3 py-2 text-gray-800 placeholder-gray-400 focus:outline-none focus:border-orange-500" />
                </div>
                <div class="w-full sm:w-44">
                    <select v-model="statusFilter" class="w-full text-xs border border-gray-200 px-3 py-2 text-gray-700 focus:outline-none focus:border-orange-500">
                        <option value="">Semua Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                    </select>
                </div>
                <div class="w-full sm:w-44">
                    <select v-model="institutionFilter" class="w-full text-xs border border-gray-200 px-3 py-2 text-gray-700 focus:outline-none focus:border-orange-500">
                        <option value="">Semua Instansi</option>
                        <option v-for="inst in institutions" :key="inst.id" :value="inst.id">{{ inst.name }}</option>
                    </select>
                </div>
                <button type="submit" class="w-full sm:w-auto px-4 py-2 text-xs bg-orange-500 text-white font-medium rounded-lg hover:bg-orange-600 transition-colors whitespace-nowrap">
                    Terapkan Filter
                </button>
                <button v-if="search || statusFilter || institutionFilter" @click="resetFilter" type="button" class="px-3 py-2 text-xs text-gray-500 hover:text-gray-700 border border-gray-200 rounded-lg">
                    Reset
                </button>
            </form>
        </div>

        <!-- Table Card -->
        <div class="bg-white border border-blue-100/70 overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[700px] text-left text-xs text-gray-600">
                    <thead class="bg-gray-50/70 border-b border-gray-100 text-gray-400 font-semibold uppercase tracking-wider text-[10.5px]">
                        <tr>
                            <th class="py-3.5 px-4">CUSTOMER</th>
                            <th class="py-3.5 px-4">CUSTOMER CODE</th>
                            <th class="py-3.5 px-4">INSTANSI</th>
                            <th class="py-3.5 px-4">EMAIL</th>
                            <th class="py-3.5 px-4">TELEPON</th>
                            <th class="py-3.5 px-4">STATUS</th>
                            <th class="py-3.5 px-4 text-right">AKSI</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-if="loading">
                            <td colspan="7" class="p-8 text-center text-gray-400">Memuat data customer...</td>
                        </tr>
                        <tr v-else-if="customers.length === 0">
                            <td colspan="7" class="p-8 text-center text-gray-400">Tidak ada customer ditemukan.</td>
                        </tr>
                        <tr v-for="c in customers" :key="c.id" class="hover:bg-blue-50/30 transition-colors">
                            <td class="py-3.5 px-4 font-semibold text-gray-900">
                                <button @click="emit('navigate', `/customers/${c.id}`)" class="hover:text-orange-600 text-left">
                                    {{ c.name }}
                                </button>
                            </td>
                            <td class="py-3.5 px-4 font-mono text-gray-500 text-[11px]">
                                {{ c.customer_code || '-' }}
                            </td>
                            <td class="py-3.5 px-4">
                                <div class="font-medium text-gray-900">{{ c.institution?.name || c.company || '-' }}</div>
                                <div v-if="c.institution?.type" class="text-[10.5px] text-gray-400">{{ c.institution.type }}</div>
                            </td>
                            <td class="py-3.5 px-4 text-gray-600">{{ c.email || '-' }}</td>
                            <td class="py-3.5 px-4 text-gray-600">{{ c.phone || '-' }}</td>
                            <td class="py-3.5 px-4">
                                <span class="px-2 py-0.5 text-[10px] font-medium border"
                                    :class="(c.status === 'active' || c.status === 'aktif' || !c.status) ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-gray-100 text-gray-500 border-gray-200'">
                                    {{ (c.status === 'active' || c.status === 'aktif' || !c.status) ? 'Active' : 'Inactive' }}
                                </span>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div class="flex items-center justify-end gap-1.5">
                                    <button @click="emit('navigate', `/customers/${c.id}`)" class="p-1.5 text-gray-400 hover:text-orange-600 rounded-lg hover:bg-orange-50 transition-colors" title="Detail">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </button>
                                    <button @click="emit('navigate', `/customers/${c.id}/edit`)" class="p-1.5 text-gray-400 hover:text-orange-600 rounded-lg hover:bg-orange-50 transition-colors" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                    <button @click="deleteCustomer(c.id)" class="p-1.5 text-gray-400 hover:text-red-600 rounded-lg hover:bg-red-50 transition-colors" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div v-if="pagination.total > 0" class="p-3 border-t border-gray-100 flex items-center justify-between">
                <Pagination :pagination="pagination" @page="loadCustomers" />
            </div>
        </div>
    </div>
</template>