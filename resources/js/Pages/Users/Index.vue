<script setup>
import { onMounted, ref } from 'vue';
import { api } from '../../services/api';
import Pagination from '../../Components/Pagination.vue';

const props = defineProps({
    user: { type: Object, default: () => ({}) },
});

const emit = defineEmits(['navigate']);

const users = ref([]);
const pagination = ref({ current_page: 1, last_page: 1, total: 0 });
const loading = ref(true);

async function loadUsers(page = 1) {
    loading.value = true;
    try {
        const res = await api.list('users', { page: String(page), per_page: '20' });
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
    <div class="space-y-4">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-xl font-semibold text-gray-900">Pengguna & Akun Tim</h1>
                <p class="text-xs text-gray-500 mt-0.5">Kelola akun Admin dan Sales tim Disty Akademi</p>
            </div>
            <button @click="emit('navigate', '/users/create')"
                class="self-start sm:self-auto inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-medium text-white bg-orange-500 rounded-full hover:bg-orange-600 transition-colors shadow-sm shrink-0">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Pengguna
            </button>
        </div>

        <!-- Table Card -->
        <div class="bg-white border border-blue-100 overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[680px] text-left text-xs text-gray-600">
                    <thead class="bg-gray-50/70 border-b border-gray-100 text-gray-500 font-medium uppercase tracking-wider text-[11px]">
                        <tr>
                            <th class="py-3 px-4">Nama & Email</th>
                            <th class="py-3 px-4">Role</th>
                            <th class="py-3 px-4">No. HP / WA</th>
                            <th class="py-3 px-4">Rekening Bank (Komisi)</th>
                            <th class="py-3 px-4">Status</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-if="loading">
                            <td colspan="6" class="py-8 text-center text-gray-400">Memuat data pengguna...</td>
                        </tr>
                        <tr v-else-if="users.length === 0">
                            <td colspan="6" class="py-8 text-center text-gray-400">Belum ada pengguna.</td>
                        </tr>
                        <tr v-for="u in users" :key="u.id" class="hover:bg-blue-50/30 transition-colors">
                            <td class="py-3 px-4">
                                <div class="font-semibold text-gray-900">{{ u.name }}</div>
                                <div class="text-[11px] text-gray-400">{{ u.email }}</div>
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                <span class="inline-flex items-center whitespace-nowrap px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wider border"
                                    :class="u.role === 'admin' ? 'bg-purple-50 text-purple-700 border-purple-200' : (u.role === 'manager' ? 'bg-blue-50 text-blue-700 border-blue-200' : 'bg-emerald-50 text-emerald-700 border-emerald-200')">
                                    {{ u.role }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-gray-700 whitespace-nowrap">{{ u.phone || '-' }}</td>
                            <td class="py-3 px-4">
                                <div v-if="u.bank_account_number" class="text-xs">
                                    <div class="flex items-center gap-1.5 flex-wrap">
                                        <span class="px-1.5 py-0.5 bg-gray-100 text-gray-800 font-mono text-[10px] font-bold border border-gray-200">
                                            {{ u.bank_name || 'Bank' }}
                                        </span>
                                        <span class="font-mono text-gray-900 font-bold">{{ u.bank_account_number }}</span>
                                    </div>
                                    <div v-if="u.bank_account_name" class="text-[10px] text-gray-500 mt-0.5">
                                        a.n {{ u.bank_account_name }}
                                    </div>
                                </div>
                                <span v-else class="text-[11px] text-gray-400 italic">Belum diatur</span>
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                <span class="inline-flex items-center whitespace-nowrap px-2 py-0.5 text-[10px] font-medium border"
                                    :class="u.is_active ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-gray-100 text-gray-500 border-gray-200'">
                                    {{ u.is_active ? 'Aktif' : 'Non-aktif' }}
                                </span>
                            </td>
                            <td class="py-3 px-4 text-right">
                                <button @click="emit('navigate', `/users/${u.id}/edit`)" class="p-1 text-gray-400 hover:text-blue-600 text-xs">
                                    Edit
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div v-if="pagination.total > 0" class="p-3 border-t border-gray-100 flex items-center justify-between">
                <Pagination :pagination="pagination" @page="loadUsers" />
            </div>
        </div>
    </div>
</template>