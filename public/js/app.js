/**
 * CRM Disty Akademi — Single Page Application Core
 * Router, Shell (Sidebar + Topbar), Utilities
 */

const App = {
    pages: {},

    state: {
        currentPath: '/',
        route: null,
        user: window.api.user,
        sidebarCollapsed: localStorage.getItem('sidebar-collapsed') === '1',
        mobileSidebarOpen: false,
        notifOpen: false,
        profileOpen: false,
        reminders: [],
        unreadCount: 0,
    },

    // ── Initialization ──
    init() {
        window.addEventListener('popstate', () => {
            this.state.currentPath = window.location.pathname;
            this.render();
        });

        document.addEventListener('click', (e) => {
            if (this.state.notifOpen && !e.target.closest('#notif-dropdown') && !e.target.closest('#notif-btn')) {
                this.state.notifOpen = false;
                const dd = document.getElementById('notif-dropdown');
                if (dd) dd.classList.add('hidden');
            }
            if (this.state.profileOpen && !e.target.closest('#profile-dropdown') && !e.target.closest('#profile-btn')) {
                this.state.profileOpen = false;
                const dd = document.getElementById('profile-dropdown');
                if (dd) dd.classList.add('hidden');
            }
        });

        const path = window.location.pathname;
        if (!window.api.isAuthenticated()) {
            this.state.user = null;
            if (path !== '/login') {
                this.navigate('/login', true);
            } else {
                this.state.currentPath = '/login';
                this.render();
            }
        } else if (path === '/login') {
            this.navigate('/', true);
        } else {
            this.state.currentPath = path;
            this.render();
        }
    },

    // ── Router ──
    parseRoute(path) {
        const parts = path.replace(/^\//, '').split('/').filter(Boolean);
        if (parts.length === 0 || parts[0] === 'dashboard') return { resource: 'dashboard', action: 'index', id: null };
        if (parts[0] === 'login') return { resource: 'login', action: 'index', id: null };
        if (parts.length === 1) return { resource: parts[0], action: 'index', id: null };
        if (parts[1] === 'create') return { resource: parts[0], action: 'create', id: null };
        if (parts.length === 2 && /^\d+$/.test(parts[1])) return { resource: parts[0], action: 'show', id: parseInt(parts[1]) };
        if (parts.length === 3 && parts[2] === 'edit') return { resource: parts[0], action: 'edit', id: parseInt(parts[1]) };
        return { resource: parts[0], action: 'index', id: null };
    },

    navigate(path, replace = false) {
        if (!window.api.isAuthenticated() && path !== '/login') path = '/login';
        if (window.api.isAuthenticated() && path === '/login') path = '/';

        this.state.currentPath = path;
        this.state.mobileSidebarOpen = false;

        if (replace) {
            window.history.replaceState(null, '', path);
        } else {
            window.history.pushState(null, '', path);
        }
        this.render();
    },

    // ── Render Master ──
    async render() {
        const root = document.getElementById('app');
        if (!root) return;

        const route = this.parseRoute(this.state.currentPath);
        this.state.route = route;

        if (route.resource === 'login') {
            if (this.pages.login) {
                root.innerHTML = '';
                await this.pages.login.index(root);
            }
            return;
        }

        const shellExists = document.getElementById('main-content') !== null && document.getElementById('sidebar-desktop') !== null;
        if (!shellExists) {
            root.innerHTML = this.renderShell();
            this.bindShellEvents();
            this.setupSidebarScrollPersistence();
            if (window.api.isSales()) this.loadReminders();
        } else {
            this.updateActiveNavLinks();
            this.updateTopbarTitle();
        }

        const main = document.getElementById('main-content');
        if (main) {
            main.scrollTop = 0;
            main.innerHTML = `
                <div class="flex items-center justify-center py-20 text-gray-400">
                    <svg class="w-5 h-5 animate-spin text-orange-500 mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                    <span class="text-xs">Memuat...</span>
                </div>
            `;
        }

        await this.loadPage();
    },

    renderShell() {
        const c = this.state.sidebarCollapsed;
        return `
            <div class="h-screen h-[100dvh] max-h-[100dvh] flex bg-gray-50 text-gray-900 overflow-hidden">
                <!-- Desktop Sidebar (Independent vertical scroll container) -->
                <aside id="sidebar-desktop" class="hidden lg:flex flex-col h-full h-[100dvh] max-h-[100dvh] bg-white border-r border-gray-100 shrink-0 sidebar-transition overflow-hidden ${c ? 'w-[72px]' : 'w-[260px]'}">
                    ${this.renderSidebarContent(c, false)}
                </aside>

                <!-- Mobile Sidebar Overlay -->
                <div id="mobile-overlay" class="fixed inset-0 z-40 bg-black/50 backdrop-blur-sm lg:hidden ${this.state.mobileSidebarOpen ? '' : 'hidden'}" onclick="App.toggleMobileSidebar()"></div>
                <aside id="sidebar-mobile" class="fixed inset-y-0 left-0 z-50 w-72 max-w-[85vw] h-full h-[100dvh] max-h-[100dvh] bg-white shadow-2xl lg:hidden flex flex-col overflow-hidden transform transition-transform duration-300 ${this.state.mobileSidebarOpen ? 'translate-x-0' : '-translate-x-full'}">
                    ${this.renderSidebarContent(false, true)}
                </aside>

                <!-- Main Area (Topbar + Independent vertical scroll container) -->
                <div class="flex-1 flex flex-col min-w-0 min-h-0 h-full h-[100dvh] max-h-[100dvh] overflow-hidden">
                    ${this.renderTopbar()}
                    <main id="main-content" class="flex-1 min-h-0 overflow-y-auto p-5 scrollbar-thin overscroll-contain">
                        <div class="flex items-center justify-center py-20 text-gray-400">
                            <svg class="w-5 h-5 animate-spin text-orange-500 mr-2" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg>
                            <span class="text-xs">Memuat...</span>
                        </div>
                    </main>
                </div>
            </div>
        `;
    },

    renderSidebarContent(collapsed, isMobile) {
        const user = this.state.user || {};
        const isAdmin = user.role === 'admin';
        const p = this.state.currentPath;
        const c = collapsed;

        const navItem = (path, icon, label, badge = '') => {
            const active = p === path || (path !== '/' && p.startsWith(path));
            return `
                <a href="${path}" onclick="event.preventDefault(); App.navigate('${path}')"
                   class="flex items-center gap-3 ${c ? 'justify-center px-0' : 'px-3.5'} py-2.5 rounded-xl text-[13px] font-semibold transition-all group relative
                   ${active ? 'bg-orange-500 text-white shadow-sm shadow-orange-500/25' : 'text-gray-600 hover:text-orange-600 hover:bg-orange-50'}"
                   ${c ? `onmouseenter="App.showTooltip(this, '${label}')" onmouseleave="App.hideTooltip()"` : ''}>
                    <span class="w-[19px] h-[19px] shrink-0">${icon}</span>
                    ${c ? '' : `<span class="truncate">${label}</span>`}
                    ${(!c && badge) ? badge : ''}
                </a>
            `;
        };

        const sectionLabel = (text) => c ? `<div class="border-t border-gray-100 my-2"></div>` :
            `<div class="text-[10px] font-bold tracking-wider text-gray-400 uppercase px-3.5 pt-4 pb-1.5">${text}</div>`;

        // Icon SVGs (Heroicons outline)
        const ic = {
            dashboard: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/></svg>',
            leads: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>',
            followup: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>',
            activity: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
            customer: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>',
            institution: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M8 14v3m4-3v3m4-3v3M3 21h18M3 10h18M3 7l9-4 9 4M4 10h16v11H4V10z"/></svg>',
            registration: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>',
            schedule: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>',
            trainer: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m0 0V10"/></svg>',
            product: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>',
            payment: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>',
            commission: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>',
            reminder: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>',
            user: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>',
            import: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>',
            logout: '<svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>',
        };

        const unreadBadge = this.state.unreadCount > 0
            ? `<span class="ml-auto bg-red-500 text-white text-[9px] font-bold px-1.5 py-0.5 rounded-full leading-none">${this.state.unreadCount}</span>` : '';

        let nav = '';
        nav += navItem('/', ic.dashboard, 'Dashboard');

        if (isAdmin) {
            nav += sectionLabel('PROSPEK & LEADS');
            nav += navItem('/leads', ic.leads, 'Semua Leads');
            nav += navItem('/follow-ups', ic.followup, 'Follow-up');
            nav += navItem('/activities', ic.activity, 'Riwayat Aktivitas');

            nav += sectionLabel('CUSTOMER & MITRA');
            nav += navItem('/customers', ic.customer, 'Daftar Customer');
            nav += navItem('/institutions', ic.institution, 'Daftar Instansi');

            nav += sectionLabel('PELATIHAN & OPERASIONAL');
            nav += navItem('/registrations', ic.registration, 'Pendaftaran Peserta');
            nav += navItem('/training-schedules', ic.schedule, 'Jadwal Pelatihan');
            nav += navItem('/trainers', ic.trainer, 'Daftar Trainer');
            nav += navItem('/products', ic.product, 'Katalog Program');

            nav += sectionLabel('KEUANGAN & KOMISI');
            nav += navItem('/payments', ic.payment, 'Verifikasi Bayar');
            nav += navItem('/commissions', ic.commission, 'Komisi Sales');

            nav += sectionLabel('PENGATURAN (ADMIN)');
            nav += navItem('/users', ic.user, 'Manajemen Pengguna');
            nav += navItem('/lead-imports', ic.import, 'Import Batch CRM');
        } else {
            nav += sectionLabel('AFFILIATE & LEADS');
            nav += navItem('/leads', ic.leads, 'Leads & Prospek');
            nav += navItem('/follow-ups', ic.followup, 'Follow-up Saya');
            nav += navItem('/reminders', ic.reminder, 'Notifikasi & Pengingat', unreadBadge);
            nav += navItem('/activities', ic.activity, 'Riwayat Aktivitas');

            nav += sectionLabel('PRODUK & HARGA');
            nav += navItem('/products', ic.product, 'Katalog Program');

            nav += sectionLabel('TRANSAKSI & KOMISI');
            nav += navItem('/payments', ic.payment, 'Kirim Bukti Bayar');
            nav += navItem('/commissions', ic.commission, 'Komisi Saya (10%)');
        }

        return `
            <!-- Header (Logo) -->
            <div class="p-4 flex items-center shrink-0 ${c ? 'justify-center' : 'gap-3.5'} border-b border-gray-100">
                <div class="w-10 h-10 rounded-xl bg-orange-100 text-orange-600 flex items-center justify-center shrink-0 shadow-xs">
                    <img src="/images/disty-icon.png" alt="Logo" class="w-7 h-7 rounded-lg object-contain" onerror="this.outerHTML='<span class=\\'font-black text-sm text-orange-600\\'>D</span>'">
                </div>
                ${c ? '' : `
                    <div class="flex-1 min-w-0 leading-tight">
                        <h2 class="font-bold text-[15px] text-gray-900 leading-none">Disty</h2>
                        <p class="text-xs text-gray-500 font-medium mt-0.5">Akademi</p>
                    </div>
                `}
                ${isMobile ? `
                    <button onclick="App.toggleMobileSidebar()" class="p-1 rounded-lg text-gray-400 hover:text-gray-600">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                ` : ''}
            </div>

            <!-- Nav (Independent Scrollable Container) -->
            <nav class="flex-1 min-h-0 overflow-y-auto p-3 space-y-0.5 no-scrollbar scrollbar-none overscroll-contain" style="scrollbar-width: none; -ms-overflow-style: none;">
                ${nav}
            </nav>

            <!-- Footer: Logout (Anchored at Bottom) -->
            <div class="p-3 shrink-0 border-t border-gray-100">
                <button onclick="App.handleLogout()" class="w-full flex items-center ${c ? 'justify-center' : 'gap-3.5 px-3.5'} py-2.5 rounded-xl text-[13px] font-semibold text-gray-500 hover:text-red-600 hover:bg-red-50 transition-all">
                    <span class="w-[19px] h-[19px] shrink-0">${ic.logout}</span>
                    ${c ? '' : '<span>Sign Out</span>'}
                </button>
            </div>
        `;
    },

    getPageTitle(resource) {
        const titles = {
            dashboard: 'Dashboard', leads: 'Leads & Prospek', customers: 'Customer',
            'follow-ups': 'Follow-up', activities: 'Riwayat Aktivitas', products: 'Katalog Program',
            payments: 'Pembayaran', commissions: 'Komisi Sales', reminders: 'Notifikasi',
            institutions: 'Instansi', registrations: 'Pendaftaran', 'training-schedules': 'Jadwal Pelatihan',
            trainers: 'Trainer', users: 'Pengguna', 'lead-imports': 'Import Batch'
        };
        return titles[resource] || 'Dashboard';
    },

    updateActiveNavLinks() {
        const p = this.state.currentPath;
        const allNavs = document.querySelectorAll('#sidebar-desktop nav, #sidebar-mobile nav');
        allNavs.forEach(nav => {
            const links = nav.querySelectorAll('a[href]');
            links.forEach(a => {
                const href = a.getAttribute('href');
                if (!href) return;
                const active = p === href || (href !== '/' && p.startsWith(href));
                if (active) {
                    a.classList.remove('text-gray-600', 'hover:text-orange-600', 'hover:bg-orange-50');
                    a.classList.add('bg-orange-500', 'text-white', 'shadow-sm', 'shadow-orange-500/25');
                } else {
                    a.classList.remove('bg-orange-500', 'text-white', 'shadow-sm', 'shadow-orange-500/25');
                    a.classList.add('text-gray-600', 'hover:text-orange-600', 'hover:bg-orange-50');
                }
            });
        });
    },

    updateTopbarTitle() {
        const titleEl = document.getElementById('topbar-page-title');
        if (titleEl) {
            titleEl.textContent = this.getPageTitle(this.state.route?.resource);
        }
    },

    setupSidebarScrollPersistence() {
        const desktopNav = document.querySelector('#sidebar-desktop nav');
        const mobileNav = document.querySelector('#sidebar-mobile nav');
        [desktopNav, mobileNav].forEach(nav => {
            if (nav) {
                this.setupSidebarNavScroll(nav);
            }
        });
    },

    setupSidebarNavScroll(nav) {
        if (!nav) return;
        const savedScroll = sessionStorage.getItem('disty_sidebar_scroll_top');
        if (savedScroll !== null) {
            nav.scrollTop = parseInt(savedScroll, 10) || 0;
        }
        nav.addEventListener('scroll', () => {
            sessionStorage.setItem('disty_sidebar_scroll_top', nav.scrollTop);
        }, { passive: true });
    },

    renderTopbar() {
        const user = this.state.user || {};
        const route = this.state.route || {};
        const title = this.getPageTitle(route.resource);
        const now = new Date();
        const dateStr = now.toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });

        return `
            <header class="h-14 bg-white border-b border-gray-100 px-4 flex items-center justify-between sticky top-0 z-30 shrink-0">
                <div class="flex items-center gap-3">
                    <button onclick="App.toggleMobileSidebar()" class="lg:hidden p-1.5 rounded-lg text-gray-900 hover:bg-gray-100 transition-colors" title="Buka Menu">
                        <svg class="w-5 h-5 text-gray-900" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <button onclick="App.toggleSidebar()" class="hidden lg:flex p-1.5 rounded-lg text-gray-900 hover:bg-gray-100 transition-colors" title="Toggle Sidebar">
                        <svg class="w-5 h-5 text-gray-900" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    <div class="text-xs flex items-center gap-3">
                        <span class="text-gray-500 font-medium">Disty Akademi</span>
                        <span id="topbar-page-title" class="text-gray-900 font-bold">${title}</span>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    <span class="hidden sm:inline text-[11px] text-gray-400 font-medium">${dateStr}</span>

                    ${user.role === 'sales' ? `
                        <div class="relative">
                            <button id="notif-btn" onclick="App.toggleNotif()" class="relative p-2 rounded-lg text-gray-400 hover:text-orange-600 hover:bg-orange-50 transition-colors">
                                <svg class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.75" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                                ${this.state.unreadCount > 0 ? `<span class="absolute -top-0.5 -right-0.5 bg-red-500 text-white text-[8px] font-bold w-4 h-4 rounded-full flex items-center justify-center animate-pulse">${this.state.unreadCount}</span>` : ''}
                            </button>
                            <div id="notif-dropdown" class="hidden absolute right-0 mt-2 w-80 bg-white border border-gray-200 rounded-2xl shadow-xl z-50 overflow-hidden">
                                <div class="p-3 border-b border-gray-100 flex items-center justify-between">
                                    <span class="text-xs font-bold text-gray-900">Notifikasi</span>
                                    <button onclick="App.markAllRead()" class="text-[10px] font-semibold text-orange-600 hover:text-orange-700">Tandai Dibaca</button>
                                </div>
                                <div id="notif-list" class="max-h-64 overflow-y-auto divide-y divide-gray-50">
                                    <div class="p-4 text-center text-xs text-gray-400">Memuat...</div>
                                </div>
                                <a href="/reminders" onclick="event.preventDefault(); App.navigate('/reminders')" class="block p-2.5 text-center text-[10px] font-bold text-orange-600 bg-orange-50/50 hover:bg-orange-50">Lihat Semua Notifikasi</a>
                            </div>
                        </div>
                    ` : ''}

                    <div class="relative">
                        <button id="profile-btn" onclick="App.toggleProfile()" class="flex items-center gap-2 px-2 py-1 rounded-xl hover:bg-gray-50 transition-colors">
                            <div class="w-7 h-7 rounded-full bg-orange-500 text-white flex items-center justify-center text-xs font-bold shadow-xs">
                                ${((user && user.name) || 'Admin CRM').charAt(0).toUpperCase()}
                            </div>
                            <span class="text-xs font-bold text-gray-900 hidden sm:inline">${(user && user.name) || 'Admin CRM'}</span>
                        </button>
                        <div id="profile-dropdown" class="hidden absolute right-0 mt-1 w-44 bg-white border border-gray-200 rounded-xl shadow-lg z-50 py-1">
                            <div class="px-3 py-2 border-b border-gray-100">
                                <p class="text-xs font-bold text-gray-900">${(user && user.name) || 'User'}</p>
                                <p class="text-[10px] text-gray-400">${(user && user.email) || ''}</p>
                            </div>
                            <button onclick="App.handleLogout()" class="w-full text-left px-3 py-2 text-xs text-red-600 hover:bg-red-50 font-semibold flex items-center gap-2">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                Keluar
                            </button>
                        </div>
                    </div>
                </div>
            </header>
        `;
    },

    // ── Page Loading ──
    async loadPage() {
        const container = document.getElementById('main-content');
        if (!container) return;

        const { resource, action, id } = this.state.route;
        const page = this.pages[resource];

        if (!page) {
            container.innerHTML = `<div class="text-center py-20"><p class="text-gray-400 text-sm">Halaman <strong>${resource}</strong> belum tersedia.</p><button onclick="App.navigate('/')" class="mt-3 px-4 py-2 bg-orange-500 text-white rounded-xl text-xs font-semibold hover:bg-orange-600">Kembali ke Dashboard</button></div>`;
            return;
        }

        try {
            if (action === 'create' && page.create) {
                await page.create(container);
            } else if (action === 'edit' && page.edit) {
                await page.edit(container, id);
            } else if (action === 'show' && page.show) {
                await page.show(container, id);
            } else if (page.index) {
                await page.index(container);
            }
        } catch (error) {
            container.innerHTML = `
                <div class="p-6 rounded-2xl bg-red-50 border border-red-200 text-red-700 space-y-2">
                    <h3 class="font-bold text-sm">Gagal memuat halaman:</h3>
                    <p class="text-xs font-mono">${error.message}</p>
                    <button onclick="App.loadPage()" class="mt-3 px-4 py-2 text-xs font-semibold rounded-xl bg-red-600 text-white hover:bg-red-700">Coba Lagi</button>
                </div>
            `;
        }
    },

    // ── Shell Events ──
    bindShellEvents() { /* bound inline via onclick */ },

    toggleSidebar() {
        this.state.sidebarCollapsed = !this.state.sidebarCollapsed;
        localStorage.setItem('sidebar-collapsed', this.state.sidebarCollapsed ? '1' : '0');
        const sb = document.getElementById('sidebar-desktop');
        if (sb) {
            const nav = sb.querySelector('nav');
            const savedScroll = nav ? nav.scrollTop : 0;
            sb.className = `hidden lg:flex flex-col h-full h-[100dvh] max-h-[100dvh] bg-white border-r border-gray-100 shrink-0 sidebar-transition overflow-hidden ${this.state.sidebarCollapsed ? 'w-[72px]' : 'w-[260px]'}`;
            sb.innerHTML = this.renderSidebarContent(this.state.sidebarCollapsed, false);
            const newNav = sb.querySelector('nav');
            if (newNav) {
                newNav.scrollTop = savedScroll;
                this.setupSidebarNavScroll(newNav);
            }
        }
    },

    toggleMobileSidebar() {
        this.state.mobileSidebarOpen = !this.state.mobileSidebarOpen;
        const overlay = document.getElementById('mobile-overlay');
        const sidebar = document.getElementById('sidebar-mobile');
        if (overlay) overlay.classList.toggle('hidden', !this.state.mobileSidebarOpen);
        if (sidebar) sidebar.classList.toggle('-translate-x-full', !this.state.mobileSidebarOpen);
        if (sidebar) sidebar.classList.toggle('translate-x-0', this.state.mobileSidebarOpen);
    },

    toggleNotif() {
        this.state.notifOpen = !this.state.notifOpen;
        const dd = document.getElementById('notif-dropdown');
        if (dd) dd.classList.toggle('hidden');
        if (this.state.notifOpen) this.renderNotifDropdown();
    },

    toggleProfile() {
        this.state.profileOpen = !this.state.profileOpen;
        const dd = document.getElementById('profile-dropdown');
        if (dd) dd.classList.toggle('hidden');
    },

    showTooltip(el, text) {
        const tip = document.getElementById('sidebar-tooltip');
        if (!tip) return;
        const r = el.getBoundingClientRect();
        tip.textContent = text;
        tip.style.top = (r.top + r.height / 2 - 14) + 'px';
        tip.style.left = (r.right + 10) + 'px';
        tip.style.opacity = '1';
    },

    hideTooltip() {
        const tip = document.getElementById('sidebar-tooltip');
        if (tip) tip.style.opacity = '0';
    },

    // ── Notifications ──
    async loadReminders() {
        try {
            const res = await api.list('reminders', { per_page: 10 });
            const items = res.data || [];
            this.state.reminders = items;
            this.state.unreadCount = items.filter(r => !r.is_read).length;
            const badge = document.querySelector('#notif-btn span.animate-pulse');
            if (badge) badge.textContent = this.state.unreadCount || '';
            if (this.state.unreadCount === 0 && badge) badge.style.display = 'none';
        } catch (e) { /* silent */ }
    },

    renderNotifDropdown() {
        const list = document.getElementById('notif-list');
        if (!list) return;
        const items = this.state.reminders;
        if (items.length === 0) {
            list.innerHTML = '<div class="p-4 text-center text-xs text-gray-400">Belum ada notifikasi.</div>';
            return;
        }
        list.innerHTML = items.slice(0, 5).map(r => {
            const isCommission = r.type === 'commission_paid';
            return `
                <div class="px-3 py-2.5 hover:bg-gray-50 ${r.is_read ? 'opacity-60' : ''}">
                    <div class="flex items-start gap-2">
                        <div class="w-2 h-2 rounded-full mt-1.5 shrink-0 ${isCommission ? 'bg-emerald-500' : 'bg-amber-500'}"></div>
                        <div class="flex-1 min-w-0">
                            <p class="text-[11px] font-bold text-gray-900 truncate">${isCommission ? 'Komisi Masuk' : 'Pengingat Follow-Up'}</p>
                            <p class="text-[10px] text-gray-500 line-clamp-2 mt-0.5">${r.message || '-'}</p>
                        </div>
                    </div>
                </div>
            `;
        }).join('');
    },

    async markAllRead() {
        try {
            await api.markAllRemindersRead();
            this.state.unreadCount = 0;
            this.state.reminders.forEach(r => r.is_read = true);
            this.renderNotifDropdown();
            this.showToast('Semua notifikasi ditandai dibaca.');
        } catch (e) { this.showToast(e.message, 'error'); }
    },

    // ── Auth ──
    async handleLogout() {
        if (!confirm('Yakin ingin keluar dari akun CRM?')) return;
        await window.api.logout();
        this.state.user = null;
        this.showToast('Berhasil keluar.');
        this.navigate('/login', true);
    },

    // ── Utilities ──
    showToast(message, type = 'success') {
        const toast = document.getElementById('toast');
        const msg = document.getElementById('toast-msg');
        const icon = document.getElementById('toast-icon');
        if (!toast) return;

        msg.textContent = message;
        if (type === 'success') {
            toast.className = 'fixed bottom-5 right-5 z-[9999] flex items-center gap-2.5 px-4 py-3 rounded-xl bg-gray-900 text-white shadow-xl border border-gray-700 text-sm transition-all duration-300 transform translate-y-0 opacity-100';
            icon.innerHTML = '<svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>';
        } else {
            toast.className = 'fixed bottom-5 right-5 z-[9999] flex items-center gap-2.5 px-4 py-3 rounded-xl bg-red-950 text-white shadow-xl border border-red-800 text-sm transition-all duration-300 transform translate-y-0 opacity-100';
            icon.innerHTML = '<svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>';
        }
        clearTimeout(this._toastTimer);
        this._toastTimer = setTimeout(() => {
            toast.className = 'fixed bottom-5 right-5 z-[9999] flex items-center gap-2.5 px-4 py-3 rounded-xl bg-gray-900 text-white shadow-xl text-sm transition-all duration-300 transform translate-y-8 opacity-0 pointer-events-none';
        }, 3500);
    },

    openModal(html) {
        const ov = document.getElementById('modal-overlay');
        if (!ov) return;
        ov.innerHTML = `
            <div class="fixed inset-0 z-[60] flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm" onclick="if(event.target===this)App.closeModal()">
                <div class="bg-white rounded-2xl max-w-lg w-full max-h-[90vh] overflow-y-auto shadow-2xl">${html}</div>
            </div>
        `;
    },

    closeModal() {
        const ov = document.getElementById('modal-overlay');
        if (ov) ov.innerHTML = '';
    },

    formatRupiah(n) {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', maximumFractionDigits: 0 }).format(n || 0);
    },

    formatDate(d) {
        if (!d) return '-';
        return new Date(d).toLocaleDateString('id-ID', { day: 'numeric', month: 'short', year: 'numeric' });
    },

    formatDateTime(d) {
        if (!d) return '-';
        return new Date(d).toLocaleString('id-ID', { day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
    },

    statusBadge(status) {
        const map = {
            new: 'bg-slate-100 text-slate-700 border-slate-200',
            contacted: 'bg-blue-50 text-blue-700 border-blue-200',
            active: 'bg-cyan-50 text-cyan-700 border-cyan-200',
            negotiation: 'bg-amber-50 text-amber-700 border-amber-200',
            won: 'bg-emerald-50 text-emerald-700 border-emerald-200',
            lost: 'bg-red-50 text-red-700 border-red-200',
            inactive: 'bg-gray-100 text-gray-500 border-gray-200',
            pending: 'bg-amber-50 text-amber-700 border-amber-200',
            approved: 'bg-blue-50 text-blue-700 border-blue-200',
            paid: 'bg-emerald-50 text-emerald-700 border-emerald-200',
            valid: 'bg-emerald-50 text-emerald-700 border-emerald-200',
            verified: 'bg-emerald-50 text-emerald-700 border-emerald-200',
            invalid: 'bg-red-50 text-red-700 border-red-200',
            recheck: 'bg-orange-50 text-orange-700 border-orange-200',
            registered: 'bg-blue-50 text-blue-700 border-blue-200',
            confirmed: 'bg-emerald-50 text-emerald-700 border-emerald-200',
            attended: 'bg-emerald-50 text-emerald-700 border-emerald-200',
            cancelled: 'bg-gray-100 text-gray-500 border-gray-200',
            scheduled: 'bg-blue-50 text-blue-700 border-blue-200',
            ongoing: 'bg-cyan-50 text-cyan-700 border-cyan-200',
            completed: 'bg-emerald-50 text-emerald-700 border-emerald-200',
            prospect: 'bg-amber-50 text-amber-700 border-amber-200',
        };
        const cls = map[status] || 'bg-gray-100 text-gray-600 border-gray-200';
        return `<span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-semibold uppercase tracking-wider border ${cls}">${status || '-'}</span>`;
    },

    statusLabel(status) {
        const labels = {
            new: 'Baru', contacted: 'Dikontak', active: 'Aktif', negotiation: 'Negosiasi',
            won: 'Berhasil', lost: 'Ditolak', inactive: 'Tidak Aktif', pending: 'Pending',
            approved: 'Disetujui', paid: 'Dibayar', valid: 'Valid', verified: 'Terverifikasi',
            invalid: 'Tidak Valid', recheck: 'Cek Ulang', registered: 'Terdaftar',
            confirmed: 'Dikonfirmasi', attended: 'Hadir', cancelled: 'Dibatalkan',
            scheduled: 'Terjadwal', ongoing: 'Berlangsung', completed: 'Selesai',
            prospect: 'Prospek',
        };
        return labels[status] || status || '-';
    },

    // ── Table Shell Helper ──
    tableShell(headers, rows, emptyText = 'Belum ada data.') {
        return `
            <div class="bg-white rounded-2xl border border-gray-100 shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-xs">
                        <thead class="bg-gray-50/80 border-b border-gray-100">
                            <tr>${headers.map(h => `<th class="py-3 px-4 text-[10.5px] font-semibold uppercase tracking-wider text-gray-500">${h}</th>`).join('')}</tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            ${rows.length === 0 ? `<tr><td colspan="${headers.length}" class="text-center py-10 text-gray-400 text-xs">${emptyText}</td></tr>` : rows.join('')}
                        </tbody>
                    </table>
                </div>
            </div>
        `;
    },

    // ── Page Header Helper ──
    pageHeader(title, subtitle, actions = '') {
        return `
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div>
                    <h2 class="text-lg font-extrabold text-gray-900 tracking-tight">${title}</h2>
                    ${subtitle ? `<p class="text-[11px] text-gray-400 mt-0.5">${subtitle}</p>` : ''}
                </div>
                <div class="flex items-center gap-2">${actions}</div>
            </div>
        `;
    },

    // ── Form Helpers ──
    inputField(label, name, value = '', type = 'text', required = false, placeholder = '', extra = '') {
        return `
            <div ${extra}>
                <label class="block text-[11px] font-semibold text-gray-700 mb-1">${label}${required ? ' <span class="text-red-400">*</span>' : ''}</label>
                <input type="${type}" name="${name}" value="${value}" ${required ? 'required' : ''} placeholder="${placeholder}"
                    class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none transition-all">
            </div>
        `;
    },

    selectField(label, name, options, value = '', required = false) {
        return `
            <div>
                <label class="block text-[11px] font-semibold text-gray-700 mb-1">${label}${required ? ' <span class="text-red-400">*</span>' : ''}</label>
                <select name="${name}" ${required ? 'required' : ''} class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none bg-white">
                    <option value="">-- Pilih --</option>
                    ${options.map(o => `<option value="${o.value}" ${String(o.value) === String(value) ? 'selected' : ''}>${o.label}</option>`).join('')}
                </select>
            </div>
        `;
    },

    textareaField(label, name, value = '', required = false, placeholder = '') {
        return `
            <div>
                <label class="block text-[11px] font-semibold text-gray-700 mb-1">${label}</label>
                <textarea name="${name}" rows="3" ${required ? 'required' : ''} placeholder="${placeholder}"
                    class="w-full px-3 py-2 rounded-xl border border-gray-200 text-xs focus:ring-2 focus:ring-orange-500 focus:border-orange-500 outline-none resize-none">${value}</textarea>
            </div>
        `;
    },

    primaryBtn(text, type = 'submit') {
        return `<button type="${type}" class="px-5 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-semibold text-xs shadow-sm transition-all">${text}</button>`;
    },

    secondaryBtn(text, onclick = '') {
        return `<button type="button" ${onclick ? `onclick="${onclick}"` : ''} class="px-4 py-2.5 rounded-xl border border-gray-200 text-gray-700 font-semibold text-xs hover:bg-gray-50 transition-all">${text}</button>`;
    },

    // ── Form Submission Helper ──
    bindForm(formId, resource, redirectPath, isEdit = false, id = null) {
        const form = document.getElementById(formId);
        if (!form) return;
        form.onsubmit = async (e) => {
            e.preventDefault();
            const btn = form.querySelector('button[type="submit"]');
            const origText = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<svg class="w-4 h-4 animate-spin inline mr-1" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z"></path></svg> Menyimpan...';

            const formData = new FormData(form);
            const data = Object.fromEntries(formData.entries());

            // Remove empty values
            Object.keys(data).forEach(k => { if (data[k] === '') delete data[k]; });

            try {
                if (isEdit) {
                    await api.update(resource, id, data);
                    App.showToast('Data berhasil diperbarui!');
                } else {
                    await api.create(resource, data);
                    App.showToast('Data berhasil ditambahkan!');
                }
                App.navigate(redirectPath);
            } catch (err) {
                App.showToast(err.message, 'error');
                btn.disabled = false;
                btn.innerHTML = origText;
            }
        };
    },

    // ── Pagination Helper ──
    pagination(meta) {
        if (!meta || !meta.last_page || meta.last_page <= 1) return '';
        let pages = '';
        for (let i = 1; i <= meta.last_page; i++) {
            pages += `<button onclick="App.pages['${this.state.route.resource}'].goToPage(${i})" class="px-3 py-1.5 rounded-lg text-xs font-semibold ${i === meta.current_page ? 'bg-orange-500 text-white' : 'bg-white border border-gray-200 text-gray-600 hover:bg-gray-50'}">${i}</button>`;
        }
        return `<div class="flex items-center justify-between pt-4"><p class="text-[11px] text-gray-400">Menampilkan ${meta.from || 0}-${meta.to || 0} dari ${meta.total || 0}</p><div class="flex gap-1">${pages}</div></div>`;
    },
};

window.App = App;
document.addEventListener('DOMContentLoaded', () => App.init());
