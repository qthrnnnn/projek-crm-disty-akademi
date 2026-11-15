<script setup>
import { onMounted, ref } from 'vue';
import { api } from '../../services/api';
import Badge from '../../Components/Badge.vue';

const props = defineProps({
    id: { type: [String, Number], required: true },
});

const emit = defineEmits(['navigate']);

const customer = ref(null);
const loading = ref(true);
const error = ref('');

async function loadCustomer() {
    loading.value = true;
    try {
        const res = await api.get('customers', props.id);
        customer.value = res.data || res;
    } catch (err) {
        error.value = err.message || 'Gagal memuat customer.';
    } finally {
        loading.value = false;
    }
}

onMounted(() => {
    loadCustomer();
});
</script>

<template>
    <div class="space-y-5">
        <div v-if="loading" class="text-center py-20 text-gray-400 text-xs">Memuat detail customer...</div>
        <div v-else-if="error" class="p-4 bg-red-50 border border-red-200 text-red-700 text-xs">{{ error }}</div>
        <div v-else class="space-y-5">
            <div class="bg-white p-5 border border-gray-100 shadow-xs flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-mono font-bold text-gray-400">#{{ customer.id }}</span>
                        <Badge :status="customer.status || 'aktif'" />
                    </div>
                    <h1 class="text-xl font-black text-gray-900 mt-2">{{ customer.name }}</h1>
                    <p class="text-xs text-gray-500 mt-0.5">{{ customer.company || 'Customer Individu' }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <button @click="emit('navigate', '/customers')" class="px-3.5 py-2 rounded-xl border border-gray-200 text-xs font-semibold text-gray-600 hover:bg-gray-50">
                        &larr; Kembali
                    </button>
                    <button @click="emit('navigate', `/customers/${customer.id}/edit`)" class="px-3.5 py-2 rounded-xl border border-gray-200 text-xs font-semibold text-gray-700 hover:bg-gray-50">
                        Edit Profil
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
                <div class="bg-white p-5 border border-gray-100 shadow-xs space-y-3">
                    <h2 class="text-xs font-bold text-gray-400 uppercase tracking-wider">Identitas & Kontak</h2>
                    <div class="text-xs space-y-2">
                        <div>
                            <span class="text-gray-400 block text-[11px]">Nomor NIK (KTP)</span>
                            <span class="font-bold text-gray-900 font-mono">{{ customer.nik || customer.document_number || '-' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400 block text-[11px]">Tanggal Lahir</span>
                            <span class="font-bold text-gray-900">
                                {{ customer.birth_date ? new Date(customer.birth_date).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) : '-' }}
                            </span>
                        </div>
                        <div>
                            <span class="text-gray-400 block text-[11px]">WhatsApp / Telepon</span>
                            <span class="font-bold text-gray-900">{{ customer.phone || '-' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400 block text-[11px]">Email</span>
                            <span class="font-bold text-gray-900">{{ customer.email || '-' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400 block text-[11px]">Instansi / Perusahaan</span>
                            <span class="font-bold text-gray-900">{{ customer.company || '-' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400 block text-[11px]">Alamat</span>
                            <span class="text-gray-700">{{ customer.address || '-' }}</span>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-2 bg-white p-5 border border-gray-100 shadow-xs space-y-4">
                    <h2 class="text-xs font-bold text-gray-400 uppercase tracking-wider">Histori Pembelian & Pendaftaran</h2>
                    <div v-if="customer.registrations?.length" class="divide-y divide-gray-50">
                        <div v-for="r in customer.registrations" :key="r.id" class="py-2.5 flex justify-between text-xs">
                            <span class="font-bold text-gray-900">{{ r.training_schedule?.product?.name || 'Pelatihan' }}</span>
                            <span class="text-gray-400">{{ r.created_at ? new Date(r.created_at).toLocaleDateString('id-ID') : '-' }}</span>
                        </div>
                    </div>
                    <div v-else class="text-center py-6 text-xs text-gray-400">
                        Belum ada riwayat pendaftaran pelatihan.
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
