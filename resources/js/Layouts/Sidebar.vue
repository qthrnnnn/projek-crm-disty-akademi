<script setup>
import { computed, onMounted, ref, watch } from 'vue';

const props = defineProps({
    user: { type: Object, default: () => ({}) },
    collapsed: { type: Boolean, default: false },
    mobileOpen: { type: Boolean, default: false },
    currentPath: { type: String, default: '/' },
    unreadCount: { type: Number, default: 0 },
});

const emit = defineEmits(['toggle-sidebar', 'toggle-mobile', 'navigate', 'logout']);

const desktopNavRef = ref(null);
const mobileNavRef = ref(null);

const isAdmin = computed(() => props.user?.role === 'admin');

const isItemActive = (path) => {
    const p = props.currentPath;
    return p === path || (path !== '/' && p.startsWith(path));
};

function handleNavClick(path) {
    emit('navigate', path);
    if (props.mobileOpen) {
        emit('toggle-mobile');
    }
}

function saveScroll(el) {
    if (el) {
        sessionStorage.setItem('disty_sidebar_scroll_top', String(el.scrollTop));
    }
}

function restoreScroll(el) {
    if (!el) return;
    const saved = sessionStorage.getItem('disty_sidebar_scroll_top');
    if (saved !== null) {
        el.scrollTop = parseInt(saved, 10) || 0;
    }
}

onMounted(() => {
    restoreScroll(desktopNavRef.value);
    restoreScroll(mobileNavRef.value);
});

watch(() => props.collapsed, () => {
    setTimeout(() => restoreScroll(desktopNavRef.value), 50);
});
</script>

<template>
    <!-- Desktop Sidebar -->
    <aside id="sidebar-desktop"
        class="hidden lg:flex flex-col h-full h-[100dvh] max-h-[100dvh] bg-white border-r border-gray-100 shrink-0 sidebar-transition overflow-hidden"
        :class="collapsed ? 'w-[72px]' : 'w-[260px]'">
        
        <!-- Header / Logo -->
        <div class="p-4 flex items-center shrink-0 border-b border-gray-100"
            :class="collapsed ? 'justify-center' : 'gap-3.5'">
            <div class="w-10 h-10 rounded-xl bg-orange-100 text-orange-600 flex items-center justify-center shrink-0 shadow-xs">
                <img src="/images/disty-icon.png" alt="Logo" class="w-7 h-7 rounded-lg object-contain" />
            </div>
            <div v-if="!collapsed" class="flex-1 min-w-0 leading-tight">
                <h2 class="font-bold text-[15px] text-gray-900 leading-none">Disty</h2>
                <p class="text-xs text-gray-500 font-medium mt-0.5">Akademi</p>
            </div>
        </div>

        <!-- Navigation items (Independent Vertical Scroll, Hidden Scrollbar) -->
        <nav ref="desktopNavRef"
            @scroll="saveScroll(desktopNavRef)"
            class="flex-1 min-h-0 overflow-y-auto p-3 space-y-0.5 no-scrollbar scrollbar-none overscroll-contain"
            style="scrollbar-width: none; -ms-overflow-style: none;">
            
            <a href="/" @click.prevent="handleNavClick('/')"
                class="flex items-center gap-3 py-2.5 rounded-xl text-[13px] font-semibold transition-all group relative"
                :class="[
                    collapsed ? 'justify-center px-0' : 'px-3.5',
                    isItemActive('/') ? 'bg-orange-500 text-white shadow-sm shadow-orange-500/25' : 'text-gray-600 hover:text-orange-600 hover:bg-orange-50'
                ]"
                :title="collapsed ? 'Dashboard' : ''">
                <svg class="w-[19px] h-[19px] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/></svg>
                <span v-if="!collapsed" class="truncate">Dashboard</span>
            </a>

            <!-- Section 1: Prospek & Leads -->
            <div v-if="collapsed" class="border-t border-gray-100 my-2"></div>
            <div v-else class="text-[10px] font-bold tracking-wider text-gray-400 uppercase px-3.5 pt-4 pb-1.5">
                {{ isAdmin ? 'PROSPEK & LEADS' : 'AFFILIATE & LEADS' }}
            </div>

            <a href="/leads" @click.prevent="handleNavClick('/leads')"
                class="flex items-center gap-3 py-2.5 rounded-xl text-[13px] font-semibold transition-all group relative"
                :class="[collapsed ? 'justify-center px-0' : 'px-3.5', isItemActive('/leads') ? 'bg-orange-500 text-white shadow-sm shadow-orange-500/25' : 'text-gray-600 hover:text-orange-600 hover:bg-orange-50']">
                <svg class="w-[19px] h-[19px] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span v-if="!collapsed" class="truncate">{{ isAdmin ? 'Semua Leads' : 'Leads & Prospek' }}</span>
            </a>

            <a href="/follow-ups" @click.prevent="handleNavClick('/follow-ups')"
                class="flex items-center gap-3 py-2.5 rounded-xl text-[13px] font-semibold transition-all group relative"
                :class="[collapsed ? 'justify-center px-0' : 'px-3.5', isItemActive('/follow-ups') ? 'bg-orange-500 text-white shadow-sm shadow-orange-500/25' : 'text-gray-600 hover:text-orange-600 hover:bg-orange-50']">
                <svg class="w-[19px] h-[19px] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                <span v-if="!collapsed" class="truncate">{{ isAdmin ? 'Follow-up' : 'Follow-up Saya' }}</span>
            </a>

            <a v-if="!isAdmin" href="/reminders" @click.prevent="handleNavClick('/reminders')"
                class="flex items-center gap-3 py-2.5 rounded-xl text-[13px] font-semibold transition-all group relative"
                :class="[collapsed ? 'justify-center px-0' : 'px-3.5', isItemActive('/reminders') ? 'bg-orange-500 text-white shadow-sm shadow-orange-500/25' : 'text-gray-600 hover:text-orange-600 hover:bg-orange-50']">
                <svg class="w-[19px] h-[19px] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                <span v-if="!collapsed" class="truncate">Notifikasi & Pengingat</span>
                <span v-if="!collapsed && unreadCount > 0" class="ml-auto bg-red-500 text-white text-[9px] font-bold px-1.5 py-0.5 rounded-full">{{ unreadCount }}</span>
            </a>

            <a href="/activities" @click.prevent="handleNavClick('/activities')"
                class="flex items-center gap-3 py-2.5 rounded-xl text-[13px] font-semibold transition-all group relative"
                :class="[collapsed ? 'justify-center px-0' : 'px-3.5', isItemActive('/activities') ? 'bg-orange-500 text-white shadow-sm shadow-orange-500/25' : 'text-gray-600 hover:text-orange-600 hover:bg-orange-50']">
                <svg class="w-[19px] h-[19px] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span v-if="!collapsed" class="truncate">Riwayat Aktivitas</span>
            </a>

            <!-- Section 2: Customer & Mitra (Admin) or Produk & Harga (Sales) -->
            <template v-if="isAdmin">
                <div v-if="collapsed" class="border-t border-gray-100 my-2"></div>
                <div v-else class="text-[10px] font-bold tracking-wider text-gray-400 uppercase px-3.5 pt-4 pb-1.5">CUSTOMER & MITRA</div>

                <a href="/customers" @click.prevent="handleNavClick('/customers')"
                    class="flex items-center gap-3 py-2.5 rounded-xl text-[13px] font-semibold transition-all group relative"
                    :class="[collapsed ? 'justify-center px-0' : 'px-3.5', isItemActive('/customers') ? 'bg-orange-500 text-white shadow-sm shadow-orange-500/25' : 'text-gray-600 hover:text-orange-600 hover:bg-orange-50']">
                    <svg class="w-[19px] h-[19px] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    <span v-if="!collapsed" class="truncate">Daftar Customer</span>
                </a>

                <a href="/institutions" @click.prevent="handleNavClick('/institutions')"
                    class="flex items-center gap-3 py-2.5 rounded-xl text-[13px] font-semibold transition-all group relative"
                    :class="[collapsed ? 'justify-center px-0' : 'px-3.5', isItemActive('/institutions') ? 'bg-orange-500 text-white shadow-sm shadow-orange-500/25' : 'text-gray-600 hover:text-orange-600 hover:bg-orange-50']">
                    <svg class="w-[19px] h-[19px] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/></svg>
                    <span v-if="!collapsed" class="truncate">Daftar Instansi</span>
                </a>

                <!-- Section 3: Pelatihan & Operasional (Admin) -->
                <div v-if="collapsed" class="border-t border-gray-100 my-2"></div>
                <div v-else class="text-[10px] font-bold tracking-wider text-gray-400 uppercase px-3.5 pt-4 pb-1.5">PELATIHAN & OPERASIONAL</div>

                <a href="/registrations" @click.prevent="handleNavClick('/registrations')"
                    class="flex items-center gap-3 py-2.5 rounded-xl text-[13px] font-semibold transition-all group relative"
                    :class="[collapsed ? 'justify-center px-0' : 'px-3.5', isItemActive('/registrations') ? 'bg-orange-500 text-white shadow-sm shadow-orange-500/25' : 'text-gray-600 hover:text-orange-600 hover:bg-orange-50']">
                    <svg class="w-[19px] h-[19px] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                    <span v-if="!collapsed" class="truncate">Pendaftaran Peserta</span>
                </a>

                <a href="/training-schedules" @click.prevent="handleNavClick('/training-schedules')"
                    class="flex items-center gap-3 py-2.5 rounded-xl text-[13px] font-semibold transition-all group relative"
                    :class="[collapsed ? 'justify-center px-0' : 'px-3.5', isItemActive('/training-schedules') ? 'bg-orange-500 text-white shadow-sm shadow-orange-500/25' : 'text-gray-600 hover:text-orange-600 hover:bg-orange-50']">
                    <svg class="w-[19px] h-[19px] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    <span v-if="!collapsed" class="truncate">Jadwal Pelatihan</span>
                </a>

                <a href="/trainers" @click.prevent="handleNavClick('/trainers')"
                    class="flex items-center gap-3 py-2.5 rounded-xl text-[13px] font-semibold transition-all group relative"
                    :class="[collapsed ? 'justify-center px-0' : 'px-3.5', isItemActive('/trainers') ? 'bg-orange-500 text-white shadow-sm shadow-orange-500/25' : 'text-gray-600 hover:text-orange-600 hover:bg-orange-50']">
                    <svg class="w-[19px] h-[19px] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m0 0V10"/></svg>
                    <span v-if="!collapsed" class="truncate">Daftar Trainer</span>
                </a>

                <a href="/products" @click.prevent="handleNavClick('/products')"
                    class="flex items-center gap-3 py-2.5 rounded-xl text-[13px] font-semibold transition-all group relative"
                    :class="[collapsed ? 'justify-center px-0' : 'px-3.5', isItemActive('/products') ? 'bg-orange-500 text-white shadow-sm shadow-orange-500/25' : 'text-gray-600 hover:text-orange-600 hover:bg-orange-50']">
                    <svg class="w-[19px] h-[19px] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    <span v-if="!collapsed" class="truncate">Katalog Program</span>
                </a>
            </template>

            <template v-else>
                <div v-if="collapsed" class="border-t border-gray-100 my-2"></div>
                <div v-else class="text-[10px] font-bold tracking-wider text-gray-400 uppercase px-3.5 pt-4 pb-1.5">PRODUK & HARGA</div>

                <a href="/products" @click.prevent="handleNavClick('/products')"
                    class="flex items-center gap-3 py-2.5 rounded-xl text-[13px] font-semibold transition-all group relative"
                    :class="[collapsed ? 'justify-center px-0' : 'px-3.5', isItemActive('/products') ? 'bg-orange-500 text-white shadow-sm shadow-orange-500/25' : 'text-gray-600 hover:text-orange-600 hover:bg-orange-50']">
                    <svg class="w-[19px] h-[19px] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    <span v-if="!collapsed" class="truncate">Katalog Program</span>
                </a>
            </template>

            <!-- Section 4: Keuangan & Komisi -->
            <div v-if="collapsed" class="border-t border-gray-100 my-2"></div>
            <div v-else class="text-[10px] font-bold tracking-wider text-gray-400 uppercase px-3.5 pt-4 pb-1.5">
                {{ isAdmin ? 'KEUANGAN & KOMISI' : 'TRANSAKSI & KOMISI' }}
            </div>

            <a href="/payments" @click.prevent="handleNavClick('/payments')"
                class="flex items-center gap-3 py-2.5 rounded-xl text-[13px] font-semibold transition-all group relative"
                :class="[collapsed ? 'justify-center px-0' : 'px-3.5', isItemActive('/payments') ? 'bg-orange-500 text-white shadow-sm shadow-orange-500/25' : 'text-gray-600 hover:text-orange-600 hover:bg-orange-50']">
                <svg class="w-[19px] h-[19px] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                <span v-if="!collapsed" class="truncate">{{ isAdmin ? 'Verifikasi Bayar' : 'Kirim Bukti Bayar' }}</span>
            </a>

            <a href="/commissions" @click.prevent="handleNavClick('/commissions')"
                class="flex items-center gap-3 py-2.5 rounded-xl text-[13px] font-semibold transition-all group relative"
                :class="[collapsed ? 'justify-center px-0' : 'px-3.5', isItemActive('/commissions') ? 'bg-orange-500 text-white shadow-sm shadow-orange-500/25' : 'text-gray-600 hover:text-orange-600 hover:bg-orange-50']">
                <svg class="w-[19px] h-[19px] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span v-if="!collapsed" class="truncate">{{ isAdmin ? 'Komisi Sales' : 'Komisi Saya (10%)' }}</span>
            </a>

            <!-- Section 5: Pengaturan (Admin) -->
            <template v-if="isAdmin">
                <div v-if="collapsed" class="border-t border-gray-100 my-2"></div>
                <div v-else class="text-[10px] font-bold tracking-wider text-gray-400 uppercase px-3.5 pt-4 pb-1.5">PENGATURAN (ADMIN)</div>

                <a href="/users" @click.prevent="handleNavClick('/users')"
                    class="flex items-center gap-3 py-2.5 rounded-xl text-[13px] font-semibold transition-all group relative"
                    :class="[collapsed ? 'justify-center px-0' : 'px-3.5', isItemActive('/users') ? 'bg-orange-500 text-white shadow-sm shadow-orange-500/25' : 'text-gray-600 hover:text-orange-600 hover:bg-orange-50']">
                    <svg class="w-[19px] h-[19px] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span v-if="!collapsed" class="truncate">Manajemen Pengguna</span>
                </a>

                <a href="/lead-imports" @click.prevent="handleNavClick('/lead-imports')"
                    class="flex items-center gap-3 py-2.5 rounded-xl text-[13px] font-semibold transition-all group relative"
                    :class="[collapsed ? 'justify-center px-0' : 'px-3.5', isItemActive('/lead-imports') ? 'bg-orange-500 text-white shadow-sm shadow-orange-500/25' : 'text-gray-600 hover:text-orange-600 hover:bg-orange-50']">
                    <svg class="w-[19px] h-[19px] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                    <span v-if="!collapsed" class="truncate">Import Batch CRM</span>
                </a>
            </template>
        </nav>

        <!-- Footer / Sign Out -->
        <div class="p-3 shrink-0 border-t border-gray-100">
            <button @click="emit('logout')"
                class="w-full flex items-center py-2.5 rounded-xl text-[13px] font-semibold text-gray-500 hover:text-red-600 hover:bg-red-50 transition-all"
                :class="collapsed ? 'justify-center' : 'gap-3.5 px-3.5'">
                <svg class="w-[19px] h-[19px] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                <span v-if="!collapsed">Sign Out</span>
            </button>
        </div>
    </aside>

    <!-- Mobile Sidebar Drawer & Overlay -->
    <div v-if="mobileOpen"
        class="fixed inset-0 z-40 bg-black/50 backdrop-blur-sm lg:hidden"
        @click="emit('toggle-mobile')"></div>
    <aside id="sidebar-mobile"
        class="fixed inset-y-0 left-0 z-50 w-72 max-w-[85vw] h-full h-[100dvh] max-h-[100dvh] bg-white shadow-2xl lg:hidden flex flex-col overflow-hidden transform transition-transform duration-300"
        :class="mobileOpen ? 'translate-x-0' : '-translate-x-full'">
        
        <div class="p-4 flex items-center justify-between border-b border-gray-100">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-orange-100 text-orange-600 flex items-center justify-center shrink-0">
                    <img src="/images/disty-icon.png" alt="Logo" class="w-7 h-7 rounded-lg object-contain" />
                </div>
                <div>
                    <h2 class="font-bold text-[15px] text-gray-900 leading-none">Disty</h2>
                    <p class="text-xs text-gray-500 font-medium mt-0.5">Akademi</p>
                </div>
            </div>
            <button @click="emit('toggle-mobile')" class="p-1 rounded-lg text-gray-400 hover:text-gray-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>

        <nav ref="mobileNavRef"
            @scroll="saveScroll(mobileNavRef)"
            class="flex-1 min-h-0 overflow-y-auto p-3 space-y-0.5 no-scrollbar scrollbar-none overscroll-contain"
            style="scrollbar-width: none; -ms-overflow-style: none;">
            <!-- Mobile nav items (same paths) -->
            <a href="/" @click.prevent="handleNavClick('/')"
                class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-[13px] font-semibold transition-all"
                :class="isItemActive('/') ? 'bg-orange-500 text-white shadow-sm shadow-orange-500/25' : 'text-gray-600 hover:text-orange-600 hover:bg-orange-50'">
                <span class="truncate">Dashboard</span>
            </a>
            <div class="text-[10px] font-bold tracking-wider text-gray-400 uppercase px-3.5 pt-4 pb-1.5">
                {{ isAdmin ? 'PROSPEK & LEADS' : 'AFFILIATE & LEADS' }}
            </div>
            <a href="/leads" @click.prevent="handleNavClick('/leads')"
                class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-[13px] font-semibold transition-all"
                :class="isItemActive('/leads') ? 'bg-orange-500 text-white shadow-sm' : 'text-gray-600 hover:text-orange-600 hover:bg-orange-50'">
                <span class="truncate">{{ isAdmin ? 'Semua Leads' : 'Leads & Prospek' }}</span>
            </a>
            <a href="/follow-ups" @click.prevent="handleNavClick('/follow-ups')"
                class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-[13px] font-semibold transition-all"
                :class="isItemActive('/follow-ups') ? 'bg-orange-500 text-white shadow-sm' : 'text-gray-600 hover:text-orange-600 hover:bg-orange-50'">
                <span class="truncate">{{ isAdmin ? 'Follow-up' : 'Follow-up Saya' }}</span>
            </a>
            <a href="/activities" @click.prevent="handleNavClick('/activities')"
                class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-[13px] font-semibold transition-all"
                :class="isItemActive('/activities') ? 'bg-orange-500 text-white shadow-sm' : 'text-gray-600 hover:text-orange-600 hover:bg-orange-50'">
                <span class="truncate">Riwayat Aktivitas</span>
            </a>
            <template v-if="isAdmin">
                <div class="text-[10px] font-bold tracking-wider text-gray-400 uppercase px-3.5 pt-4 pb-1.5">CUSTOMER & MITRA</div>
                <a href="/customers" @click.prevent="handleNavClick('/customers')"
                    class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-[13px] font-semibold transition-all"
                    :class="isItemActive('/customers') ? 'bg-orange-500 text-white shadow-sm' : 'text-gray-600 hover:text-orange-600 hover:bg-orange-50'">
                    <span class="truncate">Daftar Customer</span>
                </a>
                <a href="/institutions" @click.prevent="handleNavClick('/institutions')"
                    class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-[13px] font-semibold transition-all"
                    :class="isItemActive('/institutions') ? 'bg-orange-500 text-white shadow-sm' : 'text-gray-600 hover:text-orange-600 hover:bg-orange-50'">
                    <span class="truncate">Daftar Instansi</span>
                </a>
                <div class="text-[10px] font-bold tracking-wider text-gray-400 uppercase px-3.5 pt-4 pb-1.5">PELATIHAN & OPERASIONAL</div>
                <a href="/registrations" @click.prevent="handleNavClick('/registrations')"
                    class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-[13px] font-semibold transition-all"
                    :class="isItemActive('/registrations') ? 'bg-orange-500 text-white shadow-sm' : 'text-gray-600 hover:text-orange-600 hover:bg-orange-50'">
                    <span class="truncate">Pendaftaran Peserta</span>
                </a>
                <a href="/training-schedules" @click.prevent="handleNavClick('/training-schedules')"
                    class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-[13px] font-semibold transition-all"
                    :class="isItemActive('/training-schedules') ? 'bg-orange-500 text-white shadow-sm' : 'text-gray-600 hover:text-orange-600 hover:bg-orange-50'">
                    <span class="truncate">Jadwal Pelatihan</span>
                </a>
                <a href="/trainers" @click.prevent="handleNavClick('/trainers')"
                    class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-[13px] font-semibold transition-all"
                    :class="isItemActive('/trainers') ? 'bg-orange-500 text-white shadow-sm' : 'text-gray-600 hover:text-orange-600 hover:bg-orange-50'">
                    <span class="truncate">Daftar Trainer</span>
                </a>
            </template>
            <div class="text-[10px] font-bold tracking-wider text-gray-400 uppercase px-3.5 pt-4 pb-1.5">
                {{ isAdmin ? 'KEUANGAN & KOMISI' : 'TRANSAKSI & KOMISI' }}
            </div>
            <a href="/payments" @click.prevent="handleNavClick('/payments')"
                class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-[13px] font-semibold transition-all"
                :class="isItemActive('/payments') ? 'bg-orange-500 text-white shadow-sm' : 'text-gray-600 hover:text-orange-600 hover:bg-orange-50'">
                <span class="truncate">{{ isAdmin ? 'Verifikasi Bayar' : 'Kirim Bukti Bayar' }}</span>
            </a>
            <a href="/commissions" @click.prevent="handleNavClick('/commissions')"
                class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-[13px] font-semibold transition-all"
                :class="isItemActive('/commissions') ? 'bg-orange-500 text-white shadow-sm' : 'text-gray-600 hover:text-orange-600 hover:bg-orange-50'">
                <span class="truncate">{{ isAdmin ? 'Komisi Sales' : 'Komisi Saya' }}</span>
            </a>
            <template v-if="isAdmin">
                <div class="text-[10px] font-bold tracking-wider text-gray-400 uppercase px-3.5 pt-4 pb-1.5">PENGATURAN</div>
                <a href="/users" @click.prevent="handleNavClick('/users')"
                    class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-[13px] font-semibold transition-all"
                    :class="isItemActive('/users') ? 'bg-orange-500 text-white shadow-sm' : 'text-gray-600 hover:text-orange-600 hover:bg-orange-50'">
                    <span class="truncate">Manajemen Pengguna</span>
                </a>
                <a href="/lead-imports" @click.prevent="handleNavClick('/lead-imports')"
                    class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-[13px] font-semibold transition-all"
                    :class="isItemActive('/lead-imports') ? 'bg-orange-500 text-white shadow-sm' : 'text-gray-600 hover:text-orange-600 hover:bg-orange-50'">
                    <span class="truncate">Import Batch CRM</span>
                </a>
            </template>
        </nav>

        <div class="p-3 shrink-0 border-t border-gray-100">
            <button @click="emit('logout')"
                class="w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-[13px] font-semibold text-gray-500 hover:text-red-600 hover:bg-red-50 transition-all">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                <span>Sign Out</span>
            </button>
        </div>
    </aside>
</template>
