<script setup>
import { onMounted, ref } from 'vue';
import { api } from '../../services/api';
import Pagination from '../../Components/Pagination.vue';
import Badge from '../../Components/Badge.vue';

const emit = defineEmits(['navigate']);

const customers = ref([]);
const pagination = ref({ current_page: 1, last_page: 1, total: 0 });
const search = ref('');
const loading = ref(true);

async function loadCustomers(page = 1) {
    loading.value = true;
    try {
        const params = { page: String(page), per_page: '20' };
        if (search.value) params.search = search.value;
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

onMounted(() => {
    loadCustomers();
});
</script>

<template>
    <div class="space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Daftar Customer</h1>
                <p class="text-xs text-gray-500 mt-0.5">Database pelanggan, alumni pelatihan, & peserta aktif</p>
            </div>
            <div class="flex items-center gap-2">
                <input v-model="search" @input="loadCustomers(1)" type="text" placeholder="Cari nama / email / instansi..."
                    class="px-3.5 py-2 rounded-xl border border-gray-200 text-xs bg-white focus:outline-none focus:ring-2 focus:ring-orange-500/20" />
                <button @click="emit('navigate', '/customers/create')"
                    class="px-4 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-bold text-xs shadow-sm whitespace-nowrap">
                    + Tambah Customer
                </button>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-gray-50/80 text-gray-400 font-bold uppercase tracking-wider text-[10px] border-b border-gray-100">
                        <tr>
                            <th class="p-4">Customer</th>
                            <th class="p-4">Kontak</th>
                            <th class="p-4">Instansi</th>
                            <th class="p-4">Status</th>
                            <th class="p-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 text-gray-700">
                        <tr v-if="loading">
                            <td colspan="5" class="p-8 text-center text-gray-400 text-xs">Memuat data customer...</td>
                        </tr>
                        <tr v-else-if="customers.length === 0">
                            <td colspan="5" class="p-8 text-center text-gray-400 text-xs">Tidak ada customer ditemukan.</td>
                        </tr>
                        <tr v-for="c in customers" :key="c.id" class="hover:bg-gray-50/50 transition-colors">
                            <td class="p-4">
                                <a href="#" @click.prevent="emit('navigate', `/customers/${c.id}`)" class="font-bold text-gray-900 hover:text-orange-600 block">
                                    {{ c.name }}
                                </a>
                                <span class="text-[10px] text-gray-400">ID: #{{ c.id }}</span>
                            </td>
                            <td class="p-4 text-gray-600">
                                <div>{{ c.phone || '-' }}</div>
                                <div class="text-[10px] text-gray-400">{{ c.email || '-' }}</div>
                            </td>
                            <td class="p-4 font-medium text-gray-700">{{ c.company || c.institution || '-' }}</td>
                            <td class="p-4"><Badge :status="c.status || 'aktif'" /></td>
                            <td class="p-4 text-right space-x-2">
                                <button @click="emit('navigate', `/customers/${c.id}`)" class="font-bold text-gray-500 hover:text-gray-900">Detail</button>
                                <button @click="emit('navigate', `/customers/${c.id}/edit`)" class="font-bold text-orange-600 hover:text-orange-700">Edit</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="p-4">
                <Pagination :meta="pagination" :loading="loading" @change-page="loadCustomers" />
            </div>
        </div>
    </div>
</template>
