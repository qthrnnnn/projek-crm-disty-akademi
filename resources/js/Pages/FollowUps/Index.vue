<script setup>
import { onMounted, ref } from 'vue';
import { api } from '../../services/api';
import Pagination from '../../Components/Pagination.vue';
import Badge from '../../Components/Badge.vue';

const emit = defineEmits(['navigate']);

const followups = ref([]);
const pagination = ref({ current_page: 1, last_page: 1, total: 0 });
const loading = ref(true);

async function loadFollowUps(page = 1) {
    loading.value = true;
    try {
        const res = await api.list('follow-ups', { page: String(page), per_page: '20' });
        followups.value = res.data || [];
        pagination.value = {
            current_page: res.current_page || page,
            last_page: res.last_page || 1,
            total: res.total || followups.value.length,
            from: res.from || 1,
            to: res.to || followups.value.length,
        };
    } catch (err) {
        console.error(err);
    } finally {
        loading.value = false;
    }
}

onMounted(() => {
    loadFollowUps();
});
</script>

<template>
    <div class="space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Jadwal & Riwayat Follow-Up</h1>
                <p class="text-xs text-gray-500 mt-0.5">Rencana kontak prospek dan evaluasi respon</p>
            </div>
            <button @click="emit('navigate', '/follow-ups/create')"
                class="px-4 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-bold text-xs shadow-sm">
                + Tambah Follow-Up
            </button>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-gray-50/80 text-gray-400 font-bold uppercase tracking-wider text-[10px] border-b border-gray-100">
                        <tr>
                            <th class="p-4">Tanggal Rencana</th>
                            <th class="p-4">Nama Prospek</th>
                            <th class="p-4">Jenis Kontak</th>
                            <th class="p-4">Status / Respon</th>
                            <th class="p-4">Catatan</th>
                            <th class="p-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 text-gray-700">
                        <tr v-if="loading">
                            <td colspan="6" class="p-8 text-center text-gray-400 text-xs">Memuat data follow-up...</td>
                        </tr>
                        <tr v-else-if="followups.length === 0">
                            <td colspan="6" class="p-8 text-center text-gray-400 text-xs">Belum ada agenda follow-up.</td>
                        </tr>
                        <tr v-for="fu in followups" :key="fu.id" class="hover:bg-gray-50/50 transition-colors">
                            <td class="p-4 font-bold text-gray-900 whitespace-nowrap">{{ fu.scheduled_at || '-' }}</td>
                            <td class="p-4 font-semibold text-gray-900">
                                <a v-if="fu.lead" href="#" @click.prevent="emit('navigate', `/leads/${fu.lead.id}`)" class="hover:text-orange-600">
                                    {{ fu.lead.name }}
                                </a>
                                <span v-else class="text-gray-400">-</span>
                            </td>
                            <td class="p-4 capitalize text-gray-600">{{ fu.type || 'WhatsApp' }}</td>
                            <td class="p-4"><Badge :status="fu.status || 'pending'" /></td>
                            <td class="p-4 max-w-xs truncate text-gray-600">{{ fu.notes || '-' }}</td>
                            <td class="p-4 text-right">
                                <button @click="emit('navigate', `/follow-ups/${fu.id}/edit`)" class="font-bold text-orange-600 hover:text-orange-700">Edit</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="p-4">
                <Pagination :meta="pagination" :loading="loading" @change-page="loadFollowUps" />
            </div>
        </div>
    </div>
</template>
