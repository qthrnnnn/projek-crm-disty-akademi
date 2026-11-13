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
    <div class="bg-white p-5 rounded-2xl border border-gray-100 shadow-xs flex flex-col justify-between">
        <div>
            <p class="text-xs font-semibold text-gray-500">{{ title }}</p>
            <p class="text-2xl font-black text-gray-900 mt-2">{{ value }}</p>
        </div>
        <p v-if="subtext" class="text-[11px] font-medium mt-3" :class="subtextColor">{{ subtext }}</p>
    </div>
</template>
