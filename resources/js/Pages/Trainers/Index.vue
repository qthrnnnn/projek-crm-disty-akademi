<script setup>
import { onMounted, ref } from 'vue';
import { api } from '../../services/api';
import Pagination from '../../Components/Pagination.vue';
import Badge from '../../Components/Badge.vue';

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

onMounted(() => {
    loadTrainers();
});
</script>

<template>
    <div class="space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Daftar Trainer & Instruktur</h1>
                <p class="text-xs text-gray-500 mt-0.5">Pengajar bersertifikasi dan praktisi industri</p>
            </div>
            <div class="flex items-center gap-2">
                <input v-model="search" @input="loadTrainers(1)" type="text" placeholder="Cari trainer..."
                    class="px-3.5 py-2 rounded-xl border border-gray-200 text-xs bg-white focus:outline-none" />
                <button @click="emit('navigate', '/trainers/create')"
                    class="px-4 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-bold text-xs shadow-sm">
                    + Tambah Trainer
                </button>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-gray-50/80 text-gray-400 font-bold uppercase tracking-wider text-[10px] border-b border-gray-100">
                        <tr>
                            <th class="p-4">Nama Trainer</th>
                            <th class="p-4">Bidang Keahlian</th>
                            <th class="p-4">Kontak / WA</th>
                            <th class="p-4">Status</th>
                            <th class="p-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 text-gray-700">
                        <tr v-if="loading">
                            <td colspan="5" class="p-8 text-center text-gray-400 text-xs">Memuat data trainer...</td>
                        </tr>
                        <tr v-else-if="trainers.length === 0">
                            <td colspan="5" class="p-8 text-center text-gray-400 text-xs">Belum ada trainer terdaftar.</td>
                        </tr>
                        <tr v-for="t in trainers" :key="t.id" class="hover:bg-gray-50/50 transition-colors">
                            <td class="p-4 font-bold text-gray-900">{{ t.name }}</td>
                            <td class="p-4 font-semibold text-gray-700">{{ t.expertise || 'Software Engineering' }}</td>
                            <td class="p-4 text-gray-600">
                                <div>{{ t.phone || '-' }}</div>
                                <div class="text-[10px] text-gray-400">{{ t.email || '' }}</div>
                            </td>
                            <td class="p-4"><Badge :status="t.is_active ? 'aktif' : 'inactive'" /></td>
                            <td class="p-4 text-right">
                                <button @click="emit('navigate', `/trainers/${t.id}/edit`)" class="font-bold text-orange-600 hover:text-orange-700">Edit</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="p-4">
                <Pagination :meta="pagination" :loading="loading" @change-page="loadTrainers" />
            </div>
        </div>
    </div>
</template>
