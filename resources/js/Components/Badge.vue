<script setup>
import { computed } from 'vue';

const props = defineProps({
    status: { type: String, default: '' },
    text: { type: String, default: '' },
});

const labelMap = {
    new: 'Baru',
    baru: 'Baru',
    contacted: 'Dikontak',
    qualified: 'Aktif (Merespon)',
    active: 'Aktif (Merespon)',
    aktif: 'Aktif',
    proposal: 'Negosiasi',
    negotiation: 'Negosiasi',
    won: 'Berhasil (Closing)',
    closing: 'Closing',
    lost: 'Ditolak',
    ditolak: 'Ditolak',
    inactive: 'Tidak Aktif',
    tidak_aktif: 'Tidak Aktif',
    invalid: 'Tidak Valid',
    pending: 'Pending',
    approved: 'Siap Ditransfer',
    paid: 'Lunas Ditransfer',
    completed: 'Completed',
    cancelled: 'Cancelled',
    registered: 'REGISTERED',
    confirmed: 'CONFIRMED',
    attended: 'ATTENDED',
    valid: 'Valid / Diterima',
    recheck: 'Perlu Cek Ulang',
};

const displayLabel = computed(() => {
    if (props.text) return props.text;
    const s = (props.status || '').toLowerCase();
    return labelMap[s] || props.status;
});

const colorClasses = computed(() => {
    const s = (props.status || '').toLowerCase();
    if (['won', 'confirmed', 'completed', 'valid', 'aktif', 'active_customer', 'paid', 'lunas', 'closing'].includes(s)) {
        return 'bg-emerald-50 text-emerald-700 border border-emerald-200';
    }
    if (s === 'active' && !props.text) {
        return 'bg-cyan-50 text-cyan-700 border border-cyan-200';
    }
    if (['pending', 'contacted', 'proposal', 'negotiation', 'menunggu'].includes(s)) {
        return 'bg-amber-50 text-amber-700 border border-amber-200';
    }
    if (['lost', 'rejected', 'ditolak', 'invalid'].includes(s)) {
        return 'bg-rose-50 text-rose-700 border border-rose-200';
    }
    if (['registered', 'recheck', 'dikonfirmasi', 'approved'].includes(s)) {
        return 'bg-blue-50 text-blue-700 border border-blue-200';
    }
    if (['new', 'baru'].includes(s)) {
        return 'bg-slate-100 text-slate-700 border border-slate-200';
    }
    if (['inactive', 'cancelled', 'tidak_aktif'].includes(s)) {
        return 'bg-gray-100 text-gray-500 border border-gray-200';
    }
    return 'bg-gray-50 text-gray-700 border border-gray-200';
});
</script>

<template>
    <span class="inline-flex items-center px-2 py-0.5 text-[10.5px] font-medium border" :class="colorClasses">
        {{ displayLabel }}
    </span>
</template>
