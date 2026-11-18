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
        await api.request(`/reminders/${id}/read`, { method: 'PATCH' });
        loadReminders(pagination.value.current_page);
    } catch (e) {
        /* ignore */
    }
}

function getWaUrl(lead) {
    if (!lead) return null;
    const raw = lead.whatsapp || lead.phone;
    if (!raw) return null;
    let digits = String(raw).replace(/\D/g, '');
    if (digits.startsWith('0')) digits = '62' + digits.substring(1);
    const prog = lead.product?.name || lead.product_name || lead.target_program || 'program pelatihan';
    const text = encodeURIComponent(`Halo Kak ${lead.name}, saya dari Disty Akademi ingin menanyakan kelanjutan informasi pelatihan ${prog}. Apakah ada yang bisa kami bantu?`);
    return `https://wa.me/${digits}?text=${text}`;
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

        <div class="bg-white border border-gray-100 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-left text-xs">
                    <thead class="bg-gray-50/80 text-gray-400 font-bold uppercase tracking-wider text-[10px] border-b border-gray-100">
                        <tr>
                            <th class="p-4">Waktu</th>
                            <th class="p-4">Jenis Notifikasi</th>
                            <th class="p-4">Terkait Prospek</th>
                            <th class="p-4">Program & Keterangan</th>
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
                        <tr v-for="r in reminders" :key="r.id" class="hover:bg-gray-50/50 transition-colors" :class="r.is_read ? 'opacity-80' : 'bg-orange-50/25'">
                            <td class="p-4 text-gray-500 whitespace-nowrap text-[11px] align-top">
                                {{ r.created_at ? new Date(r.created_at).toLocaleString('id-ID') : '-' }}
                            </td>
                            <td class="p-4 font-bold text-gray-900 align-top">
                                <span v-if="r.type === 'payment_rejected'" class="block text-red-600 font-bold">Bukti Bayar Tidak Valid</span>
                                <span v-else class="block">{{ r.title || (r.type === 'commission_paid' ? 'Komisi Cair' : 'Instruksi Admin') }}</span>
                                <span v-if="r.type === 'payment_rejected'" class="inline-block mt-1 px-1.5 py-0.5 text-[9.5px] font-bold bg-red-100 text-red-700">
                                    Ditolak Admin
                                </span>
                                <span v-else-if="r.priority === 'urgent'" class="inline-block mt-1 px-1.5 py-0.5 text-[9.5px] font-bold bg-red-100 text-red-700">
                                    Prioritas Cepat
                                </span>
                            </td>
                            <td class="p-4 align-top">
                                <div v-if="r.lead">
                                    <a href="#" @click.prevent="emit('navigate', `/leads/${r.lead.id}`)" class="font-bold text-gray-900 hover:text-orange-600 block text-xs">
                                        {{ r.lead.name }}
                                    </a>
                                    <div class="text-[11px] text-gray-500 mt-0.5">
                                        {{ r.lead.company || r.lead.institution || 'Individu' }}
                                    </div>
                                    <div v-if="r.type !== 'commission_paid'" class="mt-1.5 flex items-center gap-1.5">
                                        <span class="inline-flex items-center gap-1 text-emerald-700 font-semibold font-mono text-[11px] bg-emerald-50 px-2 py-0.5 border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                                            WA: {{ r.lead.whatsapp || r.lead.phone || '-' }}
                                        </span>
                                    </div>
                                    <div v-else class="mt-1 text-[11px] text-gray-500 font-mono">
                                        {{ r.lead.whatsapp || r.lead.phone || '-' }}
                                    </div>
                                </div>
                                <span v-else class="text-gray-400">-</span>
                            </td>
                            <td class="p-4 max-w-md align-top space-y-1.5">
                                <div v-if="r.lead?.product?.name || r.lead?.product_name || r.lead?.target_program">
                                    <span class="inline-flex items-center px-2 py-0.5 text-[10.5px] font-bold bg-orange-50 text-orange-700 border border-orange-200">
                                        Program: {{ r.lead.product?.name || r.lead.product_name || r.lead.target_program }}
                                    </span>
                                </div>
                                <p class="text-gray-800 text-xs leading-relaxed font-medium bg-gray-50/80 p-2.5 border border-gray-100">
                                    {{ r.message || r.notes || '-' }}
                                </p>
                            </td>
                            <td class="p-4 align-top">
                                <Badge :status="r.is_read ? 'aktif' : 'pending'" :text="r.is_read ? 'DIBACA' : 'BARU'" />
                            </td>
                            <td class="p-4 text-right align-top">
                                <div class="flex items-center justify-end gap-1.5 flex-wrap">
                                    <!-- Tombol Chat WA HANYA untuk follow-up instruksi admin, tidak ditampilkan untuk komisi cair -->
                                    <a v-if="r.type !== 'commission_paid' && getWaUrl(r.lead)" :href="getWaUrl(r.lead)" target="_blank" rel="noopener noreferrer"
                                        class="px-2.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg text-[11px] transition inline-flex items-center gap-1 shadow-2xs"
                                        title="Buka WhatsApp dengan pesan follow-up">
                                        Chat WA
                                    </a>
                                    <!-- Tombol Lihat Komisi untuk notifikasi pencairan komisi -->
                                    <button v-if="r.type === 'commission_paid'" @click="emit('navigate', '/commissions')"
                                        class="px-2.5 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg text-[11px] transition inline-flex items-center gap-1 shadow-2xs"
                                        title="Lihat halaman komisi sales">
                                        Lihat Komisi
                                    </button>
                                    <button v-if="r.lead" @click="emit('navigate', `/leads/${r.lead.id}`)"
                                        class="px-2.5 py-1.5 border border-gray-200 hover:bg-gray-50 text-gray-700 rounded-lg text-[11px] font-semibold transition"
                                        title="Lihat rincian data prospek">
                                        Detail
                                    </button>
                                    <button v-if="!r.is_read" @click="markAsRead(r.id)"
                                        class="text-[11px] text-orange-600 hover:underline font-semibold ml-1"
                                        title="Tandai pengingat sudah dibaca">
                                        Tandai Dibaca
                                    </button>
                                </div>
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
