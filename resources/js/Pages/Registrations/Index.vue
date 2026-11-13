<script setup>
import { onMounted, ref } from 'vue';
import { api } from '../../services/api';
import Pagination from '../../Components/Pagination.vue';
import Badge from '../../Components/Badge.vue';

const emit = defineEmits(['navigate']);

const registrations = ref([]);
const pagination = ref({ current_page: 1, last_page: 1, total: 0 });
const search = ref('');
const loading = ref(true);

async function loadRegistrations(page = 1) {
    loading.value = true;
    try {
        const params = { page: String(page), per_page: '20' };
        if (search.value) params.search = search.value;
        const res = await api.list('registrations', params);
        registrations.value = res.data || [];
        pagination.value = {
            current_page: res.current_page || page,
            last_page: res.last_page || 1,
            total: res.total || registrations.value.length,
            from: res.from || 1,
            to: res.to || registrations.value.length,
        };
    } catch (err) {
        console.error(err);
    } finally {
        loading.value = false;
    }
}

onMounted(() => {
    loadRegistrations();
});
</script>

<template>
    <div class="space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Pendaftaran Peserta Pelatihan</h1>
                <p class="text-xs text-gray-500 mt-0.5">Daftar calon peserta yang telah terdaftar ke batch kelas aktif</p>
            </div>
            <div class="flex items-center gap-2">
                <input v-model="search" @input="loadRegistrations(1)" type="text" placeholder="Cari peserta / kode..."
                    class="px-3.5 py-2 rounded-xl border border-gray-200 text-xs bg-white focus:outline-none" />
                <button @click="emit('navigate', '/registrations/create')"
                    class="px-4 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-bold text-xs shadow-sm">
                    + Daftarkan Peserta
                </button>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-gray-50/80 text-gray-400 font-bold uppercase tracking-wider text-[10px] border-b border-gray-100">
                        <tr>
                            <th class="p-4">Reg Code</th>
                            <th class="p-4">Nama Peserta</th>
                            <th class="p-4">Jadwal & Program</th>
                            <th class="p-4">Status Peserta</th>
                            <th class="p-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 text-gray-700">
                        <tr v-if="loading">
                            <td colspan="5" class="p-8 text-center text-gray-400 text-xs">Memuat pendaftaran...</td>
                        </tr>
                        <tr v-else-if="registrations.length === 0">
                            <td colspan="5" class="p-8 text-center text-gray-400 text-xs">Belum ada peserta terdaftar.</td>
                        </tr>
                        <tr v-for="r in registrations" :key="r.id" class="hover:bg-gray-50/50 transition-colors">
                            <td class="p-4 font-mono font-bold text-orange-600">#{{ r.id }}</td>
                            <td class="p-4">
                                <span class="font-bold text-gray-900 block">{{ r.customer?.name || r.lead?.name || 'Peserta' }}</span>
                                <span class="text-[10px] text-gray-400">{{ r.customer?.phone || r.lead?.phone || '-' }}</span>
                            </td>
                            <td class="p-4 text-gray-700">
                                <span class="font-bold block">{{ r.training_schedule?.product?.name || 'Pelatihan' }}</span>
                                <span class="text-[10px] text-gray-400">{{ r.training_schedule?.start_date || '-' }}</span>
                            </td>
                            <td class="p-4"><Badge :status="r.status || 'aktif'" /></td>
                            <td class="p-4 text-right">
                                <button @click="emit('navigate', `/registrations/${r.id}/edit`)" class="font-bold text-orange-600 hover:text-orange-700">Edit</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="p-4">
                <Pagination :meta="pagination" :loading="loading" @change-page="loadRegistrations" />
            </div>
        </div>
    </div>
</template>
