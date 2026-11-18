<script setup>
import { computed, onMounted, ref } from 'vue';
import { api } from '../../services/api';

const props = defineProps({
    user: { type: Object, default: () => ({}) },
});

const emit = defineEmits(['navigate']);

const isAdmin = computed(() => props.user?.role === 'admin');

const form = ref({
    name: '',
    nik: '',
    birth_date: '',
    phone: '',
    email: '',
    institution: '',
    product_id: '',
    source_id: '',
    notes: '',
});

const products = ref([]);
const sources = ref([]);
const loading = ref(false);
const error = ref('');

async function loadDependencies() {
    try {
        const [pRes, sRes] = await Promise.all([
            api.list('products', { per_page: 100 }),
            api.list('lead-sources', { per_page: 100 }),
        ]);
        products.value = pRes.data || [];
        sources.value = sRes.data || [];
    } catch (e) {
        console.error(e);
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
    loading.value = true;
    error.value = '';
    try {
        const payload = {
            ...form.value,
            company: form.value.institution,
            whatsapp: form.value.phone,
        };
        await api.create('leads', payload);
        emit('navigate', '/leads');
    } catch (err) {
        error.value = err.message || 'Gagal menyimpan prospek.';
    } finally {
        loading.value = false;
    }
}

onMounted(() => {
    loadDependencies();
});
</script>

<template>
    <div class="max-w-2xl mx-auto space-y-5">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Tambah Prospek Baru</h1>
                <p class="text-xs text-gray-500 mt-0.5">Input data calon peserta atau mitra pelatihan</p>
            </div>
            <button @click="emit('navigate', '/leads')" class="text-xs font-semibold text-gray-500 hover:text-gray-900">
                &larr; Batal & Kembali
            </button>
        </div>

        <!-- Akses Dibatasi untuk Admin -->
        <div v-if="isAdmin" class="p-5 bg-amber-50 border border-amber-200 text-amber-900 text-xs space-y-2.5">
            <div class="font-bold text-sm text-amber-800">
                Akses Dibatasi untuk Akun Admin
            </div>
            <p>
                Sesuai kebijakan operasional CRM, akun <strong>Administrator hanya memiliki akses pemantauan</strong>. 
                Penambahan data calon pelanggan (lead) baru hanya dapat dilakukan oleh akun <strong>Sales</strong> sebagai penanggung jawab prospek.
            </p>
            <div class="pt-1">
                <button type="button" @click="emit('navigate', '/leads')"
                    class="px-3.5 py-2 bg-amber-600 text-white rounded-xl font-semibold hover:bg-amber-700 transition-colors">
                    &larr; Kembali ke Daftar Lead
                </button>
            </div>
        </div>

        <template v-else>
            <div v-if="error" class="p-3 bg-red-50 border border-red-200 text-red-600 text-xs">
                {{ error }}
            </div>

            <div class="bg-white p-6 border border-gray-100 shadow-xs space-y-4">
                <!-- Nama Lengkap -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Nama Lengkap *</label>
                    <input v-model="form.name" type="text" name="lead_full_name" required autocomplete="off"
                        class="w-full px-3.5 py-2.5 border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 outline-none" placeholder="Misal: Budi Santoso" />
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

                <!-- WhatsApp & Email -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Nomor WhatsApp *</label>
                        <input v-model="form.phone" type="text" name="lead_phone_no" required autocomplete="off"
                            class="w-full px-3.5 py-2.5 border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 outline-none" placeholder="08xxxxxxxxxx" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Email</label>
                        <input v-model="form.email" type="email" name="lead_email_addr" autocomplete="off"
                            class="w-full px-3.5 py-2.5 border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 outline-none" placeholder="budi@example.com" />
                    </div>
                </div>

                <!-- Instansi / Kampus -->
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Instansi / Asal Kampus / Kantor</label>
                    <input v-model="form.institution" type="text" name="lead_institution_office" autocomplete="off"
                        class="w-full px-3.5 py-2.5 border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 outline-none" placeholder="Misal: Universitas Indonesia / PT Maju Bersama" />
                </div>

                <!-- Program & Sumber Prospek -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Program Diminati</label>
                        <select v-model="form.product_id"
                            class="w-full px-3.5 py-2.5 border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 outline-none bg-white">
                            <option value="">Pilih Program</option>
                            <option v-for="p in products" :key="p.id" :value="p.id">{{ p.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Sumber Prospek (Source)</label>
                        <select v-model="form.source_id"
                            class="w-full px-3.5 py-2.5 border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 outline-none bg-white">
                            <option value="">Pilih Sumber</option>
                            <option v-for="s in sources" :key="s.id" :value="s.id">{{ s.name }}</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Catatan Kebutuhan</label>
                    <textarea v-model="form.notes" rows="3"
                        class="w-full px-3.5 py-2.5 border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 outline-none" placeholder="Tambahkan catatan khusus..."></textarea>
                </div>

                <div class="pt-2 flex justify-end gap-2">
                    <button type="button" @click="emit('navigate', '/leads')" class="px-4 py-2.5 rounded-xl border border-gray-200 font-semibold text-xs text-gray-600 hover:bg-gray-50">Batal</button>
                    <button type="button" @click="save" :disabled="loading" class="px-5 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-bold text-xs shadow-sm disabled:opacity-50">
                        <span v-if="loading">Menyimpan...</span>
                        <span v-else>Simpan Data Prospek</span>
                    </button>
                </div>
            </div>
        </template>
    </div>
</template>
