<script setup>
import { onMounted, ref } from 'vue';
import { api } from '../../services/api';
import Badge from '../../Components/Badge.vue';

const props = defineProps({
    id: { type: [String, Number], required: true },
    user: { type: Object, default: () => ({}) },
});

const emit = defineEmits(['navigate']);

const lead = ref(null);
const loading = ref(true);
const error = ref('');

async function loadLead() {
    loading.value = true;
    try {
        const res = await api.get('leads', props.id);
        lead.value = res.data || res;
    } catch (err) {
        error.value = err.message || 'Gagal memuat detail prospek.';
    } finally {
        loading.value = false;
    }
}

onMounted(() => {
    loadLead();
});
</script>

<template>
    <div class="space-y-5">
        <div v-if="loading" class="text-center py-20 text-gray-400 text-xs">Memuat detail prospek...</div>
        <div v-else-if="error" class="p-4 rounded-xl bg-red-50 border border-red-200 text-red-700 text-xs">{{ error }}</div>
        <div v-else class="space-y-5">
            <!-- Header Card -->
            <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-xs flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-mono font-bold text-orange-600 bg-orange-50 px-2 py-0.5 rounded-md">
                            {{ lead.lead_code || `#${lead.id}` }}
                        </span>
                        <Badge :status="lead.status" />
                    </div>
                    <h1 class="text-xl font-black text-gray-900 mt-2">{{ lead.name }}</h1>
                    <p class="text-xs text-gray-500 mt-0.5">{{ lead.institution || 'Individu' }} &bull; {{ lead.phone || '-' }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <button @click="emit('navigate', '/leads')" class="px-3.5 py-2 rounded-xl border border-gray-200 text-xs font-semibold text-gray-600 hover:bg-gray-50">
                        &larr; Kembali
                    </button>
                    <button @click="emit('navigate', `/leads/${lead.id}/edit`)" class="px-3.5 py-2 rounded-xl border border-gray-200 text-xs font-semibold text-gray-700 hover:bg-gray-50">
                        Edit
                    </button>
                    <button @click="emit('navigate', `/payments/create?lead_id=${lead.id}`)" class="px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-xs">
                        + Input Bayar
                    </button>
                </div>
            </div>

            <!-- Detail Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
                <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-xs space-y-4">
                    <h2 class="text-xs font-bold text-gray-400 uppercase tracking-wider">Informasi Kontak</h2>
                    <div class="space-y-3 text-xs">
                        <div>
                            <span class="text-gray-400 block text-[11px]">Nomor WhatsApp</span>
                            <span class="font-bold text-gray-900">{{ lead.phone || '-' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400 block text-[11px]">Email</span>
                            <span class="font-bold text-gray-900">{{ lead.email || '-' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400 block text-[11px]">Asal Instansi / Kampus</span>
                            <span class="font-bold text-gray-900">{{ lead.institution || '-' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400 block text-[11px]">Program Diminati</span>
                            <span class="font-bold text-gray-900">{{ lead.product?.name || lead.target_program || '-' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400 block text-[11px]">Sales Bertanggung Jawab</span>
                            <span class="font-bold text-gray-900">{{ lead.sales?.name || lead.assigned_user?.name || 'Belum ditugaskan' }}</span>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-2 bg-white p-5 rounded-2xl border border-gray-100 shadow-xs space-y-4">
                    <h2 class="text-xs font-bold text-gray-400 uppercase tracking-wider">Catatan & Histori Aktivitas</h2>
                    <p class="text-xs text-gray-600 bg-gray-50 p-3 rounded-xl border border-gray-100">
                        {{ lead.notes || 'Belum ada catatan khusus untuk prospek ini.' }}
                    </p>

                    <div v-if="lead.activities?.length" class="divide-y divide-gray-50 pt-2">
                        <div v-for="act in lead.activities" :key="act.id" class="py-2.5 flex items-start justify-between text-xs">
                            <div>
                                <span class="font-bold text-gray-900 capitalize">{{ act.type }}</span>
                                <p class="text-gray-500 text-[11px] mt-0.5">{{ act.notes || act.description }}</p>
                            </div>
                            <span class="text-[10px] text-gray-400">{{ act.created_at ? new Date(act.created_at).toLocaleString('id-ID') : '-' }}</span>
                        </div>
                    </div>
                    <div v-else class="text-center py-6 text-xs text-gray-400">
                        Belum ada interaksi tercatat.
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
