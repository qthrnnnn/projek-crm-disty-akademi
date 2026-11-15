<script setup>
import { computed, onMounted, ref } from 'vue';
import { api } from '../../services/api';

const props = defineProps({
    user: { type: Object, default: () => ({}) },
});

const emit = defineEmits(['navigate']);

const file = ref(null);
const sourceId = ref('');
const sources = ref([]);
const salesUsers = ref([]);
const selectedSalesId = ref('');
const loading = ref(false);
const error = ref('');
const successMessage = ref('');

const isSales = computed(() => props.user?.role === 'sales');
const isAdmin = computed(() => props.user?.role === 'admin');

async function loadSources() {
    try {
        const res = await api.list('lead-sources', { per_page: 100 });
        const all = res.data || [];
        // Hanya sumber dari Excel dan CSV sesuai permintaan
        sources.value = all.filter(s => s.name === 'import_excel' || s.name === 'import_csv');
        if (sources.value.length > 0 && !sourceId.value) {
            sourceId.value = sources.value[0].id;
        }
    } catch (e) {
        console.error(e);
    }
}

async function loadSalesUsers() {
    try {
        const res = await api.list('users', { per_page: 100 });
        salesUsers.value = (res.data || []).filter(u => u.role === 'sales');
        if (salesUsers.value.length > 0) {
            selectedSalesId.value = salesUsers.value[0].id;
        }
    } catch (e) {
        console.error(e);
    }
}

function handleFile(e) {
    const f = e.target.files[0] || null;
    file.value = f;
    if (f) {
        const ext = f.name.split('.').pop().toLowerCase();
        if (['xlsx', 'xls'].includes(ext)) {
            const excelSource = sources.value.find(s => s.name === 'import_excel');
            if (excelSource) sourceId.value = excelSource.id;
        } else if (['csv', 'txt'].includes(ext)) {
            const csvSource = sources.value.find(s => s.name === 'import_csv');
            if (csvSource) sourceId.value = csvSource.id;
        }
    }
}

function downloadCsvTemplate() {
    // Format CSV UTF-8 dengan BOM agar langsung terbaca rapi di Microsoft Excel
    const csvContent = "\uFEFF" +
        "Nama,NIK,Tanggal Lahir,WhatsApp,Email,Instansi,Kota,Provinsi,Program,Catatan\n" +
        "Ahmad Fauzi,3578012304950001,1995-04-23,081234567890,ahmad.fauzi@gmail.com,PT Pelindo Daya Sejahtera,Surabaya,Jawa Timur,Pelatihan dan Sertifikasi Digital Filing,Peminat program Digital Filing batch Surabaya\n" +
        "Siti Nurhaliza,3578025508980002,1998-08-15,082198765432,siti.nur@unair.ac.id,Universitas Airlangga,Surabaya,Jawa Timur,Pelatihan Pelayanan Prima,Peningkatan keterampilan pelayanan prima staf\n" +
        "Bambang Prasetyo,3578031201900003,1990-01-12,085712345678,bambang@dinas-jatim.go.id,Dinas Pendidikan Prov Jatim,Surabaya,Jawa Timur,Sertifikasi Pelayanan Prima (Excellent Service) Lisensi BNSP,Mengikuti uji kompetensi sertifikasi BNSP resmi\n";

    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const url = URL.createObjectURL(blob);
    const link = document.createElement('a');
    link.href = url;
    link.setAttribute('download', 'template_import_leads.csv');
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    URL.revokeObjectURL(url);
}

function downloadExcelTemplate() {
    const link = document.createElement('a');
    link.href = '/leads/template-excel';
    link.setAttribute('download', 'template_import_leads.xlsx');
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

async function upload() {
    if (!file.value) {
        error.value = 'Silakan pilih file Excel (.xlsx) atau CSV.';
        return;
    }
    loading.value = true;
    error.value = '';
    successMessage.value = '';

    try {
        const fd = new FormData();
        fd.append('file', file.value);
        if (sourceId.value) fd.append('lead_source_id', sourceId.value);
        
        // Jika Sales yang mengunggah, otomatis dirinya sendiri yang jadi Sales PIC
        if (isSales.value && props.user?.id) {
            fd.append('sales_id', props.user.id);
        } else if (isAdmin.value && selectedSalesId.value) {
            fd.append('sales_id', selectedSalesId.value);
        }
        
        const res = await api.request('/leads/import', {
            method: 'POST',
            body: fd,
        });

        successMessage.value = res.message || 'File Excel berhasil diimpor dan seluruh prospek otomatis ditugaskan ke Sales PIC!';
        setTimeout(() => {
            emit('navigate', '/leads');
        }, 1200);
    } catch (err) {
        error.value = err.message || 'Gagal mengunggah file import.';
    } finally {
        loading.value = false;
    }
}

onMounted(() => {
    loadSources();
    if (isAdmin.value) {
        loadSalesUsers();
    }
});
</script>

<template>
    <div class="max-w-2xl mx-auto space-y-5">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Upload Data Leads Excel</h1>
                <p class="text-xs text-gray-500 mt-0.5">Unggah prospek masal dari spreadsheet (.xlsx / .csv)</p>
            </div>
            <button @click="emit('navigate', '/leads')" class="text-xs font-semibold text-gray-500 hover:text-gray-900">
                &larr; Batal & Kembali
            </button>
        </div>

        <!-- Banner Informasi Eksklusif Sales -->
        <div v-if="isSales" class="p-3.5 bg-blue-50 border border-blue-200 text-blue-900 text-xs flex items-center gap-2.5">
            <div class="w-8 h-8 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-xs shrink-0">
                {{ ((user?.name || 'S').charAt(0)).toUpperCase() }}
            </div>
            <div>
                <span class="font-bold">Otomatisasi Sales PIC:</span> Seluruh data prospek yang ada di file Excel ini akan otomatis ditetapkan ke akun Anda (<strong>{{ user?.name }}</strong>) sebagai Sales PIC penanggung jawab.
            </div>
        </div>

        <!-- Banner Informasi Admin -->
        <div v-else-if="isAdmin" class="p-3.5 bg-orange-50 border border-orange-200 text-orange-900 text-xs">
            <span class="font-bold">Mode Administrator:</span> Pilih akun Sales PIC yang akan ditugaskan untuk seluruh calon customer / lead yang diimpor dari file Excel.
        </div>

        <div v-if="error" class="p-3 bg-red-50 border border-red-200 text-red-600 text-xs">{{ error }}</div>
        <div v-if="successMessage" class="p-3 bg-emerald-50 border border-emerald-200 text-emerald-700 text-xs font-semibold">{{ successMessage }}</div>

        <form @submit.prevent="upload" class="bg-white p-6 border border-gray-100 shadow-xs space-y-4">
            <!-- Pilihan Sales PIC khusus untuk Admin -->
            <div v-if="isAdmin">
                <label class="block text-xs font-semibold text-gray-700 mb-1">Tugaskan ke Sales PIC *</label>
                <select v-model="selectedSalesId" required
                    class="w-full px-3.5 py-2.5 border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500/20 bg-white font-medium text-gray-800">
                    <option value="" disabled>-- Pilih Sales Penanggung Jawab --</option>
                    <option v-for="s in salesUsers" :key="s.id" :value="s.id">
                        {{ s.name }} ({{ s.email }})
                    </option>
                </select>
                <p class="text-[11px] text-gray-400 mt-1">Seluruh lead dari file Excel akan otomatis ditugaskan ke sales yang dipilih.</p>
            </div>

            <div>
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-1 mb-1.5">
                    <label class="block text-xs font-semibold text-gray-700">Pilih Berkas Excel / CSV *</label>
                    <div class="flex items-center gap-1.5 flex-wrap">
                        <span class="text-[11px] text-gray-400">Unduh Template:</span>
                        <button type="button" @click="downloadExcelTemplate"
                            class="text-[11px] font-semibold text-emerald-700 hover:text-emerald-800 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 px-2 py-0.5 rounded inline-flex items-center gap-1 transition-colors cursor-pointer">
                            <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            Format Excel (.xlsx)
                        </button>
                        <button type="button" @click="downloadCsvTemplate"
                            class="text-[11px] font-semibold text-blue-700 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 border border-blue-200 px-2 py-0.5 rounded inline-flex items-center gap-1 transition-colors cursor-pointer">
                            <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            Format CSV
                        </button>
                    </div>
                </div>
                <input type="file" required accept=".xlsx,.xls,.csv,.txt" @change="handleFile"
                    class="w-full px-3.5 py-2.5 border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500/20 bg-white" />
                <p class="text-[11px] text-gray-400 mt-1.5">Format kolom yang didukung: Nama, No_WA / Telepon, Email, Instansi / Perusahaan, Program / Produk, NIK, Tanggal Lahir, Catatan</p>

            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Sumber Prospek</label>
                <select v-model="sourceId"
                    class="w-full px-3.5 py-2.5 border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500/20 bg-white font-medium text-gray-800">
                    <option v-for="s in sources" :key="s.id" :value="s.id">
                        {{ s.label || (s.name === 'import_excel' ? 'Import Excel' : 'Import CSV') }}
                    </option>
                </select>
                <p class="text-[11px] text-gray-400 mt-1">Otomatis terpilih berdasarkan format file yang diunggah (.xlsx / .csv).</p>
            </div>

            <div class="pt-2 flex justify-end gap-2">
                <button type="button" @click="emit('navigate', '/leads')" class="px-4 py-2.5 rounded-xl border border-gray-200 font-semibold text-xs text-gray-600 hover:bg-gray-50">Batal</button>
                <button type="submit" :disabled="loading" class="px-5 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-bold text-xs shadow-sm disabled:opacity-50">
                    <span v-if="loading">Memproses Berkas Excel...</span>
                    <span v-else>Unggah & Import Data Leads</span>
                </button>
            </div>
        </form>
    </div>
</template>
