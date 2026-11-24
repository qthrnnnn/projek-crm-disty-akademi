<script setup>
import { computed, onMounted, onUnmounted, ref } from 'vue';
import { api } from '../../services/api';
import Pagination from '../../Components/Pagination.vue';

const props = defineProps({
    user: { type: Object, default: () => ({}) },
});

const emit = defineEmits(['navigate']);

const commissions = ref([]);
const salesUsers = ref([]);
const pagination = ref({ current_page: 1, last_page: 1, total: 0 });
const statusFilter = ref('');
const userFilter = ref('');
const dateFilter = ref('');
const search = ref('');
const loading = ref(true);
const processingId = ref(null);
const feedbackMessage = ref(null);

// Print Modal state
const showPrintModal = ref(false);
const printFilterStatus = ref(''); // default ke Semua Status agar data historis langsung tampil
const printFilterSales = ref('');
const printFilterDate = ref(''); // Filter tanggal transfer / komisi (YYYY-MM-DD)
const allCommissionsForPrint = ref([]);
const loadingPrintData = ref(false);

// Searchable Sales Filter in Print Modal (Bisa diketik & dicari)
const salesSearchQuery = ref('');
const isSalesDropdownOpen = ref(false);
const salesDropdownRef = ref(null);

// Bank Account Modal state (for sales to add/edit their payout account)
const showBankModal = ref(false);
const savingBank = ref(false);
const currentUser = ref(props.user || {});
const bankForm = ref({
    bank_name: 'BCA',
    bank_account_number: '',
    bank_account_name: '',
});

const copiedAccountNumber = ref('');
function copyToClipboard(text) {
    if (!text) return;
    navigator.clipboard.writeText(text).then(() => {
        copiedAccountNumber.value = text;
        setTimeout(() => {
            if (copiedAccountNumber.value === text) {
                copiedAccountNumber.value = '';
            }
        }, 2000);
    }).catch(err => {
        console.error('Failed to copy text', err);
    });
}

async function loadCurrentUser() {
    try {
        const res = await api.getUser();
        const u = res.data || res;
        if (u) {
            currentUser.value = u;
            bankForm.value = {
                bank_name: u.bank_name || 'BCA',
                bank_account_number: u.bank_account_number || '',
                bank_account_name: u.bank_account_name || u.name || '',
            };
        }
    } catch (e) {
        console.error('Failed to load current user', e);
    }
}

function openBankModal() {
    bankForm.value = {
        bank_name: currentUser.value?.bank_name || 'BCA',
        bank_account_number: currentUser.value?.bank_account_number || '',
        bank_account_name: currentUser.value?.bank_account_name || currentUser.value?.name || '',
    };
    showBankModal.value = true;
}

function closeBankModal() {
    showBankModal.value = false;
}

async function submitBankForm() {
    if (!bankForm.value.bank_name || !bankForm.value.bank_account_number || !bankForm.value.bank_account_name) {
        alert('Mohon lengkapi Nama Bank, Nomor Rekening, dan Atas Nama Pemilik Rekening.');
        return;
    }
    savingBank.value = true;
    try {
        const res = await api.updateBankAccount(bankForm.value);
        const updated = res.data || res;
        if (updated) {
            currentUser.value = updated;
            api.setUser(updated);
        }
        showFeedback('success', 'Rekening bank pencairan komisi Anda berhasil disimpan.');
        closeBankModal();
        await loadCommissions(pagination.value.current_page);
    } catch (err) {
        showFeedback('error', err.message || 'Gagal menyimpan rekening bank.');
    } finally {
        savingBank.value = false;
    }
}

const summary = ref({
    total_earned: 0,
    total_pending: 0,
    total_approved: 0,
    total_paid: 0,
    total_unpaid: 0,
});

const isAdmin = computed(() => props.user?.role === 'admin');

function formatCurrency(val) {
    if (!val || isNaN(val)) return 'Rp 0';
    return 'Rp ' + Math.round(Number(val)).toLocaleString('id-ID');
}

function formatDate(dateStr) {
    if (!dateStr) return '-';
    try {
        const d = new Date(dateStr);
        return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', timeZone: 'Asia/Jakarta' });
    } catch {
        return dateStr;
    }
}

function formatDateTime(dateStr) {
    if (!dateStr) return '-';
    try {
        const d = new Date(dateStr);
        return d.toLocaleDateString('id-ID', {
            day: 'numeric',
            month: 'short',
            year: 'numeric',
            hour: '2-digit',
            minute: '2-digit',
            timeZone: 'Asia/Jakarta',
        });
    } catch {
        return dateStr;
    }
}

function showFeedback(type, text) {
    feedbackMessage.value = { type, text };
    setTimeout(() => {
        if (feedbackMessage.value?.text === text) {
            feedbackMessage.value = null;
        }
    }, 5000);
}

async function loadSalesUsers() {
    if (!isAdmin.value) return;
    try {
        const res = await api.list('users', { per_page: '100' });
        salesUsers.value = (res.data || []).filter(u => u.role === 'sales');
    } catch (e) {
        console.error('Failed to load sales users', e);
    }
}

async function loadCommissions(page = 1) {
    loading.value = true;
    try {
        const params = { page: String(page), per_page: '20' };
        if (statusFilter.value) params.status = statusFilter.value;
        if (userFilter.value) params.user_id = userFilter.value;
        if (dateFilter.value) params.date = dateFilter.value;

        const res = await api.list('commissions', params);
        let items = res.data || [];

        // Optional client-side search filter
        if (search.value && search.value.trim() !== '') {
            const q = search.value.toLowerCase().trim();
            items = items.filter(c =>
                (c.user?.name || '').toLowerCase().includes(q) ||
                (c.lead?.name || '').toLowerCase().includes(q) ||
                (c.lead?.product?.name || '').toLowerCase().includes(q) ||
                (c.notes || '').toLowerCase().includes(q)
            );
        }

        commissions.value = items;

        if (res.summary) {
            summary.value = {
                total_earned: Number(res.summary.total_earned) || 0,
                total_pending: Number(res.summary.total_pending) || 0,
                total_approved: Number(res.summary.total_approved) || 0,
                total_paid: Number(res.summary.total_paid) || 0,
                total_unpaid: Number(res.summary.total_unpaid) || 0,
            };
        } else {
            // Fallback calculation from items
            const earned = items.reduce((acc, c) => acc + (Number(c.commission_amount) || Number(c.amount) || 0), 0);
            const pending = items.filter(c => c.status === 'pending').reduce((acc, c) => acc + (Number(c.commission_amount) || Number(c.amount) || 0), 0);
            const approved = items.filter(c => c.status === 'approved').reduce((acc, c) => acc + (Number(c.commission_amount) || Number(c.amount) || 0), 0);
            const paid = items.filter(c => c.status === 'paid').reduce((acc, c) => acc + (Number(c.commission_amount) || Number(c.amount) || 0), 0);
            summary.value = {
                total_earned: earned,
                total_pending: pending,
                total_approved: approved,
                total_paid: paid,
                total_unpaid: pending + approved,
            };
        }

        pagination.value = {
            current_page: res.current_page || page,
            last_page: res.last_page || 1,
            total: res.total || items.length,
            from: res.from || 1,
            to: res.to || items.length,
        };
    } catch (err) {
        console.error('Failed to load commissions', err);
        showFeedback('error', 'Gagal memuat data komisi.');
    } finally {
        loading.value = false;
    }
}

// Transfer Modal state (Input jam bukti komisi udah di tf sesuai mutasi bank)
const showTransferModal = ref(false);
const selectedCommissionForPay = ref(null);
const transferForm = ref({
    paid_at: '',
    notes: '',
});
const savingTransfer = ref(false);

// Customer Transaction Proof Modal state
const showProofModal = ref(false);
const selectedProofCommission = ref(null);

function formatDatetimeLocal(dateOrStr) {
    const d = dateOrStr ? new Date(dateOrStr) : new Date();
    const parts = Object.fromEntries(new Intl.DateTimeFormat('en-GB', {
        timeZone: 'Asia/Jakarta',
        year: 'numeric', month: '2-digit', day: '2-digit',
        hour: '2-digit', minute: '2-digit', hourCycle: 'h23',
    }).formatToParts(d).map(({ type, value }) => [type, value]));
    return `${parts.year}-${parts.month}-${parts.day}T${parts.hour}:${parts.minute}`;
}

function openPayModal(commission) {
    selectedCommissionForPay.value = commission;
    transferForm.value = {
        paid_at: commission.paid_at ? formatDatetimeLocal(commission.paid_at) : formatDatetimeLocal(new Date()),
        notes: commission.notes || '',
    };
    showTransferModal.value = true;
}

function setNowDatetime() {
    transferForm.value.paid_at = formatDatetimeLocal(new Date());
}

function closePayModal() {
    showTransferModal.value = false;
    selectedCommissionForPay.value = null;
}

async function submitTransferCommission() {
    if (!selectedCommissionForPay.value) return;
    if (!transferForm.value.paid_at) {
        alert('Silakan pilih tanggal dan jam bukti transfer sesuai mutasi bank.');
        return;
    }

    savingTransfer.value = true;
    try {
        const paidAt = `${transferForm.value.paid_at}+07:00`;
        await api.payCommission(selectedCommissionForPay.value.id, {
            paid_at: paidAt,
            notes: transferForm.value.notes,
        });
        showFeedback('success', `Komisi #${selectedCommissionForPay.value.id} berhasil ditandai sudah ditransfer pada ${formatDateTime(paidAt)} WIB.`);
        closePayModal();
        await loadCommissions(pagination.value.current_page);
    } catch (e) {
        showFeedback('error', e.message || 'Gagal memproses pembayaran komisi.');
    } finally {
        savingTransfer.value = false;
    }
}

function openProofModal(commission) {
    selectedProofCommission.value = commission;
    showProofModal.value = true;
}

function closeProofModal() {
    showProofModal.value = false;
    selectedProofCommission.value = null;
}

function getProofUrl(commission) {
    const path = commission.payment?.proof_file || commission.lead?.payments?.[0]?.proof_file;
    if (!path) return null;
    return path.startsWith('http') ? path : `/storage/${path}`;
}

async function approveCommission(commission) {
    if (!confirm(`Setujui komisi sebesar ${formatCurrency(commission.commission_amount || commission.amount)} untuk sales ${commission.user?.name || 'Sales'}?\n\nStatus komisi akan diubah menjadi "Siap Ditransfer".`)) {
        return;
    }

    processingId.value = commission.id;
    try {
        await api.approveCommission(commission.id);
        showFeedback('success', `Komisi #${commission.id} berhasil disetujui. Siap dicairkan & ditransfer.`);
        await loadCommissions(pagination.value.current_page);
    } catch (e) {
        showFeedback('error', e.message || 'Gagal menyetujui komisi.');
    } finally {
        processingId.value = null;
    }
}

let searchTimeout = null;
function onSearchInput() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        loadCommissions(1);
    }, 250);
}

function resetFilter() {
    clearTimeout(searchTimeout);
    statusFilter.value = '';
    userFilter.value = '';
    dateFilter.value = '';
    search.value = '';
    loadCommissions(1);
}

function getDateKey(dtStr) {
    if (!dtStr) return '';
    try {
        const d = new Date(dtStr);
        if (!isNaN(d.getTime())) {
            return formatDatetimeLocal(d).slice(0, 10);
        }
    } catch (e) {}
    const match = String(dtStr).match(/^(\d{4}-\d{2}-\d{2})/);
    return match ? match[1] : '';
}

// ── Print Report Logic ──
async function openPrintModal() {
    if (dateFilter.value) {
        printFilterDate.value = dateFilter.value;
    }
    if (statusFilter.value) {
        printFilterStatus.value = statusFilter.value;
    }
    if (userFilter.value) {
        printFilterSales.value = userFilter.value;
        const found = (salesUsers.value || []).find(s => String(s.id) === String(userFilter.value));
        salesSearchQuery.value = found ? found.name : '';
    } else if (printFilterSales.value) {
        const found = (salesUsers.value || []).find(s => String(s.id) === String(printFilterSales.value));
        salesSearchQuery.value = found ? found.name : '';
    } else {
        salesSearchQuery.value = '';
    }
    isSalesDropdownOpen.value = false;
    showPrintModal.value = true;
    loadingPrintData.value = true;
    try {
        // Fetch all commissions (up to 100) for the print sheet
        const params = { per_page: '100' };
        if (!isAdmin.value && props.user?.id) {
            params.user_id = props.user.id;
        }
        const res = await api.list('commissions', params);
        allCommissionsForPrint.value = res.data || [];
    } catch (e) {
        console.error('Failed to load print data', e);
        allCommissionsForPrint.value = [...commissions.value];
    } finally {
        loadingPrintData.value = false;
    }
}

function closePrintModal() {
    showPrintModal.value = false;
    isSalesDropdownOpen.value = false;
}

// ── Searchable Sales Dropdown Logic ──
const filteredModalSalesList = computed(() => {
    const list = salesUsers.value || [];
    if (!salesSearchQuery.value || !salesSearchQuery.value.trim()) {
        return list;
    }
    const q = salesSearchQuery.value.toLowerCase().trim();
    return list.filter(s =>
        (s.name || '').toLowerCase().includes(q) ||
        (s.email || '').toLowerCase().includes(q)
    );
});

const selectedSalesObj = computed(() => {
    if (!printFilterSales.value) return null;
    return (salesUsers.value || []).find(s => String(s.id) === String(printFilterSales.value)) || null;
});

function openSalesDropdown() {
    isSalesDropdownOpen.value = true;
}

function closeSalesDropdown() {
    isSalesDropdownOpen.value = false;
    if (!printFilterSales.value) {
        salesSearchQuery.value = '';
    } else if (selectedSalesObj.value) {
        salesSearchQuery.value = selectedSalesObj.value.name;
    }
}

function toggleSalesDropdown() {
    if (isSalesDropdownOpen.value) {
        closeSalesDropdown();
    } else {
        openSalesDropdown();
    }
}

function selectSales(s) {
    if (!s) {
        printFilterSales.value = '';
        salesSearchQuery.value = '';
    } else {
        printFilterSales.value = s.id;
        salesSearchQuery.value = s.name;
    }
    isSalesDropdownOpen.value = false;
}

function selectFirstMatchingSales() {
    if (filteredModalSalesList.value.length > 0) {
        selectSales(filteredModalSalesList.value[0]);
    } else if (!salesSearchQuery.value) {
        selectSales(null);
    }
}

function clearSalesSelection() {
    printFilterSales.value = '';
    salesSearchQuery.value = '';
    isSalesDropdownOpen.value = false;
}

function handleClickOutsideSales(e) {
    if (salesDropdownRef.value && !salesDropdownRef.value.contains(e.target)) {
        closeSalesDropdown();
    }
}

const printFilterDateFormatted = computed(() => {
    if (!printFilterDate.value) return 'Semua Tanggal';
    try {
        const parts = printFilterDate.value.split('-');
        if (parts.length === 3) {
            const d = new Date(Number(parts[0]), Number(parts[1]) - 1, Number(parts[2]));
            if (!isNaN(d.getTime())) {
                return d.toLocaleDateString('id-ID', {
                    day: 'numeric',
                    month: 'long',
                    year: 'numeric',
                });
            }
        }
    } catch (e) {}
    return printFilterDate.value;
});

const selectedSalesName = computed(() => {
    if (!printFilterSales.value) return 'Semua Sales';
    const s = salesUsers.value.find(u => String(u.id) === String(printFilterSales.value));
    return s?.name || 'Sales';
});

const printFilteredItems = computed(() => {
    let items = allCommissionsForPrint.value;
    if (printFilterStatus.value) {
        items = items.filter(c => c.status === printFilterStatus.value);
    }
    if (printFilterSales.value) {
        items = items.filter(c => String(c.user_id) === String(printFilterSales.value));
    }
    if (printFilterDate.value) {
        const target = printFilterDate.value;
        items = items.filter(c => {
            const paidKey = c.paid_at ? getDateKey(c.paid_at) : '';
            const approvedKey = c.approved_at ? getDateKey(c.approved_at) : '';
            const createdKey = c.created_at ? getDateKey(c.created_at) : '';

            if (printFilterStatus.value === 'paid') {
                return paidKey === target || (!paidKey && (approvedKey === target || createdKey === target));
            }
            if (printFilterStatus.value === 'approved') {
                return approvedKey === target || (!approvedKey && createdKey === target);
            }
            return paidKey === target || approvedKey === target || createdKey === target;
        });
    }
    return items;
});

const printGrandTotal = computed(() => {
    return printFilteredItems.value.reduce((acc, c) => acc + (Number(c.commission_amount) || Number(c.amount) || 0), 0);
});

const printTotalDeal = computed(() => {
    return printFilteredItems.value.reduce((acc, c) => acc + (Number(c.deal_amount) || Number(c.lead?.deal_price) || 0), 0);
});

function printDocument() {
    const printArea = document.getElementById('print-area');
    if (!printArea) {
        window.print();
        return;
    }

    // Clean up any previously created print iframe
    const prevFrame = document.getElementById('print-iframe-isolated');
    if (prevFrame) {
        prevFrame.remove();
    }

    const iframe = document.createElement('iframe');
    iframe.id = 'print-iframe-isolated';
    iframe.style.position = 'fixed';
    iframe.style.top = '-9999px';
    iframe.style.left = '-9999px';
    iframe.style.width = '210mm';
    iframe.style.height = '297mm';
    iframe.style.border = 'none';
    iframe.style.opacity = '0';
    iframe.style.pointerEvents = 'none';
    document.body.appendChild(iframe);

    // Extract all styles and links from the parent document
    const headNodes = Array.from(document.querySelectorAll('link[rel="stylesheet"], style')).map(n => n.outerHTML).join('\n');

    const iframeDoc = iframe.contentWindow.document;
    iframeDoc.open();
    iframeDoc.write(`
        <!DOCTYPE html>
        <html lang="id">
        <head>
            <meta charset="utf-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Rekapitulasi Transfer Komisi Sales - Disty Akademi</title>
            <link rel="preconnect" href="https://fonts.googleapis.com">
            <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
            <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800;900&display=swap" rel="stylesheet">
            <script src="https://cdn.tailwindcss.com"><\/script>
            ${headNodes}
            <style>
                @page {
                    size: A4 portrait;
                    margin: 8mm 10mm 10mm 10mm;
                }
                *, *::before, *::after {
                    box-sizing: border-box !important;
                    -webkit-print-color-adjust: exact !important;
                    print-color-adjust: exact !important;
                }
                html, body {
                    width: 100% !important;
                    height: auto !important;
                    min-height: auto !important;
                    max-height: none !important;
                    overflow: visible !important;
                    margin: 0 !important;
                    padding: 0 !important;
                    background: #ffffff !important;
                    color: #111827 !important;
                    font-family: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif !important;
                    font-size: 11px !important;
                }
                #print-sheet-root {
                    width: 100% !important;
                    max-width: 100% !important;
                    margin: 0 !important;
                    padding: 0 !important;
                    background: #ffffff !important;
                }
                table {
                    width: 100% !important;
                    max-width: 100% !important;
                    border-collapse: collapse !important;
                    table-layout: fixed !important;
                }
                thead {
                    display: table-header-group !important;
                }
                tfoot {
                    display: table-footer-group !important;
                }
                tr {
                    page-break-inside: avoid !important;
                    break-inside: avoid !important;
                }
                th, td {
                    box-sizing: border-box !important;
                    vertical-align: middle !important;
                }
                .whitespace-nowrap,
                th.whitespace-nowrap,
                td.whitespace-nowrap,
                .no-break {
                    white-space: nowrap !important;
                    word-break: keep-all !important;
                    overflow-wrap: normal !important;
                    word-wrap: normal !important;
                }
                .signature-section {
                    page-break-inside: avoid !important;
                    break-inside: avoid !important;
                    margin-top: 20px !important;
                }
            </style>
        </head>
        <body class="bg-white text-gray-900 p-0 m-0">
            <div id="print-sheet-root">
                ${printArea.innerHTML}
            </div>
        </body>
        </html>
    `);
    iframeDoc.close();

    // Trigger print dialog once loaded
    setTimeout(() => {
        try {
            iframe.contentWindow.focus();
            iframe.contentWindow.print();
        } catch (err) {
            console.error('Iframe print failed, falling back to window.print', err);
            window.print();
        } finally {
            setTimeout(() => {
                iframe.remove();
            }, 3000);
        }
    }, 450);
}

const todayFormatted = computed(() => {
    return new Date().toLocaleDateString('id-ID', {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    });
});

const reportDocNumber = computed(() => {
    const now = new Date();
    const year = now.getFullYear();
    const month = String(now.getMonth() + 1).padStart(2, '0');
    return `RKB-DISTY/${year}${month}/${String(printFilteredItems.value.length).padStart(3, '0')}`;
});

onMounted(() => {
    loadCurrentUser();
    loadSalesUsers();
    loadCommissions();
    window.addEventListener('click', handleClickOutsideSales);
});

onUnmounted(() => {
    window.removeEventListener('click', handleClickOutsideSales);
});
</script>

<template>
    <div>
        <!-- Main Page Content (Hidden during Print) -->
        <div class="no-print space-y-4">
        <!-- Feedback Banner -->
        <div v-if="feedbackMessage"
            class="p-3.5 flex items-center justify-between text-xs transition-all shadow-sm"
            :class="feedbackMessage.type === 'success' ? 'bg-emerald-50 text-emerald-800 border border-emerald-200' : 'bg-rose-50 text-rose-800 border border-rose-200'">
            <div class="flex items-center gap-2">
                <svg v-if="feedbackMessage.type === 'success'" class="w-4 h-4 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <svg v-else class="w-4 h-4 text-rose-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                <span class="font-medium">{{ feedbackMessage.text }}</span>
            </div>
            <button @click="feedbackMessage = null" class="text-gray-400 hover:text-gray-600 ml-4 font-bold text-sm">&times;</button>
        </div>

        <!-- Page Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <div>
                <h1 class="text-xl font-bold text-gray-900 tracking-tight">
                    {{ isAdmin ? 'Manajemen Komisi Sales' : 'Komisi Saya (10%)' }}
                </h1>
                <p class="text-xs text-gray-500 mt-0.5">
                    {{ isAdmin ? 'Komisi 10% berbasis harga katalog resmi produk Disty Akademi, persetujuan, pencairan, dan cetak transfer bank.' : 'Komisi 10% berbasis harga katalog resmi produk Disty Akademi atas setiap pendaftaran dan closing yang Anda tangani.' }}
                </p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <button @click="loadCommissions(pagination.current_page)"
                    class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-medium text-gray-600 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors shadow-xs">
                    <svg class="w-3.5 h-3.5" :class="{ 'animate-spin': loading }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Refresh
                </button>
                <button @click="openPrintModal"
                    class="px-3.5 py-2 text-xs font-semibold text-white bg-orange-500 rounded-lg hover:bg-orange-600 transition-colors shadow-xs">
                    Cetak Laporan Transfer
                </button>
            </div>
        </div>

        <!-- Sales Bank Account Banner (Khusus Sales) -->
        <div v-if="!isAdmin" class="bg-gradient-to-r from-orange-50 via-amber-50 to-white border border-orange-200 p-3.5 sm:p-4 shadow-xs flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 sm:gap-4">
            <div class="flex items-start gap-3">
                <div class="w-10 h-10 bg-orange-500 text-white flex items-center justify-center shrink-0 shadow-sm">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                </div>
                <div class="min-w-0">
                    <div class="flex flex-wrap items-center gap-2">
                        <h3 class="text-xs font-bold uppercase tracking-wider text-orange-950">Rekening Pencairan Komisi Anda</h3>
                        <span v-if="currentUser.bank_account_number" class="inline-flex items-center whitespace-nowrap px-2 py-0.5 text-[10px] font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                            Aktif
                        </span>
                        <span v-else class="inline-flex items-center whitespace-nowrap px-2 py-0.5 text-[10px] font-semibold bg-amber-100 text-amber-800 border border-amber-200">
                            Belum Diatur
                        </span>
                    </div>
                    <div v-if="currentUser.bank_account_number" class="mt-1 flex items-center gap-2 flex-wrap text-xs">
                        <span class="font-bold text-gray-900 bg-white px-2 py-0.5 border border-orange-200 font-mono shadow-2xs">
                            {{ currentUser.bank_name }}: {{ currentUser.bank_account_number }}
                        </span>
                        <span class="text-gray-600 font-medium">a.n {{ currentUser.bank_account_name || currentUser.name }}</span>
                    </div>
                    <p v-else class="text-[11px] text-gray-500 mt-0.5">
                        Tambahkan akun rekening bank Anda agar Admin dapat mentransfer komisi closing Anda dengan cepat.
                    </p>
                </div>
            </div>
            <button @click="openBankModal" type="button"
                class="w-full sm:w-auto inline-flex items-center justify-center gap-1.5 px-3.5 py-2 text-xs font-bold text-white bg-orange-500 hover:bg-orange-600 rounded-xl transition-all shadow-xs shrink-0">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                {{ currentUser.bank_account_number ? 'Ubah Rekening Bank' : '+ Tambah Akun Bank' }}
            </button>
        </div>

        <!-- 4 Summary Metric Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
            <!-- 1. Total Komisi Diperoleh -->
            <div class="bg-white border border-gray-200/80 p-4 shadow-xs hover:border-blue-300 transition-all">
                <span class="text-[11px] font-semibold text-gray-500 uppercase tracking-wider block">
                    {{ isAdmin ? 'Total Seluruh Komisi' : 'Total Komisi Diperoleh' }}
                </span>
                <div class="mt-2">
                    <div class="text-xl font-bold text-gray-900 tracking-tight">{{ formatCurrency(summary.total_earned) }}</div>
                    <div class="text-[11px] text-gray-400 mt-0.5">Akumulasi komisi 10% closing</div>
                </div>
            </div>

            <!-- 2. Menunggu Persetujuan (Pending) -->
            <div class="bg-white border border-amber-200/80 p-4 shadow-xs hover:border-amber-400 transition-all">
                <span class="text-[11px] font-semibold text-amber-700 uppercase tracking-wider block">
                    {{ isAdmin ? 'Perlu Persetujuan' : 'Menunggu Persetujuan' }}
                </span>
                <div class="mt-2">
                    <div class="text-xl font-bold text-amber-600 tracking-tight">{{ formatCurrency(summary.total_pending) }}</div>
                    <div class="text-[11px] text-amber-600/80 mt-0.5">Menunggu verifikasi admin</div>
                </div>
            </div>

            <!-- 3. Siap Dicairkan (Approved) -->
            <div class="bg-white border border-sky-200/80 p-4 shadow-xs hover:border-sky-400 transition-all">
                <span class="text-[11px] font-semibold text-sky-700 uppercase tracking-wider block">
                    {{ isAdmin ? 'Siap Ditransfer' : 'Siap Dicairkan' }}
                </span>
                <div class="mt-2">
                    <div class="text-xl font-bold text-sky-700 tracking-tight">{{ formatCurrency(summary.total_approved) }}</div>
                    <div class="text-[11px] text-sky-600/80 mt-0.5">Disetujui, siap ditransfer</div>
                </div>
            </div>

            <!-- 4. Sudah Dibayarkan (Paid) -->
            <div class="bg-white border border-emerald-200/80 p-4 shadow-xs hover:border-emerald-400 transition-all">
                <span class="text-[11px] font-semibold text-emerald-700 uppercase tracking-wider block">
                    {{ isAdmin ? 'Total Sudah Dibayar' : 'Sudah Ditransfer' }}
                </span>
                <div class="mt-2">
                    <div class="text-xl font-bold text-emerald-600 tracking-tight">{{ formatCurrency(summary.total_paid) }}</div>
                    <div class="text-[11px] text-emerald-600/80 mt-0.5">Lunas ditransfer ke rekening</div>
                </div>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="bg-white border border-gray-200/80 p-3.5 shadow-xs">
            <form @submit.prevent="loadCommissions(1)" class="flex flex-wrap items-center gap-2.5">
                <div class="relative w-full sm:w-auto sm:flex-1 sm:min-w-[200px]">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-gray-400">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                    </div>
                    <input v-model="search" @input="onSearchInput" type="text"
                        :placeholder="isAdmin ? 'Cari nama peserta, sales, atau program...' : 'Cari nama peserta atau program...'"
                        class="w-full text-xs pl-9 pr-3 py-2 border border-gray-200 text-gray-800 placeholder-gray-400 focus:outline-none focus:border-orange-500 focus:ring-1 focus:ring-orange-500 transition-colors">
                </div>

                <select v-model="statusFilter" @change="loadCommissions(1)" class="w-full sm:w-auto text-xs border border-gray-200 px-3 py-2 text-gray-700 bg-white focus:outline-none focus:border-orange-500">
                    <option value="">Semua Status Komisi</option>
                    <option value="pending">Pending (Menunggu Persetujuan)</option>
                    <option value="approved">Disetujui (Siap Dicairkan)</option>
                    <option value="paid">Sudah Dibayar (Lunas)</option>
                </select>

                <select v-if="isAdmin" v-model="userFilter" @change="loadCommissions(1)" class="w-full sm:w-auto text-xs border border-gray-200 px-3 py-2 text-gray-700 bg-white focus:outline-none focus:border-orange-500">
                    <option value="">Semua Sales</option>
                    <option v-for="s in salesUsers" :key="s.id" :value="s.id">{{ s.name }}</option>
                </select>

                <input type="date" v-model="dateFilter" @change="loadCommissions(1)"
                    class="w-full sm:w-auto text-xs border border-gray-200 px-3 py-2 text-gray-700 bg-white focus:outline-none focus:border-orange-500 cursor-pointer"
                    title="Filter Tanggal Transaksi / Transfer" />

                <button type="submit" class="w-full sm:w-auto text-xs bg-orange-500 text-white font-medium py-2 px-3.5 rounded-lg hover:bg-orange-600 transition-colors shadow-xs">
                    Terapkan Filter
                </button>

                <button v-if="statusFilter || userFilter || dateFilter || search" @click="resetFilter" type="button"
                    class="w-full sm:w-auto text-xs text-gray-500 hover:text-gray-800 px-2 py-1 font-medium transition-colors">
                    Reset
                </button>
            </form>
        </div>

        <!-- Table Card -->
        <div class="bg-white border border-gray-200/80 overflow-hidden shadow-xs">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[750px] text-left text-xs text-gray-600">
                    <thead class="bg-gray-50/80 border-b border-gray-100 text-gray-600 font-semibold uppercase tracking-wider text-[11px]">
                        <tr>
                            <th v-if="isAdmin" class="py-3 px-4">Sales Penerima</th>
                            <th class="py-3 px-4">Peserta / Lead</th>
                            <th class="py-3 px-4">Nilai Transaksi (Deal)</th>
                            <th class="py-3 px-4 text-center">Tarif</th>
                            <th class="py-3 px-4">Nominal Komisi</th>
                            <th class="py-3 px-4">Status & Waktu</th>
                            <th class="py-3 px-4 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <tr v-if="loading">
                            <td :colspan="isAdmin ? 7 : 6" class="py-12 text-center text-gray-400">
                                <div class="inline-flex items-center gap-2">
                                    <svg class="w-4 h-4 animate-spin text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                                    </svg>
                                    Memuat data komisi sales...
                                </div>
                            </td>
                        </tr>
                        <tr v-else-if="commissions.length === 0">
                            <td :colspan="isAdmin ? 7 : 6" class="py-12 text-center text-gray-400">
                                <p v-if="search" class="font-medium text-gray-500">Tidak ada komisi ditemukan dengan kata kunci "<strong>{{ search }}</strong>".</p>
                                <p v-else class="font-medium text-gray-500">Tidak ada data komisi ditemukan.</p>
                                <p class="text-[11px] text-gray-400 mt-1">Komisi otomatis dibuat 10% setiap kali peserta menyelesaikan transaksi pembayaran valid.</p>
                            </td>
                        </tr>
                        <tr v-for="c in commissions" :key="c.id" class="hover:bg-orange-50/20 transition-colors">
                            <!-- Sales Penerima (Hanya untuk Admin) -->
                            <td v-if="isAdmin" class="py-3.5 px-4">
                                <div class="flex items-center gap-2.5">
                                    <span class="w-8 h-8 rounded-full bg-orange-100 text-orange-700 flex items-center justify-center text-xs font-bold shrink-0">
                                        {{ ((c.user?.name || '?').charAt(0)).toUpperCase() }}
                                    </span>
                                    <div>
                                        <div class="font-semibold text-gray-900 leading-tight">{{ c.user?.name || '-' }}</div>
                                        <div class="mt-1 flex items-center gap-1.5 flex-wrap">
                                            <span class="inline-flex items-center px-1.5 py-0.5 bg-gray-100 text-gray-800 font-mono text-[10px] font-bold border border-gray-200">
                                                {{ c.bank_name || c.user?.bank_name || 'Bank' }}
                                            </span>
                                            <span class="font-mono text-gray-900 font-bold select-all text-xs">
                                                {{ c.bank_account_number || c.user?.bank_account_number || c.bank_info }}
                                            </span>
                                            <button v-if="c.bank_account_number || c.user?.bank_account_number" type="button"
                                                @click.stop="copyToClipboard(c.bank_account_number || c.user?.bank_account_number)"
                                                title="Salin No. Rekening"
                                                class="px-1.5 py-0.5 rounded bg-orange-50 hover:bg-orange-100 text-orange-600 font-bold text-[10px] border border-orange-200 transition-colors">
                                                {{ copiedAccountNumber === (c.bank_account_number || c.user?.bank_account_number) ? 'Tersalin!' : 'Salin' }}
                                            </button>
                                        </div>
                                        <div v-if="c.bank_account_name || c.user?.bank_account_name" class="text-[10.5px] text-gray-500 mt-0.5">
                                            a.n {{ c.bank_account_name || c.user?.bank_account_name }}
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <!-- Peserta / Lead -->
                            <td class="py-3.5 px-4">
                                <div class="font-semibold text-gray-900">{{ c.lead?.name || 'Peserta' }}</div>
                                <div class="text-[11px] text-gray-500 flex items-center gap-1 mt-0.5">
                                    <span class="inline-block w-1.5 h-1.5 rounded-full bg-orange-400"></span>
                                    {{ c.lead?.product?.name || 'Program Pelatihan' }}
                                </div>
                                <div v-if="getProofUrl(c)" class="mt-1">
                                    <button @click="openProofModal(c)" type="button"
                                        class="inline-block text-[10.5px] font-medium text-blue-600 hover:text-blue-800 bg-blue-50/70 hover:bg-blue-100/70 px-2 py-0.5 rounded-md border border-blue-200 transition-colors">
                                        Bukti TF Transaksi
                                    </button>
                                </div>
                            </td>

                            <!-- Nilai Transaksi -->
                            <td class="py-3.5 px-4 font-medium text-gray-800">
                                <div class="font-bold text-gray-900">{{ formatCurrency(c.deal_amount || c.lead?.deal_price || ((c.commission_amount || c.amount) ? (c.commission_amount || c.amount) * 10 : 0)) }}</div>
                                <div class="text-[10.5px] text-gray-400 mt-0.5">
                                    Katalog: {{ formatCurrency(c.lead?.product?.price || (Number(c.deal_amount || 0) - Number(c.commission_amount || c.amount || 0))) }}
                                </div>
                            </td>

                            <!-- Tarif Komisi -->
                            <td class="py-3.5 px-4 text-center">
                                <span class="px-2 py-0.5 text-[11px] font-bold bg-gray-100 text-gray-700">
                                    {{ Math.round(Number(c.commission_rate || 10)) }}%
                                </span>
                            </td>

                            <!-- Nominal Komisi -->
                            <td class="py-3.5 px-4">
                                <div class="font-bold text-sm text-emerald-600">
                                    {{ formatCurrency(c.commission_amount || c.amount) }}
                                </div>
                            </td>

                            <!-- Status & Waktu -->
                            <td class="py-3.5 px-4">
                                <div class="flex flex-col gap-1">
                                    <span class="inline-block px-2 py-0.5 text-[10.5px] font-semibold border w-fit"
                                        :class="{
                                            'bg-emerald-50 text-emerald-700 border-emerald-200': c.status === 'paid',
                                            'bg-sky-50 text-sky-700 border-sky-200': c.status === 'approved',
                                            'bg-amber-50 text-amber-700 border-amber-200': c.status === 'pending'
                                        }">
                                        {{ c.status === 'paid' ? 'Lunas Ditransfer' : (c.status === 'approved' ? 'Disetujui (Siap Ditransfer)' : 'Perlu Persetujuan') }}
                                    </span>
                                    <div v-if="c.status === 'paid'" class="space-y-0.5">
                                        <div class="text-[10.5px] font-semibold text-emerald-800">
                                            Ditransfer: {{ formatDateTime(c.paid_at) }} WIB
                                        </div>
                                        <div v-if="c.notes" class="text-[10px] text-gray-500 font-mono">
                                            Ref Mutasi: {{ c.notes }}
                                        </div>
                                        <button v-if="isAdmin" @click="openPayModal(c)" type="button" class="text-[10px] text-blue-600 hover:text-blue-800 underline block text-left">
                                            Ubah Jam Mutasi
                                        </button>
                                    </div>
                                    <div v-else-if="c.status === 'approved'" class="text-[10px] text-sky-700">
                                        Disetujui: {{ formatDateTime(c.approved_at) }}
                                    </div>
                                    <div v-else class="text-[10px] text-gray-400">
                                        Diajukan: {{ formatDate(c.created_at) }}
                                    </div>
                                </div>
                            </td>

                            <!-- Aksi -->
                            <td class="py-3.5 px-4 text-right whitespace-nowrap">
                                <div v-if="isAdmin" class="inline-flex items-center gap-1.5 justify-end">
                                    <!-- Pending -> Approve button -->
                                    <button v-if="c.status === 'pending'"
                                        @click="approveCommission(c)"
                                        :disabled="processingId === c.id"
                                        class="px-3 py-1.5 text-xs font-semibold text-sky-700 bg-sky-50 hover:bg-sky-100 rounded-lg border border-sky-300 transition-colors shadow-xs disabled:opacity-50">
                                        Setujui Komisi
                                    </button>

                                    <!-- Approved -> Open Transfer Modal button -->
                                    <button v-if="c.status === 'approved'"
                                        @click="openPayModal(c)"
                                        class="px-3 py-1.5 text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg transition-colors shadow-xs">
                                        Transfer Komisi
                                    </button>

                                    <!-- Paid -> Verified indicator & Edit jam -->
                                    <div v-if="c.status === 'paid'" class="inline-flex items-center gap-1.5">
                                        <span class="inline-block text-[11px] font-semibold text-emerald-700 bg-emerald-50 px-2 py-1 border border-emerald-200">
                                            Sudah Ditransfer
                                        </span>
                                        <button @click="openPayModal(c)" type="button"
                                            class="text-xs text-blue-600 hover:text-blue-800 underline font-medium">
                                            Edit Jam
                                        </button>
                                    </div>
                                </div>

                                <div v-else class="text-right">
                                    <span v-if="c.status === 'paid'" class="text-[11px] font-semibold text-emerald-600 inline-flex items-center gap-1 bg-emerald-50 px-2 py-0.5 border border-emerald-200">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        Sudah Ditransfer
                                    </span>
                                    <span v-else-if="c.status === 'approved'" class="text-[11px] font-medium text-sky-600 bg-sky-50 px-2 py-0.5 border border-sky-200">
                                        Disetujui - Siap Ditransfer
                                    </span>
                                    <span v-else class="text-[11px] font-medium text-amber-600 bg-amber-50 px-2 py-0.5 border border-amber-200">
                                        Menunggu Review Admin
                                    </span>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="pagination.total > 0" class="p-3.5 border-t border-gray-100 flex items-center justify-between">
                <Pagination :pagination="pagination" @page="loadCommissions" />
            </div>
        </div>
        </div>

        <!-- ── MODAL TRANSFER KOMISI (INPUT JAM MUTASI BANK) ── -->
        <div v-if="showTransferModal && selectedCommissionForPay" class="no-print fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-xs flex items-start sm:items-center justify-center p-3 sm:p-4">
            <div class="bg-white max-w-lg w-full my-auto max-h-[92dvh] overflow-y-auto shadow-2xl border border-gray-100 transform transition-all animate-in fade-in zoom-in-95">
                <!-- Header -->
                <div class="p-4 sm:p-5 border-b border-gray-100 flex items-center justify-between gap-2 bg-emerald-50/50">
                    <div class="min-w-0">
                        <h3 class="text-sm font-bold text-gray-900">
                            {{ selectedCommissionForPay.status === 'paid' ? 'Ubah Waktu Transfer Komisi' : 'Konfirmasi Transfer Komisi Sales' }}
                        </h3>
                        <p class="text-[11px] text-gray-500">Catat tanggal & jam transfer sesuai mutasi bank resmi</p>
                    </div>
                    <button @click="closePayModal" class="text-gray-400 hover:text-gray-600 font-bold text-lg shrink-0">&times;</button>
                </div>

                <!-- Form Body -->
                <form @submit.prevent="submitTransferCommission" class="p-4 sm:p-5 space-y-4">
                    <!-- Info Box -->
                    <div class="bg-gray-50 border border-gray-200/80 p-3.5 space-y-2 text-xs">
                        <div class="flex justify-between items-center">
                            <span class="text-gray-500">Sales Penerima:</span>
                            <span class="font-bold text-gray-900">{{ selectedCommissionForPay.user?.name || '-' }}</span>
                        </div>

                        <!-- Highlighted Rekening Tujuan Sales -->
                        <div class="bg-amber-50/80 border border-amber-200 p-3 my-1 space-y-1">
                            <div class="text-[10px] font-bold uppercase tracking-wider text-amber-800">Rekening Tujuan Transfer:</div>
                            <div class="flex items-center justify-between gap-2 flex-wrap">
                                <span class="font-mono font-bold text-gray-900 text-xs bg-white px-2 py-1 border border-amber-200 select-all shadow-2xs">
                                    {{ selectedCommissionForPay.bank_name || selectedCommissionForPay.user?.bank_name || 'Bank' }}: {{ selectedCommissionForPay.bank_account_number || selectedCommissionForPay.user?.bank_account_number || selectedCommissionForPay.bank_info }}
                                </span>
                                <button v-if="selectedCommissionForPay.bank_account_number || selectedCommissionForPay.user?.bank_account_number" type="button"
                                    @click="copyToClipboard(selectedCommissionForPay.bank_account_number || selectedCommissionForPay.user?.bank_account_number)"
                                    class="px-2 py-1 text-[11px] font-bold bg-orange-500 hover:bg-orange-600 text-white rounded-lg shadow-2xs transition-colors">
                                    {{ copiedAccountNumber === (selectedCommissionForPay.bank_account_number || selectedCommissionForPay.user?.bank_account_number) ? 'Tersalin!' : 'Salin No. Rekening' }}
                                </button>
                            </div>
                            <div class="text-[11px] text-gray-600">
                                Atas Nama: <strong class="text-gray-800">{{ selectedCommissionForPay.bank_account_name || selectedCommissionForPay.user?.bank_account_name || selectedCommissionForPay.user?.name || '-' }}</strong>
                            </div>
                        </div>

                        <div class="flex justify-between items-center">
                            <span class="text-gray-500">Customer / Lead:</span>
                            <span class="font-medium text-gray-900">{{ selectedCommissionForPay.lead?.name || 'Peserta' }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-gray-500">Program:</span>
                            <span class="text-gray-700 font-medium">{{ selectedCommissionForPay.lead?.product?.name || '-' }}</span>
                        </div>
                        <div class="flex justify-between items-center pt-2 border-t border-gray-200">
                            <span class="text-gray-600 font-semibold">Nominal Komisi (10%):</span>
                            <span class="text-base font-black text-emerald-600">
                                {{ formatCurrency(selectedCommissionForPay.commission_amount || selectedCommissionForPay.amount) }}
                            </span>
                        </div>
                    </div>

                    <!-- Input Jam & Tanggal Mutasi -->
                    <div>
                        <div class="flex flex-wrap items-center justify-between gap-1 mb-1.5">
                            <label class="block text-xs font-semibold text-gray-700">
                                Tanggal & Jam Transfer (Sesuai Mutasi Bank) *
                            </label>
                            <button @click="setNowDatetime" type="button" class="text-[11px] text-orange-600 hover:text-orange-700 font-semibold underline">
                                Gunakan Waktu Sekarang
                            </button>
                        </div>
                        <input v-model="transferForm.paid_at" type="datetime-local" required
                            class="w-full text-xs px-3.5 py-2.5 border border-gray-300 text-gray-800 font-medium focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 bg-white shadow-xs" />
                        <p class="text-[11px] text-gray-500 mt-1">
                            Jam dan menit harus sesuai mutasi transaksi pada rekening admin saat transfer komisi dikirim ke sales.
                        </p>
                    </div>

                    <!-- Input Catatan / No Referensi Mutasi -->
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1.5">
                            Catatan / No. Referensi Mutasi (Opsional)
                        </label>
                        <input v-model="transferForm.notes" type="text"
                            placeholder="Contoh: Ref BCA 819283 / Mutasi jam 14.30 / M-Banking"
                            class="w-full text-xs px-3.5 py-2.5 border border-gray-300 text-gray-800 focus:outline-none focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500 bg-white shadow-xs" />
                    </div>

                    <!-- Footer Actions -->
                    <div class="pt-2 flex flex-wrap items-center justify-end gap-2">
                        <button @click="closePayModal" type="button"
                            class="px-4 py-2 text-xs font-medium text-gray-600 bg-white border border-gray-300 hover:bg-gray-50 rounded-xl transition-colors">
                            Batal
                        </button>
                        <button type="submit" :disabled="savingTransfer"
                            class="px-4 py-2 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl transition-colors shadow-xs disabled:opacity-50">
                            {{ savingTransfer ? 'Menyimpan...' : 'Simpan & Tandai Sudah Ditransfer' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- ── MODAL BUKTI TF TRANSAKSI CUSTOMER ── -->
        <div v-if="showProofModal && selectedProofCommission" class="no-print fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-xs flex items-start sm:items-center justify-center p-3 sm:p-4">
            <div class="bg-white max-w-lg w-full my-auto max-h-[92dvh] overflow-y-auto shadow-2xl border border-gray-100 transform transition-all animate-in fade-in zoom-in-95">
                <div class="p-4 border-b border-gray-100 flex items-center justify-between gap-2 bg-blue-50/50">
                    <div class="min-w-0">
                        <h3 class="text-sm font-bold text-gray-900">Bukti Transfer Transaksi Customer</h3>
                        <p class="text-[11px] text-gray-500">Verifikasi bukti transfer transaksi antara customer & sales</p>
                    </div>
                    <button @click="closeProofModal" class="text-gray-400 hover:text-gray-600 font-bold text-lg shrink-0">&times;</button>
                </div>
                <div class="p-4 sm:p-5 space-y-3">
                    <div class="text-xs bg-gray-50 p-3 border border-gray-200/80 space-y-1.5">
                        <div class="flex justify-between gap-2"><span class="text-gray-500 shrink-0">Nama Customer:</span> <span class="font-bold text-gray-800 text-right">{{ selectedProofCommission.lead?.name || 'Customer' }}</span></div>
                        <div class="flex justify-between gap-2"><span class="text-gray-500 shrink-0">Program:</span> <span class="font-medium text-gray-800 text-right">{{ selectedProofCommission.lead?.product?.name || '-' }}</span></div>
                        <div class="flex justify-between gap-2"><span class="text-gray-500 shrink-0">Nilai Deal / Transaksi:</span> <span class="font-bold text-gray-900 text-right">{{ formatCurrency(selectedProofCommission.deal_amount || selectedProofCommission.lead?.deal_price) }}</span></div>
                        <div class="flex justify-between gap-2"><span class="text-gray-500 shrink-0">Sales PIC:</span> <span class="font-medium text-gray-800 text-right">{{ selectedProofCommission.user?.name || '-' }}</span></div>
                    </div>

                    <div v-if="getProofUrl(selectedProofCommission)" class="border border-gray-200 overflow-hidden bg-gray-900/5 flex items-center justify-center min-h-[220px] max-h-[360px]">
                        <img :src="getProofUrl(selectedProofCommission)" alt="Bukti Transfer Customer" class="object-contain max-h-[350px] w-full" />
                    </div>
                    <div v-else class="py-10 text-center bg-gray-50 border border-dashed border-gray-300 text-gray-400 text-xs">
                        Bukti transfer transaksi belum diunggah untuk data ini.
                    </div>

                    <div class="pt-2 flex flex-wrap items-center justify-between gap-2">
                        <button @click="closeProofModal" type="button" class="px-4 py-2 text-xs font-medium text-gray-600 bg-white border border-gray-300 hover:bg-gray-50 rounded-xl">
                            Tutup
                        </button>
                        <button v-if="isAdmin && selectedProofCommission.status === 'pending'"
                            @click="approveCommission(selectedProofCommission); closeProofModal();"
                            type="button"
                            class="px-4 py-2 text-xs font-semibold text-white bg-sky-600 hover:bg-sky-700 rounded-xl shadow-xs">
                            Verifikasi Valid & Setujui Komisi
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── MODAL CETAK LAPORAN TRANSFER ── -->
        <div v-if="showPrintModal" class="print-modal-wrapper fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-xs flex items-start sm:items-center justify-center p-2 sm:p-6 print:static print:block print:bg-white print:overflow-visible print:inset-auto print:p-0">
            <div class="print-modal-container bg-white max-w-4xl w-full my-auto max-h-[92dvh] overflow-y-auto shadow-2xl flex flex-col border border-gray-200 print:static print:block print:max-h-none print:h-auto print:my-0 print:border-none print:shadow-none print:overflow-visible print:max-w-none">
                <!-- Modal Controls Header (Hidden during Print) -->
                <div class="p-3 sm:p-4 border-b border-gray-200 flex flex-wrap items-center justify-between gap-2.5 sm:gap-3 bg-gray-50/80 shrink-0 no-print">
                    <div class="min-w-0">
                        <h2 class="text-sm font-bold text-gray-900 leading-tight">Cetak Rekapitulasi Transfer Komisi Sales</h2>
                        <p class="text-[11px] text-gray-500">Pratinjau lembar instruksi transfer bank resmi Disty Akademi</p>
                    </div>

                    <!-- Filter Controls inside Modal -->
                    <div class="w-full sm:w-auto flex items-center flex-wrap gap-2">
                        <select v-model="printFilterStatus" class="w-full sm:w-auto text-xs border border-gray-300 px-2.5 py-1.5 bg-white text-gray-700 focus:outline-none focus:border-orange-500">
                            <option value="">Semua Status</option>
                            <option value="approved">Hanya Siap Dicairkan (Approved)</option>
                            <option value="paid">Hanya Sudah Ditransfer (Paid)</option>
                            <option value="pending">Hanya Pending</option>
                        </select>

                        <!-- Filter Sales (Searchable Combobox: Bisa Dicari & Diketik Langsung) -->
                        <div v-if="isAdmin" class="relative w-full sm:w-auto" ref="salesDropdownRef">
                            <div class="flex items-center border border-gray-300 bg-white px-2 py-1 text-xs focus-within:border-orange-500 transition-colors w-full sm:min-w-[150px] sm:max-w-[190px]">
                                <svg class="w-3.5 h-3.5 text-gray-400 shrink-0 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                                <input
                                    type="text"
                                    v-model="salesSearchQuery"
                                    @focus="openSalesDropdown"
                                    @click="openSalesDropdown"
                                    @keydown.enter.prevent="selectFirstMatchingSales"
                                    @keydown.esc="closeSalesDropdown"
                                    :placeholder="selectedSalesObj ? selectedSalesObj.name : 'Semua Sales (Ketik)...'"
                                    class="w-full text-xs text-gray-800 placeholder-gray-500 bg-transparent focus:outline-none"
                                />
                                <button
                                    v-if="printFilterSales || salesSearchQuery"
                                    @click.stop="clearSalesSelection"
                                    type="button"
                                    class="text-xs text-gray-400 hover:text-rose-600 px-1 font-bold shrink-0 transition-colors"
                                    title="Reset pilihan sales">
                                    &times;
                                </button>
                                <button
                                    @click.stop="toggleSalesDropdown"
                                    type="button"
                                    class="text-gray-400 hover:text-gray-600 pl-0.5 shrink-0"
                                    title="Tampilkan daftar sales">
                                    <svg class="w-3.5 h-3.5 transition-transform" :class="{ 'rotate-180': isSalesDropdownOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                    </svg>
                                </button>
                            </div>

                            <!-- Dropdown Menu List Floating -->
                            <div
                                v-if="isSalesDropdownOpen"
                                class="absolute left-0 mt-1 w-full sm:w-56 max-h-60 overflow-y-auto bg-white border border-gray-200 shadow-xl z-50 py-1 divide-y divide-gray-100">
                                <!-- Opsi Semua Sales -->
                                <div
                                    @click="selectSales(null)"
                                    class="px-3 py-2 text-xs cursor-pointer flex items-center justify-between hover:bg-orange-50 transition-colors"
                                    :class="{ 'bg-orange-50/80 font-bold text-orange-600': !printFilterSales }">
                                    <span class="font-medium text-gray-800">Semua Sales</span>
                                    <svg v-if="!printFilterSales" class="w-3.5 h-3.5 text-orange-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </div>

                                <!-- Opsi Sales Hasil Pencarian -->
                                <div class="py-0.5">
                                    <div
                                        v-if="filteredModalSalesList.length === 0"
                                        class="px-3 py-3 text-[11px] text-gray-400 text-center italic">
                                        Sales "{{ salesSearchQuery }}" tidak ditemukan
                                    </div>
                                    <div
                                        v-for="s in filteredModalSalesList"
                                        :key="s.id"
                                        @click="selectSales(s)"
                                        class="px-3 py-2 text-xs cursor-pointer flex items-center justify-between hover:bg-orange-50 transition-colors"
                                        :class="{ 'bg-orange-50/80 font-bold text-orange-600': String(printFilterSales) === String(s.id) }">
                                        <div>
                                            <div class="font-semibold text-gray-900 leading-tight">{{ s.name }}</div>
                                            <div v-if="s.email" class="text-[10px] text-gray-400">{{ s.email }}</div>
                                        </div>
                                        <svg v-if="String(printFilterSales) === String(s.id)" class="w-3.5 h-3.5 text-orange-600 shrink-0 ml-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Filter Tanggal Cetak Transfer -->
                        <div class="w-full sm:w-auto flex items-center border border-gray-300 bg-white px-2.5 py-1 gap-1.5">
                            <label class="text-[11px] font-semibold text-gray-500 whitespace-nowrap">Tanggal:</label>
                            <input type="date" v-model="printFilterDate"
                                class="flex-1 sm:flex-none text-xs text-gray-700 bg-transparent focus:outline-none cursor-pointer"
                                title="Pilih tanggal transfer / komisi yang ingin dicetak" />
                            <button v-if="printFilterDate" @click="printFilterDate = ''" type="button"
                                class="text-xs text-gray-400 hover:text-rose-600 px-1 font-bold transition-colors"
                                title="Reset filter tanggal (tampilkan semua tanggal)">
                                &times;
                            </button>
                        </div>

                        <div class="w-full sm:w-auto flex items-center gap-2">
                            <button @click="printDocument" class="flex-1 sm:flex-none px-3 py-1.5 text-xs font-semibold text-white bg-orange-500 hover:bg-orange-600 rounded-lg transition-colors shadow-xs">
                                Cetak (PDF)
                            </button>

                            <button @click="closePrintModal" class="px-3 py-1.5 text-xs font-medium text-gray-600 bg-white border border-gray-300 hover:bg-gray-100 rounded-lg transition-colors">
                                Tutup
                            </button>
                        </div>
                    </div>
                </div>

                <!-- ── PRINTABLE SHEET AREA ── -->
                <div class="flex-1 overflow-x-auto bg-gray-50 p-3 sm:p-6 print:overflow-visible print:bg-white print:p-0">
                    <p class="sm:hidden no-print text-[11px] text-gray-500 text-center mb-2">
                        Geser ke samping untuk melihat seluruh pratinjau dokumen A4 &harr;
                    </p>
                <div id="print-area" class="min-w-[760px] md:min-w-0 p-6 sm:p-10 bg-white text-gray-900 border border-gray-200 shadow-xs mx-auto print:min-w-0 print:border-none print:shadow-none print:p-0">
                    <!-- KOP SURAT RESMI -->
                    <div class="border-b-4 border-double border-gray-800 pb-4 mb-5">
                        <div class="flex items-center justify-between gap-4">
                            <div class="flex items-center gap-3">
                                <div class="w-12 h-12 bg-orange-500 text-white flex items-center justify-center font-black text-2xl shadow-sm shrink-0">
                                    D
                                </div>
                                <div>
                                    <h1 class="text-xl font-black tracking-wider text-gray-900 uppercase leading-none mb-1">DISTY AKADEMI</h1>
                                    <p class="text-[11px] font-semibold text-gray-700 tracking-wide leading-tight mb-0.5">LEMBAGA PELATIHAN KERJA & PENGEMBANGAN SDM PROFESIONAL</p>
                                    <p class="text-[10px] text-gray-500 leading-tight">Jl. Raya Rungkut Madya No. 1, Gunung Anyar, Surabaya, Jawa Timur 60294</p>
                                </div>
                            </div>
                            <div class="text-right text-[10px] text-gray-600 leading-tight border-l border-gray-300 pl-4 block shrink-0">
                                <p><span class="font-semibold text-gray-800">Email:</span> info@distyakademi.com</p>
                                <p><span class="font-semibold text-gray-800">Website:</span> www.distyakademi.com</p>
                                <p><span class="font-semibold text-gray-800">Telepon:</span> (031) 878-2233</p>
                            </div>
                        </div>
                    </div>

                    <!-- JUDUL LAPORAN & METADATA -->
                    <div class="text-center mb-5">
                        <h2 class="text-base font-black tracking-wide uppercase text-gray-900 underline underline-offset-4">
                            REKAPITULASI PEMBAYARAN KOMISI SALES (TRANSFER BANK)
                        </h2>
                        <p class="text-[11px] text-gray-600 mt-1">
                            Lampiran Bukti Instruksi Pembayaran Komisi 10% Penjualan Produk Pelatihan Resmi
                        </p>
                    </div>

                    <div class="grid grid-cols-2 gap-4 text-xs mb-5 bg-gray-50 p-3.5 border border-gray-200">
                        <div class="space-y-1">
                            <div><span class="text-gray-500 font-medium">Nomor Dokumen:</span> <span class="font-bold text-gray-900">{{ reportDocNumber }}</span></div>
                            <div><span class="text-gray-500 font-medium">Tanggal Cetak:</span> <span class="font-semibold text-gray-900">{{ todayFormatted }}</span></div>
                            <div><span class="text-gray-500 font-medium">Periode / Tanggal Transfer:</span> <span class="font-bold text-gray-900">{{ printFilterDateFormatted }}</span></div>
                            <div><span class="text-gray-500 font-medium">Dicetak Oleh:</span> <span class="font-semibold text-gray-900">{{ props.user?.name || 'Administrator CRM' }}</span></div>
                        </div>
                        <div class="space-y-1 text-right">
                            <div><span class="text-gray-500 font-medium">Status Data:</span>
                                <span class="font-bold uppercase ml-1 px-1.5 py-0.5 text-[10px]"
                                    :class="printFilterStatus === 'paid' ? 'bg-emerald-100 text-emerald-800' : (printFilterStatus === 'approved' ? 'bg-blue-100 text-blue-800' : 'bg-gray-200 text-gray-800')">
                                    {{ printFilterStatus === 'approved' ? 'Siap Dicairkan (Approved)' : (printFilterStatus === 'paid' ? 'Lunas Ditransfer (Paid)' : 'Semua Status') }}
                                </span>
                            </div>
                            <div v-if="printFilterSales"><span class="text-gray-500 font-medium">Sales PIC:</span> <span class="font-bold text-gray-900 ml-1">{{ selectedSalesName }}</span></div>
                            <div><span class="text-gray-500 font-medium">Jumlah Transaksi:</span> <span class="font-bold text-gray-900">{{ printFilteredItems.length }} Transaksi</span></div>
                            <div><span class="text-gray-500 font-medium">Total Nilai Deal:</span> <span class="font-bold text-gray-900">{{ formatCurrency(printTotalDeal) }}</span></div>
                        </div>
                    </div>

                    <!-- TABEL DATA REKAP -->
                    <div class="border border-gray-300 mb-6 print:border print:border-gray-300 print:mb-4 print:overflow-visible">
                        <table class="w-full text-left text-xs border-collapse table-fixed">
                            <thead class="bg-gray-100 text-gray-700 font-bold border-b border-gray-300 text-[11px]">
                                <tr>
                                    <th class="py-2 px-1.5 border-r border-gray-300 text-center w-[4%]">No</th>
                                    <th class="py-2 px-2 border-r border-gray-300 w-[13%]">Nama Sales</th>
                                    <th class="py-2 px-2 border-r border-gray-300 w-[16%]">Rekening Transfer</th>
                                    <th class="py-2 px-2 border-r border-gray-300 w-[14%]">Peserta (Customer)</th>
                                    <th class="py-2 px-2 border-r border-gray-300 w-[17%]">Program Pelatihan</th>
                                    <th class="py-2 px-2 border-r border-gray-300 text-right w-[14%]">Nilai Deal</th>
                                    <th class="py-2 px-2 border-r border-gray-300 text-right w-[13%]">Komisi 10%</th>
                                    <th class="py-2 px-1.5 border-r-0 text-center w-[9%]">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 text-[11px]">
                                <tr v-if="printFilteredItems.length === 0">
                                    <td colspan="8" class="py-6 text-center text-gray-400 italic">
                                        Tidak ada catatan komisi pada kriteria filter ini.
                                    </td>
                                </tr>
                                <tr v-for="(item, idx) in printFilteredItems" :key="item.id" class="hover:bg-gray-50/50">
                                    <td class="py-2 px-1.5 border-r border-gray-200 text-center font-medium">{{ idx + 1 }}</td>
                                    <td class="py-2 px-2 border-r border-gray-200 font-bold text-gray-900 break-words">{{ item.user?.name || '-' }}</td>
                                    <td class="py-2 px-2 border-r border-gray-200 font-mono text-[10px] text-gray-800 break-words">{{ item.bank_info || 'BCA 883019281 a.n ' + (item.user?.name || 'Sales') }}</td>
                                    <td class="py-2 px-2 border-r border-gray-200 font-medium text-gray-800 break-words">{{ item.lead?.name || 'Peserta' }}</td>
                                    <td class="py-2 px-2 border-r border-gray-200 text-gray-700 break-words">{{ item.lead?.product?.name || 'Pelatihan Disty Akademi' }}</td>
                                    <td class="py-2 px-2 border-r border-gray-200 text-right text-gray-700 font-medium whitespace-nowrap">{{ formatCurrency(item.deal_amount || item.lead?.deal_price) }}</td>
                                    <td class="py-2 px-2 border-r border-gray-200 text-right font-bold text-gray-900 whitespace-nowrap">{{ formatCurrency(item.commission_amount || item.amount) }}</td>
                                    <td class="py-2 px-1.5 text-center">
                                        <span class="text-[10px] font-bold uppercase block"
                                            :class="item.status === 'paid' ? 'text-emerald-700' : (item.status === 'approved' ? 'text-blue-700' : 'text-amber-700')">
                                            {{ item.status === 'paid' ? 'LUNAS' : (item.status === 'approved' ? 'APPROVED' : 'PENDING') }}
                                        </span>
                                        <div v-if="item.paid_at" class="text-[9px] text-gray-500 font-medium mt-0.5 whitespace-nowrap">
                                            {{ formatDate(item.paid_at) }}
                                        </div>
                                        <div v-else-if="item.approved_at" class="text-[9px] text-gray-400 mt-0.5 whitespace-nowrap">
                                            {{ formatDate(item.approved_at) }}
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                            <tfoot class="bg-gray-50 font-bold border-t-2 border-gray-300 text-xs text-gray-900">
                                <tr>
                                    <td colspan="5" class="py-2.5 px-3 border-r border-gray-300 text-right uppercase tracking-wider text-[11px]">
                                        TOTAL NOMINAL TRANSFER KOMISI:
                                    </td>
                                    <td class="py-2.5 px-2 border-r border-gray-300 text-right font-bold text-gray-900 text-[11px] whitespace-nowrap">
                                        {{ formatCurrency(printTotalDeal) }}
                                    </td>
                                    <td class="py-2.5 px-2 border-r border-gray-300 text-right font-black text-emerald-700 text-[11.5px] whitespace-nowrap">
                                        {{ formatCurrency(printGrandTotal) }}
                                    </td>
                                    <td class="py-2.5 px-1.5 text-center text-[10px] text-gray-600 font-semibold whitespace-nowrap">
                                        10% Net
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>

                    <!-- LEMBAR TANDA TANGAN / PENGESAHAN RESMI -->
                    <div class="mt-6 pt-2 signature-section">
                        <p class="text-right text-xs text-gray-600 mb-5">
                            Surabaya, {{ todayFormatted }}
                        </p>
                        <div class="grid grid-cols-3 gap-6 text-center text-xs">
                            <!-- Kolom 1 -->
                            <div class="flex flex-col justify-between h-24">
                                <p class="font-medium text-gray-600">Dibuat Oleh (Sales/Admin),</p>
                                <div>
                                    <p class="font-bold text-gray-900 underline">{{ props.user?.name || 'Staf Operasional' }}</p>
                                    <p class="text-[10px] text-gray-500 uppercase">{{ props.user?.role === 'admin' ? 'Administrator CRM' : 'Sales Representative' }}</p>
                                </div>
                            </div>

                            <!-- Kolom 2 -->
                            <div class="flex flex-col justify-between h-24">
                                <p class="font-medium text-gray-600">Diverifikasi (Finance & Accounting),</p>
                                <div>
                                    <p class="font-bold text-gray-900 underline">Finance & Tax Division</p>
                                    <p class="text-[10px] text-gray-500">Disty Akademi Surabaya</p>
                                </div>
                            </div>

                            <!-- Kolom 3 -->
                            <div class="flex flex-col justify-between h-24">
                                <p class="font-medium text-gray-600">Mengetahui & Menyetujui,</p>
                                <div>
                                    <p class="font-bold text-gray-900 underline">Direktur Operasional</p>
                                    <p class="text-[10px] text-gray-500">PT Sentra Vidya Utama</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                </div>

                <!-- Modal Footer (Hidden during Print) -->
                <div class="p-3 sm:p-4 border-t border-gray-200 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2.5 bg-gray-50/80 shrink-0 no-print">
                    <span class="text-[11px] sm:text-xs text-gray-500">
                        Format cetak dioptimalkan untuk kertas A4 potret atau landscape saat dicetak ke PDF/Printer.
                    </span>
                    <div class="flex items-center justify-end gap-2 shrink-0">
                        <button @click="closePrintModal" class="px-4 py-2 text-xs font-medium text-gray-600 bg-white border border-gray-300 hover:bg-gray-100 rounded-lg transition-colors">
                            Tutup
                        </button>
                        <button @click="printDocument" class="px-4 py-2 text-xs font-semibold text-white bg-orange-500 hover:bg-orange-600 rounded-lg transition-colors shadow-xs">
                            Cetak Laporan Sekarang
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- ── MODAL PENGATURAN REKENING BANK SALES ── -->
        <div v-if="showBankModal" class="no-print fixed inset-0 z-50 overflow-y-auto bg-black/60 backdrop-blur-xs flex items-start sm:items-center justify-center p-3 sm:p-4">
            <div class="bg-white max-w-md w-full my-auto max-h-[92dvh] overflow-y-auto shadow-2xl border border-gray-100 transform transition-all animate-in fade-in zoom-in-95">
                <div class="p-4 border-b border-gray-100 flex items-center justify-between gap-2 bg-orange-50/70">
                    <div class="min-w-0">
                        <h3 class="text-sm font-bold text-gray-900">Rekening Bank Pencairan Komisi</h3>
                        <p class="text-[11px] text-gray-500">Admin akan mentransfer komisi closing Anda ke nomor rekening ini</p>
                    </div>
                    <button @click="closeBankModal" class="text-gray-400 hover:text-gray-600 font-bold text-lg shrink-0">&times;</button>
                </div>
                <form @submit.prevent="submitBankForm" class="p-4 sm:p-5 space-y-4">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Nama Bank *</label>
                        <select v-model="bankForm.bank_name" required class="w-full text-xs px-3.5 py-2.5 border border-gray-300 text-gray-800 bg-white focus:outline-none focus:border-orange-500">
                            <option value="BCA">BCA (Bank Central Asia)</option>
                            <option value="Bank Mandiri">Bank Mandiri</option>
                            <option value="BNI">BNI (Bank Negara Indonesia)</option>
                            <option value="BRI">BRI (Bank Rakyat Indonesia)</option>
                            <option value="BSI">BSI (Bank Syariah Indonesia)</option>
                            <option value="CIMB Niaga">CIMB Niaga</option>
                            <option value="Bank Permata">Bank Permata</option>
                            <option value="Bank Danamon">Bank Danamon</option>
                            <option value="Bank Jatim">Bank Jatim</option>
                            <option value="Bank Jago">Bank Jago</option>
                            <option value="Seabank">Seabank</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Nomor Rekening *</label>
                        <input v-model="bankForm.bank_account_number" type="text" required placeholder="Contoh: 883019281"
                            class="w-full text-xs px-3.5 py-2.5 border border-gray-300 text-gray-800 font-mono font-medium focus:outline-none focus:border-orange-500" />
                        <p class="text-[11px] text-gray-500 mt-1">Pastikan nomor rekening aktif dan tidak salah ketik.</p>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Atas Nama Pemilik Rekening *</label>
                        <input v-model="bankForm.bank_account_name" type="text" required placeholder="Nama sesuai buku tabungan / m-banking"
                            class="w-full text-xs px-3.5 py-2.5 border border-gray-300 text-gray-800 focus:outline-none focus:border-orange-500" />
                    </div>

                    <div class="pt-2 flex items-center justify-end gap-2">
                        <button @click="closeBankModal" type="button" class="px-4 py-2 text-xs font-medium text-gray-600 bg-white border border-gray-300 hover:bg-gray-50 rounded-xl">
                            Batal
                        </button>
                        <button type="submit" :disabled="savingBank" class="px-4 py-2 text-xs font-bold text-white bg-orange-500 hover:bg-orange-600 rounded-xl shadow-xs disabled:opacity-50">
                            {{ savingBank ? 'Menyimpan...' : 'Simpan Rekening Bank' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</template>

<style>
@media print {
    @page {
        size: A4 portrait;
        margin: 8mm 10mm 10mm 10mm;
    }

    /* 1. Reset root layout containers so they never clip or scroll */
    html,
    body,
    #app,
    [class*="h-screen"],
    [class*="max-h-"],
    [class*="overflow-"],
    #main-content,
    main {
        width: 100% !important;
        height: auto !important;
        min-height: auto !important;
        max-height: none !important;
        overflow: visible !important;
        position: static !important;
        margin: 0 !important;
        padding: 0 !important;
        background: #ffffff !important;
        color: #111827 !important;
        box-shadow: none !important;
        border: none !important;
        -webkit-print-color-adjust: exact !important;
        print-color-adjust: exact !important;
    }

    /* 2. Hide all sidebar, header, nav, and .no-print elements */
    aside,
    header,
    nav,
    .no-print,
    .no-print * {
        display: none !important;
        visibility: hidden !important;
        height: 0 !important;
        margin: 0 !important;
        padding: 0 !important;
    }

    /* 3. Modal wrapper becomes a clean static block without clipping */
    .print-modal-wrapper,
    .print-modal-container {
        display: block !important;
        position: static !important;
        width: 100% !important;
        max-width: 100% !important;
        height: auto !important;
        max-height: none !important;
        overflow: visible !important;
        background: transparent !important;
        box-shadow: none !important;
        border: none !important;
        padding: 0 !important;
        margin: 0 !important;
    }

    /* 4. Document sheet in normal flow at document top */
    #print-area {
        display: block !important;
        position: static !important;
        width: 100% !important;
        max-width: 100% !important;
        margin: 0 !important;
        padding: 0 !important;
        background: #ffffff !important;
        color: #111827 !important;
        box-sizing: border-box !important;
    }

    /* 5. Table rules for perfect A4 fit */
    table {
        width: 100% !important;
        max-width: 100% !important;
        border-collapse: collapse !important;
        table-layout: fixed !important;
        page-break-inside: auto !important;
    }

    thead {
        display: table-header-group !important;
    }

    tfoot {
        display: table-footer-group !important;
    }

    tr {
        page-break-inside: avoid !important;
        break-inside: avoid !important;
    }

    th, td {
        box-sizing: border-box !important;
        vertical-align: middle !important;
    }

    .whitespace-nowrap,
    th.whitespace-nowrap,
    td.whitespace-nowrap,
    .no-break {
        white-space: nowrap !important;
        word-break: keep-all !important;
        overflow-wrap: normal !important;
        word-wrap: normal !important;
    }

    /* 6. Signature section stays intact */
    .signature-section {
        page-break-inside: avoid !important;
        break-inside: avoid !important;
        margin-top: 1.5rem !important;
    }
}
</style>
