@extends('layouts.app')

@section('title', 'Daftar Leads')

@section('content')
<div class="space-y-4" x-data="{ importModalOpen: false }">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Manajemen Leads</h1>
            <p class="text-xs text-gray-500 mt-0.5">Kelola prospek dan calon peserta pelatihan (Aturan: 1 Lead = 1 Sales Eksklusif)</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            {{-- Tombol Import CSV / Excel --}}
            <button type="button" @click="importModalOpen = true"
                    class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-medium text-gray-700 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 hover:border-gray-300 shadow-2xs transition-all">
                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                </svg>
                Import CSV / Excel
            </button>

            {{-- Tombol Template CSV --}}
            <a href="{{ route('leads.template') }}"
               data-turbo="false"
               class="inline-flex items-center gap-1.5 px-3 py-2 text-xs font-medium text-gray-600 bg-white border border-gray-200 rounded-xl hover:bg-gray-50 shadow-2xs transition-all"
               title="Unduh Format Template CSV">
                <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Template
            </a>

            {{-- Tombol Tambah Lead --}}
            <a href="{{ route('leads.create') }}" class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-semibold text-white bg-orange-600 rounded-xl hover:bg-orange-700 shadow-sm transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Tambah Lead
            </a>
        </div>
    </div>

    {{-- Tabs Kepemilikan Lead (Aturan Pembagian Lead: 1 Calon Pelanggan = 1 Sales) --}}
    <div class="flex items-center gap-2 overflow-x-auto pb-1 border-b border-gray-200 text-xs">
        @if(auth()->user()->isSales())
        <a href="{{ route('leads.index', array_merge(request()->except(['page']), ['scope' => 'my_leads'])) }}"
           class="px-3.5 py-2 font-medium rounded-lg transition-colors whitespace-nowrap flex items-center gap-1.5 {{ $scope === 'my_leads' ? 'bg-orange-500 text-white shadow-xs' : 'text-gray-600 hover:bg-gray-100' }}">
            <span>Lead Saya</span>
            <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $scope === 'my_leads' ? 'bg-white/25 text-white' : 'bg-gray-200 text-gray-700' }}">{{ $myLeadsCount }}</span>
        </a>
        @endif

        <a href="{{ route('leads.index', array_merge(request()->except(['page']), ['scope' => 'unassigned'])) }}"
           class="px-3.5 py-2 font-medium rounded-lg transition-colors whitespace-nowrap flex items-center gap-1.5 {{ $scope === 'unassigned' ? 'bg-orange-500 text-white shadow-xs' : 'text-gray-600 hover:bg-gray-100' }}">
            <span>Belum Ditugaskan (Pool Lead)</span>
            <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $scope === 'unassigned' ? 'bg-white/25 text-white' : 'bg-amber-100 text-amber-800' }}">{{ $unassignedCount }}</span>
        </a>

        <a href="{{ route('leads.index', array_merge(request()->except(['page']), ['scope' => 'lost_pool'])) }}"
           class="px-3.5 py-2 font-medium rounded-lg transition-colors whitespace-nowrap flex items-center gap-1.5 {{ $scope === 'lost_pool' ? 'bg-orange-500 text-white shadow-xs' : 'text-gray-600 hover:bg-gray-100' }}"
           title="Prospek yang lost oleh sales sebelumnya dan siap difollow-up ulang oleh sales lain">
            <span>Lost / Siap Follow-up Ulang</span>
            <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $scope === 'lost_pool' ? 'bg-white/25 text-white' : 'bg-red-100 text-red-800' }}">{{ $lostPoolCount }}</span>
        </a>

        <a href="{{ route('leads.index', array_merge(request()->except(['page']), ['scope' => 'all'])) }}"
           class="px-3.5 py-2 font-medium rounded-lg transition-colors whitespace-nowrap flex items-center gap-1.5 {{ $scope === 'all' ? 'bg-orange-500 text-white shadow-xs' : 'text-gray-600 hover:bg-gray-100' }}">
            <span>Semua Lead</span>
            <span class="px-1.5 py-0.2 rounded-full text-[10px] {{ $scope === 'all' ? 'bg-white/25 text-white' : 'bg-gray-200 text-gray-700' }}">{{ $totalLeadsCount }}</span>
        </a>
    </div>

    {{-- Filter bar --}}
 <div class="bg-white border border-gray-200 p-3.5 shadow-xs">
        <form method="GET" action="{{ route('leads.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-2.5">
            <input type="hidden" name="scope" value="{{ $scope }}">
            <div>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari nama, email, hp, instansi..."
 class="w-full text-xs border border-gray-200 px-3.5 py-2 text-gray-800 placeholder-gray-400 focus:outline-none focus:border-orange-500">
            </div>
            <div>
 <select name="status" class="w-full text-xs border border-gray-200 px-3 py-2 text-gray-700 focus:outline-none focus:border-orange-500">
                    <option value="">Semua Status</option>
                    @foreach(\App\Models\Lead::STATUS_LABELS as $key => $lbl)
                        <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>{{ $lbl }}</option>
                    @endforeach
                </select>
            </div>
            @if(auth()->user()->isAdmin())
            <div>
 <select name="assigned" class="w-full text-xs border border-gray-200 px-3 py-2 text-gray-700 focus:outline-none focus:border-orange-500">
                    <option value="">Semua Sales PIC</option>
                    @foreach($salesUsers as $sales)
                        <option value="{{ $sales->id }}" {{ request('assigned') == $sales->id ? 'selected' : '' }}>{{ $sales->name }}</option>
                    @endforeach
                </select>
            </div>
            @else
            <div>
 <input type="text" readonly value="PIC: {{ auth()->user()->name }} (Sales Aktif)" class="w-full text-xs border border-gray-100 bg-gray-50 px-3 py-2 text-gray-500">
            </div>
            @endif
            <div class="flex items-center gap-1.5">
                <button type="submit" class="w-full text-xs bg-gray-900 text-white font-medium py-2 px-3 rounded-xl hover:bg-black transition-colors">
                    Filter
                </button>
                @if(request()->anyFilled(['search', 'status', 'assigned']))
                    <a href="{{ route('leads.index', ['scope' => $scope]) }}" class="p-2 text-xs text-gray-500 hover:text-gray-700 border border-gray-200 rounded-xl hover:bg-gray-50" title="Reset">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- Leads Table --}}
 <div class="bg-white border border-gray-200 overflow-hidden shadow-xs">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-gray-600 min-w-[700px]">
                <thead class="bg-gray-50/70 border-b border-gray-100 text-gray-500 font-semibold uppercase tracking-wider text-[10.5px]">
                    <tr>
                        <th class="py-3.5 px-4">Calon Pelanggan / Lead</th>
                        <th class="py-3.5 px-4">Kontak</th>
                        <th class="py-3.5 px-4">Program Minat</th>
                        <th class="py-3.5 px-4">Sales Penanggung Jawab</th>
                        <th class="py-3.5 px-4">Status Prospek</th>
                        <th class="py-3.5 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($leads as $lead)
                    @php
                        $isMyLead = auth()->user()->id === $lead->assigned_to;
                        $isUnassigned = is_null($lead->assigned_to);
                        $isOtherSales = auth()->user()->isSales() && !$isMyLead && !$isUnassigned;
                    @endphp
                    <tr class="hover:bg-orange-50/20 transition-colors {{ $isMyLead ? 'bg-orange-50/10' : '' }}">
                        <td class="py-3.5 px-4">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('leads.show', $lead) }}" class="font-semibold text-gray-900 hover:text-orange-600 transition-colors">
                                    {{ $lead->name }}
                                </a>
                                @if($lead->latestPendingReminder)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 text-[9.5px] font-bold rounded-full bg-red-100 text-red-700 border border-red-200" title="Admin mengingatkan follow up: {{ $lead->latestPendingReminder->message }}">
                                        <span class="w-1.5 h-1.5 rounded-full bg-red-500 animate-ping"></span>
                                        Perlu Follow-Up
                                    </span>
                                @endif
                                @if($isMyLead)
 <span class="px-1.5 py-0.2 text-[9.5px] font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">PIC Saya</span>
                                @endif
                            </div>
                            <div class="flex items-center gap-1.5 text-[11px] text-gray-400 mt-0.5 font-mono">
                                <span>{{ $lead->lead_code }}</span>
                                @if($lead->company)
                                    <span class="text-gray-300">•</span>
                                    <span class="text-gray-500 font-sans truncate max-w-[180px]">{{ $lead->company }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="py-3.5 px-4">
                            <div class="flex items-center gap-1 font-medium text-gray-800">
                                @if($lead->whatsapp || $lead->phone)
                                    <span class="text-emerald-600 font-bold text-[10px]">WA</span>
                                    <span>{{ $lead->whatsapp ?: $lead->phone }}</span>
                                @else
                                    <span class="text-gray-400">-</span>
                                @endif
                            </div>
                            <div class="text-[11px] text-gray-400 truncate max-w-[160px]">{{ $lead->email ?: '-' }}</div>
                        </td>
                        <td class="py-3.5 px-4">
                            <span class="font-medium text-gray-900 block">{{ $lead->product?->name ?? 'Belum memilih' }}</span>
                            @if($lead->deal_price)
                                <div class="text-[11px] text-orange-600 font-semibold mt-0.5">
                                    Deal: Rp {{ number_format($lead->deal_price, 0, ',', '.') }}
                                    @if($lead->product)
                                        <span class="text-[10px] text-gray-400 font-normal line-through">Rp {{ number_format($lead->product->price, 0, ',', '.') }}</span>
                                    @endif
                                </div>
                            @elseif($lead->product)
                                <span class="text-[11px] text-gray-400 block mt-0.5">Harga: Rp {{ number_format($lead->product->price, 0, ',', '.') }}</span>
                            @endif
                            <div class="text-[10px] text-emerald-600 font-medium">Komisi 10%: Rp {{ number_format($lead->estimatedCommission(), 0, ',', '.') }}</div>
                        </td>
                        <td class="py-3.5 px-4">
                            @if($isUnassigned)
                                <div class="flex items-center gap-2">
 <span class="inline-flex items-center px-2 py-0.5 text-[10px] font-medium bg-amber-50 text-amber-700 border border-amber-200">
                                        Belum Ada PIC
                                    </span>
                                    @if(auth()->user()->isSales())
                                    <form method="POST" action="{{ route('leads.claim', $lead) }}">
                                        @csrf
                                        <button type="submit" class="px-2.5 py-1 text-[11px] font-bold text-white bg-emerald-600 rounded-lg hover:bg-emerald-700 transition-colors shadow-2xs whitespace-nowrap">
                                            + Ambil Lead
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            @elseif($lead->isLost() && auth()->user()->isSales() && !$isMyLead)
                                <div class="flex items-center gap-2">
                                    <span class="text-xs text-gray-400 line-through">{{ $lead->assignedUser?->name }}</span>
                                    <form method="POST" action="{{ route('leads.claim', $lead) }}">
                                        @csrf
                                        <button type="submit" class="px-2.5 py-1 text-[11px] font-bold text-white bg-orange-600 rounded-lg hover:bg-orange-700 transition-colors shadow-2xs whitespace-nowrap" title="Ambil alih pelanggan lost untuk difollow-up ulang">
                                            + Follow-up Ulang
                                        </button>
                                    </form>
                                </div>
                            @else
                                <div class="inline-flex items-center gap-1.5">
                                    <span class="w-5 h-5 rounded-full {{ $isMyLead ? 'bg-orange-500 text-white' : 'bg-gray-200 text-gray-700' }} flex items-center justify-center text-[10px] font-bold">
                                        {{ strtoupper(substr($lead->assignedUser?->name ?? '?', 0, 1)) }}
                                    </span>
                                    <span class="font-medium text-gray-700 text-xs">{{ $lead->assignedUser?->name ?? '-' }}</span>
                                    @if($isOtherSales)
                                        <svg class="w-3.5 h-3.5 text-gray-400 ml-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" title="Terkunci: Sedang aktif ditangani sales lain">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                        </svg>
                                    @endif
                                </div>
                            @endif
                        </td>
                        <td class="py-3.5 px-4">
                            @php
                                $sClasses = [
                                    'new'         => 'bg-slate-100 text-slate-700 border-slate-200',
                                    'contacted'   => 'bg-blue-50 text-blue-700 border-blue-200',
                                    'active'      => 'bg-cyan-50 text-cyan-700 border-cyan-200',
                                    'negotiation' => 'bg-amber-50 text-amber-700 border-amber-200',
                                    'won'         => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    'lost'        => 'bg-red-50 text-red-700 border-red-200',
                                    'inactive'    => 'bg-gray-100 text-gray-600 border-gray-200',
                                ];
                            @endphp
 <span class="inline-block px-2 py-0.5 border text-[10.5px] font-semibold {{ $sClasses[$lead->status] ?? 'bg-gray-50 text-gray-600' }}">
                                {{ \App\Models\Lead::STATUS_LABELS[$lead->status] ?? $lead->status }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <div class="inline-flex items-center gap-1">
                                <a href="{{ route('leads.show', $lead) }}" class="p-1.5 text-gray-400 hover:text-orange-600 hover:bg-orange-50 rounded-lg transition-colors" title="Lihat Detail">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>

                                {{-- Jika user adalah sales dan bukan pemilik lead, sembunyikan edit agar tidak bentrok --}}
                                @if(!auth()->user()->isSales() || $isMyLead)
                                <a href="{{ route('leads.edit', $lead) }}" class="p-1.5 text-gray-400 hover:text-amber-600 hover:bg-amber-50 rounded-lg transition-colors" title="Edit Lead">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/></svg>
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-12 text-center text-gray-400">
                            <svg class="w-9 h-9 mx-auto mb-2 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                            <p class="font-medium text-gray-500">Tidak ada data lead dalam filter ini</p>
                            <p class="text-xs text-gray-400 mt-0.5">Silakan pilih tab lain atau bersihkan kata kunci pencarian</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($leads->hasPages())
        <div class="p-3 border-t border-gray-100 bg-gray-50/50">
            {{ $leads->links() }}
        </div>
        @endif
    </div>

    {{-- Modal Import CSV / Excel --}}
    <div x-show="importModalOpen" x-cloak
         class="fixed inset-0 z-50 overflow-y-auto bg-gray-900/50 backdrop-blur-xs flex items-center justify-center p-4"
         @keydown.escape.window="importModalOpen = false">
 <div class="bg-white max-w-lg w-full p-6 shadow-xl border border-gray-100 space-y-4"
             @click.away="importModalOpen = false">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                <div class="flex items-center gap-2.5">
 <div class="w-9 h-9 bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-gray-900">Import Leads (CSV / Excel)</h2>
                        <p class="text-[11px] text-gray-500">Mendukung format file .csv dan spreadsheet Excel .xlsx</p>
                    </div>
                </div>
                <button type="button" @click="importModalOpen = false" class="text-gray-400 hover:text-gray-600 text-xl font-bold">&times;</button>
            </div>

            {{-- Info Format Kolom --}}
 <div class="bg-blue-50/60 border border-blue-100 p-3.5 space-y-2 text-xs">
                <p class="font-semibold text-blue-900 text-[11.5px]">Ketentuan Format Kolom File:</p>
                <div class="grid grid-cols-2 gap-x-2 gap-y-1 text-gray-700 text-[11px]">
                    <div>1. <strong class="text-gray-900">Nama</strong> <span class="text-red-500 font-semibold">(Wajib)</span></div>
                    <div>2. <strong>WhatsApp / HP</strong></div>
                    <div>3. <strong>Email</strong></div>
                    <div>4. <strong>Perusahaan / Instansi</strong></div>
                    <div>5. <strong>Kota</strong></div>
                    <div>6. <strong>Provinsi</strong></div>
                    <div>7. <strong>Program Minat</strong></div>
                    <div>8. <strong>Catatan</strong></div>
                </div>
                <p class="text-[10.5px] text-gray-500 pt-1 border-t border-blue-100">
                    * Data nama instansi akan otomatis disinkronkan ke <strong>Daftar Instansi</strong>.
                </p>
                <div class="pt-1">
                    <a href="{{ route('leads.template') }}" class="inline-flex items-center gap-1.5 text-blue-700 hover:text-blue-800 hover:underline font-semibold text-[11px]">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Download Contoh Format Template CSV
                    </a>
                </div>
            </div>

            <form method="POST" action="{{ route('leads.import') }}" enctype="multipart/form-data" class="space-y-3.5 text-xs">
                @csrf
                <div>
                    <label class="block font-semibold text-gray-700 mb-1">Pilih File CSV atau Excel (.csv, .xlsx)</label>
                    <input type="file" name="file" accept=".csv, .xlsx, .xls, text/csv, application/vnd.openxmlformats-officedocument.spreadsheetml.sheet, application/vnd.ms-excel" required
 class="w-full text-xs text-gray-700 border border-gray-200 p-2 file:mr-3 file:py-1.5 file:px-3 file: file:border-0 file:text-xs file:font-semibold file:bg-orange-50 file:text-orange-700 hover:file:bg-orange-100">
                </div>

                @if(auth()->user()->isAdmin())
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">Tugaskan ke Sales (PIC)</label>
 <select name="assigned_to" class="w-full text-xs border border-gray-200 px-2.5 py-2 text-gray-700 focus:outline-none focus:border-orange-500">
                            <option value="">-- Belum Ditugaskan (Pool Lead) --</option>
                            @foreach($salesUsers as $sales)
                                <option value="{{ $sales->id }}">{{ $sales->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">Sumber Lead</label>
 <select name="lead_source_id" class="w-full text-xs border border-gray-200 px-2.5 py-2 text-gray-700 focus:outline-none focus:border-orange-500">
                            <option value="">Default (Import Excel/CSV)</option>
                            @if(isset($sources))
                                @foreach($sources as $src)
                                    <option value="{{ $src->id }}" {{ in_array($src->name, ['import_excel', 'import_csv']) ? 'selected' : '' }}>
                                        {{ $src->label }}
                                    </option>
                                @endforeach
                            @endif
                        </select>
                    </div>
                </div>
                @else
 <p class="text-[11px] text-gray-500 bg-gray-50 p-2.5 border border-gray-100">
                    Sesuai mode sales Anda, seluruh lead yang Anda impor akan otomatis ditugaskan secara eksklusif kepada Anda (<strong>{{ auth()->user()->name }}</strong>).
                </p>
                @endif

                <div class="flex items-center justify-end gap-2 pt-3 border-t border-gray-100">
                    <button type="button" @click="importModalOpen = false" class="px-4 py-2 text-xs text-gray-500 hover:text-gray-700 font-medium">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 text-xs text-white bg-orange-600 rounded-xl hover:bg-orange-700 shadow-xs font-semibold inline-flex items-center gap-1.5">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                        </svg>
                        Mulai Import Data
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
