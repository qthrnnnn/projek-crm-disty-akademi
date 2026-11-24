<script setup>
import { computed, onBeforeUnmount, onMounted, ref, watch } from 'vue';
import { api } from '../../services/api';

const props = defineProps({
    user: { type: Object, default: () => ({}) },
});
const canRegister = computed(() => props.user?.role === 'sales');

const emit = defineEmits(['navigate']);

const form = ref({
    customer_id: '',
    lead_id: '',
    product_id: '',
    training_schedule_id: '',
    amount: 0,
    status: 'registered',
    notes: '',
});

const comboboxRef = ref(null);
const searchQuery = ref('');
const isDropdownOpen = ref(false);
const selectedParticipant = ref('');

const customers = ref([]);
const leads = ref([]);
const schedules = ref([]);
const products = ref([]);
const loading = ref(false);
const error = ref('');

const selectedLeadDetail = computed(() => {
    if (!selectedParticipant.value.startsWith('lead:')) return null;
    const id = selectedParticipant.value.split(':')[1];
    return leads.value.find(l => String(l.id) === String(id));
});

const selectedCustomerDetail = computed(() => {
    if (!selectedParticipant.value.startsWith('customer:')) return null;
    const id = selectedParticipant.value.split(':')[1];
    return customers.value.find(c => String(c.id) === String(id));
});

const availableSchedules = computed(() => {
    if (!form.value.product_id) return schedules.value;
    return schedules.value.filter(s => String(s.product_id) === String(form.value.product_id));
});

const filteredLeads = computed(() => {
    const q = searchQuery.value.trim().toLowerCase();
    
    // Saring keluar lead yang sudah closing (won) atau sudah terdaftar resmi sebagai Customer
    const customerLeadIds = new Set(customers.value.map(c => String(c.lead_id)).filter(Boolean));
    const customerPhones = new Set(customers.value.map(c => c.phone).filter(Boolean));
    
    const activeLeads = leads.value.filter(l => {
        if (l.status === 'won' || l.converted_at) return false;
        if (customerLeadIds.has(String(l.id))) return false;
        if ((l.phone || l.whatsapp) && customerPhones.has(l.phone || l.whatsapp)) return false;
        return true;
    });

    if (!q) return activeLeads;
    return activeLeads.filter(l => {
        const name = (l.name || '').toLowerCase();
        const phone = (l.phone || l.whatsapp || '').toLowerCase();
        const company = (l.company || l.institution || '').toLowerCase();
        const nik = (l.nik || '').toLowerCase();
        const code = (l.lead_code || '').toLowerCase();
        return name.includes(q) || phone.includes(q) || company.includes(q) || nik.includes(q) || code.includes(q);
    });
});

const filteredCustomers = computed(() => {
    const q = searchQuery.value.trim().toLowerCase();
    if (!q) return customers.value;
    return customers.value.filter(c => {
        const name = (c.name || '').toLowerCase();
        const code = (c.customer_code || '').toLowerCase();
        const phone = (c.phone || '').toLowerCase();
        const company = (c.company || c.institution?.name || '').toLowerCase();
        const nik = (c.nik || c.document_number || '').toLowerCase();
        return name.includes(q) || code.includes(q) || phone.includes(q) || company.includes(q) || nik.includes(q);
    });
});

const hasResults = computed(() => {
    return filteredLeads.value.length > 0 || filteredCustomers.value.length > 0;
});

const selectedProduct = computed(() => {
    return products.value.find(prod => String(prod.id) === String(form.value.product_id)) || null;
});

function onProductChange() {
    if (form.value.product_id) {
        const p = products.value.find(prod => String(prod.id) === String(form.value.product_id));
        if (p) {
            // Nominal transfer customer: Harga Asli Katalog + 10% Komisi Sales
            form.value.amount = Math.round(Number(p.price || 0) * 1.10);
        }
        if (form.value.training_schedule_id) {
            const cur = schedules.value.find(s => String(s.id) === String(form.value.training_schedule_id));
            if (cur && String(cur.product_id) !== String(form.value.product_id)) {
                form.value.training_schedule_id = '';
            }
        }
    }
}

function formatRupiahInput(val) {
    if (val === null || val === undefined || val === '') return '';
    const num = Number(String(val).replace(/\D/g, ''));
    if (isNaN(num)) return '';
    return num.toLocaleString('id-ID');
}

function handleAmountInput(e) {
    const raw = e.target.value.replace(/\D/g, '');
    form.value.amount = raw ? Number(raw) : 0;
    e.target.value = raw ? Number(raw).toLocaleString('id-ID') : '';
}

function selectLead(lead) {
    selectedParticipant.value = `lead:${lead.id}`;
    form.value.lead_id = lead.id;
    form.value.customer_id = '';
    searchQuery.value = lead.name;
    isDropdownOpen.value = false;

    // Jika lead sudah memiliki preferensi produk, otomatis pilihkan
    if (lead.product_id) {
        form.value.product_id = lead.product_id;
        onProductChange();
    } else {
        form.value.product_id = '';
        form.value.amount = 0;
        form.value.training_schedule_id = '';
    }

    // Jika lead sudah pernah valid bayar, status otomatis Confirmed
    const hasValidPayment = Boolean(lead.payments && lead.payments.some(p => p.status === 'valid'));
    if (hasValidPayment) {
        form.value.status = 'confirmed';
    } else {
        form.value.status = 'registered';
    }
}

function selectCustomer(cust) {
    selectedParticipant.value = `customer:${cust.id}`;
    form.value.customer_id = cust.id;
    form.value.lead_id = '';
    searchQuery.value = cust.name;
    isDropdownOpen.value = false;

    // Pembelian baru / repeat order oleh customer terdaftar berstatus registered (Menunggu Pembayaran)
    // agar bukti transfer dapat diinput oleh sales di menu Kirim Bukti Bayar
    form.value.status = 'registered';
}

function clearSelection() {
    selectedParticipant.value = '';
    form.value.lead_id = '';
    form.value.customer_id = '';
    form.value.product_id = '';
    form.value.training_schedule_id = '';
    form.value.amount = 0;
    searchQuery.value = '';
    isDropdownOpen.value = true;
}

function handleClickOutside(event) {
    if (comboboxRef.value && !comboboxRef.value.contains(event.target)) {
        isDropdownOpen.value = false;
    }
}

async function loadDependencies() {
    try {
        const [cRes, lRes, sRes, pRes] = await Promise.all([
            api.list('customers', { per_page: 100 }),
            api.list('leads', { per_page: 100 }),
            api.list('training-schedules', { per_page: 100 }),
            api.list('products', { per_page: 100 }),
        ]);
        customers.value = cRes.data || [];
        leads.value = lRes.data || [];
        schedules.value = sRes.data || [];
        products.value = pRes.data || [];

        // Deteksi jika dibuka dari parameter URL (misal: /registrations/create?lead_id=14)
        const urlParams = new URLSearchParams(window.location.search);
        const qLeadId = urlParams.get('lead_id');
        const qCustId = urlParams.get('customer_id');

        if (qLeadId) {
            const found = leads.value.find(l => String(l.id) === String(qLeadId));
            if (found) selectLead(found);
        } else if (qCustId) {
            const found = customers.value.find(c => String(c.id) === String(qCustId));
            if (found) selectCustomer(found);
        }
    } catch (e) {
        console.error(e);
    }
}

function onScheduleChange() {
    const selected = schedules.value.find(s => s.id == form.value.training_schedule_id);
    if (selected) {
        if (selected.product_id) {
            form.value.product_id = selected.product_id;
        }
        if (selected.product && selected.product.price) {
            // Nominal transfer customer: Harga Asli Katalog + 10% Komisi Sales
            form.value.amount = Math.round(Number(selected.product.price) * 1.10);
        }
    }
}

async function save() {
    if (!canRegister.value) return;
    error.value = '';

    if (!selectedParticipant.value) {
        error.value = 'Silakan pilih atau ketik nama calon peserta.';
        return;
    }

    if (!form.value.product_id) {
        error.value = 'Silakan pilih Program Minat / Produk Pelatihan dari katalog resmi.';
        return;
    }

    loading.value = true;
    try {
        const payload = {
            product_id: form.value.product_id,
            amount: form.value.amount,
            status: form.value.status,
            notes: form.value.notes,
        };

        if (form.value.training_schedule_id) {
            payload.training_schedule_id = form.value.training_schedule_id;
        }

        if (form.value.lead_id) {
            payload.lead_id = form.value.lead_id;
        } else if (form.value.customer_id) {
            payload.customer_id = form.value.customer_id;
        }

        await api.create('registrations', payload);
        emit('navigate', '/registrations');
    } catch (err) {
        error.value = err.message || 'Gagal mendaftarkan peserta.';
    } finally {
        loading.value = false;
    }
}

onMounted(() => {
    if (!canRegister.value) {
        emit('navigate', '/registrations');
        return;
    }
    loadDependencies();
    window.addEventListener('click', handleClickOutside);
});

onBeforeUnmount(() => {
    window.removeEventListener('click', handleClickOutside);
});
</script>

<template>
    <div v-if="canRegister" class="max-w-2xl mx-auto space-y-5">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
            <div>
                <h1 class="text-xl font-bold text-gray-900">Pendaftaran Peserta ke Kelas</h1>
                <p class="text-xs text-gray-500 mt-0.5">Ketik atau pilih calon peserta (leads) / customer ke jadwal pelatihan aktif</p>
            </div>
            <button @click="emit('navigate', '/registrations')" class="self-start sm:self-auto whitespace-nowrap shrink-0 text-xs font-semibold text-gray-500 hover:text-gray-900">
                &larr; Batal & Kembali
            </button>
        </div>

        <div v-if="error" class="p-3 bg-red-50 border border-red-200 text-red-600 text-xs">{{ error }}</div>

        <form @submit.prevent="save" class="bg-white p-4 sm:p-6 border border-gray-100 shadow-xs space-y-4">
            <!-- Field Peserta (Bisa Diketik & Bisa Dipilih) -->
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">
                    Pilih Calon Peserta / Customer *
                </label>

                <div ref="comboboxRef" class="relative">
                    <div class="relative flex items-center">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>

                        <input
                            v-model="searchQuery"
                            type="text"
                            @focus="isDropdownOpen = true"
                            placeholder="Cari nama, WA, NIK, atau instansi..."
                            class="w-full pl-9 pr-14 sm:pr-20 py-2.5 border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500/20 focus:border-orange-500 outline-none bg-white transition"
                        />

                        <!-- Tombol Clear atau Chevron -->
                        <div class="absolute right-2.5 flex items-center gap-1.5">
                            <button v-if="searchQuery || selectedParticipant" type="button" @click="clearSelection"
                                class="p-1 text-gray-400 hover:text-gray-600 rounded-md hover:bg-gray-100" title="Hapus pilihan">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                            <button type="button" @click="isDropdownOpen = !isDropdownOpen" class="p-1 text-gray-400 hover:text-gray-600" title="Buka daftar">
                                <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': isDropdownOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- FLOATING DROPDOWN LIST -->
                    <div v-if="isDropdownOpen"
                        class="absolute z-30 left-0 right-0 mt-1 max-h-72 overflow-y-auto bg-white border border-gray-200 shadow-lg divide-y divide-gray-100">
                        
                        <!-- Grup Calon Peserta (Leads) -->
                        <div v-if="filteredLeads.length > 0">
                            <div class="px-3 py-1.5 bg-blue-50/70 text-[10.5px] font-bold text-blue-800 uppercase tracking-wider flex items-center justify-between sticky top-0">
                                <span>Calon Peserta (Dari Leads / Prospek)</span>
                                <span class="text-[10px] text-blue-600">({{ filteredLeads.length }} data)</span>
                            </div>
                            <div class="divide-y divide-gray-50">
                                <button
                                    v-for="l in filteredLeads"
                                    :key="'lead_' + l.id"
                                    type="button"
                                    @click="selectLead(l)"
                                    class="w-full text-left px-3.5 py-2.5 hover:bg-blue-50/50 transition flex items-center justify-between group"
                                    :class="{ 'bg-blue-50/80 font-semibold': selectedParticipant === `lead:${l.id}` }">
                                    <div class="min-w-0 pr-2">
                                        <div class="text-xs font-semibold text-gray-900 group-hover:text-blue-700 flex items-center gap-1.5">
                                            <span>{{ l.name }}</span>
                                            <span class="text-[10px] font-medium px-1.5 py-0.2 bg-blue-100 text-blue-700">Leads</span>
                                        </div>
                                        <div class="text-[11px] text-gray-500 mt-0.5 flex flex-wrap gap-x-2">
                                            <span>WA: {{ l.whatsapp || l.phone || '-' }}</span>
                                            <span>&bull;</span>
                                            <span>{{ l.company || l.institution || 'Individu' }}</span>
                                            <span v-if="l.nik">&bull; NIK: {{ l.nik }}</span>
                                        </div>
                                    </div>
                                    <span v-if="selectedParticipant === `lead:${l.id}`" class="text-blue-600 shrink-0 font-bold text-xs">Terpilih</span>
                                </button>
                            </div>
                        </div>

                        <!-- Grup Customer Terdaftar -->
                        <div v-if="filteredCustomers.length > 0">
                            <div class="px-3 py-1.5 bg-emerald-50/70 text-[10.5px] font-bold text-emerald-800 uppercase tracking-wider flex items-center justify-between sticky top-0">
                                <span>Customer Terdaftar</span>
                                <span class="text-[10px] text-emerald-600">({{ filteredCustomers.length }} data)</span>
                            </div>
                            <div class="divide-y divide-gray-50">
                                <button
                                    v-for="c in filteredCustomers"
                                    :key="'cust_' + c.id"
                                    type="button"
                                    @click="selectCustomer(c)"
                                    class="w-full text-left px-3.5 py-2.5 hover:bg-emerald-50/50 transition flex items-center justify-between group"
                                    :class="{ 'bg-emerald-50/80 font-semibold': selectedParticipant === `customer:${c.id}` }">
                                    <div class="min-w-0 pr-2">
                                        <div class="text-xs font-semibold text-gray-900 group-hover:text-emerald-700 flex items-center gap-1.5">
                                            <span>{{ c.name }}</span>
                                            <span class="text-[10px] font-mono px-1.5 py-0.2 bg-emerald-100 text-emerald-700">{{ c.customer_code || 'CUST' }}</span>
                                        </div>
                                        <div class="text-[11px] text-gray-500 mt-0.5 flex flex-wrap gap-x-2">
                                            <span>Telp: {{ c.phone || '-' }}</span>
                                            <span>&bull;</span>
                                            <span>{{ c.company || c.institution?.name || '-' }}</span>
                                        </div>
                                    </div>
                                    <span v-if="selectedParticipant === `customer:${c.id}`" class="text-emerald-600 shrink-0 font-bold text-xs">Terpilih</span>
                                </button>
                            </div>
                        </div>

                        <!-- Jika Tidak Ada Hasil Pencarian -->
                        <div v-if="!hasResults" class="p-4 text-center text-xs text-gray-400">
                            Tidak ditemukan calon peserta atau customer dengan kata kunci "{{ searchQuery }}".
                        </div>
                    </div>
                </div>
            </div>

            <!-- Preview Data Peserta Terpilih (Jika Memilih Lead) -->
            <div v-if="selectedLeadDetail" class="p-3.5 bg-blue-50/70 border border-blue-200/80 text-xs space-y-1.5">
                <div class="flex flex-wrap items-center justify-between gap-1.5">
                    <span class="font-bold text-blue-900 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                        Calon Peserta dari Leads: {{ selectedLeadDetail.name }}
                    </span>
                    <span class="text-[11px] font-mono px-2 py-0.5 bg-blue-100 text-blue-800 font-semibold">
                        {{ selectedLeadDetail.lead_code || `#${selectedLeadDetail.id}` }}
                    </span>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 pt-1 text-[11.5px] text-gray-700">
                    <div>
                        <span class="text-gray-400 block text-[10px]">WhatsApp</span>
                        <span class="font-semibold">{{ selectedLeadDetail.whatsapp || selectedLeadDetail.phone || '-' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400 block text-[10px]">Instansi</span>
                        <span class="font-semibold">{{ selectedLeadDetail.company || selectedLeadDetail.institution || 'Individu' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400 block text-[10px]">NIK (KTP)</span>
                        <span class="font-semibold font-mono">{{ selectedLeadDetail.nik || '-' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400 block text-[10px]">Program Minat</span>
                        <span v-if="selectedLeadDetail.product?.name" class="font-semibold text-emerald-700">
                            {{ selectedLeadDetail.product.name }}
                        </span>
                        <span v-else-if="form.product_id" class="font-semibold text-blue-700">
                            {{ products.find(p => p.id == form.product_id)?.name || 'Pilihan Baru' }}
                        </span>
                        <span v-else class="font-semibold text-amber-700 bg-amber-50 px-1.5 py-0.5 border border-amber-200 inline-block mt-0.5">
                            Belum Memilih (Tentukan di bawah)
                        </span>
                    </div>
                </div>
                <p class="text-[10.5px] text-blue-700 pt-1 border-t border-blue-100">
                    Pendaftaran awal berstatus <strong>Registered (Menunggu Pembayaran)</strong>. Peserta baru akan resmi masuk ke <strong>Daftar Customer</strong> setelah pembayaran terkonfirmasi (status Confirmed / Bayar Valid).
                </p>
            </div>

            <!-- Preview Data Peserta Terpilih (Jika Memilih Customer) -->
            <div v-else-if="selectedCustomerDetail" class="p-3.5 bg-emerald-50/70 border border-emerald-200/80 text-xs space-y-1.5">
                <div class="flex flex-wrap items-center justify-between gap-1.5">
                    <span class="font-bold text-emerald-900 flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        Customer Terdaftar: {{ selectedCustomerDetail.name }}
                    </span>
                    <span class="text-[11px] font-mono px-2 py-0.5 bg-emerald-100 text-emerald-800 font-semibold">
                        {{ selectedCustomerDetail.customer_code || '-' }}
                    </span>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-3 gap-2 pt-1 text-[11.5px] text-gray-700">
                    <div>
                        <span class="text-gray-400 block text-[10px]">Telepon/WA</span>
                        <span class="font-semibold">{{ selectedCustomerDetail.phone || '-' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400 block text-[10px]">Instansi</span>
                        <span class="font-semibold">{{ selectedCustomerDetail.company || selectedCustomerDetail.institution?.name || '-' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400 block text-[10px]">Status</span>
                        <span class="font-semibold text-emerald-700 uppercase">{{ selectedCustomerDetail.status || 'Active' }}</span>
                    </div>
                </div>
                <p class="text-[10.5px] text-emerald-700 pt-1 border-t border-emerald-100">
                    Pendaftaran repeat order berstatus <strong>Registered (Menunggu Pembayaran)</strong>. Data peserta akan otomatis terhubung ke menu <strong>Kirim Bukti Bayar</strong> agar sales dapat mengunggah bukti transfer.
                </p>
            </div>

            <!-- 1. PILIH PROGRAM MINAT (KATALOG RESMI DISTY AKADEMI) -->
            <div>
                <label class="block text-xs font-bold text-gray-800 mb-1">
                    Pilih Program Minat / Katalog Pelatihan *
                </label>
                <select v-model="form.product_id" @change="onProductChange" required
                    class="w-full px-3.5 py-2.5 border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500/20 bg-white font-medium text-gray-900">
                    <option value="" disabled>-- Pilih Program Minat dari Katalog --</option>
                    <option v-for="p in products" :key="p.id" :value="p.id">
                        {{ p.name }} ({{ p.code }}) &bull; Total Transfer: Rp {{ Number(Math.round((p.price || 0) * 1.10)).toLocaleString('id-ID') }} (Katalog Rp {{ Number(p.price || 0).toLocaleString('id-ID') }} + Komisi 10%)
                    </option>
                </select>
                <p class="text-[11px] text-gray-500 mt-1">
                    Pilih program pelatihan yang diminati calon peserta sesuai hasil komunikasi / follow-up via WhatsApp.
                </p>
            </div>

            <!-- Rincian Biaya & Komisi Sales Transparan -->
            <div v-if="selectedProduct" class="p-3.5 sm:p-4 bg-orange-50/70 border border-orange-200/90 space-y-2 text-xs">
                <div class="flex flex-wrap items-center justify-between gap-1.5 font-bold text-gray-900">
                    <span>Rincian Pembayaran Program: {{ selectedProduct.name }}</span>
                    <span class="text-orange-700 font-mono text-[11px] px-2 py-0.5 bg-orange-100">{{ selectedProduct.code }}</span>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 pt-1 text-[11.5px]">
                    <div class="bg-white p-3 border border-orange-100 shadow-2xs">
                        <span class="text-gray-400 block text-[10px] font-semibold uppercase tracking-wider">Harga Resmi Katalog</span>
                        <span class="font-bold text-gray-800 text-xs">Rp {{ Number(selectedProduct.price || 0).toLocaleString('id-ID') }}</span>
                    </div>
                    <div class="bg-white p-3 border border-emerald-200 shadow-2xs">
                        <span class="text-emerald-700 block text-[10px] font-bold uppercase tracking-wider">Komisi Sales Anda (10%)</span>
                        <span class="font-black text-emerald-600 text-sm">Rp {{ Number(Math.round((selectedProduct.price || 0) * 0.10)).toLocaleString('id-ID') }}</span>
                    </div>
                    <div class="bg-white p-3 border border-orange-200 shadow-2xs">
                        <span class="text-orange-700 block text-[10px] font-bold uppercase tracking-wider">Customer Wajib Transfer</span>
                        <span class="font-black text-orange-600 text-sm">Rp {{ Number(Math.round((selectedProduct.price || 0) * 1.10)).toLocaleString('id-ID') }}</span>
                    </div>
                </div>
                <p class="text-[10.5px] text-gray-600 pt-1 border-t border-orange-200/60">
                    Informasikan ke customer untuk mentransfer nominal <strong>Rp {{ Number(Math.round((selectedProduct.price || 0) * 1.10)).toLocaleString('id-ID') }}</strong>. Komisi Anda sebesar <strong>Rp {{ Number(Math.round((selectedProduct.price || 0) * 0.10)).toLocaleString('id-ID') }}</strong> otomatis dicatat dan siap dicairkan setelah pembayaran diverifikasi admin.
                </p>
            </div>

            <!-- 2. PILIH JADWAL PELATIHAN (OPSIONAL) -->
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">
                    Pilih Jadwal Pelatihan Aktif
                    <span class="text-gray-400 font-normal">(Opsional)</span>
                </label>
                <select v-model="form.training_schedule_id" @change="onScheduleChange"
                    class="w-full px-3.5 py-2.5 border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500/20 bg-white">
                    <option value="">-- Jadwal Ditentukan Nanti / Menyusul --</option>
                    <option v-for="s in availableSchedules" :key="s.id" :value="s.id">
                        {{ s.product?.name || s.product_name || 'Pelatihan' }} &bull; Tgl: {{ s.training_date ? s.training_date.substring(0, 10) : '-' }} (Trainer: {{ s.trainer?.name || s.trainer_name || '-' }})
                    </option>
                </select>
                <p v-if="availableSchedules.length === 0 && form.product_id" class="text-[11px] text-amber-600 mt-1">
                    Saat ini belum ada jadwal spesifik untuk program ini. Peserta tetap bisa didaftarkan, jadwal dapat ditentukan menyusul oleh admin/operasional.
                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Total Biaya Transfer Customer (Rp) *</label>
                    <div class="flex items-center border border-gray-200 bg-white overflow-hidden shadow-2xs focus-within:border-orange-500 focus-within:ring-2 focus-within:ring-orange-500/20 transition-all">
                        <span class="inline-flex items-center self-stretch px-3.5 bg-gray-50 text-xs font-bold text-gray-500 border-r border-gray-200 select-none">
                            Rp
                        </span>
                        <input
                            type="text"
                            :value="formatRupiahInput(form.amount)"
                            @input="handleAmountInput"
                            required
                            class="flex-1 w-full px-3.5 py-2.5 text-xs font-bold text-gray-900 outline-none border-0 focus:ring-0 bg-transparent"
                            placeholder="0"
                        />
                    </div>
                    <p class="text-[11px] text-gray-500 mt-1">
                        Nominal transfer customer adalah harga resmi katalog ditambah komisi sales 10%.
                        <span v-if="selectedProduct" class="block text-orange-700 font-medium mt-0.5">
                            Katalog Rp {{ Number(selectedProduct.price || 0).toLocaleString('id-ID') }} + Komisi 10% Rp {{ Number(Math.round((selectedProduct.price || 0) * 0.10)).toLocaleString('id-ID') }}
                        </span>
                    </p>
                </div>
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Status Pendaftaran</label>
                    <select v-model="form.status"
                        class="w-full px-3.5 py-2.5 border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500/20 bg-white">
                        <option value="registered">Registered (Terdaftar)</option>
                        <option value="confirmed">Confirmed (Dikonfirmasi)</option>
                        <option value="attended">Attended (Hadir)</option>
                        <option value="cancelled">Cancelled (Dibatalkan)</option>
                    </select>
                </div>
            </div>

            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Catatan Tambahan</label>
                <textarea v-model="form.notes" rows="2"
                    class="w-full px-3.5 py-2.5 border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500/20 outline-none" placeholder="Keterangan pendaftaran..."></textarea>
            </div>

            <div class="pt-2 flex justify-end gap-2">
                <button type="button" @click="emit('navigate', '/registrations')" class="px-4 py-2.5 rounded-xl border border-gray-200 font-semibold text-xs text-gray-600 hover:bg-gray-50">Batal</button>
                <button type="submit" :disabled="loading" class="px-5 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-bold text-xs shadow-sm disabled:opacity-50">
                    <span v-if="loading">Menyimpan...</span>
                    <span v-else>Simpan Pendaftaran</span>
                </button>
            </div>
        </form>
    </div>
</template>
