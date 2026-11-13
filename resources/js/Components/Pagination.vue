<script setup>
const props = defineProps({
    meta: { type: Object, default: () => ({ current_page: 1, last_page: 1, total: 0, from: 0, to: 0 }) },
    loading: { type: Boolean, default: false },
});

const emit = defineEmits(['change-page']);
</script>

<template>
    <div v-if="meta && meta.total > 0" class="flex flex-col sm:flex-row items-center justify-between gap-3 pt-4 border-t border-gray-100 text-xs text-gray-500">
        <div>
            Menampilkan <span class="font-bold text-gray-700">{{ meta.from || 0 }}</span> sampai
            <span class="font-bold text-gray-700">{{ meta.to || 0 }}</span> dari
            <span class="font-bold text-gray-700">{{ meta.total || 0 }}</span> data
        </div>
        <div class="flex items-center gap-1.5">
            <button
                @click="emit('change-page', meta.current_page - 1)"
                :disabled="meta.current_page <= 1 || loading"
                class="px-3 py-1.5 rounded-lg border border-gray-200 font-semibold hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed">
                Sebelumnya
            </button>
            <span class="px-2 font-semibold text-gray-700">Hal {{ meta.current_page }} dari {{ meta.last_page }}</span>
            <button
                @click="emit('change-page', meta.current_page + 1)"
                :disabled="meta.current_page >= meta.last_page || loading"
                class="px-3 py-1.5 rounded-lg border border-gray-200 font-semibold hover:bg-gray-50 disabled:opacity-40 disabled:cursor-not-allowed">
                Berikutnya
            </button>
        </div>
    </div>
</template>
