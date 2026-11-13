<!DOCTYPE html>
<html lang="id" class="h-full overflow-hidden">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — Disty Akademi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/@hotwired/turbo@7.3.0/dist/turbo.es2017-umd.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        // Turbo SPA Settings & Progress Bar
        document.addEventListener('turbo:load', function() {
            if (window.Turbo) {
                window.Turbo.setProgressBarDelay(50);
            }
        });

        // Re-initialize Alpine saat navigasi halaman via SPA (Turbo)
        document.addEventListener('turbo:render', function() {
            if (window.Alpine) {
                window.Alpine.initTree(document.body);
            }
        });
    </script>
    <style>
        [x-cloak] { display: none !important; }

        /* ── Turbo SPA Progress Bar ── */
        .turbo-progress-bar {
            height: 3px !important;
            background: linear-gradient(90deg, #f97316, #ea580c) !important;
            box-shadow: 0 0 8px rgba(249, 115, 22, 0.8) !important;
        }

        /* ── Sidebar transition ── */
        .sidebar-desktop {
            transition: width 200ms cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* ── Nav items ── */
        .nav-item {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 13.5px;
            color: #6b7280;
            text-decoration: none;
            transition: background 150ms, color 150ms;
            white-space: nowrap;
        }
        .nav-item:hover {
            background: #fff7ed;
            color: #ea580c;
        }
        .nav-item.active {
            background: #f97316;
            color: #ffffff;
            font-weight: 600;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.12);
        }
        .nav-item svg {
            flex-shrink: 0;
        }

        /* ── Section labels & dividers ── */
        .nav-section-title {
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #9ca3af;
            padding: 0 12px;
            margin-bottom: 4px;
        }
        .nav-section-divider {
            display: none;
            height: 1px;
            background: #e5e7eb;
            margin: 8px 6px;
        }

        /* ── Collapsed mode adjustments ── */
        .sidebar-collapsed .nav-item {
            justify-content: center;
            padding: 9px;
            width: 42px;
            height: 42px;
            margin-left: auto;
            margin-right: auto;
            border-radius: 8px;
        }
        .sidebar-collapsed .nav-item .nav-label {
            display: none;
        }
        .sidebar-collapsed .nav-section-title {
            display: none;
        }
        .sidebar-collapsed .nav-section-divider {
            display: block;
        }

        /* ── Mobile Sidebar Overrides (always full & touch-friendly) ── */
        .mobile-sidebar .nav-label {
            display: inline !important;
        }
        .mobile-sidebar .nav-section-title {
            display: block !important;
        }
        .mobile-sidebar .nav-section-divider {
            display: none !important;
        }
        .mobile-sidebar .nav-item {
            justify-content: flex-start !important;
            width: 100% !important;
            padding: 10px 14px !important;
            font-size: 14px !important;
            min-height: 44px !important;
        }

        /* ── Consistent primary actions ── */
        button.bg-gray-900,
        button.bg-slate-900,
        button.bg-blue-600,
        button.bg-orange-500,
        a.bg-gray-900,
        a.bg-slate-900,
        a.bg-blue-600,
        a.bg-orange-500 {
            background-color: #f97316 !important;
        }
        button.hover\:bg-gray-800:hover,
        button.hover\:bg-slate-800:hover,
        button.hover\:bg-blue-700:hover,
        button.hover\:bg-orange-600:hover,
        a.hover\:bg-black:hover,
        a.hover\:bg-gray-800:hover,
        a.hover\:bg-slate-800:hover,
        a.hover\:bg-blue-700:hover,
        a.hover\:bg-orange-600:hover {
            background-color: #ea580c !important;
        }

        /* ── Hide scrollbar cleanly ── */
        aside, aside nav {
            scrollbar-width: none;
            -ms-overflow-style: none;
        }
        aside::-webkit-scrollbar, aside nav::-webkit-scrollbar {
            display: none;
        }
    </style>
    @stack('styles')
</head>
<body class="h-full h-[100dvh] max-h-[100dvh] overflow-hidden bg-gray-100"
      x-data="{
          mob: false,
          collapsed: localStorage.getItem('sidebar-collapsed') === '1',
          tooltip: { show: false, text: '', top: 0, left: 0 },
          toggleSidebar() {
              this.collapsed = !this.collapsed;
              localStorage.setItem('sidebar-collapsed', this.collapsed ? '1' : '0');
              this.tooltip.show = false;
          },
          showTip(el, text) {
              if (!this.collapsed) return;
              const r = el.getBoundingClientRect();
              this.tooltip = {
                  show: true,
                  text: text,
                  top: r.top + (r.height / 2),
                  left: r.right + 10
              };
          },
          hideTip() {
              this.tooltip.show = false;
          }
      }">

<div class="flex h-full h-[100dvh] max-h-[100dvh] overflow-hidden">

    {{-- ── Desktop Sidebar (always visible lg+) ── --}}
    <aside class="hidden lg:flex flex-col flex-shrink-0 sidebar-desktop relative h-full h-[100dvh] max-h-[100dvh] overflow-hidden"
           :class="collapsed ? 'sidebar-collapsed' : ''"
           :style="'width:' + (collapsed ? '68px' : '230px') + ';background-color: #ffffff;'">
        @include('layouts._sidebar')
    </aside>

    {{-- ── Mobile Sidebar Overlay ── --}}
    <div x-show="mob" x-cloak class="lg:hidden fixed inset-0 z-50 flex">
        {{-- backdrop --}}
        <div class="fixed inset-0 bg-black/50 backdrop-blur-xs"
             x-transition:enter="transition-opacity duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="mob = false"></div>
        {{-- panel --}}
        <aside class="relative z-10 flex flex-col w-64 max-w-[85vw] h-full overflow-y-auto shadow-2xl bg-white mobile-sidebar"
               x-transition:enter="transition ease-out duration-200"
               x-transition:enter-start="-translate-x-full opacity-0"
               x-transition:enter-end="translate-x-0 opacity-100"
               x-transition:leave="transition ease-in duration-150"
               x-transition:leave-start="translate-x-0 opacity-100"
               x-transition:leave-end="-translate-x-full opacity-0">
            @include('layouts._sidebar')
        </aside>
    </div>

    {{-- ── Main area ── --}}
    <div class="flex flex-col flex-1 min-w-0 min-h-0 h-full h-[100dvh] overflow-hidden">

        {{-- Topbar --}}
        <header class="h-13 bg-white border-b border-orange-100 flex items-center px-3 sm:px-4 gap-2 sm:gap-3 flex-shrink-0" style="height:56px">
            {{-- Mobile hamburger button --}}
            <button @click="mob = !mob"
                    class="lg:hidden w-9 h-9 flex items-center justify-center rounded-xl bg-orange-50 text-orange-600 hover:bg-orange-100 transition-colors -ml-1"
                    aria-label="Buka Menu">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            {{-- Desktop collapse toggle button --}}
            <button @click="toggleSidebar()"
                    type="button"
                    class="hidden lg:flex items-center justify-center w-8 h-8 rounded-lg text-gray-500 hover:text-orange-600 hover:bg-orange-50 transition-colors -ml-1 mr-1"
                    :title="collapsed ? 'Perluas Menu' : 'Perkecil Menu'">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                </svg>
            </button>

            {{-- breadcrumb --}}
            <div class="flex items-center gap-1.5 text-sm">
                <span class="text-gray-400 hidden sm:block">Disty Akademi</span>
                <span class="text-gray-300 hidden sm:block">/</span>
                <span class="text-gray-700 font-medium">@yield('title', 'Dashboard')</span>
            </div>

            <div class="ml-auto flex items-center gap-2">
                <span class="hidden md:block text-xs text-gray-400">{{ now()->translatedFormat('d M Y') }}</span>

                @if(auth()->user()->isSales())
                {{-- Notifications Bell (Hanya untuk Sales - Penerima Instruksi Follow-up dari Admin) --}}
                <div x-data="{
                    bellOpen: false,
                    unreadCount: {{ $unreadRemindersCount ?? 0 }},
                    toggleBell() {
                        this.bellOpen = !this.bellOpen;
                        if (this.bellOpen && this.unreadCount > 0) {
                            this.unreadCount = 0;
                            fetch('{{ route('reminders.mark-all-read') }}', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json'
                                }
                            }).catch(() => {});
                        }
                    }
                }" class="relative">
                    <button @click="toggleBell()"
                            type="button"
                            class="relative p-2 text-gray-500 hover:text-orange-600 hover:bg-orange-50 rounded-xl transition-colors"
                            title="Notifikasi">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        <span x-show="unreadCount > 0"
                              x-text="unreadCount"
                              class="absolute top-1 right-1 flex h-4 min-w-4 px-1 items-center justify-center rounded-full bg-red-500 text-[9px] font-bold text-white ring-2 ring-white animate-pulse">
                        </span>
                    </button>

                    <div x-show="bellOpen" @click.away="bellOpen = false" x-cloak
 class="absolute right-0 top-full mt-2 w-84 sm:w-96 bg-white border border-gray-200 shadow-xl py-2 z-50 overflow-hidden">
                        <div class="px-4 py-2 border-b border-gray-100 flex items-center justify-between">
                            <div class="flex items-center gap-1.5">
                                <span class="font-bold text-xs text-gray-900">
                                    Notifikasi & Pemberitahuan
                                </span>
                                @if(isset($unreadRemindersCount) && $unreadRemindersCount > 0)
                                    <span class="bg-red-100 text-red-700 text-[10px] font-bold px-1.5 py-0.2 rounded-full">
                                        {{ $unreadRemindersCount }} Baru
                                    </span>
                                @endif
                            </div>
                            <div class="flex items-center gap-2">
                                @if(isset($unreadRemindersCount) && $unreadRemindersCount > 0)
                                    <form method="POST" action="{{ route('reminders.mark-all-read') }}">
                                        @csrf
                                        <button type="submit" class="text-[11px] text-gray-500 hover:text-gray-800 transition-colors">
                                            Tandai Dibaca
                                        </button>
                                    </form>
                                    <span class="text-gray-300 text-xs">•</span>
                                @endif
                                <a href="{{ route('reminders.index') }}" class="text-[11px] font-semibold text-orange-600 hover:underline">
                                    Lihat Semua
                                </a>
                            </div>
                        </div>

                        <div class="max-h-84 overflow-y-auto divide-y divide-gray-50">
                            @if(isset($activeReminders) && $activeReminders->count() > 0)
                                @foreach($activeReminders as $rem)
                                    @if($rem->type === 'commission_paid')
                                        {{-- ── NOTIFIKASI KOMISI SUDAH DIBAYAR (STATUS PAID) ── --}}
                                        <div class="p-3 bg-emerald-50/40 hover:bg-emerald-50/70 transition-colors space-y-2">
                                            <div class="flex items-start justify-between gap-2">
                                                <div class="min-w-0">
                                                    <div class="flex items-center gap-1.5">
                                                        <span class="text-xs font-bold text-emerald-900">
                                                            Komisi Rp {{ number_format($rem->commission?->commission_amount ?? 150000, 0, ',', '.') }} Cair!
                                                        </span>
                                                    </div>
                                                    <p class="text-[11px] text-gray-600 truncate mt-0.5">
                                                        Prospek: <span class="font-semibold text-gray-800">{{ $rem->lead->name }}</span>
                                                        {{ $rem->lead->company ? '• ' . $rem->lead->company : '' }}
                                                    </p>
                                                </div>
                                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-200">
                                                    PAID
                                                </span>
                                            </div>

 <div class="bg-white/90 p-2 text-[11px] text-emerald-950 border border-emerald-200/80">
                                                {{ $rem->message }}
                                            </div>

                                            <div class="flex items-center justify-between pt-1">
                                                <span class="text-[10px] text-gray-400">{{ $rem->created_at->diffForHumans() }}</span>
                                                <div class="flex items-center gap-1.5">
                                                    <a href="{{ route('commissions.index') }}"
                                                       class="inline-flex items-center gap-1 px-2.5 py-1 text-[11px] font-bold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-2xs">
                                                        <span>Lihat Komisi</span>
                                                    </a>
                                                    <a href="{{ route('leads.show', $rem->lead) }}"
                                                       class="px-2 py-1 text-[11px] font-medium text-gray-700 bg-white hover:bg-gray-100 border border-gray-200 rounded-lg">
                                                        Lead
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        {{-- ── NOTIFIKASI PENGINGAT FOLLOW-UP ── --}}
                                        <div class="p-3 hover:bg-orange-50/40 transition-colors space-y-2">
                                            <div class="flex items-start justify-between gap-2">
                                                <div class="min-w-0">
                                                    <p class="text-xs font-bold text-gray-900 truncate">
                                                        {{ $rem->lead->name }}
                                                    </p>
                                                    <p class="text-[11px] text-gray-500 truncate">
                                                        {{ $rem->lead->company ? $rem->lead->company . ' • ' : '' }}{{ $rem->lead->product?->name ?? 'Program Akademi' }}
                                                    </p>
                                                </div>
                                                <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full {{ $rem->priority === 'urgent' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-600' }}">
                                                    {{ $rem->priority === 'urgent' ? 'Mendesak' : 'Pengingat' }}
                                                </span>
                                            </div>

 <div class="bg-amber-50/80 p-2 text-[11px] text-amber-950 italic border border-amber-100">
                                                "{{ \Illuminate\Support\Str::limit($rem->message, 80) }}"
                                            </div>

                                            <div class="flex items-center justify-between pt-1">
                                                <span class="text-[10px] text-gray-400">{{ $rem->created_at->diffForHumans() }}</span>
                                                <div class="flex items-center gap-1.5">
                                                    @if($rem->lead->whatsapp_follow_up_url)
                                                        <a href="{{ $rem->lead->whatsapp_follow_up_url }}" target="_blank"
                                                           class="inline-flex items-center gap-1 px-2.5 py-1 text-[11px] font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-2xs">
                                                            <span>Chat WA</span>
                                                        </a>
                                                    @endif
                                                    <a href="{{ route('leads.show', $rem->lead) }}"
                                                       class="px-2 py-1 text-[11px] font-medium text-gray-700 bg-gray-100 hover:bg-gray-200 rounded-lg">
                                                        Detail
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            @else
                                <div class="py-8 text-center text-gray-400">
                                    <div class="w-10 h-10 bg-gray-100 text-gray-400 rounded-full flex items-center justify-center mx-auto mb-2">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    </div>
                                    <p class="text-xs font-medium text-gray-600">Tidak ada notifikasi baru</p>
                                    <p class="text-[10px] text-gray-400 mt-0.5">Semua prospek & komisi sudah terpantau</p>
                                </div>
                            @endif
                        </div>

                        <div class="px-4 py-2 bg-gray-50 border-t border-gray-100 flex items-center justify-between text-xs font-semibold">
                            <a href="{{ route('commissions.index') }}" class="text-emerald-600 hover:text-emerald-700">
                                Komisi Saya &rarr;
                            </a>
                            <a href="{{ route('reminders.index') }}" class="text-orange-600 hover:text-orange-700">
                                Semua Notifikasi &rarr;
                            </a>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Profile --}}
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open"
                            class="flex items-center gap-2 px-2 py-1 rounded-lg hover:bg-gray-50 border border-transparent hover:border-gray-200 transition-colors">
                        <div class="w-7 h-7 rounded-full bg-orange-500 flex items-center justify-center text-white text-xs font-bold">
                            {{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}
                        </div>
                        <span class="hidden sm:block text-sm text-gray-700 font-medium">{{ auth()->user()->name ?? 'User' }}</span>
                        <svg class="w-3 h-3 text-gray-400 hidden sm:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="open" @click.away="open = false" x-cloak
 class="absolute right-0 top-full mt-1 w-44 bg-white border border-gray-200 shadow-lg py-1.5 z-50">
                        <div class="px-3 py-2 border-b border-gray-100 mb-1">
                            <p class="text-xs font-semibold text-gray-900 truncate">{{ auth()->user()->name }}</p>
                            <p class="text-xs text-gray-400 truncate">{{ ucfirst(auth()->user()->role) }}</p>
                        </div>
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit"
                                    class="w-full flex items-center gap-2 px-3 py-1.5 text-sm text-gray-600 hover:bg-red-50 hover:text-red-600 transition-colors text-left">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                          d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                                Keluar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        {{-- Content --}}
        <main class="flex-1 min-h-0 overflow-y-auto p-5 overscroll-contain">
            @if(session('success'))
 <div class="mb-4 flex gap-3 items-center bg-green-50 border border-green-200 text-green-700 text-sm px-4 py-2.5">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                {{ session('success') }}
            </div>
            @endif
            @if(session('error'))
 <div class="mb-4 flex gap-3 items-center bg-red-50 border border-red-200 text-red-700 text-sm px-4 py-2.5">
                <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ session('error') }}
            </div>
            @endif
            @yield('content')
        </main>
    </div>
</div>

{{-- ── Floating Tooltip for Collapsed Sidebar (never clipped, rendered at body level) ── --}}
<div x-show="collapsed && tooltip.show"
     x-cloak
     class="fixed z-[9999] pointer-events-none flex items-center transition-opacity duration-150"
     :style="`top: ${tooltip.top}px; left: ${tooltip.left}px; transform: translateY(-50%);`">
    <div class="bg-orange-500 text-white text-xs font-semibold px-3 py-1.5 rounded-md whitespace-nowrap tracking-wide">
        <span x-text="tooltip.text"></span>
    </div>
</div>

<script>
    (() => {
        const storageKey = 'disty-sidebar-scroll-top';
        const navs = document.querySelectorAll('.sidebar-nav');
        const savedScrollTop = Number(sessionStorage.getItem(storageKey) || 0);

        navs.forEach((nav) => {
            nav.scrollTop = savedScrollTop;
            nav.addEventListener('scroll', () => {
                sessionStorage.setItem(storageKey, String(nav.scrollTop));
            }, { passive: true });
        });
    })();
</script>

</body>
</html>
