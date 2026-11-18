<script setup>
import { computed, onMounted, ref } from 'vue';
import { api } from '../../services/api';
import Badge from '../../Components/Badge.vue';

const props = defineProps({
    id: { type: [String, Number], required: true },
    user: { type: Object, default: () => ({}) },
});

const emit = defineEmits(['navigate']);

const lead = ref(null);
const salesUsers = ref([]);
const loading = ref(true);
const error = ref('');

const isAdmin = computed(() => props.user?.role === 'admin');
const isSales = computed(() => props.user?.role === 'sales');

// Modal Pengingat Admin -> Sales
const showReminderModal = ref(false);
const reminderForm = ref({
    sales_id: '',
    interval: '1_week',
    due_date: '',
    message: '',
});
const sendingReminder = ref(false);
const reminderSuccess = ref('');
const reminderError = ref('');

// Re-engage state
const reengaging = ref(false);

// Modal Penugasan Sales oleh Admin
const showAssignModal = ref(false);
const selectedSalesId = ref('');
const assigning = ref(false);
const assignError = ref('');

function openAssignModal() {
    selectedSalesId.value = lead.value?.assigned_to || (salesUsers.value[0]?.id || '');
    assignError.value = '';
    showAssignModal.value = true;
}

async function saveAssignment() {
    if (!lead.value || !selectedSalesId.value) return;
    assigning.value = true;
    assignError.value = '';
    try {
        await api.update('leads', lead.value.id, {
            assigned_to: selectedSalesId.value,
            assigned_at: new Date().toISOString(),
        });
        showAssignModal.value = false;
        await loadLead();
    } catch (err) {
        assignError.value = err.message || 'Gagal menugaskan lead ke sales.';
    } finally {
        assigning.value = false;
    }
}

async function loadSalesUsers() {
    try {
        const res = await api.list('users', { per_page: '100' });
        salesUsers.value = (res.data || []).filter(u => u.role === 'sales');
    } catch (e) {
        console.error(e);
    }
}

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

function calculateDueDate(interval) {
    const d = new Date();
    if (interval === '3_days') d.setDate(d.getDate() + 3);
    else if (interval === '1_week') d.setDate(d.getDate() + 7);
    else if (interval === '2_weeks') d.setDate(d.getDate() + 14);
    return d.toISOString().split('T')[0];
}

function openReminderModal() {
    const program = lead.value?.product?.name || lead.value?.target_program || 'Program Pelatihan';
    const phone = lead.value?.whatsapp || lead.value?.phone || '-';
    reminderForm.value = {
        sales_id: lead.value?.assigned_to || (salesUsers.value[0]?.id || ''),
        interval: '1_week',
        due_date: calculateDueDate('1_week'),
        message: `Mohon segera tindak lanjuti prospek "${lead.value?.name}" (WA: ${phone}) untuk penawaran program ${program}. Berikan detail materi dan jadwal pelatihan terdekat.`,
    };
    reminderSuccess.value = '';
    reminderError.value = '';
    showReminderModal.value = true;
}

function onIntervalChange() {
    if (reminderForm.value.interval !== 'custom') {
        reminderForm.value.due_date = calculateDueDate(reminderForm.value.interval);
    }
}

async function submitReminder() {
    sendingReminder.value = true;
    reminderError.value = '';
    reminderSuccess.value = '';
    try {
        const targetSalesId = lead.value?.assigned_to || reminderForm.value.sales_id;
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
        const res = await api.request(`/leads/${lead.value.id}/remind-sales`, {
            method: 'POST',
            body: JSON.stringify(payload),
        });
        reminderSuccess.value = res.message || 'Pengingat follow up berhasil dikirim ke Sales!';
        setTimeout(() => {
            showReminderModal.value = false;
            loadLead();
        }, 1200);
    } catch (err) {
        reminderError.value = err.message || 'Gagal mengirim pengingat.';
    } finally {
        sendingReminder.value = false;
    }
}

async function reengageLead() {
    if (!confirm(`Aktifkan kembali follow-up untuk prospek "${lead.value.name}"?`)) return;
    reengaging.value = true;
    try {
        await api.request(`/leads/${lead.value.id}/claim`, { method: 'POST' });
        await loadLead();
    } catch (err) {
        alert(err.message || 'Gagal mengaktifkan kembali follow-up.');
    } finally {
        reengaging.value = false;
    }
}

const waLink = computed(() => {
    if (!lead.value?.phone && !lead.value?.whatsapp) return '';
    let p = String(lead.value.whatsapp || lead.value.phone).replace(/\D/g, '');
    if (p.startsWith('0')) p = '62' + p.slice(1);
    const prod = lead.value.product;
    let msg = `Halo Kak ${lead.value.name}, saya dari Disty Akademi.`;
    if (prod) {
        const transferAmount = Math.round(Number(prod.price || 0) * 1.10);
        msg = `Halo Kak ${lead.value.name}, saya dari Disty Akademi terkait program pelatihan ${prod.name}. Untuk biaya pendaftaran resmi yang perlu ditransfer adalah sebesar Rp ${transferAmount.toLocaleString('id-ID')} ke rekening resmi Disty Akademi (BCA).`;
    } else {
        msg = `Halo Kak ${lead.value.name}, saya dari Disty Akademi ingin menanyakan informasi seputar program pelatihan yang diminati.`;
    }
    return `https://wa.me/${p}?text=${encodeURIComponent(msg)}`;
});

function formatDateTime(dtStr) {
    if (!dtStr) return '-';
    const d = new Date(dtStr);
    if (isNaN(d.getTime())) return dtStr;
    return d.toLocaleDateString('id-ID', {
        day: '2-digit',
        month: 'long',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

onMounted(() => {
    loadLead();
    loadSalesUsers();
});
</script>

<template>
    <div class="space-y-5">
        <div v-if="loading" class="text-center py-20 text-gray-400 text-xs">Memuat detail prospek...</div>
        <div v-else-if="error" class="p-4 bg-red-50 border border-red-200 text-red-700 text-xs">{{ error }}</div>
        <div v-else class="space-y-5">
            <!-- Alert jika status Lost -->
            <div v-if="lead.status === 'lost'" class="p-4 bg-rose-50 border border-rose-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-xs">
                <div>
                    <span class="font-bold text-rose-800 text-sm block">Prospek Berstatus Ditolak / Lost</span>
                    <p class="text-rose-700 mt-0.5">
                        Alasan: <strong>{{ lead.rejection_reason?.label || lead.rejection_reason?.name || 'Belum Ditentukan' }}</strong>
                        <span v-if="lead.rejection_notes"> &bull; Catatan: {{ lead.rejection_notes }}</span>
                    </p>
                    <p class="text-[11px] text-rose-600 mt-1">Sesuai kebijakan CRM, prospek ini dapat di-follow up ulang kembali ke pipeline aktif.</p>
                </div>
                <button @click="reengageLead" :disabled="reengaging"
                    class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs rounded-xl shadow-xs shrink-0 disabled:opacity-50 transition">
                    <span v-if="reengaging">Memproses...</span>
                    <span v-else>Follow-up Ulang Sekarang &rarr;</span>
                </button>
            </div>

            <!-- Header Card -->
            <div class="bg-white p-5 border border-gray-100 shadow-xs flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-mono font-bold text-orange-600 bg-orange-50 px-2 py-0.5">
                            {{ lead.lead_code || `#${lead.id}` }}
                        </span>
                        <Badge :status="lead.status" />
                    </div>
                    <h1 class="text-xl font-black text-gray-900 mt-2">{{ lead.name }}</h1>
                    <p class="text-xs text-gray-500 mt-0.5">{{ lead.institution || lead.company || 'Individu' }} &bull; {{ lead.phone || lead.whatsapp || '-' }}</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <button @click="emit('navigate', '/leads')" class="px-3.5 py-2 rounded-xl border border-gray-200 text-xs font-semibold text-gray-600 hover:bg-gray-50">
                        &larr; Kembali
                    </button>
                    <a v-if="isSales && waLink" :href="waLink" target="_blank" rel="noopener noreferrer"
                        class="px-3.5 py-2 rounded-xl bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200 text-xs font-semibold inline-flex items-center gap-1.5 transition">
                        WhatsApp Prospek
                    </a>
                    <button v-if="isAdmin && lead.status !== 'won'" @click="openReminderModal"
                        class="px-3.5 py-2 rounded-xl bg-orange-50 text-orange-700 hover:bg-orange-100 border border-orange-200 text-xs font-semibold transition">
                        Beri Pengingat Sales
                    </button>
                    <button @click="emit('navigate', `/leads/${lead.id}/edit`)" class="px-3.5 py-2 rounded-xl border border-gray-200 text-xs font-semibold text-gray-700 hover:bg-gray-50">
                        Edit
                    </button>
                    <button v-if="isSales && lead.status !== 'won'" @click="emit('navigate', `/registrations/create?lead_id=${lead.id}`)" class="px-3.5 py-2 rounded-xl bg-blue-50 text-blue-700 hover:bg-blue-100 border border-blue-200 text-xs font-bold transition">
                        + Daftarkan ke Pelatihan
                    </button>
                    <button v-else-if="lead.status === 'won'" @click="emit('navigate', '/customers')" class="px-3.5 py-2 rounded-xl bg-emerald-50 text-emerald-800 hover:bg-emerald-100 border border-emerald-200 text-xs font-bold transition">
                        Sudah Resmi Jadi Customer &rarr;
                    </button>
                    <button v-if="isSales" @click="emit('navigate', `/payments/create?lead_id=${lead.id}`)" class="px-4 py-2 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-bold text-xs shadow-xs">
                        + Input Bayar
                    </button>
                </div>
            </div>

            <!-- Detail Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
                <div class="bg-white p-5 border border-gray-100 shadow-xs space-y-4">
                    <h2 class="text-xs font-bold text-gray-400 uppercase tracking-wider">Informasi Kontak & PIC</h2>
                    <div class="space-y-3 text-xs">
                        <div>
                            <span class="text-gray-400 block text-[11px]">Nomor NIK (KTP)</span>
                            <span class="font-bold text-gray-900 font-mono">{{ lead.nik || '-' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400 block text-[11px]">Tanggal Lahir</span>
                            <span class="font-bold text-gray-900">
                                {{ lead.birth_date ? new Date(lead.birth_date).toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' }) : '-' }}
                            </span>
                        </div>
                        <div>
                            <span class="text-gray-400 block text-[11px]">Nomor WhatsApp</span>
                            <span class="font-bold text-gray-900">{{ lead.phone || lead.whatsapp || '-' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400 block text-[11px]">Email</span>
                            <span class="font-bold text-gray-900">{{ lead.email || '-' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400 block text-[11px]">Asal Instansi / Kampus</span>
                            <span class="font-bold text-gray-900">{{ lead.institution || lead.company || '-' }}</span>
                        </div>
                        <div class="col-span-full" v-if="lead.product">
                            <span class="text-gray-400 block text-[11px] mb-1">Program Diminati & Rincian Transaksi</span>
                            <div class="p-3.5 bg-orange-50/70 border border-orange-200/80 space-y-2 text-xs">
                                <div class="font-bold text-gray-900 text-sm">
                                    {{ lead.product.name }}
                                    <span class="text-orange-600 font-mono text-xs font-normal ml-1">({{ lead.product.code }})</span>
                                </div>
                                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2 text-[11px]">
                                    <div class="bg-white p-2 border border-orange-100 shadow-2xs">
                                        <span class="text-gray-400 block text-[10px]">Harga Resmi Katalog:</span>
                                        <span class="font-bold text-gray-800">Rp {{ Number(lead.product.price || 0).toLocaleString('id-ID') }}</span>
                                    </div>
                                    <div class="bg-white p-2 border border-emerald-200 shadow-2xs">
                                        <span class="text-emerald-700 block text-[10px] font-bold">Komisi Sales (10%):</span>
                                        <span class="font-black text-emerald-600">Rp {{ Number(Math.round((lead.product.price || 0) * 0.10)).toLocaleString('id-ID') }}</span>
                                    </div>
                                    <div class="bg-white p-2 border border-orange-200 shadow-2xs">
                                        <span class="text-orange-700 block text-[10px] font-bold">Customer Wajib Transfer:</span>
                                        <span class="font-black text-orange-600">Rp {{ Number(Math.round((lead.product.price || 0) * 1.10)).toLocaleString('id-ID') }}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div v-else>
                            <span class="text-gray-400 block text-[11px]">Program Diminati</span>
                            <span class="font-bold text-gray-900">{{ lead.target_program || 'Belum memilih program' }}</span>
                        </div>
                        <div>
                            <span class="text-gray-400 block text-[11px]">Sales PIC Eksklusif</span>
                            <div class="flex items-center justify-between gap-2 mt-0.5">
                                <span class="font-bold text-gray-900">{{ lead.sales?.name || lead.assigned_user?.name || 'Belum ditugaskan' }}</span>
                                <button v-if="isAdmin" @click="openAssignModal"
                                    class="px-2 py-0.5 text-[10px] font-semibold text-orange-700 bg-orange-50 hover:bg-orange-100 border border-orange-200 rounded transition">
                                    {{ lead.assigned_to ? 'Ubah PIC' : '+ Tugaskan Sales' }}
                                </button>
                            </div>
                        </div>
                        <div>
                            <span class="text-gray-400 block text-[11px]">Tanggal Masuk Lead</span>
                            <span class="font-bold text-gray-900">{{ formatDateTime(lead.created_at) }}</span>
                        </div>
                        <div v-if="lead.last_contacted_at">
                            <span class="text-gray-400 block text-[11px]">Terakhir Di-Follow Up</span>
                            <span class="font-semibold text-emerald-600">{{ formatDateTime(lead.last_contacted_at) }}</span>
                        </div>
                        <div v-else>
                            <span class="text-gray-400 block text-[11px]">Status Follow Up</span>
                            <span class="inline-block px-2 py-0.5 text-[11px] font-semibold bg-red-50 text-red-700">
                                Belum pernah di-follow up
                            </span>
                        </div>
                    </div>
                </div>

                <div class="lg:col-span-2 space-y-5">
                    <!-- Catatan Lead -->
                    <div class="bg-white p-5 border border-gray-100 shadow-xs space-y-3">
                        <h2 class="text-xs font-bold text-gray-400 uppercase tracking-wider">Catatan Lead</h2>
                        <p class="text-xs text-gray-700 bg-gray-50 p-3 border border-gray-100">
                            {{ lead.notes || 'Belum ada catatan khusus untuk prospek ini.' }}
                        </p>
                    </div>



                    <!-- Aktivitas Timeline -->
                    <div class="bg-white p-5 border border-gray-100 shadow-xs space-y-3">
                        <h2 class="text-xs font-bold text-gray-400 uppercase tracking-wider">Histori Aktivitas & Perubahan Status</h2>
                        <div v-if="lead.activities?.length" class="divide-y divide-gray-50 pt-1">
                            <div v-for="act in lead.activities" :key="act.id" class="py-2.5 flex items-start justify-between text-xs">
                                <div>
                                    <span class="font-bold text-gray-900 capitalize">{{ act.subject || act.type }}</span>
                                    <p class="text-gray-500 text-[11px] mt-0.5">{{ act.notes || act.description }}</p>
                                </div>
                                <span class="text-[10px] text-gray-400 shrink-0 ml-2">
                                    {{ act.created_at ? new Date(act.created_at).toLocaleString('id-ID') : '-' }}
                                </span>
                            </div>
                        </div>
                        <div v-else class="text-center py-4 text-xs text-gray-400">
                            Belum ada interaksi tercatat.
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Beri Pengingat Sales (Admin Only) -->
        <div v-if="showReminderModal" class="fixed inset-0 z-50 bg-black/40 flex items-center justify-center p-4">
            <div class="bg-white max-w-md w-full p-6 shadow-xl border border-gray-100 space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <h3 class="text-sm font-bold text-gray-900">Beri Pengingat Follow-Up ke Sales</h3>
                    <button @click="showReminderModal = false" class="text-gray-400 hover:text-gray-600 text-lg">&times;</button>
                </div>

                <div v-if="reminderSuccess" class="p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs">
                    {{ reminderSuccess }}
                </div>
                <div v-if="reminderError" class="p-3 bg-red-50 border border-red-200 text-red-600 text-xs">
                    {{ reminderError }}
                </div>

                <form @submit.prevent="submitReminder" class="space-y-3.5 text-xs">
                    <div v-if="lead.assigned_to" class="p-3 bg-gray-50 border border-gray-100 flex items-center justify-between text-xs">
                        <div>
                            <span class="text-gray-400 block text-[11px] font-medium">Sales Penanggung Jawab</span>
                            <span class="font-bold text-gray-900 text-sm">{{ lead.sales?.name || lead.assigned_user?.name || 'Sales PIC' }}</span>
                            <span class="text-gray-500 text-[11px] block">{{ lead.sales?.email || lead.assigned_user?.email }}</span>
                        </div>
                        <span class="px-2.5 py-1 text-[10px] font-bold bg-orange-100 text-orange-800 border border-orange-200">
                            Penerima Notifikasi
                        </span>
                    </div>
                    <div v-else>
                        <label class="block font-semibold text-gray-700 mb-1">Pilih Sales PIC</label>
                        <select v-model="reminderForm.sales_id" required
                            class="w-full px-3 py-2 border border-gray-200 bg-white">
                            <option value="" disabled>Pilih Sales</option>
                            <option v-for="s in salesUsers" :key="s.id" :value="s.id">{{ s.name }} ({{ s.email }})</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">Jarak Waktu Pengingat</label>
                        <select v-model="reminderForm.interval" @change="onIntervalChange"
                            class="w-full px-3 py-2 border border-gray-200 bg-white font-medium">
                            <option value="1_week">1 Minggu (Default Standar SOP)</option>
                            <option value="3_days">3 Hari (Cepat)</option>
                            <option value="2_weeks">2 Minggu (Panjang)</option>
                            <option value="custom">Kustom Tanggal</option>
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">Tenggat Waktu Follow-Up</label>
                        <input type="date" v-model="reminderForm.due_date" required
                            class="w-full px-3 py-2 border border-gray-200" />
                    </div>

                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">Pesan / Instruksi untuk Sales</label>
                        <textarea v-model="reminderForm.message" rows="3" required
                            class="w-full px-3 py-2 border border-gray-200"
                            placeholder="Tuliskan catatan arahan follow-up..."></textarea>
                    </div>

                    <div class="pt-2 flex justify-end gap-2 border-t border-gray-100">
                        <button type="button" @click="showReminderModal = false"
                            class="px-3.5 py-2 border border-gray-200 rounded-xl font-semibold text-gray-600 hover:bg-gray-50">
                            Batal
                        </button>
                        <button type="submit" :disabled="sendingReminder"
                            class="px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white font-bold rounded-xl disabled:opacity-50 transition">
                            <span v-if="sendingReminder">Mengirim...</span>
                            <span v-else>Kirim Pengingat</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Penugasan Sales PIC (Admin Only) -->
        <div v-if="showAssignModal" class="fixed inset-0 z-50 bg-black/40 flex items-center justify-center p-4">
            <div class="bg-white max-w-sm w-full p-5 shadow-xl border border-gray-100 space-y-4">
                <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                    <h3 class="text-sm font-bold text-gray-900">Tugaskan Prospek ke Sales PIC</h3>
                    <button @click="showAssignModal = false" class="text-gray-400 hover:text-gray-600 text-lg">&times;</button>
                </div>

                <div v-if="assignError" class="p-2.5 bg-red-50 text-red-600 text-xs border border-red-200">
                    {{ assignError }}
                </div>

                <div v-if="lead" class="p-3 bg-gray-50 border border-gray-100 text-xs space-y-1">
                    <span class="text-gray-400 block text-[11px]">Prospek</span>
                    <div class="font-bold text-gray-900">{{ lead.name }}</div>
                    <div class="text-gray-500 text-[11px]">{{ lead.company || lead.institution || 'Individu' }} &bull; {{ lead.phone || '-' }}</div>
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
