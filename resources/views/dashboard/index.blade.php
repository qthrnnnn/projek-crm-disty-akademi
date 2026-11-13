@extends('layouts.app')

@section('title', 'Dashboard')

@push('styles')
<style>
    /* ── Animasi Donut Chart Berputar & Mengisi (Smooth & Professional) ── */
    @keyframes donut-spin-in {
        0% {
            transform: rotate(-180deg) scale(0.92);
            opacity: 0.25;
        }
        100% {
            transform: rotate(-90deg) scale(1);
            opacity: 1;
        }
    }
    @keyframes count-fade-pop {
        0% {
            transform: scale(0.65);
            opacity: 0;
        }
        65% {
            transform: scale(1.08);
            opacity: 1;
        }
        100% {
            transform: scale(1);
            opacity: 1;
        }
    }
    @keyframes bar-rise {
        from {
            transform: scaleY(0);
            opacity: 0.3;
        }
        to {
            transform: scaleY(1);
            opacity: 1;
        }
    }
    @keyframes progress-slide {
        from {
            width: 0% !important;
        }
    }
    @keyframes card-fade-up {
        from {
            opacity: 0;
            transform: translateY(8px);
        }
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .animate-donut-chart {
        animation: donut-spin-in 1.1s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        transform-origin: center;
    }
    .animate-donut-center {
        animation: count-fade-pop 0.9s cubic-bezier(0.16, 1, 0.3, 1) 0.15s both;
    }
    .animate-bar-grow {
        transform-origin: bottom;
        animation: bar-rise 0.85s cubic-bezier(0.16, 1, 0.3, 1) both;
    }
    .animate-progress-grow {
        animation: progress-slide 1s cubic-bezier(0.16, 1, 0.3, 1) both;
    }
    .animate-card-rise {
        animation: card-fade-up 0.5s cubic-bezier(0.16, 1, 0.3, 1) both;
    }
</style>
@endpush

@section('content')

{{-- Header baris atas --}}
<div class="flex flex-wrap items-center justify-between gap-3 mb-6">
    <div>
        <div class="flex items-center gap-2">
            <h1 class="text-xl font-semibold text-gray-900">{{ $isSales ? 'Dashboard Sales' : 'Dashboard Manajemen' }}</h1>
            <span class="inline-flex items-center gap-1.5 text-xs font-medium text-gray-500">
                <span class="w-1.5 h-1.5 rounded-full {{ $isSales ? 'bg-emerald-500' : 'bg-orange-500' }}"></span>
                {{ ucfirst(auth()->user()->role) }}
            </span>
        </div>
        <p class="text-sm text-gray-400 mt-0.5">
            {{ $isSales ? 'Menampilkan data lead yang ditugaskan kepada Anda & perolehan komisi Anda' : 'Ringkasan performa seluruh tim sales, verifikasi transaksi, & katalog akademi' }}
        </p>
    </div>
</div>

{{-- ── Filter tanggal ── --}}
<div class="grid grid-cols-1 lg:grid-cols-[minmax(0,1fr)_280px] gap-3 sm:gap-4 mb-6 items-stretch">
    <div class="h-full">
        <div class="grid grid-cols-2 gap-2.5 sm:gap-3 h-full auto-rows-fr">
            <div class="bg-white border border-gray-100 p-3 sm:p-4 h-full flex flex-col justify-between shadow-2xs animate-card-rise hover:shadow-xs transition-all" style="animation-delay: 50ms">
                <div>
                    <p class="text-[11px] sm:text-xs text-gray-400 mb-1">Lead Masuk</p>
                    <p class="text-2xl sm:text-3xl font-bold text-gray-900">{{ $todayLeadsIn }}</p>
                </div>
                <p class="mt-2 text-[10.5px] sm:text-xs text-gray-400">Total masuk tanggal ini</p>
            </div>

            <div class="bg-white border border-gray-100 p-3 sm:p-4 h-full flex flex-col justify-between shadow-2xs animate-card-rise hover:shadow-xs transition-all" style="animation-delay: 100ms">
                <div>
                    <p class="text-[11px] sm:text-xs text-gray-400 mb-1">Sudah Dijawab</p>
                    <p class="text-2xl sm:text-3xl font-bold text-gray-900">{{ $todayLeadsAnswered }}</p>
                </div>
                <p class="mt-2 text-[10.5px] sm:text-xs text-emerald-600 font-medium">Sudah dikontak sales</p>
            </div>

            <div class="bg-white border border-gray-100 p-3 sm:p-4 h-full flex flex-col justify-between shadow-2xs animate-card-rise hover:shadow-xs transition-all" style="animation-delay: 150ms">
                <div>
                    <p class="text-[11px] sm:text-xs text-gray-400 mb-1">Belum Dijawab</p>
                    <p class="text-2xl sm:text-3xl font-bold {{ $todayLeadsUnanswered > 0 ? 'text-amber-600' : 'text-gray-900' }}">
                        {{ $todayLeadsUnanswered }}
                    </p>
                </div>
                <p class="mt-2 text-[10.5px] sm:text-xs {{ $todayLeadsUnanswered > 0 ? 'text-amber-600 font-medium' : 'text-gray-400' }}">Perlu segera dikontak</p>
            </div>

            <div class="bg-white border border-gray-100 p-3 sm:p-4 h-full flex flex-col justify-between shadow-2xs animate-card-rise hover:shadow-xs transition-all" style="animation-delay: 200ms">
                <div>
                    <p class="text-[11px] sm:text-xs text-gray-400 mb-1">Sudah Merespon</p>
                    <p class="text-2xl sm:text-3xl font-bold text-gray-900">{{ $todayLeadsResponded }}</p>
                </div>
                <p class="mt-2 text-[10.5px] sm:text-xs text-blue-600 font-medium">Aktif / negosiasi</p>
            </div>
        </div>
    </div>

    <div x-data="dashboardCalendar('{{ $selectedDateString }}', '{{ today()->toDateString() }}')" class="bg-white border border-gray-100 p-3 sm:p-4 h-full shadow-2xs">
        <div class="flex items-center justify-between mb-2.5">
            <button type="button" @click="previousMonth()" class="w-8 h-8 rounded-lg text-gray-400 hover:bg-orange-50 hover:text-orange-600 transition-colors flex items-center justify-center" aria-label="Bulan sebelumnya">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </button>
            <div class="flex items-center gap-1.5">
                <p class="text-xs sm:text-sm font-semibold text-gray-900" x-text="monthNames[month] + ' ' + year"></p>
                <button x-show="selectedDate !== todayIso"
                        @click="window.location.href = '{{ route('dashboard') }}'"
                        type="button"
                        class="text-[10px] text-orange-600 font-bold hover:underline bg-orange-50 px-1.5 py-0.5 rounded transition-all"
                        title="Kembali ke Hari Ini">
                    Hari Ini
                </button>
            </div>
            <button type="button" @click="nextMonth()" class="w-8 h-8 rounded-lg text-gray-400 hover:bg-orange-50 hover:text-orange-600 transition-colors flex items-center justify-center" aria-label="Bulan berikutnya">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </button>
        </div>
        <div class="grid grid-cols-7 gap-1 text-center">
            <template x-for="weekday in weekdays" :key="weekday">
                <span class="text-[10px] font-medium text-gray-400 py-1" x-text="weekday"></span>
            </template>
            <template x-for="(day, index) in days" :key="index">
                <span>
                    <button
                        x-show="day"
                        type="button"
                        @click="selectDay(day)"
                        :class="dayClass(day)"
                        class="w-7 h-7 sm:w-8 sm:h-8 rounded-full text-[11px] sm:text-xs transition-all flex items-center justify-center mx-auto relative"
                        :title="isToday(day ? day.iso : '') ? 'Hari Ini (' + (day ? day.number : '') + ')' : ''"
                        :aria-label="day ? day.iso : ''">
                        <span x-text="day ? day.number : ''"></span>
                    </button>
                </span>
            </template>
        </div>
    </div>
</div>

{{-- ── Baris 2: Pipeline + Pilihan Periode Prospek ── --}}
@php
    $stages = [
        ['key' => 'new',         'label' => 'Baru',        'count' => $newLeads,         'dot' => 'bg-slate-400',   'hex' => '#94a3b8'],
        ['key' => 'contacted',   'label' => 'Dikontak',    'count' => $contactedLeads,   'dot' => 'bg-blue-400',    'hex' => '#60a5fa'],
        ['key' => 'active',      'label' => 'Aktif',       'count' => $activeLeads,      'dot' => 'bg-cyan-500',    'hex' => '#06b6d4'],
        ['key' => 'negotiation', 'label' => 'Negosiasi',   'count' => $negotiationLeads, 'dot' => 'bg-amber-400',   'hex' => '#fbbf24'],
        ['key' => 'won',         'label' => 'Berhasil',    'count' => $wonLeads,         'dot' => 'bg-emerald-500', 'hex' => '#10b981'],
        ['key' => 'lost',        'label' => 'Ditolak',     'count' => $lostLeads,        'dot' => 'bg-red-400',     'hex' => '#f87171'],
        ['key' => 'inactive',    'label' => 'Tidak Aktif', 'count' => $inactiveLeads,    'dot' => 'bg-gray-300',    'hex' => '#cbd5e1'],
    ];
@endphp

<div class="grid grid-cols-1 lg:grid-cols-3 gap-4 mb-5 items-stretch"
     x-data="pipelinePeriodManager(@js($pipelineLeadsData), '{{ $todayIso }}', '{{ $todayDisplay }}', {{ $currentYear }}, {{ $currentMonth }})">

    {{-- Pipeline Total (Diagram Lingkaran Reaktif terhadap Periode) --}}
    <div class="lg:col-span-2 bg-white border border-gray-100 p-5 flex flex-col justify-between shadow-2xs">
        <div>
            {{-- Header Panel (Sejajar dengan Panel Kanan) --}}
            <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-3">
                <div>
                    <p class="text-xs font-bold text-gray-800 uppercase tracking-wide">Distribusi Status Lead</p>
                    <p class="text-[11px] text-gray-400 mt-0.5" x-text="periodLabel">Hari Ini</p>
                </div>
                <span class="text-xs text-gray-500">Total: <strong class="text-gray-900 font-bold" x-text="totalCount">{{ $totalLeads }}</strong></span>
            </div>

            {{-- Konten Utama (Posisi di Atas Langsung di Bawah Header, Tidak Melorot ke Bawah) --}}
            <div class="flex flex-col sm:flex-row items-center gap-6 pt-2">
                {{-- Donut Chart --}}
                <div class="relative flex-shrink-0 mx-auto sm:mx-0">
                    <svg viewBox="0 0 160 160" class="w-44 h-44 sm:w-52 sm:h-52 animate-donut-chart">
                        <circle cx="80" cy="80" r="54" fill="none" stroke="#f1f5f9" stroke-width="20" />
                        @foreach($stages as $i => $stage)
                            <circle x-show="currentStages[{{ $i }}] && currentStages[{{ $i }}].count > 0"
                                    cx="80" cy="80" r="54" fill="none"
                                    stroke="{{ $stage['hex'] }}" stroke-width="20"
                                    :stroke-dasharray="currentStages[{{ $i }}] ? currentStages[{{ $i }}].dashArray : '0 339'"
                                    :stroke-dashoffset="currentStages[{{ $i }}] ? currentStages[{{ $i }}].dashOffset : '0'"
                                    :class="getSliceClass('{{ $stage['key'] }}')"
                                    @click="selectStage('{{ $stage['key'] }}')"
                                    @mouseenter="hoverStage = '{{ $stage['key'] }}'"
                                    @mouseleave="hoverStage = null">
                                <title>{{ $stage['label'] }}</title>
                            </circle>
                        @endforeach
                    </svg>
                    <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none animate-donut-center">
                        <span class="text-2xl sm:text-3xl font-extrabold text-gray-900" x-text="centerCount">{{ $totalLeads }}</span>
                        <span class="text-[10px] text-gray-400 font-semibold tracking-wider uppercase mt-0.5" x-text="centerLabel">Total Lead</span>
                    </div>
                </div>

                {{-- Stats grid (Tombol Interaktif Status) --}}
                <div class="flex-1 w-full">
                    <div class="grid grid-cols-4 lg:grid-cols-7 gap-2">
                        @foreach($stages as $i => $stage)
                        <button type="button"
                                @click="selectStage('{{ $stage['key'] }}')"
                                @mouseenter="hoverStage = '{{ $stage['key'] }}'"
                                @mouseleave="hoverStage = null"
                                class="text-center p-1.5 transition-all"
                                :class="activeStage === '{{ $stage['key'] }}' ? 'bg-orange-50 border border-orange-300 ring-1 ring-orange-200' : 'border border-transparent hover:bg-gray-50'">
                            <span class="inline-block w-1.5 h-1.5 rounded-full {{ $stage['dot'] }} mb-1"></span>
                            <p class="text-base sm:text-lg font-bold text-gray-900 leading-tight"
                               x-text="currentStages[{{ $i }}] ? currentStages[{{ $i }}].count : 0">{{ $stage['count'] }}</p>
                            <p class="text-[10px] text-gray-500 mt-0.5 leading-tight truncate">{{ $stage['label'] }}</p>
                        </button>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        {{-- Footer Bar (Conversion Rate di Bawah, Sejajar dengan Tombol Aksi Panel Kanan) --}}
        <div class="pt-3 border-t border-gray-100 mt-2 flex items-center justify-between text-xs text-gray-500">
            <span>Conversion rate: <strong class="text-gray-900 font-bold" x-text="conversionRate + '%'">{{ $conversionRate }}%</strong></span>
            <button type="button"
                    x-show="activeStage !== 'all'"
                    @click="activeStage = 'all'"
                    class="text-[11px] text-orange-600 hover:text-orange-700 font-semibold underline">
                Tampilkan Semua Status
            </button>
        </div>
    </div>

    {{-- Panel Kanan: Pilihan Periode Prospek & Kinerja --}}
    <div class="bg-white border border-gray-100 p-5 flex flex-col justify-between shadow-2xs">
        <div>
            {{-- Header Panel (Sejajar dengan Panel Kiri) --}}
            <div class="flex items-center justify-between pb-3 border-b border-gray-100 mb-3">
                <div>
                    <p class="text-xs font-bold text-gray-800 uppercase tracking-wide">Pilihan Periode Prospek</p>
                    <p class="text-[11px] text-gray-400 mt-0.5" x-text="periodLabel">Hari Ini</p>
                </div>
                <button type="button"
                        x-show="activeStage !== 'all'"
                        @click="activeStage = 'all'"
                        class="text-[11px] text-orange-600 hover:text-orange-700 font-semibold flex items-center gap-1 transition-colors">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    Semua Status
                </button>
            </div>

            {{-- 3 Segmented Mode Tabs --}}
            <div class="grid grid-cols-3 gap-1 p-1 bg-gray-100 mb-2.5">
                <button type="button"
                        @click="setFilterMode('today')"
                        :class="filterMode === 'today' ? 'bg-orange-500 text-white font-bold shadow-xs' : 'text-gray-600 hover:text-gray-900'"
                        class="py-1.5 px-2 text-center text-xs rounded transition-all">
                    Hari Ini
                </button>
                <button type="button"
                        @click="setFilterMode('month')"
                        :class="filterMode === 'month' ? 'bg-orange-500 text-white font-bold shadow-xs' : 'text-gray-600 hover:text-gray-900'"
                        class="py-1.5 px-2 text-center text-xs rounded transition-all">
                    Pilih Bulan
                </button>
                <button type="button"
                        @click="setFilterMode('all')"
                        :class="filterMode === 'all' ? 'bg-orange-500 text-white font-bold shadow-xs' : 'text-gray-600 hover:text-gray-900'"
                        class="py-1.5 px-2 text-center text-xs rounded transition-all">
                    Semua Data
                </button>
            </div>

            {{-- Controls per Mode --}}
            {{-- Mode: Hari Ini --}}
            <div x-show="filterMode === 'today'" class="flex items-center justify-between p-1.5 px-2.5 bg-orange-50/60 border border-orange-100 mb-2.5 text-xs">
                <span class="text-gray-600 flex items-center gap-1.5">
                    <span class="w-2 h-2 rounded-full bg-orange-500 animate-pulse"></span>
                    Target Hari Ini:
                </span>
                <span class="font-bold text-gray-900" x-text="todayDisplay"></span>
            </div>

            {{-- Mode: Pilih Bulan & Tahun (Semua Bulan & Tahun Tersedia Bebas Dipilih) --}}
            <div x-show="filterMode === 'month'" class="flex items-center gap-1.5 p-1 bg-gray-50 border border-gray-200 mb-2.5">
                <button type="button"
                        @click="prevMonth()"
                        title="Bulan sebelumnya"
                        class="p-1 rounded hover:bg-gray-200 text-gray-600 hover:text-gray-900 transition-colors shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </button>

                <div class="flex-1">
                    <select x-model.number="selectedMonth"
                            class="w-full text-xs font-semibold text-gray-800 bg-white border border-gray-300 py-1 px-2 focus:ring-1 focus:ring-orange-500 focus:border-orange-500 outline-none">
                        <template x-for="(name, idx) in monthNames" :key="idx">
                            <option :value="idx + 1" x-text="name"></option>
                        </template>
                    </select>
                </div>

                <div class="w-20">
                    <select x-model.number="selectedYear"
                            class="w-full text-xs font-semibold text-gray-800 bg-white border border-gray-300 py-1 px-2 focus:ring-1 focus:ring-orange-500 focus:border-orange-500 outline-none">
                        <template x-for="yr in availableYears" :key="yr">
                            <option :value="yr" x-text="yr"></option>
                        </template>
                    </select>
                </div>

                <button type="button"
                        @click="nextMonth()"
                        title="Bulan berikutnya"
                        class="p-1 rounded hover:bg-gray-200 text-gray-600 hover:text-gray-900 transition-colors shrink-0">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
            </div>

            {{-- Mode: Semua Data --}}
            <div x-show="filterMode === 'all'" class="flex items-center justify-between p-1.5 px-2.5 bg-blue-50/60 border border-blue-100 mb-2.5 text-xs">
                <span class="text-gray-600 flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg>
                    Akumulasi Historis:
                </span>
                <span class="font-bold text-blue-900">Sepanjang Waktu</span>
            </div>

            {{-- 3-Stat Summary Bar Periode Terpilih --}}
            <div class="grid grid-cols-3 gap-2 p-2 bg-gray-50 border border-gray-100 text-center mb-2.5">
                <div>
                    <p class="text-[10px] text-gray-500 uppercase tracking-wide">Total Lead</p>
                    <p class="text-base sm:text-lg font-bold text-gray-900" x-text="totalCount">{{ $totalLeads }}</p>
                </div>
                <div>
                    <p class="text-[10px] text-gray-500 uppercase tracking-wide">Closing</p>
                    <p class="text-base sm:text-lg font-bold text-emerald-600" x-text="wonCount">{{ $wonLeads }}</p>
                </div>
                <div>
                    <p class="text-[10px] text-gray-500 uppercase tracking-wide">Konversi</p>
                    <p class="text-base sm:text-lg font-bold text-gray-900" x-text="conversionRate + '%'">{{ $conversionRate }}%</p>
                </div>
            </div>

            {{-- Filter Status Active Pill (jika mengklik status di lingkaran) --}}
            <div x-show="activeStage !== 'all'" class="mb-2 flex items-center justify-between text-xs bg-amber-50 border border-amber-200 px-2.5 py-1 text-amber-800">
                <span>Filter status: <strong x-text="activeStageLabel"></strong></span>
                <button type="button" @click="activeStage = 'all'" class="text-[11px] underline font-medium hover:text-amber-900">✕ Tampilkan Semua</button>
            </div>

            {{-- Daftar Prospek Nyata di Periode Terpilih --}}
            <div class="overflow-y-auto max-h-[175px] space-y-2 pr-1 custom-scroll">
                <template x-if="filteredLeads.length === 0">
                    <div class="py-6 text-center text-gray-400">
                        <svg class="w-7 h-7 mx-auto mb-1.5 opacity-40 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                        <p class="text-xs text-gray-400">Tidak ada prospek pada periode & status ini</p>
                    </div>
                </template>

                <template x-for="lead in filteredLeads.slice(0, 5)" :key="lead.id">
                    <div class="p-2.5 border border-gray-100 bg-white hover:border-orange-200 transition-all">
                        <div class="flex items-start justify-between gap-2 mb-1">
                            <div class="min-w-0 flex-1">
                                <a :href="lead.show_url" class="text-xs font-bold text-gray-900 hover:text-orange-600 truncate block" x-text="lead.name"></a>
                                <p class="text-[10px] text-gray-400 truncate" x-text="lead.product"></p>
                            </div>
                            <span class="text-[9px] px-1.5 py-0.5 font-bold uppercase tracking-wider shrink-0"
                                  :class="{
                                      'bg-emerald-50 text-emerald-700': lead.status === 'won',
                                      'bg-amber-50 text-amber-700': lead.status === 'negotiation',
                                      'bg-cyan-50 text-cyan-700': lead.status === 'active',
                                      'bg-blue-50 text-blue-700': lead.status === 'contacted',
                                      'bg-slate-100 text-slate-700': lead.status === 'new',
                                      'bg-red-50 text-red-700': lead.status === 'lost',
                                      'bg-gray-100 text-gray-500': lead.status === 'inactive'
                                  }" x-text="lead.status"></span>
                        </div>
                        
                        <div class="flex items-center justify-between pt-1 text-[11px]">
                            <span class="text-gray-400 text-[10px]" x-text="lead.phone"></span>
                            
                            <div class="flex items-center gap-1.5">
                                <template x-if="lead.wa_link">
                                    <a :href="lead.wa_link" target="_blank"
                                       title="Chat WhatsApp"
                                       class="inline-flex items-center gap-1 px-2 py-0.5 rounded text-[10px] font-semibold bg-emerald-600 hover:bg-emerald-700 text-white transition-colors">
                                        <svg class="w-2.5 h-2.5" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981z"/></svg>
                                        Chat WA
                                    </a>
                                </template>
                                <a :href="lead.show_url"
                                   class="px-2 py-0.5 rounded text-[10px] font-semibold bg-gray-100 hover:bg-gray-200 text-gray-700 transition-colors">
                                    Detail
                                </a>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- Footer Aksi --}}
        <div class="pt-3 border-t border-gray-100 mt-2">
            <a :href="tableLeadUrl"
               class="w-full inline-flex items-center justify-center gap-2 px-3 py-2 text-xs font-semibold rounded-lg bg-orange-500 hover:bg-orange-600 text-white shadow-xs transition-colors">
                <span x-text="activeStage === 'all' ? 'Buka Daftar Lead di CRM' : 'Lihat Semua Prospek ' + activeStageLabel"></span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
            </a>
        </div>
    </div>
</div>

{{-- ── Modul Operasional (Admin) vs Alur Kerja Affiliate (Sales) ── --}}
<div class="mb-5">
    @if(auth()->user()->isAdmin())
    <div class="flex items-center justify-between mb-3">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Modul Operasional & Pelatihan (Khusus Admin)</p>
        <span class="text-[11px] text-gray-400">Integrasi Data Customer & Jadwal Kelas</span>
    </div>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-2.5 sm:gap-3">
        <a href="{{ route('registrations.index') }}" class="group bg-white border border-blue-100 p-3 sm:p-4 text-left hover:bg-orange-500 hover:border-orange-500 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 animate-card-rise flex flex-col min-h-[130px] sm:min-h-[150px]" style="animation-delay: 50ms">
            <div class="flex items-start justify-between text-left">
                <p class="min-h-10 sm:min-h-12 max-w-[calc(100%-2rem)] text-sm sm:text-base font-bold leading-tight text-gray-900 group-hover:text-white 2xl:whitespace-nowrap">
                    <span>Pendaftaran</span><br class="card-title-br 2xl:hidden"><span class="card-title-space hidden 2xl:inline">&nbsp;</span><span>Peserta</span>
                </p>
                <span class="w-6 h-6 sm:w-7 sm:h-7 rounded-full bg-gray-100 text-gray-500 flex items-center justify-center group-hover:bg-white/20 group-hover:text-white transition-colors shrink-0">
                    <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 17L17 7m0 0H9m8 0v8"/>
                    </svg>
                </span>
            </div>
            <div class="mt-auto">
                <p class="text-[11px] sm:text-xs text-gray-400 group-hover:text-white/80 mt-1">Daftarkan ke jadwal aktif</p>
            </div>
        </a>

        <a href="{{ route('training-schedules.index') }}" class="group bg-white border border-blue-100 p-3 sm:p-4 text-left hover:bg-orange-500 hover:border-orange-500 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 animate-card-rise flex flex-col min-h-[130px] sm:min-h-[150px]" style="animation-delay: 100ms">
            <div class="flex items-start justify-between text-left">
                <p class="min-h-10 sm:min-h-12 max-w-[calc(100%-2rem)] text-sm sm:text-base font-bold leading-tight text-gray-900 group-hover:text-white 2xl:whitespace-nowrap">
                    <span>Jadwal</span><br class="card-title-br 2xl:hidden"><span class="card-title-space hidden 2xl:inline">&nbsp;</span><span>Pelatihan</span>
                </p>
                <span class="w-6 h-6 sm:w-7 sm:h-7 rounded-full bg-gray-100 text-gray-500 flex items-center justify-center group-hover:bg-white/20 group-hover:text-white transition-colors shrink-0">
                    <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 17L17 7m0 0H9m8 0v8"/>
                    </svg>
                </span>
            </div>
            <div class="mt-auto">
                <p class="text-[11px] sm:text-xs text-gray-400 group-hover:text-white mt-1">Cek kuota & instruktur</p>
            </div>
        </a>

        <a href="{{ route('customers.index') }}" class="group bg-white border border-blue-100 p-3 sm:p-4 text-left hover:bg-orange-500 hover:border-orange-500 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 animate-card-rise flex flex-col min-h-[130px] sm:min-h-[150px]" style="animation-delay: 150ms">
            <div class="flex items-start justify-between text-left">
                <p class="min-h-10 sm:min-h-12 max-w-[calc(100%-2rem)] text-sm sm:text-base font-bold leading-tight text-gray-900 group-hover:text-white 2xl:whitespace-nowrap">
                    <span>Daftar</span><br class="card-title-br 2xl:hidden"><span class="card-title-space hidden 2xl:inline">&nbsp;</span><span>Customer</span>
                </p>
                <span class="w-6 h-6 sm:w-7 sm:h-7 rounded-full bg-gray-100 text-gray-500 flex items-center justify-center group-hover:bg-white/20 group-hover:text-white transition-colors shrink-0">
                    <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 17L17 7m0 0H9m8 0v8"/>
                    </svg>
                </span>
            </div>
            <div class="mt-auto">
                <p class="text-[11px] sm:text-xs text-gray-400 group-hover:text-white mt-1">Export CSV & Data Peserta</p>
            </div>
        </a>

        <a href="{{ route('institutions.index') }}" class="group bg-white border border-blue-100 p-3 sm:p-4 text-left hover:bg-orange-500 hover:border-orange-500 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 animate-card-rise flex flex-col min-h-[130px] sm:min-h-[150px]" style="animation-delay: 200ms">
            <div class="flex items-start justify-between text-left">
                <p class="min-h-10 sm:min-h-12 max-w-[calc(100%-2rem)] text-sm sm:text-base font-bold leading-tight text-gray-900 group-hover:text-white 2xl:whitespace-nowrap">
                    <span>Daftar</span><br class="card-title-br 2xl:hidden"><span class="card-title-space hidden 2xl:inline">&nbsp;</span><span>Instansi</span>
                </p>
                <span class="w-6 h-6 sm:w-7 sm:h-7 rounded-full bg-gray-100 text-gray-500 flex items-center justify-center group-hover:bg-white/20 group-hover:text-white transition-colors shrink-0">
                    <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 17L17 7m0 0H9m8 0v8"/>
                    </svg>
                </span>
            </div>
            <div class="mt-auto">
                <p class="text-[11px] sm:text-xs text-gray-400 group-hover:text-white mt-1">Mitra kampus & kantor</p>
            </div>
        </a>

        <a href="{{ route('trainers.index') }}" class="group bg-white border border-blue-100 p-3 sm:p-4 text-left hover:bg-orange-500 hover:border-orange-500 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 animate-card-rise flex flex-col min-h-[130px] sm:min-h-[150px]" style="animation-delay: 250ms">
            <div class="flex items-start justify-between text-left">
                <p class="min-h-10 sm:min-h-12 max-w-[calc(100%-2rem)] text-sm sm:text-base font-bold leading-tight text-gray-900 group-hover:text-white 2xl:whitespace-nowrap">
                    <span>Daftar</span><br class="card-title-br 2xl:hidden"><span class="card-title-space hidden 2xl:inline">&nbsp;</span><span>Trainer</span>
                </p>
                <span class="w-6 h-6 sm:w-7 sm:h-7 rounded-full bg-gray-100 text-gray-500 flex items-center justify-center group-hover:bg-white/20 group-hover:text-white transition-colors shrink-0">
                    <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 17L17 7m0 0H9m8 0v8"/>
                    </svg>
                </span>
            </div>
            <div class="mt-auto">
                <p class="text-[11px] sm:text-xs text-gray-400 group-hover:text-white mt-1">Instruktur & expert</p>
            </div>
        </a>
    </div>
    @else
    {{-- Menu Khusus Sales Affiliate --}}
    <div class="flex items-center justify-between mb-3">
        <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Aktivitas Affiliate & Penjualan</p>
        <span class="text-[11px] text-emerald-600 font-semibold">Komisi 10% dari setiap deal closing</span>
    </div>
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-2.5 sm:gap-3">
        <a href="{{ route('leads.create') }}" class="group bg-white border border-emerald-100 p-3 sm:p-4 text-left hover:bg-emerald-600 hover:border-emerald-600 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 animate-card-rise flex flex-col min-h-[130px] sm:min-h-[150px]" style="animation-delay: 50ms">
            <div class="flex items-start justify-between text-left">
                <p class="min-h-10 sm:min-h-12 max-w-[calc(100%-2rem)] text-sm sm:text-base font-bold leading-tight text-gray-900 group-hover:text-white">Input Prospek Baru</p>
                <span class="w-6 h-6 sm:w-7 sm:h-7 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center group-hover:bg-white/20 group-hover:text-white transition-colors shrink-0">
                    <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                </span>
            </div>
            <div class="mt-auto">
                <p class="text-[11px] sm:text-xs text-gray-400 group-hover:text-white/80 mt-1">Kenalan sales / calon deal</p>
            </div>
        </a>

        <a href="{{ route('payments.create') }}" class="group bg-white border border-blue-100 p-3 sm:p-4 text-left hover:bg-orange-500 hover:border-orange-500 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 animate-card-rise flex flex-col min-h-[130px] sm:min-h-[150px]" style="animation-delay: 100ms">
            <div class="flex items-start justify-between text-left">
                <p class="min-h-10 sm:min-h-12 max-w-[calc(100%-2rem)] text-sm sm:text-base font-bold leading-tight text-gray-900 group-hover:text-white">Kirim Bukti Bayar</p>
                <span class="w-6 h-6 sm:w-7 sm:h-7 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center group-hover:bg-white/20 group-hover:text-white transition-colors shrink-0">
                    <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/></svg>
                </span>
            </div>
            <div class="mt-auto">
                <p class="text-[11px] sm:text-xs text-gray-400 group-hover:text-white/80 mt-1">Upload transfer DP / lunas</p>
            </div>
        </a>

        <a href="{{ route('commissions.index') }}" class="group bg-white border border-amber-100 p-3 sm:p-4 text-left hover:bg-amber-500 hover:border-amber-500 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 animate-card-rise flex flex-col min-h-[130px] sm:min-h-[150px]" style="animation-delay: 150ms">
            <div class="flex items-start justify-between text-left">
                <p class="min-h-10 sm:min-h-12 max-w-[calc(100%-2rem)] text-sm sm:text-base font-bold leading-tight text-gray-900 group-hover:text-white">Komisi 10% Saya</p>
                <span class="w-6 h-6 sm:w-7 sm:h-7 rounded-full bg-amber-50 text-amber-600 flex items-center justify-center group-hover:bg-white/20 group-hover:text-white transition-colors shrink-0">
                    <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
            <div class="mt-auto">
                <p class="text-[11px] sm:text-xs text-gray-400 group-hover:text-white/80 mt-1">Cek insentif & pencairan</p>
            </div>
        </a>

        <a href="{{ route('products.index') }}" class="group bg-white border border-blue-100 p-3 sm:p-4 text-left hover:bg-orange-500 hover:border-orange-500 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 animate-card-rise flex flex-col min-h-[130px] sm:min-h-[150px]" style="animation-delay: 200ms">
            <div class="flex items-start justify-between text-left">
                <p class="min-h-10 sm:min-h-12 max-w-[calc(100%-2rem)] text-sm sm:text-base font-bold leading-tight text-gray-900 group-hover:text-white">Katalog & Harga Patokan</p>
                <span class="w-6 h-6 sm:w-7 sm:h-7 rounded-full bg-gray-100 text-gray-500 flex items-center justify-center group-hover:bg-white/20 group-hover:text-white transition-colors shrink-0">
                    <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                </span>
            </div>
            <div class="mt-auto">
                <p class="text-[11px] sm:text-xs text-gray-400 group-hover:text-white mt-1">Harga resmi sertifikasi</p>
            </div>
        </a>

        <a href="{{ route('follow-ups.index') }}" class="group bg-white border border-blue-100 p-3 sm:p-4 text-left hover:bg-orange-500 hover:border-orange-500 hover:shadow-md hover:-translate-y-0.5 transition-all duration-200 animate-card-rise flex flex-col min-h-[130px] sm:min-h-[150px]" style="animation-delay: 250ms">
            <div class="flex items-start justify-between text-left">
                <p class="min-h-10 sm:min-h-12 max-w-[calc(100%-2rem)] text-sm sm:text-base font-bold leading-tight text-gray-900 group-hover:text-white">Follow-up Prospek</p>
                <span class="w-6 h-6 sm:w-7 sm:h-7 rounded-full bg-gray-100 text-gray-500 flex items-center justify-center group-hover:bg-white/20 group-hover:text-white transition-colors shrink-0">
                    <svg class="w-3 h-3 sm:w-3.5 sm:h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/></svg>
                </span>
            </div>
            <div class="mt-auto">
                <p class="text-[11px] sm:text-xs text-gray-400 group-hover:text-white mt-1">Jadwal tindak lanjut</p>
            </div>
        </a>
    </div>
    @endif
</div>

{{-- ── Baris 3: Chart Lead 6 Bulan ── --}}
<div class="bg-white border border-gray-100 p-5 mb-5 shadow-2xs">
    <div class="flex items-center justify-between mb-5">
        <p class="text-sm font-semibold text-gray-900">Lead 6 Bulan sampai {{ $selectedDate->translatedFormat('M Y') }}</p>
        <div class="flex items-center gap-3 text-xs text-gray-400">
            <span class="flex items-center gap-1">
                <span class="inline-block w-2.5 h-2.5 bg-brand-200"></span> Total
            </span>
            <span class="flex items-center gap-1">
                <span class="inline-block w-2.5 h-2.5 bg-emerald-400"></span> Won
            </span>
        </div>
    </div>
    @php $maxLead = max(1, $sixMonthLeads->max('total')); @endphp
    <div class="flex items-end gap-2.5 h-44">
        @foreach($sixMonthLeads as $month)
        <div class="flex-1 flex flex-col items-center gap-1">
            <span class="text-xs text-gray-500 font-medium">{{ $month['total'] }}</span>
            <div class="w-full flex flex-col rounded-t overflow-hidden animate-bar-grow" style="height: {{ max(4, ($month['total'] / $maxLead) * 140) }}px; animation-delay: {{ $loop->index * 75 }}ms">
                <div class="bg-brand-200 flex-1"></div>
                @if($month['won'] > 0)
                <div class="bg-emerald-400"
                     style="height: {{ ($month['won'] / max(1, $month['total'])) * 100 }}%"></div>
                @endif
            </div>
            <span class="text-[10px] text-gray-400 leading-tight text-center">{{ $month['label'] }}</span>
        </div>
        @endforeach
    </div>
</div>

{{-- ── Baris 4: Aktivitas + Follow-up Mendatang ── --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mb-5">

    {{-- Aktivitas terbaru --}}
    <div class="bg-white border border-gray-100 p-5 shadow-2xs">
        <div class="flex items-center justify-between mb-4">
            <p class="text-sm font-semibold text-gray-900">Aktivitas Terbaru</p>
        </div>
        @if($recentActivities->isEmpty())
        <p class="text-sm text-gray-300 text-center py-8">Belum ada aktivitas</p>
        @else
        <div class="space-y-3">
            @foreach($recentActivities as $activity)
            @php
                $typeColors = [
                    'call'=>'bg-blue-100 text-blue-600','email'=>'bg-purple-100 text-purple-600',
                    'whatsapp'=>'bg-green-100 text-green-600','meeting'=>'bg-amber-100 text-amber-600',
                    'note'=>'bg-gray-100 text-gray-500','status_change'=>'bg-indigo-100 text-indigo-600',
                ];
                $typeIcons = [
                    'call'=>'M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z',
                    'email'=>'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
                    'whatsapp'=>'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z',
                    'meeting'=>'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z',
                    'note'=>'M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z',
                    'status_change'=>'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15',
                ];
                $color = $typeColors[$activity->type] ?? 'bg-gray-100 text-gray-500';
                $icon  = $typeIcons[$activity->type]  ?? $typeIcons['note'];
            @endphp
            <div class="flex items-start gap-3">
                <div class="flex-shrink-0 w-7 h-7 {{ $color }} flex items-center justify-center">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $icon }}"/>
                    </svg>
                </div>
                <div class="min-w-0 flex-1">
                    <p class="text-sm text-gray-900 truncate font-medium">{{ $activity->lead?->name ?? '-' }}</p>
                    <p class="text-xs text-gray-400 truncate">
                        {{ $activity->subject ?? ucfirst($activity->type) }}
                        @if($activity->user)· {{ $activity->user->name }}@endif
                    </p>
                </div>
                <span class="flex-shrink-0 text-[10px] text-gray-300 whitespace-nowrap">
                    {{ $activity->activity_at->diffForHumans(null, true) }}
                </span>
            </div>
            @endforeach
        </div>
        @endif
    </div>

    {{-- Follow-up mendatang --}}
    <div class="bg-white border border-gray-100 p-5 shadow-2xs">
        <div class="flex items-center justify-between mb-4">
            <p class="text-sm font-semibold text-gray-900">Follow-up Mendatang</p>
        </div>
        @if($upcomingFollowUps->isEmpty())
        <div class="flex flex-col items-center justify-center py-8 text-gray-300">
            <svg class="w-8 h-8 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-sm">Tidak ada follow-up mendatang</p>
        </div>
        @else
        <div class="space-y-3">
            @foreach($upcomingFollowUps as $fu)
            @php
                $priorityDot = ['urgent'=>'bg-red-500','high'=>'bg-orange-400','normal'=>'bg-blue-400','low'=>'bg-gray-300'];
            @endphp
            <div class="flex items-start gap-3 p-3 bg-gray-50 hover:bg-gray-100 transition-colors">
                <span class="flex-shrink-0 mt-1.5 w-2 h-2 rounded-full {{ $priorityDot[$fu->priority] ?? 'bg-gray-300' }}"></span>
                <div class="min-w-0 flex-1">
                    <p class="text-sm font-medium text-gray-900 truncate">{{ $fu->title }}</p>
                    <p class="text-xs text-gray-400 truncate">{{ $fu->lead?->name ?? '-' }} · {{ $fu->assignedUser?->name ?? '-' }}</p>
                    <p class="text-[10px] text-gray-400 mt-1">
                        {{ $fu->follow_up_at->translatedFormat('d M, H:i') }}
                    </p>
                </div>
            </div>
            @endforeach
        </div>
        @endif
    </div>
</div>

<script>
    function dashboardCalendar(selectedDate, todayDate) {
        const initialDate = new Date(`${selectedDate}T00:00:00`);
        const todayIso = todayDate || new Date().toISOString().split('T')[0];

        return {
            year: initialDate.getFullYear(),
            month: initialDate.getMonth(),
            selectedDate,
            todayIso,
            monthNames: ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'],
            weekdays: ['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'],
            get days() {
                const firstDay = new Date(this.year, this.month, 1).getDay();
                const leadingDays = (firstDay + 6) % 7;
                const daysInMonth = new Date(this.year, this.month + 1, 0).getDate();
                const days = Array.from({ length: leadingDays }, () => null);

                for (let number = 1; number <= daysInMonth; number++) {
                    const date = new Date(this.year, this.month, number);
                    const iso = [
                        date.getFullYear(),
                        String(date.getMonth() + 1).padStart(2, '0'),
                        String(date.getDate()).padStart(2, '0'),
                    ].join('-');
                    days.push({ number, iso });
                }

                return days;
            },
            isToday(iso) {
                return iso === this.todayIso;
            },
            isSelected(iso) {
                return iso === this.selectedDate;
            },
            dayClass(day) {
                if (!day || !day.iso) return '';
                const selected = this.isSelected(day.iso);
                const today = this.isToday(day.iso);

                if (selected && today) {
                    // Hari ini dan sedang dipilih: bulatan oranye pekat dengan ring tegas
                    return 'bg-orange-500 text-white font-bold shadow-xs ring-2 ring-orange-300';
                }

                if (selected) {
                    // Tanggal lain yang sedang dipilih: bulatan oranye solid
                    return 'bg-orange-500 text-white font-bold shadow-xs';
                }

                if (today) {
                    // HARI INI saat sedang melihat tanggal lain: lingkaran bergaris tepi oranye tegas & angka oranye tebal
                    return 'font-bold text-orange-600 border-2 border-orange-500 bg-orange-50 hover:bg-orange-100 shadow-2xs';
                }

                return 'text-gray-600 hover:bg-orange-50 hover:text-orange-600';
            },
            previousMonth() {
                if (this.month === 0) {
                    this.month = 11;
                    this.year--;
                    return;
                }

                this.month--;
            },
            nextMonth() {
                if (this.month === 11) {
                    this.month = 0;
                    this.year++;
                    return;
                }

                this.month++;
            },
            selectDay(day) {
                if (!day || !day.iso) return;
                window.location.href = `{{ route('dashboard') }}?date=${day.iso}`;
            },
        };
    }

    function pipelinePeriodManager(allLeads, todayIso, todayDisplay, initYear, initMonth) {
        return {
            allLeads: allLeads || [],
            todayIso: todayIso || '',
            todayDisplay: todayDisplay || '',
            selectedYear: initYear || new Date().getFullYear(),
            selectedMonth: initMonth || (new Date().getMonth() + 1),
            filterMode: 'today',
            activeStage: 'all',
            hoverStage: null,

            availableYears: [2024, 2025, 2026, 2027, 2028, 2029, 2030],
            monthNames: [
                'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
                'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
            ],

            stageDefinitions: [
                { key: 'new',         label: 'Baru',        dot: 'bg-slate-400',   hex: '#94a3b8' },
                { key: 'contacted',   label: 'Dikontak',    dot: 'bg-blue-400',    hex: '#60a5fa' },
                { key: 'active',      label: 'Aktif',       dot: 'bg-cyan-500',    hex: '#06b6d4' },
                { key: 'negotiation', label: 'Negosiasi',   dot: 'bg-amber-400',   hex: '#fbbf24' },
                { key: 'won',         label: 'Berhasil',    dot: 'bg-emerald-500', hex: '#10b981' },
                { key: 'lost',        label: 'Ditolak',     dot: 'bg-red-400',     hex: '#f87171' },
                { key: 'inactive',    label: 'Tidak Aktif', dot: 'bg-gray-300',    hex: '#cbd5e1' },
            ],

            get periodLeads() {
                if (this.filterMode === 'today') {
                    return this.allLeads.filter(l => l.date_iso === this.todayIso);
                }
                if (this.filterMode === 'month') {
                    return this.allLeads.filter(l => l.year === this.selectedYear && l.month === this.selectedMonth);
                }
                return this.allLeads;
            },

            get totalCount() {
                return this.periodLeads.length;
            },

            get wonCount() {
                return this.periodLeads.filter(l => l.status === 'won').length;
            },

            get conversionRate() {
                if (this.totalCount === 0) return 0;
                return ((this.wonCount / this.totalCount) * 100).toFixed(1);
            },

            get periodLabel() {
                if (this.filterMode === 'today') {
                    return 'Hari Ini (' + this.todayDisplay + ')';
                }
                if (this.filterMode === 'month') {
                    return this.monthNames[this.selectedMonth - 1] + ' ' + this.selectedYear;
                }
                return 'Sepanjang Waktu (Semua Data)';
            },

            get activeStageLabel() {
                const st = this.stageDefinitions.find(s => s.key === this.activeStage);
                return st ? st.label : 'Semua';
            },

            get currentStages() {
                const total = this.totalCount;
                const C = 2 * Math.PI * 54;
                let offset = 0;
                return this.stageDefinitions.map(def => {
                    const count = this.periodLeads.filter(l => l.status === def.key).length;
                    const dash = total > 0 ? (count / total) * C : 0;
                    const curOffset = offset;
                    offset += dash;
                    return {
                        ...def,
                        count,
                        dash,
                        dashArray: `${dash.toFixed(2)} ${(C - dash).toFixed(2)}`,
                        dashOffset: (-curOffset).toFixed(2),
                    };
                });
            },

            get filteredLeads() {
                if (this.activeStage === 'all') {
                    return this.periodLeads;
                }
                return this.periodLeads.filter(l => l.status === this.activeStage);
            },

            get centerCount() {
                const current = this.hoverStage || this.activeStage;
                if (current === 'all') {
                    return this.totalCount;
                }
                const st = this.currentStages.find(s => s.key === current);
                return st ? st.count : 0;
            },

            get centerLabel() {
                const current = this.hoverStage || this.activeStage;
                if (current === 'all') {
                    return 'Total Lead';
                }
                const st = this.stageDefinitions.find(s => s.key === current);
                return st ? st.label : 'Lead';
            },

            get tableLeadUrl() {
                if (this.activeStage === 'all') {
                    return '{{ route('leads.index') }}';
                }
                return '{{ route('leads.index') }}?status=' + this.activeStage;
            },

            setFilterMode(mode) {
                this.filterMode = mode;
                this.activeStage = 'all';
                this.hoverStage = null;
            },

            prevMonth() {
                if (this.selectedMonth === 1) {
                    this.selectedMonth = 12;
                    this.selectedYear--;
                } else {
                    this.selectedMonth--;
                }
                this.activeStage = 'all';
            },

            nextMonth() {
                if (this.selectedMonth === 12) {
                    this.selectedMonth = 1;
                    this.selectedYear++;
                } else {
                    this.selectedMonth++;
                }
                this.activeStage = 'all';
            },

            selectStage(stageKey) {
                this.activeStage = (this.activeStage === stageKey) ? 'all' : stageKey;
            },

            isSelected(stageKey) {
                return this.activeStage === stageKey || this.hoverStage === stageKey;
            },

            getSliceClass(stageKey) {
                if (this.activeStage === 'all' && !this.hoverStage) {
                    return 'opacity-100 transition-all duration-200 cursor-pointer';
                }
                if (this.isSelected(stageKey)) {
                    return 'opacity-100 transition-all duration-200 cursor-pointer drop-shadow-md stroke-[22]';
                }
                return 'opacity-30 transition-all duration-200 cursor-pointer hover:opacity-75';
            }
        };
    }
</script>

@endsection
