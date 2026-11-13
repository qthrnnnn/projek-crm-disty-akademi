<script setup>
import { computed } from 'vue';

const props = defineProps({
    status: { type: String, default: '' },
    text: { type: String, default: '' },
});

const colorClasses = computed(() => {
    const s = (props.status || '').toLowerCase();
    if (['won', 'approved', 'verified', 'aktif', 'valid'].includes(s)) {
        return 'bg-emerald-50 text-emerald-700 border-emerald-200';
    }
    if (['pending', 'contacted', 'menunggu', 'negotiation'].includes(s)) {
        return 'bg-amber-50 text-amber-700 border-amber-200';
    }
    if (['lost', 'rejected', 'ditolak', 'inactive'].includes(s)) {
        return 'bg-red-50 text-red-700 border-red-200';
    }
    if (['active', 'new', 'baru'].includes(s)) {
        return 'bg-blue-50 text-blue-700 border-blue-200';
    }
    return 'bg-gray-50 text-gray-700 border-gray-200';
});
</script>

<template>
    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-bold border uppercase tracking-wide" :class="colorClasses">
        {{ text || status }}
    </span>
</template>
