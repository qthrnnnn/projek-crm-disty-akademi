@extends('layouts.app')

@section('title', 'Verifikasi Pembayaran')

@section('content')
<div class="space-y-4">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Pembayaran & Bukti Transfer</h1>
            <p class="text-xs text-gray-500 mt-0.5">Kelola verifikasi transfer peserta dan pencatatan deal akademi</p>
        </div>
        <a href="{{ route('payments.create') }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-medium text-white bg-gray-900 rounded-full hover:bg-black transition-colors shadow-sm">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Input Pembayaran
        </a>
    </div>

    {{-- Filter --}}
 <div class="bg-white border border-blue-100 p-3.5 shadow-sm">
        <form method="GET" action="{{ route('payments.index') }}" class="flex flex-wrap items-center gap-2.5">
 <select name="status" class="text-xs border border-gray-200 px-3 py-2 text-gray-700 focus:outline-none focus:border-blue-500">
                <option value="">Semua Status Pembayaran</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Menunggu Verifikasi</option>
                <option value="valid" {{ request('status') == 'valid' ? 'selected' : '' }}>Valid / Diterima</option>
                <option value="invalid" {{ request('status') == 'invalid' ? 'selected' : '' }}>Tidak Valid</option>
                <option value="recheck" {{ request('status') == 'recheck' ? 'selected' : '' }}>Perlu Cek Ulang</option>
            </select>
            <button type="submit" class="text-xs bg-gray-900 text-white font-medium py-2 px-3 rounded-lg hover:bg-gray-800">Filter</button>
            @if(request()->filled('status'))
                <a href="{{ route('payments.index') }}" class="text-xs text-gray-500 hover:text-gray-700 px-2 py-1">Reset</a>
            @endif
        </form>
    </div>

 <div class="bg-white border border-blue-100 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[640px] text-left text-xs text-gray-600">
                <thead class="bg-gray-50/70 border-b border-gray-100 text-gray-500 font-medium uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="py-3 px-4">Tanggal & Peserta</th>
                        <th class="py-3 px-4">Program Pelatihan</th>
                        <th class="py-3 px-4">Nominal</th>
                        <th class="py-3 px-4">Metode / Bank</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($payments as $p)
                    <tr class="hover:bg-blue-50/30 transition-colors">
                        <td class="py-3 px-4">
                            <div class="font-semibold text-gray-900">{{ $p->lead?->name }}</div>
                            <div class="text-[11px] text-gray-400">{{ $p->payment_date ? $p->payment_date->translatedFormat('d M Y') : $p->created_at->translatedFormat('d M Y') }}</div>
                        </td>
                        <td class="py-3 px-4">{{ $p->product?->name ?? ($p->lead?->product?->name ?? '-') }}</td>
                        <td class="py-3 px-4 font-bold text-gray-900">Rp {{ number_format($p->amount, 0, ',', '.') }}</td>
                        <td class="py-3 px-4">
                            <div>{{ $p->bank_name ?? ucfirst($p->payment_method ?? 'Transfer') }}</div>
                            <div class="text-[11px] text-gray-400">{{ $p->account_name ? 'a.n ' . $p->account_name : '' }}</div>
                        </td>
                        <td class="py-3 px-4">
                            @php
                                $statusStyles = [
                                    'valid'   => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    'invalid' => 'bg-red-50 text-red-700 border-red-200',
                                    'recheck' => 'bg-orange-50 text-orange-700 border-orange-200',
                                    'pending' => 'bg-amber-50 text-amber-700 border-amber-200',
                                ];
                            @endphp
 <span class="px-2 py-0.5 text-[10px] font-medium border {{ $statusStyles[$p->status] ?? 'bg-gray-50 border-gray-200' }}">
                                {{ ucfirst($p->status) }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-right">
                            @if(auth()->user()->isAdmin())
                            <a href="{{ route('payments.show', $p) }}" class="inline-flex items-center gap-1 text-xs font-medium text-blue-600 hover:text-blue-800 bg-blue-50 hover:bg-blue-100 px-2.5 py-1 rounded">
                                Verifikasi
                            </a>
                            @else
                            <a href="{{ route('payments.show', $p) }}" class="inline-flex items-center gap-1 text-xs font-medium text-gray-700 hover:text-gray-900 bg-gray-100 hover:bg-gray-200 px-2.5 py-1 rounded">
                                Detail
                            </a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-8 text-center text-gray-400">Belum ada riwayat pembayaran</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($payments->hasPages())
        <div class="p-3 border-t border-gray-100">{{ $payments->links() }}</div>
        @endif
    </div>
</div>
@endsection
