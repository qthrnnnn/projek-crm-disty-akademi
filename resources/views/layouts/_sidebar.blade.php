{{-- Sidebar content partial (dipakai desktop + mobile) --}}

{{-- Logo & Toggle Header (Desktop) --}}
<div class="hidden lg:flex items-center h-16 px-3 border-b border-gray-200 flex-shrink-0" style="height:68px">
    {{-- Expanded view: Logo + collapse button --}}
    <div x-show="!collapsed" class="flex items-center justify-between w-full">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-2.5 min-w-0">
            <img src="{{ asset('images/disty-icon.png') }}" alt="Disty Akademi" class="w-11 h-11 rounded-xl bg-white p-1.5 object-contain shadow-sm">
            <span class="font-semibold leading-tight tracking-wide text-gray-900">
                <span class="block text-base">Disty</span>
                <span class="block text-xs">Akademi</span>
            </span>
        </a>
        <button @click="toggleSidebar()"
                type="button"
                class="p-1.5 rounded-lg text-gray-400 hover:text-orange-600 hover:bg-orange-50 transition-colors"
                title="Perkecil menu">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
            </svg>
        </button>
    </div>

    {{-- Collapsed view: Logo icon only (no text) --}}
    <div x-show="collapsed" class="flex items-center justify-center w-full">
        <a href="{{ route('dashboard') }}"
           @mouseenter="showTip($el, 'Disty Akademi')"
           @mouseleave="hideTip()"
           class="flex items-center justify-center w-11 h-11 rounded-xl bg-white shadow-sm hover:scale-105 transition-all p-1.5"
           title="Disty Akademi">
            <img src="{{ asset('images/disty-icon.png') }}" alt="Disty Akademi" class="w-full h-full object-contain">
        </a>
    </div>
</div>

{{-- Logo & Close Header (Mobile Only) --}}
<div class="flex lg:hidden items-center justify-between h-16 px-4 border-b border-gray-100 flex-shrink-0">
    <a href="{{ route('dashboard') }}" @click="mob = false" class="flex items-center gap-2.5 min-w-0">
        <img src="{{ asset('images/disty-icon.png') }}" alt="Disty Akademi" class="w-10 h-10 rounded-xl bg-orange-50 p-1.5 object-contain">
        <span class="font-bold leading-tight tracking-wide text-gray-900 text-sm">
            <span class="block">Disty Akademi</span>
            <span class="block text-[11px] font-normal text-gray-500">CRM & Training</span>
        </span>
    </a>
    <button @click="mob = false"
            type="button"
            class="w-9 h-9 rounded-xl flex items-center justify-center text-gray-400 hover:text-gray-700 hover:bg-gray-100 transition-colors"
            aria-label="Tutup Menu">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
        </svg>
    </button>
</div>

{{-- Navigation --}}
<nav class="sidebar-nav flex-1 min-h-0 py-3 px-2 space-y-3 overflow-y-auto overscroll-contain">

    {{-- Dashboard --}}
    <div>
        <a href="{{ route('dashboard') }}"
           @click="mob = false"
           @mouseenter="showTip($el, 'Dashboard')"
           @mouseleave="hideTip()"
           class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
            </svg>
            <span class="nav-label">Dashboard</span>
        </a>
    </div>

    @if(auth()->user()->isAdmin())
    {{-- ══════════════════════════════════════════════ --}}
    {{-- MENU KHUSUS ADMIN (MANAJEMEN & OPERASIONAL)     --}}
    {{-- ══════════════════════════════════════════════ --}}

    {{-- Prospek & Leads (Admin) --}}
    <div>
        <p class="nav-section-title">PROSPEK & LEADS</p>
        <div class="nav-section-divider"></div>
        <div class="space-y-0.5">
            <a href="{{ route('leads.index') }}"
               @click="mob = false"
               @mouseenter="showTip($el, 'Semua Leads')"
               @mouseleave="hideTip()"
               class="nav-item {{ request()->routeIs('leads*') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <span class="nav-label">Semua Leads</span>
            </a>
            <a href="{{ route('follow-ups.index') }}"
               @click="mob = false"
               @mouseenter="showTip($el, 'Follow-up')"
               @mouseleave="hideTip()"
               class="nav-item {{ request()->routeIs('follow-ups*') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
                <span class="nav-label">Follow-up</span>
            </a>
            <a href="{{ route('activities.index') }}"
               @click="mob = false"
               @mouseenter="showTip($el, 'Riwayat Aktivitas')"
               @mouseleave="hideTip()"
               class="nav-item {{ request()->routeIs('activities*') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="nav-label">Riwayat Aktivitas</span>
            </a>
        </div>
    </div>

    {{-- Customer & Instansi (Khusus Admin) --}}
    <div>
        <p class="nav-section-title">CUSTOMER & MITRA</p>
        <div class="nav-section-divider"></div>
        <div class="space-y-0.5">
            <a href="{{ route('customers.index') }}"
               @click="mob = false"
               @mouseenter="showTip($el, 'Daftar Customer')"
               @mouseleave="hideTip()"
               class="nav-item {{ request()->routeIs('customers*') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
                <span class="nav-label">Daftar Customer</span>
            </a>
            <a href="{{ route('institutions.index') }}"
               @click="mob = false"
               @mouseenter="showTip($el, 'Daftar Instansi')"
               @mouseleave="hideTip()"
               class="nav-item {{ request()->routeIs('institutions*') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                </svg>
                <span class="nav-label">Daftar Instansi</span>
            </a>
        </div>
    </div>

    {{-- Pelatihan & Operasional (Khusus Admin) --}}
    <div>
        <p class="nav-section-title">PELATIHAN & OPERASIONAL</p>
        <div class="nav-section-divider"></div>
        <div class="space-y-0.5">
            <a href="{{ route('registrations.index') }}"
               @click="mob = false"
               @mouseenter="showTip($el, 'Pendaftaran Peserta')"
               @mouseleave="hideTip()"
               class="nav-item {{ request()->routeIs('registrations*') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                <span class="nav-label">Pendaftaran Peserta</span>
            </a>
            <a href="{{ route('training-schedules.index') }}"
               @click="mob = false"
               @mouseenter="showTip($el, 'Jadwal Pelatihan')"
               @mouseleave="hideTip()"
               class="nav-item {{ request()->routeIs('training-schedules*') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                <span class="nav-label">Jadwal Pelatihan</span>
            </a>
            <a href="{{ route('trainers.index') }}"
               @click="mob = false"
               @mouseenter="showTip($el, 'Daftar Trainer')"
               @mouseleave="hideTip()"
               class="nav-item {{ request()->routeIs('trainers*') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                <span class="nav-label">Daftar Trainer</span>
            </a>
            <a href="{{ route('products.index') }}"
               @click="mob = false"
               @mouseenter="showTip($el, 'Katalog Program')"
               @mouseleave="hideTip()"
               class="nav-item {{ request()->routeIs('products*') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                <span class="nav-label">Katalog Program</span>
            </a>
        </div>
    </div>

    {{-- Keuangan (Admin: Verifikasi & Komisi Semua Sales) --}}
    <div>
        <p class="nav-section-title">KEUANGAN & KOMISI</p>
        <div class="nav-section-divider"></div>
        <div class="space-y-0.5">
            <a href="{{ route('payments.index') }}"
               @click="mob = false"
               @mouseenter="showTip($el, 'Verifikasi Pembayaran')"
               @mouseleave="hideTip()"
               class="nav-item {{ request()->routeIs('payments*') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
                <span class="nav-label">Verifikasi Bayar</span>
            </a>
            <a href="{{ route('commissions.index') }}"
               @click="mob = false"
               @mouseenter="showTip($el, 'Komisi Sales')"
               @mouseleave="hideTip()"
               class="nav-item {{ request()->routeIs('commissions*') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="nav-label">Komisi Sales</span>
            </a>
        </div>
    </div>

    {{-- Master Data & Pengguna (Admin) --}}
    <div>
        <p class="nav-section-title">PENGATURAN (ADMIN)</p>
        <div class="nav-section-divider"></div>
        <div class="space-y-0.5">
            <a href="{{ route('users.index') }}"
               @click="mob = false"
               @mouseenter="showTip($el, 'Manajemen Pengguna')"
               @mouseleave="hideTip()"
               class="nav-item {{ request()->routeIs('users*') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="nav-label">Manajemen Pengguna</span>
            </a>
            <a href="{{ route('lead-imports.index') }}"
               @click="mob = false"
               @mouseenter="showTip($el, 'Import Batch CRM')"
               @mouseleave="hideTip()"
               class="nav-item {{ request()->routeIs('lead-imports*') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                </svg>
                <span class="nav-label">Import Batch CRM</span>
            </a>
        </div>
    </div>

    @else
    {{-- ══════════════════════════════════════════════ --}}
    {{-- MENU KHUSUS SALES (AFFILIATE MARKETER PARTNER) --}}
    {{-- ══════════════════════════════════════════════ --}}

    {{-- Prospek & Leads (Affiliate) --}}
    <div>
        <p class="nav-section-title">AFFILIATE & LEADS</p>
        <div class="nav-section-divider"></div>
        <div class="space-y-0.5">
            <a href="{{ route('leads.index') }}"
               @click="mob = false"
               @mouseenter="showTip($el, 'Leads Prospek')"
               @mouseleave="hideTip()"
               class="nav-item {{ request()->routeIs('leads*') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <span class="nav-label">Leads & Prospek</span>
            </a>
            <a href="{{ route('follow-ups.index') }}"
               @click="mob = false"
               @mouseenter="showTip($el, 'Follow-up Saya')"
               @mouseleave="hideTip()"
               class="nav-item {{ request()->routeIs('follow-ups*') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                </svg>
                <span class="nav-label">Follow-up Saya</span>
            </a>
            <a href="{{ route('reminders.index') }}"
               @click="mob = false"
               @mouseenter="showTip($el, 'Notifikasi & Pengingat')"
               @mouseleave="hideTip()"
               class="nav-item {{ request()->routeIs('reminders*') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                <span class="nav-label">Notifikasi & Pengingat</span>
                @if(isset($unreadRemindersCount) && $unreadRemindersCount > 0)
                    <span class="ml-auto text-[10px] bg-red-500 text-white font-bold px-1.5 py-0.5 rounded-full">
                        {{ $unreadRemindersCount }}
                    </span>
                @endif
            </a>
            <a href="{{ route('activities.index') }}"
               @click="mob = false"
               @mouseenter="showTip($el, 'Riwayat Aktivitas')"
               @mouseleave="hideTip()"
               class="nav-item {{ request()->routeIs('activities*') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="nav-label">Riwayat Aktivitas</span>
            </a>
        </div>
    </div>

    {{-- Program & Harga --}}
    <div>
        <p class="nav-section-title">PRODUK & HARGA</p>
        <div class="nav-section-divider"></div>
        <div class="space-y-0.5">
            <a href="{{ route('products.index') }}"
               @click="mob = false"
               @mouseenter="showTip($el, 'Katalog Program')"
               @mouseleave="hideTip()"
               class="nav-item {{ request()->routeIs('products*') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
                <span class="nav-label">Katalog Program</span>
            </a>
        </div>
    </div>

    {{-- Transaksi & Komisi Affiliate --}}
    <div>
        <p class="nav-section-title">TRANSAKSI & KOMISI</p>
        <div class="nav-section-divider"></div>
        <div class="space-y-0.5">
            <a href="{{ route('payments.index') }}"
               @click="mob = false"
               @mouseenter="showTip($el, 'Kirim Bukti Bayar')"
               @mouseleave="hideTip()"
               class="nav-item {{ request()->routeIs('payments*') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
                <span class="nav-label">Kirim Bukti Bayar</span>
            </a>
            <a href="{{ route('commissions.index') }}"
               @click="mob = false"
               @mouseenter="showTip($el, 'Komisi Saya (10%)')"
               @mouseleave="hideTip()"
               class="nav-item {{ request()->routeIs('commissions*') ? 'active' : '' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                          d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span class="nav-label">Komisi Saya (10%)</span>
            </a>
        </div>
    </div>
    @endif
</nav>

{{-- Sign Out --}}
<div class="border-t border-gray-200 p-2 flex-shrink-0">
    <form method="POST" action="{{ route('logout') }}">
        @csrf
        <button type="submit"
                @mouseenter="showTip($el, 'Sign Out')"
                @mouseleave="hideTip()"
                class="nav-item w-full hover:bg-orange-50">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
            </svg>
            <span class="nav-label">Sign Out</span>
        </button>
    </form>
</div>
