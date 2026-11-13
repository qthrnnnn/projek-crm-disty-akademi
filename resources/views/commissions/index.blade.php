@extends('layouts.app')

@section('title', 'Komisi Sales')

@section('content')
<div class="space-y-4">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Manajemen Komisi Sales</h1>
            <p class="text-xs text-gray-500 mt-0.5">Penetapan dan pembayaran insentif atas setiap peserta (pelanggan) yang berhasil mendaftar</p>
        </div>
    </div>

    {{-- Cards Summary --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
 <div class="bg-white border border-blue-100 p-4 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-500">Komisi Menunggu / Disetujui (Pending)</p>
                <p class="text-2xl font-bold text-amber-600 mt-1">Rp {{ number_format($totalPending, 0, ',', '.') }}</p>
            </div>
 <div class="w-10 h-10 bg-amber-50 text-amber-600 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
 <div class="bg-white border border-blue-100 p-4 shadow-sm flex items-center justify-between">
            <div>
                <p class="text-xs text-gray-500">Total Komisi Sudah Dibayarkan</p>
                <p class="text-2xl font-bold text-emerald-600 mt-1">Rp {{ number_format($totalPaid, 0, ',', '.') }}</p>
            </div>
 <div class="w-10 h-10 bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
        </div>
    </div>

    {{-- Filter --}}
 <div class="bg-white border border-blue-100 p-3.5 shadow-sm">
        <form method="GET" action="{{ route('commissions.index') }}" class="flex flex-wrap items-center gap-2.5">
 <select name="status" class="text-xs border border-gray-200 px-3 py-2 text-gray-700 focus:outline-none focus:border-blue-500">
                <option value="">Semua Status Komisi</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="approved" {{ request('status') == 'approved' ? 'selected' : '' }}>Disetujui (Approved)</option>
                <option value="paid" {{ request('status') == 'paid' ? 'selected' : '' }}>Sudah Dibayar (Paid)</option>
            </select>
 <select name="user_id" class="text-xs border border-gray-200 px-3 py-2 text-gray-700 focus:outline-none focus:border-blue-500">
                <option value="">Semua Sales</option>
                @foreach($salesUsers as $s)
                    <option value="{{ $s->id }}" {{ request('user_id') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="text-xs bg-gray-900 text-white font-medium py-2 px-3 rounded-lg hover:bg-gray-800">Filter</button>
            @if(request()->anyFilled(['status', 'user_id']))
                <a href="{{ route('commissions.index') }}" class="text-xs text-gray-500 hover:text-gray-700 px-2 py-1">Reset</a>
            @endif
        </form>
    </div>

    {{-- Table --}}
 <div class="bg-white border border-blue-100 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[650px] text-left text-xs text-gray-600">
                <thead class="bg-gray-50/70 border-b border-gray-100 text-gray-500 font-medium uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="py-3 px-4">Sales Penerima</th>
                        <th class="py-3 px-4">Peserta / Lead</th>
                        <th class="py-3 px-4">Nilai Deal</th>
                        <th class="py-3 px-4">Rate (%)</th>
                        <th class="py-3 px-4">Nominal Komisi</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($commissions as $comm)
                    <tr class="hover:bg-blue-50/30 transition-colors">
                        <td class="py-3 px-4 font-semibold text-gray-900">{{ $comm->user?->name ?? '-' }}</td>
                        <td class="py-3 px-4">
                            <a href="{{ route('leads.show', $comm->lead) }}" class="text-blue-600 hover:underline">
                                {{ $comm->lead?->name }}
                            </a>
                        </td>
                        <td class="py-3 px-4">Rp {{ number_format($comm->deal_amount, 0, ',', '.') }}</td>
                        <td class="py-3 px-4 font-medium">{{ $comm->commission_rate }}%</td>
                        <td class="py-3 px-4 font-bold text-emerald-600">Rp {{ number_format($comm->commission_amount, 0, ',', '.') }}</td>
                        <td class="py-3 px-4">
                            @php
                                $cStyles = [
                                    'paid'     => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    'approved' => 'bg-blue-50 text-blue-700 border-blue-200',
                                    'pending'  => 'bg-amber-50 text-amber-700 border-amber-200',
                                ];
                            @endphp
 <span class="px-2 py-0.5 text-[10px] font-medium border {{ $cStyles[$comm->status] ?? 'bg-gray-50 border-gray-200' }}">
                                {{ ucfirst($comm->status) }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-right">
                            @if(auth()->user()->isAdmin())
                            <div class="inline-flex items-center gap-1.5">
                                @if($comm->status === 'pending')
                                    <form method="POST" action="{{ route('commissions.approve', $comm) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="px-2 py-1 text-[11px] text-blue-600 bg-blue-50 hover:bg-blue-100 rounded border border-blue-200">
                                            Setujui
                                        </button>
                                    </form>
                                @endif
                                @if($comm->status === 'approved')
                                    <form method="POST" action="{{ route('commissions.pay', $comm) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="px-2 py-1 text-[11px] text-emerald-600 bg-emerald-50 hover:bg-emerald-100 rounded border border-emerald-200">
                                            Bayar
                                        </button>
                                    </form>
                                @endif
                            </div>
                            @else
                            <span class="text-[11px] text-gray-400">
                                {{ $comm->status === 'paid' ? 'Telah Diterima' : 'Menunggu Approval Admin' }}
                            </span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-gray-400">Belum ada catatan komisi sales</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($commissions->hasPages())
        <div class="p-3 border-t border-gray-100">{{ $commissions->links() }}</div>
        @endif
    </div>
</div>
@endsection
