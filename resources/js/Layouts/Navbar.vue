<script setup>
import { computed, ref, onMounted, onBeforeUnmount } from 'vue';

const props = defineProps({
    user: { type: Object, default: () => ({}) },
    title: { type: String, default: 'Dashboard' },
    unreadCount: { type: Number, default: 0 },
    collapsed: { type: Boolean, default: false },
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
    <header class="bg-white border-b border-orange-100 flex items-center px-3 sm:px-4 gap-2 sm:gap-3 shrink-0" style="height: 56px;">
        <!-- Mobile hamburger button -->
        <button @click="emit('toggle-mobile')"
            type="button"
            class="lg:hidden w-9 h-9 flex items-center justify-center rounded-xl bg-orange-50 text-orange-600 hover:bg-orange-100 transition-colors -ml-1"
            aria-label="Buka Menu">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>

        <!-- Desktop collapse toggle button -->
        <button @click="emit('toggle-sidebar')"
            type="button"
            class="hidden lg:flex items-center justify-center w-8 h-8 rounded-lg text-gray-500 hover:text-orange-600 hover:bg-orange-50 transition-colors -ml-1 mr-1"
            :title="collapsed ? 'Perluas Menu' : 'Perkecil Menu'">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
            </svg>
        </button>

        <!-- Breadcrumb -->
        <div class="flex items-center gap-1.5 text-sm">
            <span class="text-gray-400 hidden sm:block">Disty Akademi</span>
            <span class="text-gray-300 hidden sm:block">/</span>
            <span class="text-gray-700 font-medium">{{ title }}</span>
        </div>

        <div class="ml-auto flex items-center gap-2">
            <span class="hidden md:block text-xs text-gray-400">{{ dateStr }}</span>

            <!-- Notifications (Sales) -->
            <div v-if="user?.role === 'sales'" id="notif-dropdown-container" class="relative">
                <button @click="notifOpen = !notifOpen"
                    type="button"
                    class="relative p-2 text-gray-500 hover:text-orange-600 hover:bg-orange-50 rounded-xl transition-colors"
                    title="Notifikasi">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    <span v-if="unreadCount > 0"
                        class="absolute top-1 right-1 flex h-4 min-w-4 px-1 items-center justify-center rounded-full bg-red-500 text-[9px] font-bold text-white ring-2 ring-white animate-pulse">
                        {{ unreadCount }}
                    </span>
                </button>

                <div v-if="notifOpen"
                    class="absolute right-0 top-full mt-2 w-80 bg-white border border-gray-200 shadow-xl py-2 z-50 overflow-hidden rounded-lg">
                    <div class="px-4 py-2 border-b border-gray-100 flex items-center justify-between">
                        <span class="font-bold text-xs text-gray-900">Notifikasi</span>
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
                    type="button"
                    class="flex items-center gap-2 px-2 py-1 rounded-lg hover:bg-gray-50 border border-transparent hover:border-gray-200 transition-colors">
                    <div class="w-7 h-7 rounded-full bg-orange-500 flex items-center justify-center text-white text-xs font-bold">
                        {{ userInitial }}
                    </div>
                    <span class="hidden sm:block text-sm text-gray-700 font-medium">{{ user?.name || 'Admin CRM' }}</span>
                    <svg class="w-3 h-3 text-gray-400 hidden sm:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <div v-if="profileOpen"
                    class="absolute right-0 top-full mt-1 w-44 bg-white border border-gray-200 shadow-lg py-1.5 z-50 rounded-lg">
                    <div class="px-3 py-2 border-b border-gray-100 mb-1">
                        <p class="text-xs font-semibold text-gray-900 truncate">{{ user?.name || 'Admin CRM' }}</p>
                        <p class="text-xs text-gray-400 truncate">{{ user?.role ? (user.role.charAt(0).toUpperCase() + user.role.slice(1)) : 'Admin' }}</p>
                    </div>
                    <button @click="emit('logout')"
                        class="w-full flex items-center gap-2 px-3 py-1.5 text-sm text-gray-600 hover:bg-red-50 hover:text-red-600 transition-colors text-left">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                        </svg>
                        Keluar
                    </button>
                </div>
            </div>
        </div>
    </header>
</template>
