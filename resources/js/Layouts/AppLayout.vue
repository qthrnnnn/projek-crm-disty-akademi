<script setup>
import { ref } from 'vue';
import Sidebar from './Sidebar.vue';
import Navbar from './Navbar.vue';

const props = defineProps({
    user: { type: Object, default: () => ({}) },
    title: { type: String, default: 'Dashboard' },
    currentPath: { type: String, default: '/' },
    unreadCount: { type: Number, default: 0 },
});

const emit = defineEmits(['navigate', 'logout']);

const collapsed = ref(localStorage.getItem('sidebar-collapsed') === '1');
const mobileOpen = ref(false);

function toggleSidebar() {
    collapsed.value = !collapsed.value;
    localStorage.setItem('sidebar-collapsed', collapsed.value ? '1' : '0');
}

function toggleMobile() {
    mobileOpen.value = !mobileOpen.value;
}

function handleNavigate(path) {
    emit('navigate', path);
    // Scroll only the main content back to top
    const mainEl = document.getElementById('main-content');
    if (mainEl) {
        mainEl.scrollTop = 0;
    }
}
</script>

<template>
    <div class="h-screen h-[100dvh] max-h-[100dvh] flex bg-gray-100 text-gray-900 overflow-hidden font-sans">
        <!-- Sidebar (Desktop & Mobile) -->
        <Sidebar
            :user="user"
            :collapsed="collapsed"
            :mobile-open="mobileOpen"
            :current-path="currentPath"
            :unread-count="unreadCount"
            @toggle-sidebar="toggleSidebar"
            @toggle-mobile="toggleMobile"
            @navigate="handleNavigate"
            @logout="emit('logout')"
        />

        <!-- Main Content Area (Independent Vertical Scroll) -->
        <div class="flex-1 flex flex-col min-w-0 min-h-0 h-full h-[100dvh] max-h-[100dvh] overflow-hidden">
            <Navbar
                :user="user"
                :title="title"
                :unread-count="unreadCount"
                :collapsed="collapsed"
                @toggle-sidebar="toggleSidebar"
                @toggle-mobile="toggleMobile"
                @navigate="handleNavigate"
                @logout="emit('logout')"
            />

            <main id="main-content"
                class="flex-1 min-h-0 overflow-y-auto p-5 scrollbar-thin overscroll-contain bg-gray-100">
                <slot />
            </main>
        </div>
    </div>
</template>
