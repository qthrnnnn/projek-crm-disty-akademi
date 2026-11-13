<script setup>
import { onMounted, ref } from 'vue';
import { api } from '../../services/api';
import Pagination from '../../Components/Pagination.vue';
import Badge from '../../Components/Badge.vue';

const emit = defineEmits(['navigate']);

const users = ref([]);
const pagination = ref({ current_page: 1, last_page: 1, total: 0 });
const search = ref('');
const loading = ref(true);

async function loadUsers(page = 1) {
    loading.value = true;
    try {
        const params = { page: String(page), per_page: '20' };
        if (search.value) params.search = search.value;
        const res = await api.list('users', params);
        users.value = res.data || [];
        pagination.value = {
            current_page: res.current_page || page,
            last_page: res.last_page || 1,
            total: res.total || users.value.length,
            from: res.from || 1,
            to: res.to || users.value.length,
        };
    } catch (err) {
        console.error(err);
    } finally {
        loading.value = false;
    }
}

onMounted(() => {
    loadUsers();
});
</script>

<template>
    <div class="space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Manajemen Pengguna & Sales</h1>
                <p class="text-xs text-gray-500 mt-0.5">Kelola hak akses akun tim CRM dan role admin/sales</p>
            </div>
            <div class="flex items-center gap-2">
                <input v-model="search" @input="loadUsers(1)" type="text" placeholder="Cari user..."
                    class="px-3.5 py-2 rounded-xl border border-gray-200 text-xs bg-white focus:outline-none" />
                <button @click="emit('navigate', '/users/create')"
                    class="px-4 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-bold text-xs shadow-sm">
                    + Tambah Pengguna
                </button>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-gray-50/80 text-gray-400 font-bold uppercase tracking-wider text-[10px] border-b border-gray-100">
                        <tr>
                            <th class="p-4">Pengguna</th>
                            <th class="p-4">Email Login</th>
                            <th class="p-4">Role Akses</th>
                            <th class="p-4">Bergabung</th>
                            <th class="p-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 text-gray-700">
                        <tr v-if="loading">
                            <td colspan="5" class="p-8 text-center text-gray-400 text-xs">Memuat akun pengguna...</td>
                        </tr>
                        <tr v-else-if="users.length === 0">
                            <td colspan="5" class="p-8 text-center text-gray-400 text-xs">Tidak ada user ditemukan.</td>
                        </tr>
                        <tr v-for="u in users" :key="u.id" class="hover:bg-gray-50/50 transition-colors">
                            <td class="p-4 font-bold text-gray-900 flex items-center gap-2.5">
                                <div class="w-7 h-7 rounded-full bg-orange-100 text-orange-600 font-black text-xs flex items-center justify-center">
                                    {{ u.name.charAt(0).toUpperCase() }}
                                </div>
                                <span>{{ u.name }}</span>
                            </td>
                            <td class="p-4 text-gray-600 font-mono">{{ u.email }}</td>
                            <td class="p-4">
                                <span class="px-2.5 py-0.5 rounded-full text-[10px] font-bold uppercase tracking-wider border"
                                    :class="u.role === 'admin' ? 'bg-purple-50 text-purple-700 border-purple-200' : 'bg-blue-50 text-blue-700 border-blue-200'">
                                    {{ u.role || 'sales' }}
                                </span>
                            </td>
                            <td class="p-4 text-gray-500">{{ u.created_at ? new Date(u.created_at).toLocaleDateString('id-ID') : '-' }}</td>
                            <td class="p-4 text-right">
                                <button @click="emit('navigate', `/users/${u.id}/edit`)" class="font-bold text-orange-600 hover:text-orange-700">Edit</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="p-4">
                <Pagination :meta="pagination" :loading="loading" @change-page="loadUsers" />
            </div>
        </div>
    </div>
</template>
