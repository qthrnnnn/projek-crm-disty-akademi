@extends('layouts.app')

@section('title', 'Pendaftaran Peserta')

@section('content')
<div class="space-y-4">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Pendaftaran Peserta</h1>
            <p class="text-xs text-gray-500 mt-0.5">Customer didaftarkan ke jadwal yang telah dibuat, bukan menentukan tanggal sendiri.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('training-schedules.index') }}" class="px-3.5 py-2 text-xs font-medium text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 shadow-sm transition-colors">
                Lihat Jadwal
            </a>
            <a href="{{ route('registrations.create') }}" class="px-4 py-2 text-xs font-semibold text-white bg-gray-900 rounded-full hover:bg-black shadow-sm transition-colors">
                + Daftarkan Customer
            </a>
        </div>
    </div>

    {{-- Filter Card --}}
 <div class="bg-white border border-blue-100/70 p-3.5 shadow-sm">
        <form method="GET" action="{{ route('registrations.index') }}" class="flex flex-col sm:flex-row items-center gap-2.5">
            <div class="relative flex-1 w-full">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Cari nomor, customer, training, trainer..."
 class="w-full text-xs border border-gray-200 pl-3 pr-4 py-2 text-gray-800 placeholder-gray-400 focus:outline-none focus:border-blue-500">
            </div>
            <div class="w-full sm:w-48">
 <select name="status" class="w-full text-xs border border-gray-200 px-3 py-2 text-gray-700 focus:outline-none focus:border-blue-500">
                    <option value="">Semua status</option>
                    <option value="registered" {{ request('status') == 'registered' ? 'selected' : '' }}>Registered</option>
                    <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                    <option value="attended" {{ request('status') == 'attended' ? 'selected' : '' }}>Attended</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <button type="submit" class="w-full sm:w-auto px-5 py-2 text-xs font-semibold text-white bg-slate-900 rounded-lg hover:bg-slate-800 shadow-sm transition-colors">
                Filter
            </button>
            @if(request()->anyFilled(['search', 'status']))
                <a href="{{ route('registrations.index') }}" class="px-3 py-2 text-xs text-gray-500 hover:text-gray-700 border border-gray-200 rounded-lg">Reset</a>
            @endif
        </form>
    </div>

    {{-- Table Card --}}
 <div class="bg-white border border-blue-100/70 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[680px] text-left text-xs text-gray-600">
                <thead class="bg-gray-50/70 border-b border-gray-100 text-gray-400 font-semibold uppercase tracking-wider text-[10.5px]">
                    <tr>
                        <th class="py-3.5 px-4">NO. REGISTRASI</th>
                        <th class="py-3.5 px-4">CUSTOMER</th>
                        <th class="py-3.5 px-4">PELATIHAN</th>
                        <th class="py-3.5 px-4">JADWAL</th>
                        <th class="py-3.5 px-4">STATUS</th>
                        <th class="py-3.5 px-4">AMOUNT</th>
                        <th class="py-3.5 px-4 text-right">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($registrations as $reg)
                    <tr class="hover:bg-blue-50/20 transition-colors">
                        <td class="py-3.5 px-4 font-mono font-semibold text-gray-900">
                            {{ $reg->registration_number }}
                        </td>
                        <td class="py-3.5 px-4">
                            <a href="{{ route('customers.show', $reg->customer) }}" class="font-medium text-blue-600 hover:underline">
                                {{ $reg->customer?->name }}
                            </a>
                            <div class="text-[11px] text-gray-400">{{ $reg->customer?->customer_code }}</div>
                        </td>
                        <td class="py-3.5 px-4 font-medium text-gray-800">
                            {{ $reg->product?->name }}
                        </td>
                        <td class="py-3.5 px-4">
                            @if($reg->schedule)
                                <div class="font-medium text-gray-800">{{ $reg->schedule->training_date->translatedFormat('d M Y') }}</div>
                                <div class="text-[11px] text-gray-400">Trainer: {{ $reg->schedule->trainer?->name ?? '-' }}</div>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4">
                            @php
                                $rStyles = [
                                    'registered' => 'bg-blue-50 text-blue-700 border-blue-200',
                                    'confirmed'  => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    'attended'   => 'bg-purple-50 text-purple-700 border-purple-200',
                                    'cancelled'  => 'bg-gray-100 text-gray-600 border-gray-200',
                                ];
                            @endphp
 <span class="inline-block px-2 py-0.5 border text-[10px] font-semibold uppercase tracking-wider {{ $rStyles[$reg->status] ?? 'bg-gray-50 border-gray-200' }}">
                                {{ ucfirst($reg->status) }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 font-bold text-gray-900">
                            Rp {{ number_format($reg->amount, 0, ',', '.') }}
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <div class="inline-flex items-center gap-1.5">
                                <a href="{{ route('registrations.edit', $reg) }}" class="text-xs text-blue-600 hover:text-blue-800 font-medium p-1">Edit</a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-12 text-center text-gray-400 text-sm">
                            Belum ada pendaftaran.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Footer pagination & per_page --}}
        <div class="p-4 border-t border-gray-100 bg-gray-50/40 flex flex-col sm:flex-row items-center justify-between gap-3">
            <form method="GET" action="{{ route('registrations.index') }}" class="flex items-center gap-2 text-xs text-gray-500">
                <span>Tampilkan</span>
 <select name="per_page" onchange="this.form.submit()" class=" border border-gray-200 px-2.5 py-1 text-xs text-gray-700 bg-white focus:outline-none">
                    <option value="20" {{ request('per_page') == 20 ? 'selected' : '' }}>20</option>
                    <option value="50" {{ request('per_page') == 50 || !request('per_page') ? 'selected' : '' }}>50</option>
                    <option value="100" {{ request('per_page') == 100 ? 'selected' : '' }}>100</option>
                </select>
            </form>
            @if($registrations->hasPages())
                <div>{{ $registrations->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
