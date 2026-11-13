@extends('layouts.app')

@section('title', 'Daftar Customer')

@section('content')
<div class="space-y-4" x-data="{ importModalOpen: false }">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Daftar Customer</h1>
            <p class="text-xs text-gray-500 mt-0.5">Data pelanggan, peserta pelatihan, dan instansi mitra Disty Akademi.</p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            {{-- Tombol Import --}}
            <button type="button" @click="importModalOpen = true"
                    class="px-3.5 py-2 text-xs font-medium text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 shadow-sm transition-colors flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                </svg>
                Import Data
            </button>

            {{-- Tombol Export --}}
            <a href="{{ route('customers.export') }}"
               data-turbo="false"
               class="px-3.5 py-2 text-xs font-medium text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 shadow-sm transition-colors flex items-center gap-1.5"
               title="Export Customer ke CSV (8 Kolom Sesuai Ketentuan)">
                <svg class="w-3.5 h-3.5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Export Excel/CSV
            </a>

            {{-- Tombol Template --}}
            <a href="{{ route('customers.template') }}"
               data-turbo="false"
               class="px-3 py-2 text-xs text-gray-500 hover:text-gray-700 border border-gray-200 rounded-lg bg-white"
               title="Unduh Format Template CSV">
                Template CSV
            </a>

            {{-- Tombol Tambah --}}
            <a href="{{ route('customers.create') }}"
               class="px-4 py-2 text-xs font-semibold text-white bg-gray-900 rounded-full hover:bg-black shadow-sm transition-colors">
                + Tambah Customer
            </a>
        </div>
    </div>

    {{-- Filter Card (Sesuai Image 5) --}}
 <div class="bg-white border border-blue-100/70 p-3.5 shadow-sm">
        <form method="GET" action="{{ route('customers.index') }}" class="flex flex-col sm:flex-row items-center gap-2.5">
            <div class="relative flex-1 w-full">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Cari customer, email, telepon, instansi..."
 class="w-full text-xs border border-gray-200 px-3 py-2 text-gray-800 placeholder-gray-400 focus:outline-none focus:border-blue-500">
            </div>
            <div class="w-full sm:w-44">
 <select name="status" class="w-full text-xs border border-gray-200 px-3 py-2 text-gray-700 focus:outline-none focus:border-blue-500">
                    <option value="">Semua Status</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="prospect" {{ request('status') == 'prospect' ? 'selected' : '' }}>Prospect</option>
                    <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                </select>
            </div>
            <div class="w-full sm:w-52">
 <select name="institution_id" class="w-full text-xs border border-gray-200 px-3 py-2 text-gray-700 focus:outline-none focus:border-blue-500">
                    <option value="">Semua Instansi</option>
                    @foreach($institutions as $inst)
                        <option value="{{ $inst->id }}" {{ request('institution_id') == $inst->id ? 'selected' : '' }}>
                            {{ $inst->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="w-full sm:w-auto px-5 py-2 text-xs font-semibold text-white bg-slate-900 rounded-lg hover:bg-slate-800 shadow-sm transition-colors whitespace-nowrap">
                Terapkan Filter
            </button>
            @if(request()->anyFilled(['search', 'status', 'institution_id']))
                <a href="{{ route('customers.index') }}" class="px-3 py-2 text-xs text-gray-500 hover:text-gray-700 border border-gray-200 rounded-lg">Reset</a>
            @endif
        </form>
    </div>

    {{-- Table Card (Sesuai Image 5) --}}
 <div class="bg-white border border-blue-100/70 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[700px] text-left text-xs text-gray-600">
                <thead class="bg-gray-50/70 border-b border-gray-100 text-gray-400 font-semibold uppercase tracking-wider text-[10.5px]">
                    <tr>
                        <th class="py-3.5 px-4">CUSTOMER</th>
                        <th class="py-3.5 px-4">CUSTOMER CODE</th>
                        <th class="py-3.5 px-4">INSTANSI</th>
                        <th class="py-3.5 px-4">EMAIL</th>
                        <th class="py-3.5 px-4">TELEPON</th>
                        <th class="py-3.5 px-4">STATUS</th>
                        <th class="py-3.5 px-4 text-right">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($customers as $c)
                    <tr class="hover:bg-blue-50/20 transition-colors">
                        <td class="py-3.5 px-4 font-semibold text-gray-900">
                            <a href="{{ route('customers.show', $c) }}" class="hover:text-blue-600">
                                {{ $c->name }}
                            </a>
                        </td>
                        <td class="py-3.5 px-4 font-mono text-[11px] text-gray-500">
                            {{ $c->customer_code }}
                        </td>
                        <td class="py-3.5 px-4 text-gray-700 font-medium">
                            @if($c->institution)
                                <div>{{ $c->institution->name }}</div>
                                <div class="text-[10.5px] text-gray-400">{{ $c->institution->type ?? 'Instansi' }}</div>
                            @else
                                <span class="text-gray-300">-</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-gray-600">
                            {{ $c->email ?? '-' }}
                        </td>
                        <td class="py-3.5 px-4 text-gray-700 font-mono text-[11.5px]">
                            {{ $c->phone ?? '-' }}
                        </td>
                        <td class="py-3.5 px-4">
                            @php
                                $badgeStyle = match($c->status) {
                                    'active'   => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    'prospect' => 'bg-amber-50 text-amber-700 border-amber-200',
                                    'inactive' => 'bg-gray-100 text-gray-600 border-gray-200',
                                    default    => 'bg-gray-50 text-gray-600 border-gray-200',
                                };
                            @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10.5px] font-semibold border {{ $badgeStyle }}">
                                {{ ucfirst($c->status) }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-right whitespace-nowrap">
                            <div class="inline-flex items-center gap-1">
                                <a href="{{ route('customers.show', $c) }}" class="p-1.5 text-gray-400 hover:text-blue-600 rounded hover:bg-blue-50" title="Detail & Riwayat">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                                <a href="{{ route('customers.edit', $c) }}" class="p-1.5 text-gray-400 hover:text-blue-600 rounded hover:bg-blue-50" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                @if(auth()->user()->isAdmin())
                                <form method="POST" action="{{ route('customers.destroy', $c) }}" class="inline" onsubmit="return confirm('Hapus customer ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 text-gray-400 hover:text-red-600 rounded hover:bg-red-50" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-gray-400 text-xs">
                            Belum ada customer terdaftar. Silakan tambah manual atau import file CSV.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($customers->hasPages())
            <div class="px-4 py-3 border-t border-gray-100 bg-gray-50/50">
                {{ $customers->links() }}
            </div>
        @endif
    </div>

    {{-- Modal Import CSV/Excel (Sesuai Ketentuan 8 Kolom di Image 6) --}}
    <div x-show="importModalOpen" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-gray-900/40 backdrop-blur-sm">
 <div @click.away="importModalOpen = false" class="bg-white max-w-lg w-full p-6 shadow-2xl border border-blue-100 space-y-4 text-xs">
            <div class="flex items-center justify-between pb-3 border-b border-gray-100">
                <div class="flex items-center gap-2">
 <div class="w-8 h-8 bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-sm font-bold text-gray-900">Import Data CSV / Excel</h2>
                        <p class="text-[11px] text-gray-500">Ketentuan format 8 kolom sesuai standar sistem</p>
                    </div>
                </div>
                <button type="button" @click="importModalOpen = false" class="text-gray-400 hover:text-gray-600 text-lg">&times;</button>
            </div>

            {{-- Info Ketentuan 8 Kolom (Sesuai Image 6) --}}
 <div class="bg-blue-50/60 border border-blue-100 p-3.5 space-y-2">
                <p class="font-semibold text-blue-900 text-[11.5px]">Ketentuan Kolom File:</p>
                <ol class="list-decimal list-inside space-y-1 text-gray-700 text-[11px]">
                    <li><strong class="text-gray-900">Nama</strong> <span class="text-red-500 text-[10px] font-semibold">(Wajib)</span></li>
                    <li><strong>Email</strong> <span class="text-gray-400 text-[10px]">(Opsional)</span></li>
                    <li><strong>Nomor Telepon</strong> <span class="text-gray-400 text-[10px]">(Opsional)</span></li>
                    <li><strong>Nomor Dokumen</strong> <span class="text-gray-400 text-[10px]">(Opsional / NIK / NIP / ID)</span></li>
                    <li><strong>Nama Instansi</strong> <span class="text-gray-400 text-[10px]">(Opsional - otomatis dibuat jika baru)</span></li>
                    <li><strong>Jenis Instansi</strong> <span class="text-gray-400 text-[10px]">(Opsional: Perguruan Tinggi, Pemerintahan, BUMN, dll)</span></li>
                    <li><strong>Kota</strong> <span class="text-gray-400 text-[10px]">(Opsional)</span></li>
                    <li><strong>Provinsi</strong> <span class="text-gray-400 text-[10px]">(Opsional)</span></li>
                </ol>
                <div class="pt-1">
                    <a href="{{ route('customers.template') }}" class="inline-flex items-center gap-1 text-blue-700 hover:underline font-semibold text-[11px]">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Download Contoh Format Template CSV
                    </a>
                </div>
            </div>

            <form method="POST" action="{{ route('customers.import') }}" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <div>
                    <label class="block font-semibold text-gray-700 mb-1">Pilih File CSV (.csv)</label>
                    <input type="file" name="csv_file" accept=".csv,text/csv" required
 class="w-full text-xs text-gray-700 border border-gray-200 p-2 file:mr-3 file:py-1.5 file:px-3 file: file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                </div>

                <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-100">
                    <button type="button" @click="importModalOpen = false" class="px-4 py-2 text-gray-500 hover:text-gray-700">
                        Batal
                    </button>
                    <button type="submit" class="px-5 py-2 text-white bg-slate-900 rounded-lg hover:bg-slate-800 shadow-sm font-semibold">
                        Import Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
