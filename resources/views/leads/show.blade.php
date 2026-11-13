@extends('layouts.app')

@section('title', 'Detail Lead — ' . $lead->name)

@section('content')
<div class="space-y-4" x-data="{
    remindModal: false,
    selectedSales: '{{ $lead->assigned_to ?? ($salesUsers->first()->id ?? '') }}',
    reminderMsg: '',
    reminderPriority: 'urgent',
    setTemplate(text) {
        this.reminderMsg = text;
    }
}">
    {{-- Header --}}
 <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-white border border-blue-100 p-4 shadow-sm">
        <div>
            <div class="flex items-center gap-2">
 <span class="text-xs font-mono font-semibold text-blue-600 bg-blue-50 px-2 py-0.5 ">{{ $lead->lead_code }}</span>
                <span class="text-xs text-gray-400">• Dibuat {{ $lead->created_at->translatedFormat('d M Y, H:i') }}</span>
            </div>
            <h1 class="text-xl font-bold text-gray-900 mt-1">{{ $lead->name }}</h1>
            <p class="text-xs text-gray-500">{{ $lead->company ? $lead->company . ' — ' : '' }}{{ $lead->job_title ?? 'Prospek' }}</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('leads.index') }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Kembali
            </a>
            @if(auth()->user()->isAdmin())
                <button type="button" @click="remindModal = true"
                        class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-semibold text-white bg-orange-600 hover:bg-orange-700 rounded-lg transition-colors shadow-sm">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    Ingatkan Sales
                </button>
            @endif
            <a href="{{ route('leads.edit', $lead) }}" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                Edit Data
            </a>
            <a href="{{ route('payments.create') }}?lead_id={{ $lead->id }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-medium text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 transition-colors shadow-sm">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Input Pembayaran
            </a>
        </div>
    </div>

    {{-- Banner Pengingat Aktif (Jika Ada Instruksi Follow-up) --}}
    @php $pendingRem = $lead->latestPendingReminder; @endphp
    @if($pendingRem)
 <div class="bg-amber-50/70 border border-amber-200 p-4 shadow-2xs space-y-3">
            <div class="flex items-center justify-between">
                <div>
                    <h4 class="text-xs font-bold text-amber-950 uppercase tracking-wide">
                        Instruksi Follow-Up dari {{ $pendingRem->admin?->name ?? 'Admin CRM' }}
                        @if(auth()->user()->isAdmin())
                            &rarr; Ditujukan ke <span class="underline">{{ $pendingRem->sales?->name ?? 'Sales PIC' }}</span>
                        @endif
                    </h4>
                    <p class="text-[11px] text-amber-700 mt-0.5">{{ $pendingRem->created_at->diffForHumans() }} • Prioritas: {{ strtoupper($pendingRem->priority) }}</p>
                </div>
                <span class="px-2.5 py-0.5 text-[10px] font-bold rounded-full {{ $pendingRem->priority === 'urgent' ? 'bg-red-500 text-white' : 'bg-amber-200 text-amber-800' }}">
                    {{ $pendingRem->priority === 'urgent' ? 'Mendesak' : 'Pengingat' }}
                </span>
            </div>

 <div class="bg-white p-3 border border-amber-200/80 text-xs text-gray-800 font-medium">
                "{{ $pendingRem->message }}"
            </div>

            <div class="flex flex-wrap items-center justify-between gap-2 pt-1">
                <div class="flex items-center gap-2">
                    @if($lead->whatsapp_follow_up_url)
                        <a href="{{ $lead->whatsapp_follow_up_url }}" target="_blank"
                           class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-lg shadow-2xs transition-colors">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                            Chat WhatsApp Sekarang
                        </a>
                    @endif

                    @if(auth()->user()->isSales() || auth()->user()->isAdmin())
                        <form method="POST" action="{{ route('reminders.complete', $pendingRem) }}">
                            @csrf
                            @method('PATCH')
                            <button type="submit" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-semibold text-emerald-800 bg-emerald-100 hover:bg-emerald-200 border border-emerald-300 rounded-lg transition-colors">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Tandai Selesai Follow-Up
                            </button>
                        </form>
                    @endif
                </div>

                <span class="text-[11px] text-gray-500">
                    Status: <span class="font-semibold text-orange-600">Menunggu Follow-up</span>
                </span>
            </div>
        </div>
    @endif

    @if($lead->canBeClaimedBy(auth()->user()))
 <div class="bg-amber-50 border border-amber-200 p-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 shadow-sm">
        <div>
            <p class="text-xs font-bold text-amber-900 flex items-center gap-1.5">
                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                {{ $lead->isLost() ? 'Prospek Lost — Siap Difollow-up Ulang (Re-engagement)' : 'Calon Pelanggan Belum Ditugaskan (Pool Lead)' }}
            </p>
            <p class="text-[11px] text-amber-700 mt-0.5">
                {{ $lead->isLost() ? 'Lead ini berstatus Lost oleh sales sebelumnya ('.$lead->assignedUser?->name.'). Sesuai aturan, Anda boleh mengambil alih customer ini untuk mencoba follow-up ulang dan mendapatkan closing!' : 'Belum ada sales penanggung jawab. Anda dapat mengklaim lead ini secara eksklusif ke akun Anda.' }}
            </p>
        </div>
        <form method="POST" action="{{ route('leads.claim', $lead) }}">
            @csrf
            <button type="submit" class="px-4 py-2 text-xs font-semibold text-white bg-orange-600 hover:bg-orange-700 rounded-lg shadow-sm whitespace-nowrap">
                {{ $lead->isLost() ? 'Ambil Alih & Follow-up Ulang' : 'Ambil Lead Ini' }}
            </button>
        </form>
    </div>
    @endif

    {{-- Status Pipeline bar --}}
 <div class="bg-white border border-blue-100 p-4 shadow-sm" x-data="{ openReject: false }">
        <div class="flex items-center justify-between gap-3 mb-2">
            <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Status Alur Prospek</p>
            @if(! auth()->user()->isSales())
                <span class="text-[11px] text-gray-400">Hanya Sales yang dapat memperbarui</span>
            @endif
        </div>
        @if(auth()->user()->isSales())
        <div class="flex flex-wrap items-center gap-2">
            @foreach(\App\Models\Lead::STATUS_LABELS as $statusKey => $statusLabel)
                @if($statusKey !== 'lost')
                    <form method="POST" action="{{ route('leads.status', $lead) }}">
                        @csrf
                        @method('PATCH')
                        <input type="hidden" name="status" value="{{ $statusKey }}">
                        <button type="submit"
                                class="px-3 py-1.5 rounded-lg text-xs font-medium transition-colors
                                       {{ $lead->status === $statusKey ? 'bg-blue-600 text-white shadow-sm' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                            {{ $statusLabel }}
                        </button>
                    </form>
                @endif
            @endforeach

            {{-- Button Tolak (Lost) with Modal --}}
            <button @click="openReject = true"
                    class="px-3 py-1.5 rounded-lg text-xs font-medium transition-colors
                           {{ $lead->status === 'lost' ? 'bg-red-600 text-white' : 'bg-red-50 text-red-700 hover:bg-red-100' }}">
                Ditolak (Lost)
            </button>
        </div>

        {{-- Modal Tolak --}}
        <div x-show="openReject" x-cloak class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-4">
 <div @click.outside="openReject = false" class="bg-white max-w-md w-full p-5 space-y-3 shadow-xl">
                <h3 class="text-sm font-bold text-gray-900">Alasan Prospek Menolak</h3>
                <form method="POST" action="{{ route('leads.status', $lead) }}" class="space-y-3">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="lost">
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">Pilih Alasan</label>
 <select name="rejection_reason_id" required class="w-full text-xs border border-gray-200 px-3 py-2">
                            <option value="">-- Pilih Alasan Penolakan --</option>
                            @foreach($rejectionReasons as $reason)
                                <option value="{{ $reason->id }}">{{ $reason->label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs text-gray-600 mb-1">Catatan Tambahan (Opsional)</label>
 <textarea name="rejection_notes" rows="2" class="w-full text-xs border border-gray-200 px-3 py-2" placeholder="Detail penolakan..."></textarea>
                    </div>
                    <div class="flex items-center justify-end gap-2 pt-2">
                        <button type="button" @click="openReject = false" class="px-3 py-1.5 text-xs text-gray-600 hover:bg-gray-100 rounded-lg">Batal</button>
                        <button type="submit" class="px-4 py-1.5 text-xs text-white bg-red-600 hover:bg-red-700 rounded-lg">Simpan Penolakan</button>
                    </div>
                </form>
            </div>
        </div>
        @else
            <span class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-semibold bg-blue-50 text-blue-700">
                {{ \App\Models\Lead::STATUS_LABELS[$lead->status] ?? ucfirst($lead->status) }}
            </span>
        @endif
    </div>

    {{-- Main 2 Column Layout --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
        {{-- Left: Details & Info --}}
        <div class="space-y-4">
            {{-- Contact info --}}
 <div class="bg-white border border-blue-100 p-4 shadow-sm space-y-3 text-xs">
                <h3 class="font-semibold text-gray-900 text-sm border-b border-gray-100 pb-2">Informasi Prospek</h3>
                <div class="space-y-2 text-gray-600">
                    <div>
                        <span class="text-gray-400 block text-[11px]">WhatsApp</span>
                        @if($lead->whatsapp)
                            <a href="https://wa.me/{{ preg_replace('/^0/', '62', preg_replace('/[^0-9]/', '', $lead->whatsapp)) }}" target="_blank" class="font-medium text-emerald-600 hover:underline flex items-center gap-1 mt-0.5">
                                {{ $lead->whatsapp }}
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            </a>
                        @else
                            <span class="text-gray-400">-</span>
                        @endif
                    </div>
                    <div>
                        <span class="text-gray-400 block text-[11px]">Email</span>
                        <span class="font-medium text-gray-800">{{ $lead->email ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400 block text-[11px]">Telepon</span>
                        <span class="font-medium text-gray-800">{{ $lead->phone ?? '-' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400 block text-[11px]">Lokasi</span>
                        <span class="font-medium text-gray-800">{{ $lead->city ?? '-' }}{{ $lead->province ? ', ' . $lead->province : '' }}</span>
                    </div>
                </div>
            </div>

            {{-- Lead Prospek details --}}
 <div class="bg-white border border-blue-100 p-4 shadow-sm space-y-3 text-xs">
                <h3 class="font-semibold text-gray-900 text-sm border-b border-gray-100 pb-2">Detail Prospek</h3>
                <div class="space-y-2 text-gray-600">
                    <div>
                        <span class="text-gray-400 block text-[11px]">Program Pelatihan</span>
                        <span class="font-semibold text-blue-700 text-sm">{{ $lead->product?->name ?? 'Belum ditentukan' }}</span>
                    </div>

                    {{-- Card Rincian Harga Asli + 10% Komisi --}}
                    @if($lead->product)
 <div class="space-y-1.5 p-3 bg-orange-50/60 border border-orange-200">
                        <span class="text-orange-950 block text-[10.5px] font-bold uppercase tracking-wider">Rincian Harga Asli + 10%</span>
                        <div class="flex justify-between items-center text-xs">
                            <span class="text-gray-600">1. Harga Asli (Disty):</span>
                            <span class="font-bold text-gray-800">Rp {{ number_format($lead->basePrice(), 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center text-xs">
                            <span class="text-emerald-700 font-medium">2. Komisi Sales (10%):</span>
                            <span class="font-bold text-emerald-600">+ Rp {{ number_format($lead->estimatedCommission(), 0, ',', '.') }}</span>
                        </div>
                        <div class="flex justify-between items-center text-xs pt-1.5 border-t border-orange-200 font-bold">
                            <span class="text-orange-900">3. Ditagihkan ke Customer:</span>
                            <span class="text-sm text-orange-600 font-extrabold">Rp {{ number_format($lead->effectivePrice(), 0, ',', '.') }}</span>
                        </div>
                        <div class="text-[10px] text-gray-500 pt-1 border-t border-orange-100 flex items-center justify-between">
                            <span>Kas Bersih Akademi: <strong>Rp {{ number_format($lead->academyRevenue(), 0, ',', '.') }}</strong></span>
                            <span class="text-emerald-600 font-semibold">Komisi: 10%</span>
                        </div>
                    </div>
                    @endif
                    <div>
                        <span class="text-gray-400 block text-[11px]">Sales PIC</span>
                        <span class="font-medium text-gray-800">{{ $lead->assignedUser?->name ?? 'Belum ada PIC' }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400 block text-[11px]">Sumber Lead</span>
                        <span class="font-medium text-gray-800">{{ $lead->leadSource?->label ?? 'Manual' }}</span>
                    </div>
                    @if($lead->rejectionReason)
 <div class="p-2.5 bg-red-50 border border-red-100 text-red-700">
                        <span class="font-semibold block text-[11px]">Alasan Penolakan:</span>
                        {{ $lead->rejectionReason->label }}
                        @if($lead->rejection_notes)
                            <p class="text-[10px] mt-0.5 text-red-600 italic">"{{ $lead->rejection_notes }}"</p>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Right: Interactions, Follow-ups, Activities --}}
        <div class="lg:col-span-2 space-y-4">
            {{-- Quick action: Log Activity --}}
 <div class="bg-white border border-blue-100 p-4 shadow-sm" x-data="{ showForm: false }">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="text-sm font-semibold text-gray-900">Catat Aktivitas Interaksi</h3>
                        <p class="text-[11px] text-gray-400">Log sapaan, chat WA, telepon, meeting, atau catatan</p>
                    </div>
                    <button @click="showForm = !showForm" class="text-xs text-blue-600 hover:text-blue-700 font-medium px-2.5 py-1 rounded bg-blue-50 hover:bg-blue-100">
                        <span x-show="!showForm">+ Catat Aktivitas</span>
                        <span x-show="showForm">Tutup</span>
                    </button>
                </div>

                <form x-show="showForm" x-cloak method="POST" action="{{ route('leads.activities.store', $lead) }}" class="mt-3 pt-3 border-t border-gray-100 space-y-3">
                    @csrf
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        <div>
                            <label class="block text-[11px] font-semibold text-gray-700 mb-1">Jenis Aktivitas</label>
 <select name="type" required class="w-full text-xs border border-gray-200 px-3 py-1.5">
                                <option value="whatsapp">WhatsApp / Chat</option>
                                <option value="call">Panggilan Telepon</option>
                                <option value="email">Email</option>
                                <option value="meeting">Meeting / Konsultasi</option>
                                <option value="note">Catatan Internal</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-[11px] font-semibold text-gray-700 mb-1">Waktu Interaksi</label>
                            <input type="datetime-local" name="activity_at" value="{{ now()->format('Y-m-d\TH:i') }}" required
 class="w-full text-xs border border-gray-200 px-3 py-1.5">
                        </div>
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-700 mb-1">Subjek / Topik Ringkas</label>
 <input type="text" name="subject" class="w-full text-xs border border-gray-200 px-3 py-1.5" placeholder="Contoh: Kirim penawaran paket web dev">
                    </div>
                    <div>
                        <label class="block text-[11px] font-semibold text-gray-700 mb-1">Catatan Hasil Interaksi</label>
 <textarea name="description" rows="2" class="w-full text-xs border border-gray-200 px-3 py-1.5" placeholder="Tuliskan respon prospek..."></textarea>
                    </div>
                    <div class="text-right">
                        <button type="submit" class="px-3.5 py-1.5 text-xs text-white bg-blue-600 rounded-lg hover:bg-blue-700">Simpan Aktivitas</button>
                    </div>
                </form>
            </div>

            {{-- Follow-ups list --}}
 <div class="bg-white border border-blue-100 p-4 shadow-sm space-y-3">
                <div class="flex items-center justify-between border-b border-gray-100 pb-2">
                    <h3 class="text-sm font-semibold text-gray-900">Jadwal Follow-up Prospek</h3>
                    <a href="{{ route('follow-ups.create') }}?lead_id={{ $lead->id }}" class="inline-flex items-center px-3 py-1.5 text-xs text-white bg-gray-900 hover:bg-black rounded-full font-medium transition-colors">
                        + Tambah Follow-up
                    </a>
                </div>
                <div class="space-y-2">
                    @forelse($lead->followUps as $fu)
 <div class="flex items-start justify-between p-3 border border-gray-100 bg-gray-50/50 text-xs">
                            <div>
                                <div class="flex items-center gap-1.5">
                                    <span class="font-semibold text-gray-800">{{ $fu->title }}</span>
 <span class="text-[10px] px-1.5 py-0.2 border uppercase font-medium
                                        {{ $fu->status === 'completed' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-amber-50 text-amber-700 border-amber-200' }}">
                                        {{ $fu->status }}
                                    </span>
                                </div>
                                <p class="text-gray-500 text-[11px] mt-0.5">{{ $fu->description }}</p>
                                <span class="text-[10px] text-gray-400 mt-1 block">Waktu: {{ $fu->follow_up_at->translatedFormat('d M Y, H:i') }} ({{ ucfirst($fu->method) }})</span>
                            </div>
                            @if($fu->status === 'pending')
                                <form method="POST" action="{{ route('follow-ups.complete', $fu) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="text-[11px] text-emerald-600 hover:text-emerald-800 font-medium bg-emerald-50 px-2 py-1 rounded border border-emerald-200">
                                        Selesai
                                    </button>
                                </form>
                            @endif
                        </div>
                    @empty
                        <p class="text-xs text-gray-400 py-3 text-center">Belum ada agenda follow-up</p>
                    @endforelse
                </div>
            </div>

            {{-- Timeline Activities --}}
 <div class="bg-white border border-blue-100 p-4 shadow-sm space-y-3">
                <h3 class="text-sm font-semibold text-gray-900 border-b border-gray-100 pb-2">Riwayat Interaksi (Timeline)</h3>
                <div class="space-y-3">
                    @forelse($lead->activities as $act)
                        <div class="flex items-start gap-2.5 text-xs">
                            <span class="w-6 h-6 rounded-full {{ $act->type === 'admin_reminder' ? 'bg-orange-100 text-orange-700' : 'bg-blue-50 text-blue-600' }} flex items-center justify-center text-[10px] font-bold flex-shrink-0 mt-0.5">
                                {{ $act->type === 'admin_reminder' ? 'I' : strtoupper(substr($act->type, 0, 1)) }}
                            </span>
                            <div class="flex-1 border-b border-gray-50 pb-2">
                                <div class="flex items-center justify-between">
                                    <span class="font-medium {{ $act->type === 'admin_reminder' ? 'text-orange-900 font-bold' : 'text-gray-800' }}">
                                        {{ $act->subject ?? ucfirst($act->type) }}
                                    </span>
                                    <span class="text-[10px] text-gray-400">{{ $act->activity_at->diffForHumans() }}</span>
                                </div>
                                @if($act->description)
 <p class="{{ $act->type === 'admin_reminder' ? 'text-orange-950 bg-orange-50/70 p-2 border border-orange-100 mt-1 italic' : 'text-gray-500 mt-0.5' }} text-[11px]">
                                        {{ $act->description }}
                                    </p>
                                @endif
                                <span class="text-[10px] text-gray-400 mt-0.5 block">Oleh: {{ $act->user?->name ?? 'System' }}</span>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-gray-400 py-3 text-center">Belum ada riwayat interaksi</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Ingatkan Sales (Admin Only) --}}
    @if(auth()->user()->isAdmin())
    <div x-show="remindModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-xs">
 <div @click.away="remindModal = false" class="bg-white max-w-lg w-full p-6 shadow-2xl space-y-4">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                <div class="flex items-center gap-2">
 <span class="w-8 h-8 bg-orange-100 text-orange-600 flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                    </span>
                    <div>
                        <h3 class="text-sm font-bold text-gray-900">Ingatkan Tim Sales untuk Follow-Up</h3>
                        <p class="text-[11px] text-gray-500">Kirim instruksi langsung ke akun & notifikasi sales PIC</p>
                    </div>
                </div>
                <button type="button" @click="remindModal = false" class="text-gray-400 hover:text-gray-600">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <form method="POST" action="{{ route('leads.remind-sales', $lead) }}" class="space-y-4">
                @csrf
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Pilih Sales Penanggung Jawab (PIC)</label>
 <select name="sales_id" x-model="selectedSales" required class="w-full text-xs border border-gray-200 px-3 py-2 bg-gray-50/50 focus:bg-white focus:ring-orange-500 focus:border-orange-500">
                        @foreach($salesUsers as $s)
                            <option value="{{ $s->id }}" {{ ($lead->assigned_to == $s->id) ? 'selected' : '' }}>
                                {{ $s->name }} ({{ $s->email }}) {{ $lead->assigned_to == $s->id ? '— [PIC Saat Ini]' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">Pilih Template Pesan Cepat</label>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5 mb-2">
                        <button type="button" @click="setTemplate('Kak, tolong segera chat WhatsApp customer ini sekarang ya. Prospek menanyakan info program kelas!')"
                                class="text-left text-[11px] p-2 rounded-lg border border-gray-200 hover:bg-orange-50 hover:border-orange-300 transition-colors text-gray-700">
                            Segera chat WA sekarang
                        </button>
                        <button type="button" @click="setTemplate('Customer sudah 2 hari belum ada follow-up lanjutan, tolong tanyakan kelanjutan penawarannya.')"
                                class="text-left text-[11px] p-2 rounded-lg border border-gray-200 hover:bg-orange-50 hover:border-orange-300 transition-colors text-gray-700">
                            Follow up penawaran
                        </button>
                        <button type="button" @click="setTemplate('Tolong tawarkan promo diskon atau cashback komisi khusus untuk closing hari ini.')"
                                class="text-left text-[11px] p-2 rounded-lg border border-gray-200 hover:bg-orange-50 hover:border-orange-300 transition-colors text-gray-700">
                            Tawarkan promo closing
                        </button>
                        <button type="button" @click="setTemplate('Customer minta jadwal presentasi silabus dan demo, tolong koordinasikan via WA ya.')"
                                class="text-left text-[11px] p-2 rounded-lg border border-gray-200 hover:bg-orange-50 hover:border-orange-300 transition-colors text-gray-700">
                            Jadwalkan presentasi
                        </button>
                    </div>

                    <label class="block text-xs font-semibold text-gray-700 mb-1">Pesan Instruksi untuk Sales</label>
                    <textarea name="message" x-model="reminderMsg" required rows="3"
                              placeholder="Ketik instruksi follow-up atau klik template cepat di atas..."
 class="w-full text-xs border border-gray-200 p-3 focus:ring-orange-500 focus:border-orange-500"></textarea>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Tingkat Urgensi</label>
 <select name="priority" x-model="reminderPriority" class="w-full text-xs border border-gray-200 px-3 py-2 bg-gray-50/50">
                            <option value="urgent">Mendesak (Urgent)</option>
                            <option value="normal">Pengingat Biasa</option>
                        </select>
                    </div>
                    <div class="flex items-end">
                        <p class="text-[10px] text-gray-400">Sales akan menerima notifikasi lonceng & peringatan di dashboard miliknya.</p>
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-3 border-t border-gray-100">
                    <button type="button" @click="remindModal = false" class="px-4 py-2 text-xs font-medium text-gray-600 hover:bg-gray-100 rounded-xl">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 text-xs font-semibold text-white bg-orange-600 hover:bg-orange-700 rounded-xl shadow-xs transition-colors">
                        Kirim Pengingat ke Sales
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
@endsection
