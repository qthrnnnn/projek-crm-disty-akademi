<script setup>
import { onMounted, ref } from 'vue';
import { api } from '../../services/api';
import Pagination from '../../Components/Pagination.vue';
import Badge from '../../Components/Badge.vue';

const emit = defineEmits(['navigate']);

const reminders = ref([]);
const pagination = ref({ current_page: 1, last_page: 1, total: 0 });
const loading = ref(true);

async function loadReminders(page = 1) {
    loading.value = true;
    try {
        const res = await api.list('reminders', { page: String(page), per_page: '20' });
        reminders.value = res.data || [];
        pagination.value = {
            current_page: res.current_page || page,
            last_page: res.last_page || 1,
            total: res.total || reminders.value.length,
            from: res.from || 1,
            to: res.to || reminders.value.length,
        };
    } catch (err) {
        console.error(err);
    } finally {
        loading.value = false;
    }
}

async function markAsRead(id) {
    try {
        await api.update('reminders', id, { is_read: 1 });
        loadReminders(pagination.value.current_page);
    } catch (e) {
        /* ignore */
    }
}

onMounted(() => {
    loadReminders();
});
</script>

<template>
    <div class="space-y-5">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Notifikasi & Pengingat Sales</h1>
            <p class="text-xs text-gray-500 mt-0.5">Daftar instruksi follow-up dari admin & pemberitahuan pencairan komisi</p>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-gray-50/80 text-gray-400 font-bold uppercase tracking-wider text-[10px] border-b border-gray-100">
                        <tr>
                            <th class="p-4">Waktu</th>
                            <th class="p-4">Jenis Notifikasi</th>
                            <th class="p-4">Terkait Prospek</th>
                            <th class="p-4">Pesan / Instruksi</th>
                            <th class="p-4">Status</th>
                            <th class="p-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 text-gray-700">
                        <tr v-if="loading">
                            <td colspan="6" class="p-8 text-center text-gray-400 text-xs">Memuat notifikasi...</td>
                        </tr>
                        <tr v-else-if="reminders.length === 0">
                            <td colspan="6" class="p-8 text-center text-gray-400 text-xs">Tidak ada notifikasi aktif.</td>
                        </tr>
                        <tr v-for="r in reminders" :key="r.id" class="hover:bg-gray-50/50 transition-colors" :class="r.is_read ? 'opacity-70' : 'bg-orange-50/20'">
                            <td class="p-4 text-gray-500 whitespace-nowrap">{{ r.created_at ? new Date(r.created_at).toLocaleString('id-ID') : '-' }}</td>
                            <td class="p-4 font-bold text-gray-900">{{ r.title || (r.type === 'commission_paid' ? 'Komisi Cair' : 'Instruksi Admin') }}</td>
                            <td class="p-4 font-semibold text-gray-900">
                                <a v-if="r.lead" href="#" @click.prevent="emit('navigate', `/leads/${r.lead.id}`)" class="hover:text-orange-600">
                                    {{ r.lead.name }}
                                </a>
                                <span v-else class="text-gray-400">-</span>
                            </td>
                            <td class="p-4 max-w-sm truncate text-gray-600">{{ r.message || r.notes || '-' }}</td>
                            <td class="p-4"><Badge :status="r.is_read ? 'aktif' : 'pending'" :text="r.is_read ? 'DIBACA' : 'BARU'" /></td>
                            <td class="p-4 text-right">
                                <button v-if="!r.is_read" @click="markAsRead(r.id)" class="font-bold text-orange-600 hover:text-orange-700">Tandai Dibaca</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="p-4">
                <Pagination :meta="pagination" :loading="loading" @change-page="loadReminders" />
            </div>
        </div>
    </div>
</template>
