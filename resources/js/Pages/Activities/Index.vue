<script setup>
import { onMounted, ref } from 'vue';
import { api } from '../../services/api';
import Pagination from '../../Components/Pagination.vue';

const props = defineProps({
    user: { type: Object, default: () => ({}) },
});

const emit = defineEmits(['navigate']);

const activities = ref([]);
const pagination = ref({ current_page: 1, last_page: 1, total: 0 });
const activityType = ref('');
const loading = ref(true);
const error = ref('');

const typeLabels = {
    created: 'Tambah Data',
    updated: 'Ubah Data',
    deleted: 'Hapus Data',
    restored: 'Pulihkan Data',
    assigned: 'Penugasan Sales',
    call: 'Telepon',
    email: 'Email',
    whatsapp: 'WhatsApp',
    meeting: 'Meeting',
    note: 'Catatan',
    status_change: 'Ubah Status',
    admin_reminder: 'Instruksi Admin',
};

const fieldLabels = {
    name: 'Nama', company: 'Instansi', institution_id: 'ID Instansi', type: 'Jenis',
    status: 'Status', notes: 'Catatan', description: 'Deskripsi', title: 'Judul',
    phone: 'Telepon', whatsapp: 'WhatsApp', email: 'Email', address: 'Alamat',
    city: 'Kota', province: 'Provinsi', industry: 'Bidang', nik: 'NIK', birth_date: 'Tanggal lahir',
    location: 'Lokasi / Media', training_date: 'Tanggal pelatihan', start_time: 'Jam mulai',
    end_time: 'Jam selesai', capacity: 'Kapasitas', trainer_id: 'ID Trainer', product_id: 'ID Program',
    training_schedule_id: 'ID Jadwal', customer_id: 'ID Customer', lead_id: 'ID Lead',
    assigned_to: 'ID Sales penanggung jawab', user_id: 'ID Pengguna', sales_id: 'ID Sales',
    priority: 'Prioritas', price: 'Harga', amount: 'Nominal', deal_price: 'Harga kesepakatan',
    payment_method: 'Metode pembayaran', proof_file: 'Bukti pembayaran', payment_date: 'Tanggal pembayaran',
    verification_notes: 'Catatan verifikasi', verified_by: 'ID Pemeriksa', verified_at: 'Waktu verifikasi',
    commission_amount: 'Nominal komisi', commission_rate: 'Persentase komisi', deal_amount: 'Nilai transaksi',
    approved_at: 'Waktu persetujuan', paid_at: 'Waktu pembayaran',
    follow_up_at: 'Jadwal follow-up', method: 'Metode', result_notes: 'Hasil follow-up',
    completed_at: 'Waktu selesai', is_completed: 'Selesai', is_read: 'Sudah dibaca', read_at: 'Waktu dibaca',
    message: 'Pesan', sales_reply: 'Balasan Sales', role: 'Peran', is_active: 'Aktif', password: 'Password',
    bank_name: 'Bank', bank_account_number: 'Nomor rekening', bank_account_name: 'Pemilik rekening',
    lead_code: 'Kode lead', customer_code: 'Kode customer', registration_number: 'Nomor pendaftaran',
    file_name: 'Nama berkas', total_rows: 'Total baris', success_rows: 'Baris berhasil', failed_rows: 'Baris gagal',
};

function formatValue(value) {
    if (value === null || value === undefined || value === '') return '(kosong)';
    if (typeof value === 'boolean') return value ? 'Ya' : 'Tidak';
    return typeof value === 'object' ? JSON.stringify(value) : String(value);
}

async function loadActivities(page = 1) {
    loading.value = true;
    error.value = '';
    try {
        const params = { page: String(page), per_page: '20' };
        if (activityType.value) params.type = activityType.value;
        const res = await api.list('activities', params);
        activities.value = res.data || [];
        pagination.value = {
            current_page: res.current_page || page,
            last_page: res.last_page || 1,
            total: res.total || activities.value.length,
            from: res.from || 1,
            to: res.to || activities.value.length,
        };
    } catch (err) {
        error.value = err.message || 'Riwayat aktivitas gagal dimuat. Coba lagi.';
    } finally {
        loading.value = false;
    }
}

function handleFilter() {
    loadActivities(1);
}

function handleReset() {
    activityType.value = '';
    loadActivities(1);
}

function formatDateTime(dtStr) {
    if (!dtStr) return '-';
    const d = new Date(dtStr);
    if (isNaN(d.getTime())) return dtStr;
    return d.toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

onMounted(() => {
    loadActivities();
});
</script>

<template>
    <div class="space-y-4">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-xl font-semibold text-gray-900">Log Aktivitas & Riwayat Interaksi</h1>
                <p class="text-xs text-gray-500 mt-0.5">Riwayat penambahan, perubahan, penghapusan data CRM, dan interaksi prospek.</p>
            </div>
        </div>

        <!-- Filter Card -->
        <div class="bg-white border border-blue-100 p-3.5 shadow-sm">
            <form @submit.prevent="handleFilter" class="flex flex-wrap items-center gap-2.5">
                <select v-model="activityType" @change="handleFilter" class="w-full sm:w-auto text-xs border border-gray-200 px-3 py-2 text-gray-700 focus:outline-none focus:border-orange-500 bg-white">
                    <option value="">Semua Jenis Aktivitas</option>
                    <option v-for="(lbl, val) in typeLabels" :key="val" :value="val">{{ lbl }}</option>
                </select>
                <button type="submit" class="w-full sm:w-auto text-xs bg-orange-500 text-white font-medium py-2 px-3 rounded-lg hover:bg-orange-600 transition-colors">
                    Filter
                </button>
                <button v-if="activityType" @click="handleReset" type="button" class="text-xs text-gray-500 hover:text-gray-700 px-2 py-1">
                    Reset
                </button>
            </form>
        </div>

        <!-- Activities Table Card -->
        <div v-if="error" role="alert" class="p-3 bg-red-50 border border-red-200 text-red-700 text-xs">
            {{ error }}
            <button @click="loadActivities(pagination.current_page)" class="ml-2 font-semibold underline">Coba lagi</button>
        </div>
        <div class="bg-white border border-blue-100 overflow-hidden shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[700px] text-left text-xs text-gray-600">
                    <thead class="bg-gray-50/70 border-b border-gray-100 text-gray-500 font-medium uppercase tracking-wider text-[11px]">
                        <tr>
                            <th class="py-3 px-4">WAKTU</th>
                            <th class="py-3 px-4">TIPE</th>
                            <th class="py-3 px-4">DATA / PROSPEK</th>
                            <th class="py-3 px-4">SUBJEK / DETAIL</th>
                            <th class="py-3 px-4">PELAKU</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-if="loading">
                            <td colspan="5" class="p-8 text-center text-gray-400">Memuat log aktivitas...</td>
                        </tr>
                        <tr v-else-if="!error && activities.length === 0">
                            <td colspan="5" class="p-8 text-center text-gray-400">Belum ada riwayat aktivitas yang tercatat.</td>
                        </tr>
                        <tr v-for="act in activities" :key="act.id" class="hover:bg-blue-50/30 transition-colors">
                            <td class="py-3 px-4 whitespace-nowrap text-gray-500">
                                {{ formatDateTime(act.activity_at || act.created_at) }}
                            </td>
                            <td class="py-3 px-4 whitespace-nowrap">
                                <span class="inline-flex items-center whitespace-nowrap px-2 py-0.5 text-[10px] font-medium border bg-gray-50 border-gray-200">
                                    {{ typeLabels[act.type] || act.type }}
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                <div v-if="act.entity_name && act.entity_type !== 'leads'" class="font-medium text-gray-900 mb-1">{{ act.entity_name }}</div>
                                <button v-if="act.lead && !act.lead.deleted_at" @click="emit('navigate', `/leads/${act.lead.id}`)" class="font-semibold text-blue-600 hover:underline text-left">
                                    {{ act.lead.name }}
                                </button>
                                <span v-else-if="act.lead_name || act.lead" class="text-gray-600">{{ act.lead_name || act.lead.name }} (dihapus)</span>
                                <span v-else-if="!act.entity_name" class="text-gray-400">-</span>
                                <span v-else-if="act.entity_type === 'leads'">{{ act.entity_name }}</span>
                            </td>
                            <td class="py-3 px-4">
                                <div class="font-medium text-gray-900">{{ act.subject || act.notes || act.description || '-' }}</div>
                                <p v-if="act.subject && act.description" class="mt-1 text-gray-600 whitespace-pre-wrap break-words">{{ act.description }}</p>
                                <details v-if="act.changes && Object.keys(act.changes).length" class="mt-2">
                                    <summary class="cursor-pointer text-blue-700 font-medium">Lihat detail perubahan ({{ Object.keys(act.changes).length }})</summary>
                                    <dl class="mt-2 space-y-2 max-w-lg">
                                        <div v-for="(change, field) in act.changes" :key="field" class="border border-gray-200 rounded p-2 bg-gray-50 break-words">
                                            <dt class="font-semibold text-gray-800">{{ fieldLabels[field] || field.replaceAll('_', ' ') }}</dt>
                                            <dd v-if="act.type !== 'created'" class="mt-1 whitespace-pre-wrap"><span class="font-medium">Sebelum: </span>{{ formatValue(change.before) }}</dd>
                                            <dd v-if="act.type !== 'deleted'" class="mt-1 whitespace-pre-wrap"><span class="font-medium">Sesudah: </span>{{ formatValue(change.after) }}</dd>
                                        </div>
                                    </dl>
                                </details>
                            </td>
                            <td class="py-3 px-4">
                                {{ act.actor_name || act.user?.name || 'Sistem' }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div v-if="pagination.total > 0" class="p-3 border-t border-gray-100 flex items-center justify-between">
                <Pagination :pagination="pagination" @page="loadActivities" />
            </div>
        </div>
    </div>
</template>
