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
const expandedRegs = ref({});

function toggleReg(id) {
    expandedRegs.value[id] = !expandedRegs.value[id];
}

function isExpanded(id) {
    return !!expandedRegs.value[id];
}

function formatCurrency(val) {
    if (val === null || val === undefined || isNaN(val)) return 'Rp 0';
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
        maximumFractionDigits: 0,
    }).format(Number(val));
}

function formatDateTime(val) {
    if (!val) return '-';
    const d = new Date(val);
    if (isNaN(d.getTime())) return val;
    const dateStr = d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
    const timeStr = d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit', hour12: false }).replace('.', ':');
    return `${dateStr}, ${timeStr} WIB`;
}

function formatDateOnly(val) {
    if (!val) return '-';
    const d = new Date(val);
    if (isNaN(d.getTime())) return val;
    return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'long', year: 'numeric' });
}

async function loadCustomer() {
    loading.value = true;
    try {
        const res = await api.get('customers', props.id);
        customer.value = res.data || res;
        expandedRegs.value = {};
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
            <!-- Header Profil Customer -->
            <div class="bg-white p-5 border border-gray-100 shadow-xs flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <div class="flex items-center gap-2">
                        <span class="text-xs font-mono font-bold text-gray-400">#{{ customer.customer_code || customer.id }}</span>
                        <Badge :status="customer.status || 'aktif'" />
                    </div>
                    <h1 class="text-xl font-black text-gray-900 mt-2">{{ customer.name }}</h1>
                    <p class="text-xs text-gray-500 mt-0.5">{{ customer.company || 'Customer Individu' }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <button @click="emit('navigate', '/customers')" class="px-3.5 py-2 border border-gray-200 text-xs font-semibold text-gray-600 hover:bg-gray-50">
                        &larr; Kembali
                    </button>
                    <button @click="emit('navigate', `/customers/${customer.id}/edit`)" class="px-3.5 py-2 border border-gray-200 text-xs font-semibold text-gray-700 hover:bg-gray-50">
                        Edit Profil
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
                <!-- Data Identitas & Kontak -->
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

                <!-- Histori Pembelian & Pendaftaran -->
                <div class="lg:col-span-2 bg-white p-5 border border-gray-100 shadow-xs space-y-4">
                    <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                        <h2 class="text-xs font-bold text-gray-500 uppercase tracking-wider">Histori Pembelian & Pendaftaran</h2>
                        <span class="text-xs text-gray-400 font-medium">{{ customer.registrations?.length || 0 }} Transaksi</span>
                    </div>

                    <div v-if="customer.registrations?.length" class="divide-y divide-gray-100">
                        <div
                            v-for="r in customer.registrations"
                            :key="r.id"
                            class="py-3 transition-colors"
                        >
                            <!-- Baris Ringkas: Nama Program, Tanggal & Jam, dan tombol Lihat Selengkapnya -->
                            <div
                                @click="toggleReg(r.id)"
                                class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 p-2 -mx-2 cursor-pointer hover:bg-gray-50 transition-colors group select-none"
                            >
                                <div class="min-w-0 pr-2">
                                    <span class="font-bold text-sm text-gray-900 group-hover:text-orange-600 transition-colors block">
                                        {{ r.product?.name || r.product_name || 'Pelatihan' }}
                                    </span>
                                </div>
                                <div class="flex items-center gap-3 shrink-0 text-xs">
                                    <span class="text-gray-400 font-medium whitespace-nowrap">
                                        {{ formatDateTime(r.created_at) }}
                                    </span>
                                    <span class="inline-flex items-center gap-1 font-semibold text-orange-600 group-hover:text-orange-700 whitespace-nowrap">
                                        <span>{{ isExpanded(r.id) ? 'Tutup Rincian' : 'Lihat Selengkapnya' }}</span>
                                        <svg
                                            class="w-3.5 h-3.5 transform transition-transform duration-200"
                                            :class="{ 'rotate-180': isExpanded(r.id) }"
                                            fill="none"
                                            stroke="currentColor"
                                            viewBox="0 0 24 24"
                                        >
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </span>
                                </div>
                            </div>

                            <!-- Rincian yang muncul saat ditekan / diklik (Kotak Lancip) -->
                            <div
                                v-show="isExpanded(r.id)"
                                class="mt-2.5 p-4 bg-gray-50 border border-gray-200 text-xs space-y-4"
                            >
                                <div class="flex items-center justify-between pb-2 border-b border-gray-200">
                                    <div class="flex items-center gap-2">
                                        <span class="font-mono text-xs font-bold text-gray-700 bg-white px-2 py-0.5 border border-gray-200">
                                            {{ r.registration_number || ('REG-' + r.id) }}
                                        </span>
                                        <Badge :status="r.status || 'confirmed'" />
                                        <span v-if="r.payment?.status === 'valid'" class="px-2 py-0.5 text-[10px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            Lunas / Valid
                                        </span>
                                    </div>
                                    <div class="text-right">
                                        <span class="text-[10px] text-gray-400 block font-semibold uppercase">Total Nilai Deal</span>
                                        <span class="text-sm font-black text-emerald-600">
                                            {{ formatCurrency(r.amount || r.payment?.amount || r.product?.price) }}
                                        </span>
                                    </div>
                                </div>

                                <div class="grid grid-cols-1 md:grid-cols-3 gap-3.5">
                                    <!-- Kolom 1: Rincian Program & Biaya -->
                                    <div class="bg-white p-3 border border-gray-200 space-y-2">
                                        <div class="text-[10px] font-bold uppercase tracking-wider text-gray-400 pb-1 border-b border-gray-100">
                                            Rincian Program & Biaya
                                        </div>
                                        <div>
                                            <span class="text-gray-400 block text-[10px]">Kode & Kategori</span>
                                            <span class="font-medium text-gray-800">{{ r.product?.code || '-' }} • {{ r.product?.category || 'Pelatihan' }}</span>
                                        </div>
                                        <div>
                                            <span class="text-gray-400 block text-[10px]">Harga Resmi Katalog (Disty)</span>
                                            <span class="font-bold text-gray-900">{{ formatCurrency(r.product?.price || r.amount) }}</span>
                                        </div>
                                        <div>
                                            <span class="text-gray-400 block text-[10px]">Hak Komisi Sales (10%)</span>
                                            <span class="font-bold text-emerald-700">
                                                {{ formatCurrency(r.payment?.commission?.commission_amount || (r.product?.price ? r.product.price * 0.1 : (r.amount ? r.amount * 0.1 : 0))) }}
                                            </span>
                                        </div>
                                        <div>
                                            <span class="text-gray-400 block text-[10px]">Total Transfer Customer</span>
                                            <span class="font-bold text-gray-900">{{ formatCurrency(r.amount || r.payment?.amount) }}</span>
                                        </div>
                                    </div>

                                    <!-- Kolom 2: Jadwal & Trainer -->
                                    <div class="bg-white p-3 border border-gray-200 space-y-2">
                                        <div class="text-[10px] font-bold uppercase tracking-wider text-gray-400 pb-1 border-b border-gray-100">
                                            Jadwal & Penyelenggaraan
                                        </div>
                                        <div>
                                            <span class="text-gray-400 block text-[10px]">Jadwal Pelaksanaan</span>
                                            <span class="font-bold text-gray-900">
                                                {{ r.schedule_info || (r.training_date ? formatDateOnly(r.training_date) : 'Menunggu Penetapan Jadwal Kelas') }}
                                            </span>
                                        </div>
                                        <div>
                                            <span class="text-gray-400 block text-[10px]">Instruktur / Trainer</span>
                                            <span class="font-bold text-gray-900">
                                                {{ r.training_schedule?.trainer?.name || r.schedule?.trainer?.name || 'Akan Ditugaskan' }}
                                            </span>
                                        </div>
                                        <div>
                                            <span class="text-gray-400 block text-[10px]">Sales Penanggung Jawab (PIC)</span>
                                            <span class="font-bold text-gray-900">{{ r.sales_pic || customer.lead?.assigned_user?.name || 'Admin CRM' }}</span>
                                        </div>
                                        <div>
                                            <span class="text-gray-400 block text-[10px]">Catatan Registrasi</span>
                                            <span class="text-gray-600">{{ r.notes || '-' }}</span>
                                        </div>
                                    </div>

                                    <!-- Kolom 3: Bukti & Verifikasi Pembayaran -->
                                    <div class="bg-white p-3 border border-gray-200 space-y-2">
                                        <div class="text-[10px] font-bold uppercase tracking-wider text-gray-400 pb-1 border-b border-gray-100 flex items-center justify-between">
                                            <span>Bukti Pembayaran</span>
                                            <span v-if="r.payment" class="font-mono text-[10px] text-gray-400">#{{ r.payment.id }}</span>
                                        </div>
                                        <div v-if="r.payment" class="space-y-2">
                                            <div>
                                                <span class="text-gray-400 block text-[10px]">Bank Tujuan Disty</span>
                                                <span class="font-bold text-gray-900">{{ r.payment.bank_name || 'BCA' }}</span>
                                            </div>
                                            <div v-if="r.payment.account_name">
                                                <span class="text-gray-400 block text-[10px]">Rekening Pengirim Customer</span>
                                                <span class="font-semibold text-gray-800">a.n {{ r.payment.account_name }}</span>
                                            </div>
                                            <div>
                                                <span class="text-gray-400 block text-[10px]">Status Verifikasi</span>
                                                <div class="mt-0.5">
                                                    <Badge :status="r.payment.status || 'valid'" />
                                                </div>
                                            </div>
                                            <div>
                                                <span class="text-gray-400 block text-[10px]">Tanggal & Jam Verifikasi</span>
                                                <span class="text-gray-700">{{ formatDateTime(r.payment.verified_at || r.payment.updated_at) }}</span>
                                            </div>
                                            <div>
                                                <span class="text-gray-400 block text-[10px]">Diverifikasi Oleh</span>
                                                <span class="font-semibold text-gray-800">{{ r.payment.verified_by_user?.name || 'Admin CRM' }}</span>
                                            </div>
                                            <div class="pt-1.5 flex flex-col gap-1.5">
                                                <a
                                                    v-if="r.payment.proof_url"
                                                    :href="r.payment.proof_url"
                                                    target="_blank"
                                                    class="inline-flex items-center justify-center px-3 py-1.5 bg-orange-50 text-orange-700 border border-orange-200 font-bold text-[11px] hover:bg-orange-100"
                                                >
                                                    Buka Gambar Bukti Struk
                                                </a>
                                                <button
                                                    @click="emit('navigate', `/payments/${r.payment.id}`)"
                                                    class="inline-flex items-center justify-center px-3 py-1.5 border border-gray-200 text-gray-700 font-semibold text-[11px] hover:bg-gray-50"
                                                >
                                                    Buka Menu Verifikasi Pembayaran
                                                </button>
                                            </div>
                                        </div>
                                        <div v-else class="text-center py-4 space-y-2">
                                            <p class="text-gray-400 text-[11px]">Belum ada struk pembayaran terhubung langsung.</p>
                                            <button
                                                @click="emit('navigate', '/payments/create')"
                                                class="px-3 py-1.5 bg-orange-600 text-white font-semibold text-[11px] hover:bg-orange-700"
                                            >
                                                + Input Pembayaran
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div v-else class="text-center py-8 text-xs text-gray-400 bg-gray-50 border border-dashed border-gray-200">
                        Belum ada riwayat pendaftaran pelatihan.
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
