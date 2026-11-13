@extends('layouts.app')

@section('title', auth()->user()->isSales() ? 'Notifikasi & Pengingat Sales' : 'Monitoring Pengingat Sales')

@section('content')
<div class="space-y-4" x-data="{ completeModal: false, completeUrl: '', currentLead: '' }">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">
                {{ auth()->user()->isSales() ? 'Notifikasi & Pengingat Sales' : 'Monitoring Pengingat Sales' }}
            </h1>
            <p class="text-xs text-gray-500 mt-0.5">Pemberitahuan pencairan komisi penjualan dan instruksi follow-up customer dari Admin</p>
        </div>
    </div>

    {{-- Filter --}}
 <div class="bg-white border border-blue-100 p-3.5 shadow-sm">
        <form method="GET" action="{{ route('reminders.index') }}" class="flex flex-wrap items-center gap-2.5">
 <select name="type" class="text-xs border border-gray-200 px-3 py-2 text-gray-700 focus:outline-none focus:border-blue-500">
                <option value="">Semua Kategori</option>
                <option value="commission_paid" {{ request('type') == 'commission_paid' ? 'selected' : '' }}>Pencairan Komisi</option>
                <option value="follow_up" {{ request('type') == 'follow_up' ? 'selected' : '' }}>Instruksi Follow-Up</option>
            </select>
 <select name="status" class="text-xs border border-gray-200 px-3 py-2 text-gray-700 focus:outline-none focus:border-blue-500">
                <option value="">Semua Status</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Follow-Up Menunggu</option>
                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Follow-Up Selesai</option>
            </select>
            <button type="submit" class="text-xs bg-gray-900 text-white font-medium py-2 px-3 rounded-lg hover:bg-gray-800 transition-colors">Filter</button>
            @if(request()->anyFilled(['type', 'status']))
                <a href="{{ route('reminders.index') }}" class="text-xs text-gray-500 hover:text-gray-700 px-2 py-1">Reset</a>
            @endif
        </form>
    </div>

    {{-- List --}}
 <div class="bg-white border border-blue-100 overflow-hidden shadow-sm">
        @forelse($reminders as $item)
            @if($item->type === 'commission_paid')
                {{-- ═══ KOMISI MASUK ═══ --}}
                <div class="flex border-b border-gray-100 last:border-b-0 hover:bg-gray-50/40 transition-colors">
                    {{-- Garis warna hijau di kiri --}}
                    <div class="w-1 bg-emerald-500 flex-shrink-0"></div>

                    <div class="flex-1 flex flex-col sm:flex-row sm:items-center gap-3 px-5 py-4">
                        {{-- Info kiri --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-2 mb-1">
 <span class="px-2 py-0.5 text-[10px] font-bold bg-emerald-600 text-white uppercase tracking-wide">Komisi Masuk</span>
                                <span class="text-xs text-gray-400">{{ $item->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="text-sm text-gray-900">
                                Komisi <strong class="text-emerald-600">Rp {{ number_format($item->commission?->commission_amount ?? 150000, 0, ',', '.') }}</strong>
                                untuk prospek
                                <a href="{{ route('leads.show', $item->lead) }}" class="font-semibold text-gray-900 hover:underline">{{ $item->lead->name }}</a>
                                telah dibayarkan.
                            </p>
                            <p class="text-[11px] text-gray-500 mt-0.5">
                                Deal Rp {{ number_format($item->commission?->deal_amount ?? 1500000, 0, ',', '.') }} (10%)
                                — {{ $item->lead->product?->name ?? 'Pelatihan' }}
                                — <span class="text-emerald-700 font-medium">Lunas</span>
                            </p>
                        </div>

                        {{-- Aksi kanan --}}
                        <div class="flex items-center gap-1.5 flex-shrink-0">
                            <a href="{{ route('commissions.index') }}"
                               class="px-2.5 py-1 text-[11px] font-semibold text-emerald-700 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 rounded whitespace-nowrap">
                                Lihat Komisi
                            </a>
                            <a href="{{ route('leads.show', $item->lead) }}"
                               class="px-2.5 py-1 text-[11px] text-gray-600 hover:text-gray-900 border border-gray-200 rounded whitespace-nowrap">
                                Detail
                            </a>
                        </div>
                    </div>
                </div>
            @else
                {{-- ═══ FOLLOW-UP ═══ --}}
                <div class="flex border-b border-gray-100 last:border-b-0 hover:bg-gray-50/40 transition-colors {{ $item->is_completed ? 'opacity-70' : '' }}">
                    {{-- Garis warna biru di kiri --}}
                    <div class="w-1 {{ $item->is_completed ? 'bg-gray-300' : ($item->priority === 'urgent' ? 'bg-rose-500' : 'bg-blue-500') }} flex-shrink-0"></div>

                    <div class="flex-1 flex flex-col sm:flex-row sm:items-center gap-3 px-5 py-4">
                        {{-- Info kiri --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex flex-wrap items-center gap-2 mb-1">
                                @if(! $item->is_completed)
 <span class="px-2 py-0.5 text-[10px] font-bold uppercase tracking-wide {{ $item->priority === 'urgent' ? 'bg-rose-600 text-white' : 'bg-blue-600 text-white' }}">
                                        {{ $item->priority === 'urgent' ? 'Mendesak' : 'Follow-Up' }}
                                    </span>
                                @else
 <span class="px-2 py-0.5 text-[10px] font-medium bg-gray-200 text-gray-700 uppercase tracking-wide">
                                        Selesai
                                    </span>
                                @endif
                                <span class="text-xs text-gray-400">{{ $item->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="text-sm text-gray-900">
                                <a href="{{ route('leads.show', $item->lead) }}" class="font-semibold text-gray-900 hover:underline">{{ $item->lead->name }}</a>
                                @if($item->lead->company)
                                    <span class="text-gray-500 text-xs">({{ $item->lead->company }})</span>
                                @endif
                                — <span class="text-gray-700">"{{ Str::limit($item->message, 80) }}"</span>
                            </p>
                            <p class="text-[11px] text-gray-500 mt-0.5">
                                Dari: {{ $item->admin?->name ?? 'Admin CRM' }}
                                — {{ $item->lead->product?->name ?? 'Pelatihan' }}
                                — WA: {{ $item->lead->whatsapp ?: ($item->lead->phone ?: '-') }}
                            </p>
                            @if($item->is_completed && $item->sales_reply)
                                <p class="text-[11px] text-emerald-800 mt-1">
                                    Catatan: "{{ $item->sales_reply }}"
                                </p>
                            @endif
                        </div>

                        {{-- Aksi kanan --}}
                        <div class="flex items-center gap-1.5 flex-shrink-0">
                            @if($item->lead->whatsapp_follow_up_url)
                                <a href="{{ $item->lead->whatsapp_follow_up_url }}"
                                   target="_blank"
                                   class="px-2.5 py-1 text-[11px] font-semibold text-white bg-[#128C7E] hover:bg-[#075E54] rounded whitespace-nowrap">
                                    Chat WA
                                </a>
                            @endif
                            @if(! $item->is_completed)
                                <button type="button"
                                        @click="completeUrl = '{{ route('reminders.complete', $item) }}'; currentLead = '{{ addslashes($item->lead->name) }}'; completeModal = true"
                                        class="px-2.5 py-1 text-[11px] font-medium text-gray-700 bg-white hover:bg-gray-50 border border-gray-200 rounded whitespace-nowrap">
                                    Selesai
                                </button>
                            @endif
                            <a href="{{ route('leads.show', $item->lead) }}"
                               class="px-2.5 py-1 text-[11px] text-gray-600 hover:text-gray-900 border border-gray-200 rounded whitespace-nowrap">
                                Detail
                            </a>
                        </div>
                    </div>
                </div>
            @endif
        @empty
            <div class="py-12 text-center text-gray-400 text-xs">
                Belum ada notifikasi atau pengingat pada filter ini.
            </div>
        @endforelse

        @if($reminders->hasPages())
            <div class="p-3 border-t border-gray-100">{{ $reminders->links() }}</div>
        @endif
    </div>

    {{-- Modal Konfirmasi Selesai --}}
    <div x-show="completeModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-xs">
 <div @click.away="completeModal = false" class="bg-white max-w-md w-full p-5 shadow-xl space-y-4 border border-gray-200">
            <div class="flex items-center justify-between border-b border-gray-100 pb-3">
                <h4 class="text-sm font-semibold text-gray-900">Konfirmasi Selesai Follow-Up</h4>
                <button @click="completeModal = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <p class="text-xs text-gray-600">
                Menandai prospek <span class="font-semibold text-gray-900" x-text="currentLead"></span> telah selesai dihubungi.
            </p>
            <form method="POST" :action="completeUrl" class="space-y-3">
                @csrf
                @method('PATCH')
                <div>
                    <label class="block text-xs font-medium text-gray-700 mb-1">Catatan (Opsional)</label>
 <textarea name="sales_reply" rows="2" placeholder="Contoh: Sudah dihubungi via WA, prospek meminta proposal..." class="w-full text-xs border-gray-200 focus:border-gray-900 focus:ring-gray-900 p-2.5"></textarea>
                </div>
                <div class="flex justify-end gap-2 pt-2 border-t border-gray-100">
                    <button type="button" @click="completeModal = false" class="px-3 py-1.5 text-xs text-gray-600 hover:bg-gray-100 rounded-lg">Batal</button>
                    <button type="submit" class="px-4 py-1.5 text-xs font-semibold text-white bg-gray-900 hover:bg-gray-800 rounded-lg shadow-sm">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
