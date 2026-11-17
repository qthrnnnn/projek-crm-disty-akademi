<script setup>
import { computed, onMounted, onBeforeUnmount, ref } from 'vue';
import { api } from './services/api';
import { routes, resolveRoute } from './router';
import AppLayout from './Layouts/AppLayout.vue';
import Login from './Pages/Auth/Login.vue';

const user = ref(api.user);
const currentPath = ref(window.location.pathname);
const unreadCount = ref(0);
let reminderInterval = null;

const isAuthenticated = computed(() => Boolean(user.value));

const currentRoute = computed(() => resolveRoute(currentPath.value));

const activeComponent = computed(() => {
    return routes[currentRoute.value.componentKey] || routes['Dashboard.Index'];
});

function navigate(to) {
    if (to === currentPath.value) return;
    window.history.pushState({}, '', to);
    currentPath.value = to;
    checkReminders();
}

function handleAuthenticated(loggedInUser) {
    user.value = loggedInUser;
    navigate('/');
    checkReminders();
    if (!reminderInterval) {
        reminderInterval = setInterval(checkReminders, 10000);
    }
}

async function handleLogout() {
    if (!confirm('Yakin ingin keluar dari akun CRM?')) return;
    if (reminderInterval) clearInterval(reminderInterval);
    await api.logout();
    user.value = null;
    navigate('/login');
}

async function checkReminders() {
    if (user.value?.role === 'sales') {
        try {
            const res = await api.list('reminders', { per_page: 50 });
            const items = res.data || [];
            unreadCount.value = items.filter(r => !r.is_read).length;
        } catch (e) {
            /* ignore */
        }
    }
}

onMounted(async () => {
    window.addEventListener('popstate', () => {
        currentPath.value = window.location.pathname;
        checkReminders();
    });

    if (user.value && currentPath.value === '/login') {
        navigate('/');
    } else if (!user.value && currentPath.value !== '/login') {
        navigate('/login');
    }

    // Validasi sesi token di latar belakang
    if (api.getToken()) {
        try {
            const response = await api.getUser();
            const u = response.data || response.user;
            if (u) {
                user.value = u;
                api.setUser(u);
            }
        } catch {
            api.setUser(null);
            api.setToken(null);
            user.value = null;
            if (currentPath.value !== '/login') {
                navigate('/login');
            }
        }
    }

    if (isAuthenticated.value) {
        checkReminders();
        reminderInterval = setInterval(checkReminders, 10000);
    }
});

onBeforeUnmount(() => {
    if (reminderInterval) clearInterval(reminderInterval);
});
</script>

<template>
    <!-- Unauthenticated / Login View -->
    <Login v-if="!isAuthenticated || currentPath === '/login'"
        @authenticated="handleAuthenticated" />

    <!-- Authenticated App View with Shell Layout -->
    <AppLayout v-else
        :user="user"
        :title="currentRoute.title"
        :current-path="currentPath"
        :unread-count="unreadCount"
        @navigate="navigate"
        @logout="handleLogout">
        
        <Suspense>
            <template #default>
                <component
                    :is="activeComponent"
                    :id="currentRoute.id"
                    :query="currentRoute.query"
                    :user="user"
                    @navigate="navigate"
                />
            </template>
            <template #fallback>
                <div class="flex items-center justify-center py-20 text-gray-400">
                    <svg class="w-5 h-5 animate-spin text-orange-500 mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                    <span class="text-xs">Memuat modul...</span>
                </div>
            </template>
        </Suspense>
    </AppLayout>
</template>
