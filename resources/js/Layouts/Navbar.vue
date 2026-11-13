<script setup>
import { computed, ref, onMounted, onBeforeUnmount } from 'vue';

const props = defineProps({
    user: { type: Object, default: () => ({}) },
    title: { type: String, default: 'Dashboard' },
    unreadCount: { type: Number, default: 0 },
});

const emit = defineEmits(['toggle-sidebar', 'toggle-mobile', 'logout', 'navigate']);

const profileOpen = ref(false);
const notifOpen = ref(false);

const dateStr = computed(() => {
    return new Date().toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
});

const userInitial = computed(() => {
    return ((props.user?.name || 'A').charAt(0)).toUpperCase();
});

function closeDropdowns(e) {
    if (!e.target.closest('#profile-dropdown-container')) {
        profileOpen.value = false;
    }
    if (!e.target.closest('#notif-dropdown-container')) {
        notifOpen.value = false;
    }
}

onMounted(() => {
    document.addEventListener('click', closeDropdowns);
});

onBeforeUnmount(() => {
    document.removeEventListener('click', closeDropdowns);
});
</script>

<template>
    <header class="h-14 bg-white border-b border-gray-100 px-4 flex items-center justify-between sticky top-0 z-30 shrink-0">
        <!-- Left: Hamburger Toggles + Disty Akademi Title -->
        <div class="flex items-center gap-3">
            <button @click="emit('toggle-mobile')"
                class="lg:hidden p-1.5 rounded-lg text-gray-900 hover:bg-gray-100 transition-colors"
                title="Buka Menu">
                <svg class="w-5 h-5 text-gray-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            <button @click="emit('toggle-sidebar')"
                class="hidden lg:flex p-1.5 rounded-lg text-gray-900 hover:bg-gray-100 transition-colors"
                title="Toggle Sidebar">
                <svg class="w-5 h-5 text-gray-900" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            <div class="text-xs flex items-center gap-3">
                <span class="text-gray-500 font-medium">Disty Akademi</span>
                <span class="text-gray-900 font-bold">{{ title }}</span>
            </div>
        </div>

        <!-- Right: Date + Notifications + User Menu -->
        <div class="flex items-center gap-2">
            <span class="hidden sm:inline text-[11px] text-gray-400 font-medium">{{ dateStr }}</span>

            <!-- Notifications (Sales) -->
            <div v-if="user?.role === 'sales'" id="notif-dropdown-container" class="relative">
                <button @click="notifOpen = !notifOpen"
                    class="relative p-2 rounded-lg text-gray-400 hover:text-orange-600 hover:bg-orange-50 transition-colors">
                    <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    <span v-if="unreadCount > 0"
                        class="absolute -top-0.5 -right-0.5 bg-red-500 text-white text-[8px] font-bold w-4 h-4 rounded-full flex items-center justify-center animate-pulse">
                        {{ unreadCount }}
                    </span>
                </button>

                <div v-if="notifOpen"
                    class="absolute right-0 mt-2 w-80 bg-white border border-gray-200 rounded-2xl shadow-xl z-50 overflow-hidden">
                    <div class="p-3 border-b border-gray-100 flex items-center justify-between">
                        <span class="text-xs font-bold text-gray-900">Notifikasi</span>
                        <a href="/reminders" @click.prevent="emit('navigate', '/reminders'); notifOpen = false;" class="text-[10px] font-semibold text-orange-600 hover:text-orange-700">Lihat Semua</a>
                    </div>
                    <div class="p-4 text-center text-xs text-gray-400">
                        Tidak ada pengingat baru.
                    </div>
                </div>
            </div>

            <!-- Profile Dropdown -->
            <div id="profile-dropdown-container" class="relative">
                <button @click="profileOpen = !profileOpen"
                    class="flex items-center gap-2 px-2 py-1 rounded-xl hover:bg-gray-50 transition-colors">
                    <div class="w-7 h-7 rounded-full bg-orange-500 text-white flex items-center justify-center text-xs font-bold shadow-xs">
                        {{ userInitial }}
                    </div>
                    <span class="text-xs font-bold text-gray-900 hidden sm:inline">{{ user?.name || 'Admin CRM' }}</span>
                </button>

                <div v-if="profileOpen"
                    class="absolute right-0 mt-1 w-44 bg-white border border-gray-200 rounded-xl shadow-lg z-50 py-1">
                    <div class="px-3 py-2 border-b border-gray-100">
                        <p class="text-xs font-bold text-gray-900">{{ user?.name || 'User' }}</p>
                        <p class="text-[10px] text-gray-400">{{ user?.email || '' }}</p>
                    </div>
                    <button @click="emit('logout')"
                        class="w-full text-left px-3 py-2 text-xs text-red-600 hover:bg-red-50 font-semibold flex items-center gap-2">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        Keluar
                    </button>
                </div>
            </div>
        </div>
    </header>
</template>
