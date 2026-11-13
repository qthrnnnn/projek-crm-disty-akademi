<script setup>
import { onMounted, ref } from 'vue';
import { api } from '../../services/api';
import Pagination from '../../Components/Pagination.vue';

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

onMounted(() => {
    loadInstitutions();
});
</script>

<template>
    <div class="space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Daftar Instansi & Mitra</h1>
                <p class="text-xs text-gray-500 mt-0.5">Kerjasama kampus, sekolah, BUMN, & perusahaan swasta</p>
            </div>
            <div class="flex items-center gap-2">
                <input v-model="search" @input="loadInstitutions(1)" type="text" placeholder="Cari nama instansi..."
                    class="px-3.5 py-2 rounded-xl border border-gray-200 text-xs bg-white focus:outline-none" />
                <button @click="emit('navigate', '/institutions/create')"
                    class="px-4 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-bold text-xs shadow-sm">
                    + Tambah Instansi
                </button>
            </div>
        </div>

        <div class="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-gray-50/80 text-gray-400 font-bold uppercase tracking-wider text-[10px] border-b border-gray-100">
                        <tr>
                            <th class="p-4">Nama Instansi / Mitra</th>
                            <th class="p-4">Tipe & Sektor</th>
                            <th class="p-4">Kota / Lokasi</th>
                            <th class="p-4">PIC / Kontak</th>
                            <th class="p-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 text-gray-700">
                        <tr v-if="loading">
                            <td colspan="5" class="p-8 text-center text-gray-400 text-xs">Memuat data instansi...</td>
                        </tr>
                        <tr v-else-if="institutions.length === 0">
                            <td colspan="5" class="p-8 text-center text-gray-400 text-xs">Belum ada mitra instansi terdaftar.</td>
                        </tr>
                        <tr v-for="inst in institutions" :key="inst.id" class="hover:bg-gray-50/50 transition-colors">
                            <td class="p-4 font-bold text-gray-900">{{ inst.name }}</td>
                            <td class="p-4 text-gray-600">{{ inst.type || 'Perguruan Tinggi' }}</td>
                            <td class="p-4 text-gray-500">{{ inst.city || inst.address || '-' }}</td>
                            <td class="p-4 text-gray-600">
                                <div>{{ inst.pic_name || '-' }}</div>
                                <div class="text-[10px] text-gray-400">{{ inst.pic_phone || '' }}</div>
                            </td>
                            <td class="p-4 text-right">
                                <button @click="emit('navigate', `/institutions/${inst.id}/edit`)" class="font-bold text-orange-600 hover:text-orange-700">Edit</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="p-4">
                <Pagination :meta="pagination" :loading="loading" @change-page="loadInstitutions" />
            </div>
        </div>
    </div>
</template>
