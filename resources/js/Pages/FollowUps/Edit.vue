<script setup>
import { computed, onMounted, ref } from 'vue';
import { api } from '../../services/api';

const props = defineProps({
    id: { type: [String, Number], required: true },
});

const emit = defineEmits(['navigate']);

const followUp = ref(null);
const rejectionReasons = ref([]);
const loading = ref(true);
const saving = ref(false);
const error = ref('');

const form = ref({
    status: 'completed',
    feedback_type: '',
    rejection_reason_id: null,
    lead_status: 'active',
    result_notes: '',
});

const reasonMap = {
    wrong_number: 'Nomor Salah',
    unreachable: 'Tidak Dapat Dihubungi',
    not_willing: 'Tidak Bersedia',
    not_interested: 'Tidak Tertarik',
};

async function loadData() {
    loading.value = true;
    try {
        const [fuRes, rrRes] = await Promise.all([
            api.get('follow-ups', props.id),
            api.list('rejection-reasons', { per_page: '100' }),
        ]);

        const f = fuRes.data || fuRes;
        followUp.value = f;
        rejectionReasons.value = rrRes.data || [];

        form.value.status = f.status || 'pending';
        form.value.result_notes = f.result_notes || '';

        // Deteksi feedback awal dari lead jika ada
        if (f.lead?.rejection_reason_id) {
            form.value.rejection_reason_id = f.lead.rejection_reason_id;
            const matched = rejectionReasons.value.find(r => r.id === f.lead.rejection_reason_id);
            if (matched && matched.name) {
                form.value.feedback_type = matched.name;
            }
        }
    } catch (err) {
        error.value = err.message || 'Gagal memuat data follow-up.';
    } finally {
        loading.value = false;
    }
}

function selectFeedback(type) {
    form.value.feedback_type = type;
    form.value.status = 'completed';

    if (reasonMap[type]) {
        form.value.lead_status = 'lost';
        const match = rejectionReasons.value.find(
            r => r.name === type || r.label?.toLowerCase() === reasonMap[type].toLowerCase()
        );
        form.value.rejection_reason_id = match ? match.id : null;
        if (!form.value.result_notes || Object.values(reasonMap).some(lbl => form.value.result_notes.includes(lbl))) {
            form.value.result_notes = `Respon prospek: ${reasonMap[type]}. Dapat di-follow up ulang di masa depan.`;
        }
    } else if (type === 'interested') {
        form.value.lead_status = 'active';
        form.value.rejection_reason_id = null;
        if (!form.value.result_notes || Object.values(reasonMap).some(lbl => form.value.result_notes.includes(lbl))) {
            form.value.result_notes = 'Prospek berminat dan meminta informasi penawaran lebih lanjut.';
        }
    } else if (type === 'won' || type === 'closing' || type === 'negotiation') {
        form.value.lead_status = 'negotiation';
        form.value.rejection_reason_id = null;
        if (!form.value.result_notes || Object.values(reasonMap).some(lbl => form.value.result_notes.includes(lbl))) {
            form.value.result_notes = 'Prospek setuju mendaftar (Siap Bayar / Negosiasi). Menunggu proses pembayaran dan upload bukti transfer.';
        }
    }
}

const waLink = computed(() => {
    if (!followUp.value?.lead?.phone) return '';
    let p = String(followUp.value.lead.phone).replace(/\D/g, '');
    if (p.startsWith('0')) p = '62' + p.slice(1);
    const msg = encodeURIComponent(`Halo Kak ${followUp.value.lead.name}, saya dari Disty Akademi ingin menindaklanjuti perihal program pelatihan.`);
    return `https://wa.me/${p}?text=${msg}`;
});

async function save() {
    saving.value = true;
    error.value = '';
    try {
        if (form.value.status === 'completed') {
            await api.request(`/follow-ups/${props.id}/complete`, {
                method: 'PATCH',
                body: {
                    result_notes: form.value.result_notes,
                    rejection_reason_id: form.value.rejection_reason_id,
                    lead_status: form.value.lead_status,
                    feedback_type: form.value.feedback_type,
                },
            });
        } else {
            await api.update('follow-ups', props.id, {
                status: form.value.status,
                result_notes: form.value.result_notes,
                lead_status: form.value.lead_status,
                rejection_reason_id: form.value.rejection_reason_id,
                feedback_type: form.value.feedback_type,
            });
        }
        emit('navigate', '/follow-ups');
    } catch (err) {
        error.value = err.message || 'Gagal memperbarui agenda follow-up.';
    } finally {
        saving.value = false;
    }
}

onMounted(() => {
    loadData();
});
</script>

<template>
    <div class="max-w-3xl mx-auto space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Perbarui Hasil Follow-Up #{{ id }}</h1>
                <p class="text-xs text-gray-500 mt-0.5">Catat respon prospek setelah dihubungi oleh tim sales</p>
            </div>
            <button @click="emit('navigate', '/follow-ups')" class="self-start sm:self-auto whitespace-nowrap shrink-0 text-xs font-semibold text-gray-500 hover:text-gray-900">
                &larr; Batal & Kembali
            </button>
        </div>

        <div v-if="loading" class="text-center py-12 text-xs text-gray-400">Memuat data agenda...</div>
        <div v-else-if="error" class="p-3 bg-red-50 border border-red-200 text-red-600 text-xs">{{ error }}</div>

        <form v-else @submit.prevent="save" class="bg-white p-4 sm:p-6 border border-gray-100 shadow-xs space-y-5">
            <!-- Info Prospek -->
            <div v-if="followUp?.lead" class="p-4 bg-gray-50 border border-gray-100 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 text-xs">
                <div>
                    <span class="text-gray-400 block text-[11px] font-semibold">Calon Pelanggan / Prospek</span>
                    <span class="font-bold text-gray-900 text-sm">{{ followUp.lead.name }}</span>
                    <span class="text-gray-500 block text-[11px] mt-0.5">
                        {{ followUp.lead.institution || 'Individu' }} &bull; {{ followUp.lead.phone || '-' }}
                    </span>
                    <span v-if="followUp.lead.product?.name" class="inline-block mt-1 font-semibold text-orange-600 bg-orange-50 px-2 py-0.5 border border-orange-100 text-[11px]">
                        Program: {{ followUp.lead.product.name }}
                    </span>
                </div>
                <div v-if="waLink" class="shrink-0">
                    <a :href="waLink" target="_blank" rel="noopener noreferrer"
                        class="inline-flex items-center gap-1.5 px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-semibold text-xs transition">
                        Hubungi via WhatsApp &rarr;
                    </a>
                </div>
            </div>

            <!-- Status Follow Up -->
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Status Agenda Follow-Up</label>
                <select v-model="form.status"
                    class="w-full px-3.5 py-2.5 border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500/20 bg-white">
                    <option value="pending">Menunggu / Terjadwal</option>
                    <option value="completed">Selesai / Sudah Dihubungi</option>
                    <option value="cancelled">Dibatalkan</option>
                </select>
            </div>

            <!-- Opsi Cepat Feedback Sesuai Standar -->
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1.5">
                    Pilihan Respon / Feedback Cepat (Standar SOP)
                </label>
                <p class="text-[11px] text-gray-400 mb-2">Pilih feedback untuk mengkategorikan hasil follow-up secara otomatis:</p>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 text-xs">
                    <button type="button" @click="selectFeedback('wrong_number')"
                        :class="form.feedback_type === 'wrong_number' ? 'bg-red-500 text-white border-red-500 shadow-xs' : 'bg-gray-50 text-gray-700 border-gray-200 hover:bg-gray-100'"
                        class="p-2.5 rounded-xl border font-semibold text-left transition">
                        <span class="block font-bold">Nomor Salah</span>
                        <span class="text-[10px] opacity-80">Nomor tidak valid</span>
                    </button>
                    <button type="button" @click="selectFeedback('unreachable')"
                        :class="form.feedback_type === 'unreachable' ? 'bg-amber-500 text-white border-amber-500 shadow-xs' : 'bg-gray-50 text-gray-700 border-gray-200 hover:bg-gray-100'"
                        class="p-2.5 rounded-xl border font-semibold text-left transition">
                        <span class="block font-bold">Tidak Dapat Dihubungi</span>
                        <span class="text-[10px] opacity-80">Tidak aktif / tidak diangkat</span>
                    </button>
                    <button type="button" @click="selectFeedback('not_willing')"
                        :class="form.feedback_type === 'not_willing' ? 'bg-orange-600 text-white border-orange-600 shadow-xs' : 'bg-gray-50 text-gray-700 border-gray-200 hover:bg-gray-100'"
                        class="p-2.5 rounded-xl border font-semibold text-left transition">
                        <span class="block font-bold">Tidak Bersedia</span>
                        <span class="text-[10px] opacity-80">Menolak ikut pelatihan</span>
                    </button>
                    <button type="button" @click="selectFeedback('not_interested')"
                        :class="form.feedback_type === 'not_interested' ? 'bg-purple-600 text-white border-purple-600 shadow-xs' : 'bg-gray-50 text-gray-700 border-gray-200 hover:bg-gray-100'"
                        class="p-2.5 rounded-xl border font-semibold text-left transition">
                        <span class="block font-bold">Tidak Tertarik</span>
                        <span class="text-[10px] opacity-80">Belum ada kebutuhan</span>
                    </button>
                    <button type="button" @click="selectFeedback('interested')"
                        :class="form.feedback_type === 'interested' ? 'bg-blue-600 text-white border-blue-600 shadow-xs' : 'bg-gray-50 text-gray-700 border-gray-200 hover:bg-gray-100'"
                        class="p-2.5 rounded-xl border font-semibold text-left transition">
                        <span class="block font-bold">Berminat / Lanjut</span>
                        <span class="text-[10px] opacity-80">Minta info kurikulum</span>
                    </button>
                    <button type="button" @click="selectFeedback('closing')"
                        :class="(form.feedback_type === 'closing' || form.feedback_type === 'won') ? 'bg-amber-600 text-white border-amber-600 shadow-xs' : 'bg-gray-50 text-gray-700 border-gray-200 hover:bg-gray-100'"
                        class="p-2.5 rounded-xl border font-semibold text-left transition">
                        <span class="block font-bold">Siap Bayar / Negosiasi</span>
                        <span class="text-[10px] opacity-80">Setuju mendaftar (Menunggu Transfer)</span>
                    </button>
                </div>
            </div>

            <!-- Catatan Hasil -->
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Catatan Hasil & Respon Prospek</label>
                <textarea v-model="form.result_notes" rows="3"
                    class="w-full px-3.5 py-2.5 border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500/20 outline-none"
                    placeholder="Tuliskan ringkasan percakapan, keberatan, atau jadwal follow-up lanjutan..."></textarea>
            </div>

            <div class="pt-2 flex flex-wrap justify-end gap-2">
                <button type="button" @click="emit('navigate', '/follow-ups')"
                    class="px-4 py-2.5 rounded-xl border border-gray-200 font-semibold text-xs text-gray-600 hover:bg-gray-50">
                    Batal
                </button>
                <button type="submit" :disabled="saving"
                    class="px-5 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-bold text-xs shadow-sm disabled:opacity-50">
                    <span v-if="saving">Menyimpan...</span>
                    <span v-else>Simpan Perubahan</span>
                </button>
            </div>
        </form>
    </div>
</template>
