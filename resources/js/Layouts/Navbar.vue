<script setup>
import { computed, ref, onMounted, onBeforeUnmount } from 'vue';
import { api } from '../services/api';

const props = defineProps({
    user: { type: Object, default: () => ({}) },
    title: { type: String, default: 'Dashboard' },
    unreadCount: { type: Number, default: 0 },
    collapsed: { type: Boolean, default: false },
});

const emit = defineEmits(['toggle-sidebar', 'toggle-mobile', 'logout', 'navigate']);

const profileOpen = ref(false);
const notifOpen = ref(false);
const notifList = ref([]);
const loadingNotifs = ref(false);

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

async function loadNotifications() {
    if (props.user?.role !== 'sales') return;
    loadingNotifs.value = true;
    try {
        const res = await api.list('reminders', { per_page: 5 });
        notifList.value = res.data || [];
    } catch (e) {
        /* ignore */
    } finally {
        loadingNotifs.value = false;
    }
}

function toggleNotif() {
    notifOpen.value = !notifOpen.value;
    if (notifOpen.value) {
        loadNotifications();
    }
}

function getWaUrl(lead) {
    if (!lead) return null;
    const raw = lead.whatsapp || lead.phone;
    if (!raw) return null;
    let digits = String(raw).replace(/\D/g, '');
    if (digits.startsWith('0')) digits = '62' + digits.substring(1);
    const prog = lead.product?.name || lead.product_name || lead.target_program || 'program pelatihan';
    const text = encodeURIComponent(`Halo Kak ${lead.name}, saya dari Disty Akademi ingin menanyakan kelanjutan informasi pelatihan ${prog}. Apakah ada yang bisa kami bantu?`);
    return `https://wa.me/${digits}?text=${text}`;
}

async function markAndNavigate(item) {
    notifOpen.value = false;
    try {
        if (!item.is_read) {
            await api.request(`/reminders/${item.id}/read`, { method: 'PATCH' });
        }
    } catch (e) {
        /* ignore */
    }
    if (item.type === 'commission_paid') {
        emit('navigate', '/commissions');
    } else if (item.type === 'payment_rejected') {
        emit('navigate', item.lead_id ? `/leads/${item.lead_id}` : '/payments');
    } else if (item.lead_id) {
        emit('navigate', `/leads/${item.lead_id}`);
    } else {
        emit('navigate', '/reminders');
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
        <div class="flex items-center gap-1.5 text-sm min-w-0 flex-1">
            <span class="text-gray-400 hidden sm:block shrink-0">Disty Akademi</span>
            <span class="text-gray-300 hidden sm:block shrink-0">/</span>
            <span class="text-gray-700 font-medium truncate">{{ title }}</span>
        </div>

        <div class="ml-auto flex items-center gap-1.5 sm:gap-2 shrink-0">
            <span class="hidden md:block text-xs text-gray-400">{{ dateStr }}</span>

            <!-- Notifications (Sales) -->
            <div v-if="user?.role === 'sales'" id="notif-dropdown-container" class="relative">
                <button @click="toggleNotif"
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
                    class="fixed left-3 right-3 top-14 sm:absolute sm:left-auto sm:right-0 sm:top-full sm:mt-2 sm:w-96 max-h-[calc(100dvh-4.5rem)] flex flex-col bg-white border border-gray-200 shadow-xl py-2 z-50 overflow-hidden">
                    <div class="px-4 py-2 border-b border-gray-100 flex items-center justify-between">
                        <span class="font-bold text-xs text-gray-900">Notifikasi Masuk ({{ unreadCount }} Baru)</span>
                        <a href="/reminders" @click.prevent="emit('navigate', '/reminders'); notifOpen = false;" class="text-[10px] font-semibold text-orange-600 hover:text-orange-700">Lihat Semua &rarr;</a>
                    </div>

                    <div v-if="loadingNotifs" class="p-6 text-center text-xs text-gray-400">
                        Memuat notifikasi...
                    </div>
                    <div v-else-if="notifList.length === 0" class="p-6 text-center text-xs text-gray-400">
                        Tidak ada pengingat baru.
                    </div>
                    <div v-else class="divide-y divide-gray-100 max-h-80 overflow-y-auto">
                        <div v-for="item in notifList" :key="item.id"
                            @click="markAndNavigate(item)"
                            class="p-3 hover:bg-orange-50/40 cursor-pointer transition-colors text-xs flex flex-col gap-1.5"
                            :class="!item.is_read ? 'bg-orange-50/20' : ''">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-gray-900 flex items-center gap-1.5">
                                    <span v-if="!item.is_read" class="w-2 h-2 rounded-full bg-orange-500 shrink-0"></span>
                                    <span v-if="item.type === 'payment_rejected'" class="text-red-600 font-bold">Bukti Bayar Tidak Valid</span>
                                    <span v-else>{{ item.title || (item.type === 'commission_paid' ? 'Komisi Cair' : 'Instruksi Admin') }}</span>
                                </span>
                                <span class="text-[10px] text-gray-400">{{ item.created_at ? new Date(item.created_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) + ' WIB' : '-' }}</span>
                            </div>

                            <!-- Detail Prospek & Kontak & Program -->
                            <div v-if="item.lead" class="bg-gray-50/80 p-2 border border-gray-100 space-y-1">
                                <div class="flex items-center justify-between gap-1">
                                    <span class="font-bold text-gray-900 text-[11px]">{{ item.lead.name }}</span>
                                    <span v-if="item.type !== 'commission_paid'" class="text-[10px] font-mono text-emerald-700 font-semibold bg-emerald-50 px-1.5 py-0.5 border border-emerald-200">
                                        WA: {{ item.lead.whatsapp || item.lead.phone || '-' }}
                                    </span>
                                    <span v-else class="text-[10px] font-mono text-gray-500">
                                        {{ item.lead.whatsapp || item.lead.phone || '-' }}
                                    </span>
                                </div>
                                <div v-if="item.lead.product?.name || item.lead.product_name || item.lead.target_program" class="text-[10px] text-orange-700 font-semibold">
                                    Program: {{ item.lead.product?.name || item.lead.product_name || item.lead.target_program }}
                                </div>
                            </div>

                            <p class="text-gray-600 text-[11px] leading-relaxed">
                                {{ item.message }}
                            </p>

                            <!-- Quick Action Buttons -->
                            <div class="flex items-center justify-between pt-1 border-t border-gray-50">
                                <a v-if="item.type !== 'commission_paid' && getWaUrl(item.lead)" :href="getWaUrl(item.lead)" target="_blank" rel="noopener noreferrer"
                                    @click.stop
                                    class="inline-flex items-center gap-1 px-2 py-0.5 rounded-md bg-emerald-600 hover:bg-emerald-700 text-white font-semibold text-[10px] shadow-2xs">
                                    Chat WA &rarr;
                                </a>
                                <span v-else-if="item.type === 'commission_paid'" class="text-[10px] text-emerald-700 font-semibold">
                                    Komisi 10% Closing
                                </span>
                                <span class="text-[10px] text-orange-600 font-semibold hover:underline ml-auto">
                                    {{ item.type === 'commission_paid' ? 'Lihat Komisi \u2192' : 'Buka Detail \u2192' }}
                                </span>
                            </div>
                        </div>
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
                    class="absolute right-0 top-full mt-1 w-44 max-w-[calc(100vw-1.5rem)] bg-white border border-gray-200 shadow-lg py-1.5 z-50">
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
