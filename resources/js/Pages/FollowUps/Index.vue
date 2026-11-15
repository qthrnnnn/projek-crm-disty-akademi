<script setup>
import { computed, onMounted, ref } from 'vue';
import { api } from '../../services/api';
import Pagination from '../../Components/Pagination.vue';
import Badge from '../../Components/Badge.vue';

const props = defineProps({
    user: { type: Object, default: () => ({}) },
});

const emit = defineEmits(['navigate']);

const activeTab = ref('schedules'); // 'schedules' | 'unfollowed'
const followups = ref([]);
const unfollowedLeads = ref([]);
const salesUsers = ref([]);
const rejectionReasons = ref([]);

const pagination = ref({ current_page: 1, last_page: 1, total: 0 });
const unfollowedPagination = ref({ current_page: 1, last_page: 1, total: 0 });

const statusFilter = ref('');
const assignedFilter = ref('');
const loading = ref(true);
const unfollowedLoading = ref(false);

const isAdmin = computed(() => props.user?.role === 'admin');
const isSales = computed(() => props.user?.role === 'sales');

// Counts
const pendingFollowUpCount = ref(0);
const unfollowedCount = ref(0);

// Modal Pengingat Admin -> Sales
const showReminderModal = ref(false);
const reminderLead = ref(null);
const reminderForm = ref({
    sales_id: '',
    interval: '1_week',
    due_date: '',
    message: '',
});
const sendingReminder = ref(false);
const reminderMessage = ref('');
const reminderError = ref('');

// Modal Catat Hasil Follow Up
const showCompleteModal = ref(false);
const selectedFollowUp = ref(null);
const completeForm = ref({
    feedback_type: 'interested',
    rejection_reason_id: null,
    lead_status: 'active',
    result_notes: '',
});
const completing = ref(false);
const completeError = ref('');

async function loadSalesUsers() {
    try {
        const res = await api.list('users', { per_page: '100' });
        salesUsers.value = (res.data || []).filter(u => u.role === 'sales');
    } catch (e) {
        console.error(e);
    }
}

async function loadRejectionReasons() {
    try {
        const res = await api.list('rejection-reasons', { per_page: '100' });
        rejectionReasons.value = res.data || [];
    } catch (e) {
        console.error(e);
    }
}

async function loadFollowUps(page = 1) {
    loading.value = true;
    try {
        const params = { page: String(page), per_page: '20' };
        if (statusFilter.value) params.status = statusFilter.value;
        if (assignedFilter.value) params.assigned = assignedFilter.value;

        const res = await api.list('follow-ups', params);
        followups.value = res.data || [];
        pagination.value = {
            current_page: res.current_page || page,
            last_page: res.last_page || 1,
            total: res.total || followups.value.length,
            from: res.from || 1,
            to: res.to || followups.value.length,
        };
        pendingFollowUpCount.value = followups.value.filter(f => f.status === 'pending').length;
    } catch (err) {
        console.error(err);
    } finally {
        loading.value = false;
    }
}

async function loadUnfollowedLeads(page = 1) {
    unfollowedLoading.value = true;
    try {
        const params = { page: String(page), per_page: '20', unfollowed: '1' };
        if (assignedFilter.value && isAdmin.value) params.assigned = assignedFilter.value;

        const res = await api.list('leads', params);
        unfollowedLeads.value = res.data || [];
        unfollowedPagination.value = {
            current_page: res.current_page || page,
            last_page: res.last_page || 1,
            total: res.total || unfollowedLeads.value.length,
            from: res.from || 1,
            to: res.to || unfollowedLeads.value.length,
        };
        unfollowedCount.value = unfollowedPagination.value.total;
    } catch (err) {
        console.error(err);
    } finally {
        unfollowedLoading.value = false;
    }
}

function switchTab(tab) {
    activeTab.value = tab;
    if (tab === 'schedules') {
        loadFollowUps(1);
    } else {
        loadUnfollowedLeads(1);
    }
}

function handleFilter() {
    if (activeTab.value === 'schedules') {
        loadFollowUps(1);
    } else {
        loadUnfollowedLeads(1);
    }
}

function resetFilter() {
    statusFilter.value = '';
    assignedFilter.value = '';
    handleFilter();
}

// Modal Pengingat
function calculateDueDate(interval) {
    const d = new Date();
    if (interval === '3_days') d.setDate(d.getDate() + 3);
    else if (interval === '1_week') d.setDate(d.getDate() + 7);
    else if (interval === '2_weeks') d.setDate(d.getDate() + 14);
    return d.toISOString().split('T')[0];
}

function openReminderModal(lead) {
    reminderLead.value = lead;
    const program = lead.product?.name || lead.target_program || 'Program Pelatihan';
    const phone = lead.whatsapp || lead.phone || '-';
    reminderForm.value = {
        sales_id: lead.assigned_to || (salesUsers.value[0]?.id || ''),
        interval: '1_week',
        due_date: calculateDueDate('1_week'),
        message: `Mohon segera tindak lanjuti prospek "${lead.name}" (WA: ${phone}) untuk penawaran program ${program}. Berikan detail materi dan jadwal pelatihan terdekat.`,
    };
    reminderMessage.value = '';
    reminderError.value = '';
    showReminderModal.value = true;
}

function onIntervalChange() {
    if (reminderForm.value.interval !== 'custom') {
        reminderForm.value.due_date = calculateDueDate(reminderForm.value.interval);
    }
}

async function sendReminder() {
    if (!reminderLead.value) return;
    sendingReminder.value = true;
    reminderError.value = '';
    reminderMessage.value = '';

    try {
        const targetSalesId = reminderLead.value.assigned_to || reminderForm.value.sales_id;
        if (!targetSalesId) {
            reminderError.value = 'Sales penanggung jawab belum ditentukan.';
            sendingReminder.value = false;
            return;
        }

        const payload = {
            sales_id: targetSalesId,
            message: reminderForm.value.message,
            due_date: reminderForm.value.due_date,
            priority: 'urgent',
        };

        const res = await api.request(`/leads/${reminderLead.value.id}/remind-sales`, {
            method: 'POST',
            body: JSON.stringify(payload),
        });

        reminderMessage.value = res.message || 'Pengingat follow-up berhasil dikirim ke Sales!';
        setTimeout(() => {
            showReminderModal.value = false;
            loadUnfollowedLeads(unfollowedPagination.value.current_page);
            loadFollowUps(1);
        }, 1200);
    } catch (err) {
        reminderError.value = err.message || 'Gagal mengirimkan pengingat.';
    } finally {
        sendingReminder.value = false;
    }
}

// Modal Penugasan Sales oleh Admin
const showAssignModal = ref(false);
const assigningLead = ref(null);
const selectedSalesId = ref('');
const assigning = ref(false);
const assignError = ref('');

function openAssignModal(lead) {
    assigningLead.value = lead;
    selectedSalesId.value = lead.assigned_to || (salesUsers.value[0]?.id || '');
    assignError.value = '';
    showAssignModal.value = true;
}

async function saveAssignment() {
    if (!assigningLead.value || !selectedSalesId.value) return;
    assigning.value = true;
    assignError.value = '';
    try {
        await api.update('leads', assigningLead.value.id, {
            assigned_to: selectedSalesId.value,
            assigned_at: new Date().toISOString(),
        });
        showAssignModal.value = false;
        loadUnfollowedLeads(unfollowedPagination.value.current_page);
        loadFollowUps(pagination.value.current_page);
    } catch (err) {
        assignError.value = err.message || 'Gagal menugaskan lead ke sales.';
    } finally {
        assigning.value = false;
    }
}

// Modal Selesaikan Follow Up & Catat Hasil (Khusus Sales)
function openCompleteModal(fu) {
    if (isAdmin.value) return;
    selectedFollowUp.value = fu;
    completeForm.value = {
        feedback_type: 'interested',
        rejection_reason_id: null,
        lead_status: 'active',
        result_notes: '',
    };
    completeError.value = '';
    showCompleteModal.value = true;
}

function selectFeedback(type) {
    completeForm.value.feedback_type = type;

    // Map feedback to database rejection reasons or status
    const reasonMap = {
        wrong_number: 'Nomor Salah',
        unreachable: 'Tidak Dapat Dihubungi',
        not_willing: 'Tidak Bersedia',
        not_interested: 'Tidak Tertarik',
    };

    if (reasonMap[type]) {
        completeForm.value.lead_status = 'lost';
        const match = rejectionReasons.value.find(r => r.name === type || r.label?.toLowerCase() === reasonMap[type].toLowerCase());
        completeForm.value.rejection_reason_id = match ? match.id : null;
        if (!completeForm.value.result_notes || Object.values(reasonMap).some(lbl => completeForm.value.result_notes.includes(lbl))) {
            completeForm.value.result_notes = `Respon prospek: ${reasonMap[type]}. Calon peserta dapat di-follow up ulang di masa mendatang.`;
        }
    } else if (type === 'interested') {
        completeForm.value.lead_status = 'active';
        completeForm.value.rejection_reason_id = null;
        if (!completeForm.value.result_notes || Object.values(reasonMap).some(lbl => completeForm.value.result_notes.includes(lbl))) {
            completeForm.value.result_notes = 'Prospek berminat dan meminta informasi kurikulum/jadwal lebih lanjut.';
        }
    } else if (type === 'won') {
        completeForm.value.lead_status = 'won';
        completeForm.value.rejection_reason_id = null;
        if (!completeForm.value.result_notes || Object.values(reasonMap).some(lbl => completeForm.value.result_notes.includes(lbl))) {
            completeForm.value.result_notes = 'Closing berhasil! Calon peserta bersedia mendaftar.';
        }
    }
}

async function submitComplete() {
    if (!selectedFollowUp.value) return;
    completing.value = true;
    completeError.value = '';

    try {
        const payload = {
            result_notes: completeForm.value.result_notes,
            lead_status: completeForm.value.lead_status,
            rejection_reason_id: completeForm.value.rejection_reason_id,
            feedback_type: completeForm.value.feedback_type,
        };

        await api.request(`/follow-ups/${selectedFollowUp.value.id}/complete`, {
            method: 'PATCH',
            body: payload,
        });

        showCompleteModal.value = false;
        loadFollowUps(pagination.value.current_page);
    } catch (err) {
        completeError.value = err.message || 'Gagal menandai follow-up selesai.';
    } finally {
        completing.value = false;
    }
}

// Follow-up ulang lead yang lost
async function reengageLead(lead) {
    if (!confirm(`Aktifkan kembali follow-up untuk prospek "${lead.name}"?`)) return;
    try {
        await api.request(`/leads/${lead.id}/claim`, { method: 'POST' });
        loadFollowUps(1);
        loadUnfollowedLeads(1);
    } catch (err) {
        alert(err.message || 'Gagal mengaktifkan kembali follow-up.');
    }
}

function getWhatsAppUrl(lead) {
    const raw = lead.whatsapp || lead.phone;
    if (!raw) return null;
    let digits = raw.replace(/[^0-9]/g, '');
    if (digits.startsWith('0')) digits = '62' + digits.substring(1);
    let text = `Halo ${lead.name}, salam hangat dari Disty Akademi.`;
    if (lead.product?.price) {
        const transferAmount = Math.round(Number(lead.product.price) * 1.10);
        text = `Halo ${lead.name}, salam hangat dari Disty Akademi. Terkait pendaftaran program ${lead.product.name}, biaya investasi resmi yang perlu ditransfer adalah sebesar Rp ${transferAmount.toLocaleString('id-ID')}. Apakah ada hal yang perlu kami bantu?`;
    } else {
        text = `Halo ${lead.name}, salam hangat dari Disty Akademi. Kami ingin menanyakan kelanjutan informasi pelatihan ${lead.product?.name || 'Disty Akademi'}. Apakah ada yang bisa kami bantu?`;
    }
    return `https://wa.me/${digits}?text=${encodeURIComponent(text)}`;
}

function formatUnfollowedStatus(lead) {
    if (!lead.first_contacted_at) {
        return 'Belum pernah dihubungi';
    }
    const last = new Date(lead.last_contacted_at || lead.first_contacted_at);
    const now = new Date();
    const diffDays = Math.floor((now - last) / (1000 * 60 * 60 * 24));
    if (diffDays >= 7) {
        return `Terakhir kontak ${diffDays} hari lalu (> 1 minggu)`;
    }
    return `Terakhir kontak ${diffDays} hari lalu`;
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

function formatDateOnly(dtStr) {
    if (!dtStr) return '-';
    const d = new Date(dtStr);
    if (isNaN(d.getTime())) return dtStr;
    return d.toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    });
}

onMounted(() => {
    loadSalesUsers();
    loadRejectionReasons();
    loadFollowUps();
    loadUnfollowedLeads();
});
</script>

<template>
    <div class="space-y-4">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Manajemen Follow-up & Respon Prospek</h1>
                <p class="text-xs text-gray-500 mt-0.5">Pantau interaksi sales, instruksi pengingat admin, dan pencatatan feedback calon peserta</p>
            </div>
            <div v-if="isSales" class="flex items-center gap-2">
                <button @click="emit('navigate', '/follow-ups/create')"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold text-white bg-orange-500 rounded-xl hover:bg-orange-600 transition-colors shadow-2xs">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                    Tambah Jadwal
                </button>
            </div>
        </div>

        <!-- Navigation Tabs -->
        <div class="flex items-center gap-2 border-b border-gray-200 pb-1 text-xs">
            <button @click="switchTab('schedules')"
                class="px-4 py-2 font-medium rounded-lg transition-colors flex items-center gap-2"
                :class="activeTab === 'schedules' ? 'bg-orange-500 text-white shadow-xs' : 'text-gray-600 hover:bg-gray-100'">
                <span>Jadwal Follow-up</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px]"
                    :class="activeTab === 'schedules' ? 'bg-white/25 text-white' : 'bg-gray-200 text-gray-700'">
                    {{ pagination.total }}
                </span>
            </button>

            <button @click="switchTab('unfollowed')"
                class="px-4 py-2 font-medium rounded-lg transition-colors flex items-center gap-2"
                :class="activeTab === 'unfollowed' ? 'bg-orange-500 text-white shadow-xs' : 'text-gray-600 hover:bg-gray-100'">
                <span>Monitoring Belum Di-Followup</span>
                <span class="px-1.5 py-0.2 rounded-full text-[10px]"
                    :class="activeTab === 'unfollowed' ? 'bg-white/25 text-white' : 'bg-amber-100 text-amber-800'">
                    {{ unfollowedCount }}
                </span>
            </button>
        </div>

        <!-- Filter Bar -->
        <div class="bg-white border border-gray-200 p-3 shadow-xs">
            <div class="flex flex-wrap items-center gap-2.5">
                <template v-if="activeTab === 'schedules'">
                    <select v-model="statusFilter" @change="handleFilter" class="text-xs border border-gray-200 px-3 py-2 text-gray-700 focus:outline-none focus:border-orange-500 bg-white">
                        <option value="">Semua Status Jadwal</option>
                        <option value="pending">Pending (Menunggu)</option>
                        <option value="completed">Selesai (Sudah Kontak)</option>
                        <option value="cancelled">Dibatalkan</option>
                    </select>
                </template>

                <select v-if="isAdmin" v-model="assignedFilter" @change="handleFilter" class="text-xs border border-gray-200 px-3 py-2 text-gray-700 focus:outline-none focus:border-orange-500 bg-white">
                    <option value="">Semua Sales PIC</option>
                    <option v-for="s in salesUsers" :key="s.id" :value="s.id">{{ s.name }}</option>
                </select>

                <button v-if="statusFilter || assignedFilter" @click="resetFilter" type="button" class="text-xs text-gray-500 hover:text-red-600 underline px-2 py-1">
                    Reset Filter
                </button>
            </div>
        </div>

        <!-- TAB 1: Tabel Jadwal Follow Up -->
        <div v-if="activeTab === 'schedules'" class="bg-white border border-gray-200 overflow-hidden shadow-xs">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[700px] text-left text-xs text-gray-600">
                    <thead class="bg-gray-50/70 border-b border-gray-100 text-gray-500 font-semibold uppercase tracking-wider text-[10.5px]">
                        <tr>
                            <th class="py-3.5 px-4">Agenda & Prospek</th>
                            <th class="py-3.5 px-4">Waktu Terjadwal</th>
                            <th class="py-3.5 px-4">Metode</th>
                            <th class="py-3.5 px-4">Sales PIC</th>
                            <th class="py-3.5 px-4">Status & Hasil</th>
                            <th class="py-3.5 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-if="loading">
                            <td colspan="6" class="p-8 text-center text-gray-400">Memuat data follow-up...</td>
                        </tr>
                        <tr v-else-if="followups.length === 0">
                            <td colspan="6" class="p-8 text-center text-gray-400">Belum ada agenda follow-up.</td>
                        </tr>
                        <tr v-for="fu in followups" :key="fu.id" class="hover:bg-orange-50/20 transition-colors">
                            <td class="py-3.5 px-4">
                                <div class="font-semibold text-gray-900">{{ fu.title || '-' }}</div>
                                <div v-if="fu.lead" class="text-[11px] text-gray-500 mt-0.5">
                                    <button @click="emit('navigate', `/leads/${fu.lead.id}`)" class="text-blue-600 hover:underline font-medium">
                                        {{ fu.lead.name }}
                                    </button>
                                    <span class="text-gray-400"> ({{ fu.lead.phone || '-' }})</span>
                                </div>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="font-medium text-gray-800">{{ formatDateTime(fu.follow_up_at) }}</span>
                            </td>
                            <td class="py-3.5 px-4 uppercase text-[10.5px] font-semibold text-gray-500">
                                {{ fu.method || 'WHATSAPP' }}
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="font-medium text-gray-700">{{ fu.assigned_user?.name || fu.assignedUser?.name || '-' }}</span>
                            </td>
                            <td class="py-3.5 px-4">
                                <span class="px-2 py-0.5 text-[10px] font-medium border"
                                    :class="fu.status === 'completed' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : (fu.status === 'cancelled' ? 'bg-gray-100 text-gray-500 border-gray-200' : 'bg-amber-50 text-amber-700 border-amber-200')">
                                    {{ fu.status === 'completed' ? 'Selesai' : (fu.status === 'cancelled' ? 'Dibatalkan' : 'Pending') }}
                                </span>
                                <div v-if="fu.result_notes" class="text-[11px] text-gray-500 mt-1 max-w-xs truncate" :title="fu.result_notes">
                                    {{ fu.result_notes }}
                                </div>
                            </td>
                            <td class="py-3.5 px-4 text-right">
                                <div v-if="isSales" class="flex items-center justify-end gap-2">
                                    <button v-if="fu.status === 'pending'" @click="openCompleteModal(fu)"
                                        class="px-2.5 py-1 text-[11px] font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-md transition-colors"
                                        title="Catat respon customer dan selesaikan follow up">
                                        Selesaikan
                                    </button>
                                    <button @click="emit('navigate', `/follow-ups/${fu.id}/edit`)"
                                        class="text-xs text-blue-600 hover:underline">
                                        Edit
                                    </button>
                                </div>
                                <div v-else class="text-right">
                                    <button v-if="fu.lead" @click="emit('navigate', `/leads/${fu.lead.id}`)"
                                        class="text-xs text-orange-600 hover:underline font-medium">
                                        Lihat Prospek
                                    </button>
                                    <span v-else class="text-gray-400 text-xs">-</span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div v-if="pagination.total > 0" class="p-3 border-t border-gray-100 flex items-center justify-between">
                <Pagination :pagination="pagination" @page="loadFollowUps" />
            </div>
        </div>

        <!-- TAB 2: Monitoring Prospek Belum Di-Followup -->
        <div v-else-if="activeTab === 'unfollowed'" class="space-y-3">
            <div class="p-3 bg-amber-50 border border-amber-200 text-amber-900 text-xs flex items-center justify-between">
                <div>
                    <strong>Pusat Monitoring Follow-Up:</strong> Daftar calon peserta yang belum pernah dikontak atau belum di-follow up lagi selama lebih dari 1 minggu.
                </div>
                <div v-if="isAdmin" class="text-[11px] text-amber-700 font-medium">
                    Admin dapat mengirim instruksi pengingat langsung ke Sales PIC
                </div>
            </div>

            <div class="bg-white border border-gray-200 overflow-hidden shadow-xs">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[750px] text-left text-xs text-gray-600">
                        <thead class="bg-gray-50/70 border-b border-gray-100 text-gray-500 font-semibold uppercase tracking-wider text-[10.5px]">
                            <tr>
                                <th class="py-3.5 px-4">Calon Peserta</th>
                                <th class="py-3.5 px-4">Kontak WhatsApp</th>
                                <th class="py-3.5 px-4">Program & Instansi</th>
                                <th class="py-3.5 px-4">Sales PIC</th>
                                <th class="py-3.5 px-4">Status Keterlambatan</th>
                                <th class="py-3.5 px-4 text-right">Aksi Tindak Lanjut</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
                            <tr v-if="unfollowedLoading">
                                <td colspan="6" class="p-8 text-center text-gray-400">Memeriksa status follow-up prospek...</td>
                            </tr>
                            <tr v-else-if="unfollowedLeads.length === 0">
                                <td colspan="6" class="p-8 text-center text-gray-400">Semua prospek telah di-follow up dengan baik.</td>
                            </tr>
                            <tr v-for="lead in unfollowedLeads" :key="lead.id" class="hover:bg-amber-50/20 transition-colors">
                                <td class="py-3.5 px-4">
                                    <button @click="emit('navigate', `/leads/${lead.id}`)" class="font-semibold text-gray-900 hover:text-orange-600 text-left">
                                        {{ lead.name }}
                                    </button>
                                    <div class="text-[11px] text-gray-400 font-mono mt-0.5">{{ lead.lead_code }}</div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="flex items-center gap-1.5">
                                        <span class="font-medium text-gray-800">{{ lead.whatsapp || lead.phone || '-' }}</span>
                                        <a v-if="isSales && getWhatsAppUrl(lead)" :href="getWhatsAppUrl(lead)" target="_blank"
                                            class="px-1.5 py-0.5 rounded text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200 hover:bg-emerald-100 transition-colors"
                                            title="Chat WhatsApp Sekarang">
                                            Chat WA
                                        </a>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <div class="font-medium text-gray-900">{{ lead.product?.name || 'Program Pelatihan' }}</div>
                                    <div class="text-[11px] text-gray-400">{{ lead.company || lead.institution || '-' }}</div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span v-if="lead.assigned_user?.name" class="font-medium text-gray-800">
                                        {{ lead.assigned_user.name }}
                                    </span>
                                    <div v-else class="flex items-center gap-1.5">
                                        <span class="text-amber-600 italic">Belum ada PIC</span>
                                        <button v-if="isAdmin" @click="openAssignModal(lead)"
                                            class="text-[10px] text-orange-600 hover:underline font-bold"
                                            title="Tugaskan lead ini ke sales">
                                            (Tugaskan)
                                        </button>
                                    </div>
                                </td>
                                <td class="py-3.5 px-4">
                                    <span class="inline-flex items-center px-2 py-0.5 text-[10px] font-medium border"
                                        :class="!lead.first_contacted_at ? 'bg-red-50 text-red-700 border-red-200' : 'bg-amber-50 text-amber-700 border-amber-200'">
                                        {{ formatUnfollowedStatus(lead) }}
                                    </span>
                                    <div class="text-[10px] text-gray-400 mt-0.5">Tgl Masuk: {{ formatDateOnly(lead.created_at) }}</div>
                                </td>
                                <td class="py-3.5 px-4 text-right">
                                    <div class="flex items-center justify-end gap-1.5">
                                        <!-- Aksi Khusus Admin: Tugaskan ke Sales jika belum ada PIC -->
                                        <button v-if="isAdmin && !lead.assigned_to" @click="openAssignModal(lead)"
                                            class="px-3 py-1 text-xs font-semibold text-white bg-orange-500 hover:bg-orange-600 rounded-md transition-colors"
                                            title="Tugaskan prospek ini ke salah satu sales">
                                            Tugaskan ke Sales
                                        </button>

                                        <!-- Aksi Khusus Admin: Kirim Pengingat ke Sales (Jarak 1 Minggu) jika sudah ada PIC -->
                                        <button v-else-if="isAdmin && lead.assigned_to" @click="openReminderModal(lead)"
                                            class="px-3 py-1 text-xs font-semibold text-white bg-amber-600 hover:bg-amber-700 rounded-md transition-colors"
                                            title="Kirim instruksi pengingat ke sales penanggung jawab">
                                            Beri Pengingat Sales
                                        </button>

                                        <!-- Aksi Khusus Sales: Jadwalkan Follow-up -->
                                        <button v-else-if="isSales" @click="emit('navigate', `/follow-ups/create?lead_id=${lead.id}`)"
                                            class="px-3 py-1 text-xs font-semibold text-white bg-orange-500 hover:bg-orange-600 rounded-md transition-colors">
                                            Jadwalkan Follow-up
                                        </button>

                                        <!-- Jika lead lost: opsi follow-up ulang -->
                                        <button v-if="lead.status === 'lost'" @click="reengageLead(lead)"
                                            class="px-2.5 py-1 text-xs font-semibold text-emerald-700 bg-emerald-50 border border-emerald-200 hover:bg-emerald-100 rounded-md transition-colors"
                                            title="Tindak lanjuti kembali prospek yang ditolak">
                                            Follow-up Ulang
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-if="unfollowedPagination.total > 0" class="p-3 border-t border-gray-100 flex items-center justify-between">
                    <Pagination :pagination="unfollowedPagination" @page="loadUnfollowedLeads" />
                </div>
            </div>
        </div>

        <!-- MODAL 1: Beri Pengingat ke Sales (Khusus Admin, Jarak 1 Minggu) -->
        <div v-if="showReminderModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-xs p-4">
            <div class="bg-white max-w-lg w-full p-5 shadow-2xl space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <div>
                        <h2 class="text-base font-bold text-gray-900">Beri Pengingat Follow-up ke Sales</h2>
                        <p class="text-xs text-gray-500 mt-0.5">Kirim notifikasi & instruksi tindak lanjut calon peserta</p>
                    </div>
                    <button @click="showReminderModal = false" class="text-gray-400 hover:text-gray-600 text-lg">&times;</button>
                </div>

                <div v-if="reminderError" class="p-2.5 bg-red-50 text-red-600 text-xs border border-red-200">
                    {{ reminderError }}
                </div>
                <div v-if="reminderMessage" class="p-2.5 bg-emerald-50 text-emerald-700 text-xs border border-emerald-200 font-semibold">
                    {{ reminderMessage }}
                </div>

                <form @submit.prevent="sendReminder" class="space-y-3.5 text-xs">
                    <div class="p-2.5 bg-gray-50 border border-gray-100">
                        <span class="text-gray-400 block text-[11px]">Calon Peserta</span>
                        <strong class="text-gray-900">{{ reminderLead?.name }}</strong> ({{ reminderLead?.phone || '-' }})
                    </div>

                    <div v-if="reminderLead?.assigned_to" class="p-3 bg-gray-50 border border-gray-100 flex items-center justify-between text-xs">
                        <div>
                            <span class="text-gray-400 block text-[11px] font-medium">Sales Penanggung Jawab</span>
                            <span class="font-bold text-gray-900 text-sm">{{ reminderLead.assigned_user?.name || 'Sales PIC' }}</span>
                            <span class="text-gray-500 text-[11px] block">{{ reminderLead.assigned_user?.email }}</span>
                        </div>
                        <span class="px-2.5 py-1 text-[10px] font-bold bg-orange-100 text-orange-800 border border-orange-200">
                            Penerima Notifikasi
                        </span>
                    </div>
                    <div v-else>
                        <label class="block font-semibold text-gray-700 mb-1">Sales Penanggung Jawab *</label>
                        <select v-model="reminderForm.sales_id" required class="w-full border border-gray-200 px-3 py-2 bg-white focus:outline-none focus:border-orange-500">
                            <option value="">Pilih Sales PIC</option>
                            <option v-for="s in salesUsers" :key="s.id" :value="s.id">{{ s.name }} ({{ s.email }})</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">Jarak Waktu / Tenggat Pengingat</label>
                        <div class="grid grid-cols-3 gap-2 mb-2">
                            <button type="button" @click="reminderForm.interval = '1_week'; onIntervalChange()"
                                class="py-1.5 px-2 rounded-lg border font-medium text-center transition-colors"
                                :class="reminderForm.interval === '1_week' ? 'bg-orange-50 border-orange-500 text-orange-700 font-bold' : 'border-gray-200 text-gray-600 hover:bg-gray-50'">
                                1 Minggu (Rekomendasi)
                            </button>
                            <button type="button" @click="reminderForm.interval = '3_days'; onIntervalChange()"
                                class="py-1.5 px-2 rounded-lg border font-medium text-center transition-colors"
                                :class="reminderForm.interval === '3_days' ? 'bg-orange-50 border-orange-500 text-orange-700 font-bold' : 'border-gray-200 text-gray-600 hover:bg-gray-50'">
                                3 Hari
                            </button>
                            <button type="button" @click="reminderForm.interval = 'custom'"
                                class="py-1.5 px-2 rounded-lg border font-medium text-center transition-colors"
                                :class="reminderForm.interval === 'custom' ? 'bg-orange-50 border-orange-500 text-orange-700 font-bold' : 'border-gray-200 text-gray-600 hover:bg-gray-50'">
                                Tanggal Kustom
                            </button>
                        </div>
                        <input v-model="reminderForm.due_date" type="date" required
                            class="w-full border border-gray-200 px-3 py-2 bg-white focus:outline-none focus:border-orange-500" />
                        <span class="text-[10.5px] text-gray-400 mt-1 block">Tenggat tindak lanjut otomatis dijadwalkan ke kalender follow-up sales.</span>
                    </div>

                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">Pesan Instruksi Pengingat</label>
                        <textarea v-model="reminderForm.message" rows="3" required
                            class="w-full border border-gray-200 px-3 py-2 focus:outline-none focus:border-orange-500"
                            placeholder="Tulis pesan atau arahan kepada sales..."></textarea>
                    </div>

                    <div class="pt-2 flex justify-end gap-2 border-t border-gray-100">
                        <button type="button" @click="showReminderModal = false" class="px-4 py-2 border border-gray-200 rounded-lg text-gray-600 hover:bg-gray-50">Batal</button>
                        <button type="submit" :disabled="sendingReminder"
                            class="px-5 py-2 bg-amber-600 hover:bg-amber-700 text-white font-semibold rounded-lg shadow-sm disabled:opacity-50">
                            <span v-if="sendingReminder">Mengirimkan...</span>
                            <span v-else>Kirim Notifikasi Pengingat</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL 2: Selesaikan Follow-up & Catat Feedback (Khusus Sales) -->
        <div v-if="showCompleteModal && isSales" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-xs p-4">
            <div class="bg-white max-w-lg w-full p-5 shadow-2xl space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <div>
                        <h2 class="text-base font-bold text-gray-900">Catat Hasil & Feedback Follow-up</h2>
                        <p class="text-xs text-gray-500 mt-0.5">Perbarui status prospek dan simpan hasil komunikasi</p>
                    </div>
                    <button @click="showCompleteModal = false" class="text-gray-400 hover:text-gray-600 text-lg">&times;</button>
                </div>

                <div v-if="completeError" class="p-2.5 bg-red-50 text-red-600 text-xs border border-red-200">
                    {{ completeError }}
                </div>

                <form @submit.prevent="submitComplete" class="space-y-3.5 text-xs">
                    <div class="p-2.5 bg-gray-50 border border-gray-100">
                        <span class="text-gray-400 block text-[11px]">Prospek</span>
                        <strong class="text-gray-900">{{ selectedFollowUp?.lead?.name }}</strong>
                        <span class="text-gray-500"> ({{ selectedFollowUp?.lead?.phone || '-' }})</span>
                    </div>

                    <div>
                        <label class="block font-semibold text-gray-700 mb-1.5">Pilihan Feedback Hasil Komunikasi *</label>
                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-2">
                            <button type="button" @click="selectFeedback('interested')"
                                class="p-2 rounded-lg border text-left transition-colors"
                                :class="completeForm.feedback_type === 'interested' ? 'bg-blue-50 border-blue-500 text-blue-800 font-bold' : 'border-gray-200 hover:bg-gray-50'">
                                Berminat / Lanjut
                            </button>
                            <button type="button" @click="selectFeedback('wrong_number')"
                                class="p-2 rounded-lg border text-left transition-colors"
                                :class="completeForm.feedback_type === 'wrong_number' ? 'bg-red-50 border-red-500 text-red-800 font-bold' : 'border-gray-200 hover:bg-gray-50'">
                                Nomor Salah
                            </button>
                            <button type="button" @click="selectFeedback('unreachable')"
                                class="p-2 rounded-lg border text-left transition-colors"
                                :class="completeForm.feedback_type === 'unreachable' ? 'bg-red-50 border-red-500 text-red-800 font-bold' : 'border-gray-200 hover:bg-gray-50'">
                                Tidak Dapat Dihubungi
                            </button>
                            <button type="button" @click="selectFeedback('not_willing')"
                                class="p-2 rounded-lg border text-left transition-colors"
                                :class="completeForm.feedback_type === 'not_willing' ? 'bg-red-50 border-red-500 text-red-800 font-bold' : 'border-gray-200 hover:bg-gray-50'">
                                Tidak Bersedia
                            </button>
                            <button type="button" @click="selectFeedback('not_interested')"
                                class="p-2 rounded-lg border text-left transition-colors"
                                :class="completeForm.feedback_type === 'not_interested' ? 'bg-red-50 border-red-500 text-red-800 font-bold' : 'border-gray-200 hover:bg-gray-50'">
                                Tidak Tertarik
                            </button>
                            <button type="button" @click="selectFeedback('won')"
                                class="p-2 rounded-lg border text-left transition-colors"
                                :class="completeForm.feedback_type === 'won' ? 'bg-emerald-50 border-emerald-500 text-emerald-800 font-bold' : 'border-gray-200 hover:bg-gray-50'">
                                Closing / Siap Bayar
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">Catatan Respon Lengkap</label>
                        <textarea v-model="completeForm.result_notes" rows="3" required
                            class="w-full border border-gray-200 px-3 py-2 focus:outline-none focus:border-orange-500"
                            placeholder="Ketik rincian hasil follow-up, alasan penolakan, atau waktu janji temu berikutnya..."></textarea>
                    </div>

                    <div class="pt-2 flex justify-end gap-2 border-t border-gray-100">
                        <button type="button" @click="showCompleteModal = false" class="px-4 py-2 border border-gray-200 rounded-lg text-gray-600 hover:bg-gray-50">Batal</button>
                        <button type="submit" :disabled="completing"
                            class="px-5 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-semibold rounded-lg shadow-sm disabled:opacity-50">
                            <span v-if="completing">Menyimpan...</span>
                            <span v-else>Simpan & Selesaikan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- MODAL 3: Penugasan Sales PIC oleh Admin -->
        <div v-if="showAssignModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 backdrop-blur-xs p-4">
            <div class="bg-white max-w-sm w-full p-5 shadow-2xl space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <h3 class="text-sm font-bold text-gray-900">Tugaskan Prospek ke Sales PIC</h3>
                    <button @click="showAssignModal = false" class="text-gray-400 hover:text-gray-600 text-lg">&times;</button>
                </div>

                <div v-if="assignError" class="p-2.5 bg-red-50 text-red-600 text-xs border border-red-200">
                    {{ assignError }}
                </div>

                <div v-if="assigningLead" class="p-3 bg-gray-50 border border-gray-100 text-xs space-y-1">
                    <span class="text-gray-400 block text-[11px]">Prospek</span>
                    <div class="font-bold text-gray-900">{{ assigningLead.name }}</div>
                    <div class="text-gray-500 text-[11px]">{{ assigningLead.company || assigningLead.institution || 'Individu' }} &bull; {{ assigningLead.phone || '-' }}</div>
                </div>

                <form @submit.prevent="saveAssignment" class="space-y-3.5 text-xs">
                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">Pilih Sales Penanggung Jawab</label>
                        <select v-model="selectedSalesId" required class="w-full px-3 py-2 border border-gray-200 bg-white">
                            <option value="" disabled>Pilih Sales</option>
                            <option v-for="s in salesUsers" :key="s.id" :value="s.id">{{ s.name }} ({{ s.email }})</option>
                        </select>
                    </div>

                    <div class="pt-2 flex justify-end gap-2 border-t border-gray-100">
                        <button type="button" @click="showAssignModal = false"
                            class="px-3.5 py-2 border border-gray-200 rounded-xl font-semibold text-gray-600 hover:bg-gray-50">
                            Batal
                        </button>
                        <button type="submit" :disabled="assigning"
                            class="px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white font-bold rounded-xl disabled:opacity-50 transition">
                            <span v-if="assigning">Menugaskan...</span>
                            <span v-else>Simpan Penugasan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>
