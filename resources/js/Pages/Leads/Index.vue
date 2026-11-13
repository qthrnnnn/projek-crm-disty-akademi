<script setup>
import { computed, onMounted, ref } from 'vue';
import { api } from '../../services/api';
import Pagination from '../../Components/Pagination.vue';
import Badge from '../../Components/Badge.vue';

const props = defineProps({
    user: { type: Object, default: () => ({}) },
});

const emit = defineEmits(['navigate']);

const leads = ref([]);
const pagination = ref({ current_page: 1, last_page: 1, total: 0 });
const search = ref('');
const statusFilter = ref('');
const scopeTab = ref('all');
const loading = ref(true);

const isAdmin = computed(() => props.user?.role === 'admin');

async function loadLeads(page = 1) {
    loading.value = true;
    try {
        const params = { page: String(page), per_page: '20' };
        if (search.value) params.search = search.value;
        if (statusFilter.value) params.status = statusFilter.value;
        const res = await api.list('leads', params);
        let items = res.data || [];
        if (scopeTab.value === 'my' && props.user?.id) {
            items = items.filter(l => l.assigned_to === props.user.id);
        } else if (scopeTab.value === 'unassigned') {
            items = items.filter(l => !l.assigned_to);
        } else if (scopeTab.value === 'lost') {
            items = items.filter(l => l.status === 'lost');
        }
        leads.value = items;
        pagination.value = {
            current_page: res.current_page || page,
            last_page: res.last_page || 1,
            total: res.total || items.length,
            from: res.from || 1,
            to: res.to || items.length,
        };
    } catch (err) {
        console.error(err);
    } finally {
        loading.value = false;
    }
}

function setScope(tab) {
    scopeTab.value = tab;
    loadLeads(1);
}

onMounted(() => {
    loadLeads();
});
</script>

<template>
    <div class="space-y-5">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Leads & Prospek</h1>
                <p class="text-xs text-gray-500 mt-0.5">Kelola calon peserta akademi dan pipeline penjualan</p>
            </div>
            <button @click="emit('navigate', '/leads/create')"
                class="px-4 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-bold text-xs shadow-sm flex items-center justify-center gap-1.5 transition">
                <span>+</span> Tambah Prospek
            </button>
        </div>

        <!-- Scope Tabs & Filters -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div class="flex items-center gap-1 p-1 bg-gray-100 rounded-xl text-xs w-fit">
                <button @click="setScope('all')" class="px-3.5 py-1.5 rounded-lg font-semibold transition"
                    :class="scopeTab === 'all' ? 'bg-white text-gray-900 shadow-xs' : 'text-gray-500 hover:text-gray-900'">
                    Semua Lead
                </button>
                <button v-if="!isAdmin" @click="setScope('my')" class="px-3.5 py-1.5 rounded-lg font-semibold transition"
                    :class="scopeTab === 'my' ? 'bg-white text-gray-900 shadow-xs' : 'text-gray-500 hover:text-gray-900'">
                    Lead Saya
                </button>
                <button @click="setScope('unassigned')" class="px-3.5 py-1.5 rounded-lg font-semibold transition"
                    :class="scopeTab === 'unassigned' ? 'bg-white text-gray-900 shadow-xs' : 'text-gray-500 hover:text-gray-900'">
                    Belum Ditugaskan
                </button>
                <button @click="setScope('lost')" class="px-3.5 py-1.5 rounded-lg font-semibold transition"
                    :class="scopeTab === 'lost' ? 'bg-white text-gray-900 shadow-xs' : 'text-gray-500 hover:text-gray-900'">
                    Ditolak / Lost
                </button>
            </div>

            <div class="flex items-center gap-2">
                <input v-model="search" @input="loadLeads(1)" type="text" placeholder="Cari nama / WA / instansi..."
                    class="px-3.5 py-2 rounded-xl border border-gray-200 text-xs bg-white focus:outline-none focus:ring-2 focus:ring-orange-500/20" />
                <select v-model="statusFilter" @change="loadLeads(1)"
                    class="px-3 py-2 rounded-xl border border-gray-200 text-xs bg-white focus:outline-none">
                    <option value="">Semua Status</option>
                    <option value="new">Baru</option>
                    <option value="contacted">Dikontak</option>
                    <option value="active">Aktif</option>
                    <option value="negotiation">Negosiasi</option>
                    <option value="won">Berhasil</option>
                    <option value="lost">Ditolak</option>
                </select>
            </div>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-2xl border border-gray-100 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-gray-50/80 text-gray-400 font-bold uppercase tracking-wider text-[10px] border-b border-gray-100">
                        <tr>
                            <th class="p-4">Nama Prospek</th>
                            <th class="p-4">Kontak / WA</th>
                            <th class="p-4">Program Diminati</th>
                            <th class="p-4">Status Pipeline</th>
                            <th class="p-4">Sales / Assigned</th>
                            <th class="p-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50 text-gray-700">
                        <tr v-if="loading">
                            <td colspan="6" class="p-8 text-center text-gray-400 text-xs">Memuat data lead...</td>
                        </tr>
                        <tr v-else-if="leads.length === 0">
                            <td colspan="6" class="p-8 text-center text-gray-400 text-xs">Tidak ada lead ditemukan.</td>
                        </tr>
                        <tr v-for="lead in leads" :key="lead.id" class="hover:bg-gray-50/50 transition-colors">
                            <td class="p-4">
                                <a href="#" @click.prevent="emit('navigate', `/leads/${lead.id}`)" class="font-bold text-gray-900 hover:text-orange-600 block">
                                    {{ lead.name }}
                                </a>
                                <span class="text-[10px] text-gray-400">{{ lead.institution || 'Individu' }}</span>
                            </td>
                            <td class="p-4 text-gray-600">
                                <div>{{ lead.phone || '-' }}</div>
                                <div class="text-[10px] text-gray-400">{{ lead.email || '' }}</div>
                            </td>
                            <td class="p-4 font-semibold text-gray-900">
                                {{ lead.product?.name || lead.target_program || '-' }}
                            </td>
                            <td class="p-4">
                                <Badge :status="lead.status" />
                            </td>
                            <td class="p-4 text-gray-500">
                                {{ lead.sales?.name || lead.assigned_user?.name || '-' }}
                            </td>
                            <td class="p-4 text-right space-x-2">
                                <button @click="emit('navigate', `/leads/${lead.id}`)" class="font-bold text-gray-500 hover:text-gray-900">Detail</button>
                                <button @click="emit('navigate', `/leads/${lead.id}/edit`)" class="font-bold text-orange-600 hover:text-orange-700">Edit</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="p-4">
                <Pagination :meta="pagination" :loading="loading" @change-page="loadLeads" />
            </div>
        </div>
    </div>
</template>
