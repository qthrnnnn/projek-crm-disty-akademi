<script setup>
import { onMounted, ref } from 'vue';
import { api } from '../../services/api';

const props = defineProps({
    id: { type: [String, Number], required: true },
});

const emit = defineEmits(['navigate']);

const form = ref({
    name: '',
    nik: '',
    birth_date: '',
    phone: '',
    email: '',
    institution: '',
    product_id: '',
    status: '',
    notes: '',
});

const products = ref([]);
const loading = ref(true);
const saving = ref(false);
const error = ref('');

async function loadData() {
    loading.value = true;
    try {
        const [pRes, leadRes] = await Promise.all([
            api.list('products', { per_page: 100 }),
            api.get('leads', props.id),
        ]);
        products.value = pRes.data || [];
        const l = leadRes.data || leadRes;
        form.value = {
            name: l.name || '',
            nik: l.nik || '',
            birth_date: l.birth_date ? String(l.birth_date).split('T')[0] : '',
            phone: l.phone || l.whatsapp || '',
            email: l.email || '',
            institution: l.institution || l.company || '',
            product_id: l.product_id || '',
            status: l.status || 'new',
            notes: l.notes || '',
        };
    } catch (err) {
        error.value = err.message || 'Gagal memuat data prospek.';
    } finally {
        loading.value = false;
    }
}

async function save() {
    if (!form.value.name || !form.value.name.trim()) {
        error.value = 'Nama Lengkap wajib diisi.';
        return;
    }
    if (!form.value.phone || !form.value.phone.trim()) {
        error.value = 'Nomor WhatsApp wajib diisi.';
        return;
    }
    saving.value = true;
    error.value = '';
    try {
        const payload = {
            ...form.value,
            company: form.value.institution,
            whatsapp: form.value.phone,
        };
        await api.update('leads', props.id, payload);
        emit('navigate', `/leads/${props.id}`);
    } catch (err) {
        error.value = err.message || 'Gagal memperbarui prospek.';
    } finally {
        saving.value = false;
    }
}

onMounted(() => {
    loadData();
});
</script>

<template>
    <div class="max-w-2xl mx-auto space-y-5">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Edit Data Prospek #{{ id }}</h1>
                <p class="text-xs text-gray-500 mt-0.5">Perbarui profil dan status prospek</p>
            </div>
            <button @click="emit('navigate', '/leads')" class="text-xs font-semibold text-gray-500 hover:text-gray-900">
                &larr; Batal & Kembali
            </button>
        </div>

        <div v-if="loading" class="text-center py-12 text-xs text-gray-400">Memuat data...</div>
        <div v-else-if="error" class="p-3 bg-red-50 border border-red-200 text-red-600 text-xs">{{ error }}</div>

        <div v-else class="bg-white p-6 border border-gray-100 shadow-xs space-y-4">
            <!-- Nama Lengkap -->
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Nama Lengkap *</label>
                <input v-model="form.name" type="text" name="lead_full_name" required autocomplete="off"
                    class="w-full px-3.5 py-2.5 border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 outline-none" />
            </div>

            <!-- NIK & Tanggal Lahir -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Nomor NIK (KTP)</label>
                    <input v-model="form.nik" type="text" name="lead_nik_no" maxlength="20" placeholder="16 digit NIK" autocomplete="off" data-lpignore="true"
                        class="w-full px-3.5 py-2.5 border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 outline-none" />
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Tanggal Lahir</label>
                    <input v-model="form.birth_date" type="date" name="lead_birth_date" autocomplete="off"
                        class="w-full px-3.5 py-2.5 border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 outline-none" />
                </div>
            </div>

            <!-- Kontak: WhatsApp & Email -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">WhatsApp *</label>
                    <input v-model="form.phone" type="text" name="lead_phone_no" required autocomplete="off"
                        class="w-full px-3.5 py-2.5 border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 outline-none" />
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Email</label>
                    <input v-model="form.email" type="email" name="lead_email_addr" autocomplete="off"
                        class="w-full px-3.5 py-2.5 border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 outline-none" />
                </div>
            </div>

            <!-- Instansi / Perusahaan / Kampus -->
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Instansi / Asal Kampus / Kantor</label>
                <input v-model="form.institution" type="text" name="lead_institution_office" autocomplete="off" placeholder="Misal: PT Maju Bersama / Universitas Airlangga"
                    class="w-full px-3.5 py-2.5 border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 outline-none" />
            </div>

            <!-- Program & Status Pipeline -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Program</label>
                    <select v-model="form.product_id"
                        class="w-full px-3.5 py-2.5 border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500/20 bg-white">
                        <option value="">Pilih Program</option>
                        <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Status Pipeline</label>
                    <select v-model="form.status"
                        class="w-full px-3.5 py-2.5 border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500/20 bg-white">
                        <option value="new">Baru</option>
                        <option value="contacted">Dikontak</option>
                        <option value="active">Aktif</option>
                        <option value="negotiation">Negosiasi</option>
                        <option value="won">Berhasil</option>
                        <option value="lost">Ditolak</option>
                    </select>
                </div>
            </div>

            <!-- Catatan Tambahan -->
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Catatan Tambahan</label>
                <textarea v-model="form.notes" rows="3"
                    class="w-full px-3.5 py-2.5 border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500/20 outline-none"></textarea>
            </div>

            <div class="pt-2 flex justify-end gap-2">
                <button type="button" @click="emit('navigate', `/leads/${id}`)" class="px-4 py-2.5 rounded-xl border border-gray-200 font-semibold text-xs text-gray-600 hover:bg-gray-50">Batal</button>
                <button type="button" @click="save" :disabled="saving" class="px-5 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-bold text-xs shadow-sm disabled:opacity-50">
                    <span v-if="saving">Menyimpan...</span>
                    <span v-else>Simpan Perubahan</span>
                </button>
            </div>
        </div>
    </div>
</template>
