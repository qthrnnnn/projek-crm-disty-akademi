<script setup>
const props = defineProps({
    show: { type: Boolean, default: false },
    title: { type: String, default: '' },
    maxWidth: { type: String, default: 'max-w-md' },
});

const emit = defineEmits(['close']);
</script>

<template>
    <div v-if="show" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs">
        <div class="fixed inset-0" @click="emit('close')"></div>
        <div class="relative bg-white rounded-2xl shadow-2xl w-full max-h-[90vh] overflow-y-auto border border-gray-100 z-10" :class="maxWidth">
            <div v-if="title" class="p-4 border-b border-gray-100 flex items-center justify-between">
                <h3 class="font-bold text-sm text-gray-900">{{ title }}</h3>
                <button @click="emit('close')" class="p-1 rounded-lg text-gray-400 hover:text-gray-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="p-5">
                <slot />
            </div>
        </div>
    </div>
</template>
