<script setup>
const props = defineProps({
    title: { type: String, required: true },
    value: { type: [String, Number], default: 0 },
    subtext: { type: String, default: '' },
    subtextColor: { type: String, default: 'text-gray-400' },
    trend: { type: String, default: '' },
});
</script>

<template>
    <div class="bg-white border border-gray-100 p-3 sm:p-4 h-full min-w-0 flex flex-col justify-between shadow-2xs">
        <div class="min-w-0">
            <p class="text-[11px] sm:text-xs text-gray-400 mb-1 truncate">{{ title }}</p>
            <p class="text-2xl sm:text-3xl font-bold text-gray-900 leading-tight">{{ value }}</p>
        </div>
        <p v-if="subtext" class="mt-2 text-[10.5px] sm:text-xs leading-snug" :class="subtextColor">{{ subtext }}</p>
    </div>
</template>
