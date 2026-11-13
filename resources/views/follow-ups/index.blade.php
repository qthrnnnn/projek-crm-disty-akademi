@extends('layouts.app')

@section('title', 'Jadwal Follow-up')

@section('content')
<div class="space-y-4">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Jadwal Follow-up Prospek</h1>
            <p class="text-xs text-gray-500 mt-0.5">Kelola janji tindak lanjut dan respon calon peserta</p>
        </div>
        <a href="{{ route('follow-ups.create') }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-medium text-white bg-gray-900 rounded-full hover:bg-black transition-colors shadow-sm">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Jadwal
        </a>
    </div>

 <div class="bg-white border border-blue-100 p-3.5 shadow-sm">
        <form method="GET" action="{{ route('follow-ups.index') }}" class="flex flex-wrap items-center gap-2.5">
 <select name="status" class="text-xs border border-gray-200 px-3 py-2 text-gray-700 focus:outline-none focus:border-blue-500">
                <option value="">Semua Status</option>
                <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Selesai</option>
                <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Dibatalkan</option>
            </select>
 <select name="assigned" class="text-xs border border-gray-200 px-3 py-2 text-gray-700 focus:outline-none focus:border-blue-500">
                <option value="">Semua Sales</option>
                @foreach($salesUsers as $s)
                    <option value="{{ $s->id }}" {{ request('assigned') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="text-xs bg-gray-900 text-white font-medium py-2 px-3 rounded-lg hover:bg-gray-800 transition-colors">Filter</button>
            @if(request()->anyFilled(['status', 'assigned']))
                <a href="{{ route('follow-ups.index') }}" class="text-xs text-gray-500 hover:text-gray-700 px-2 py-1">Reset</a>
            @endif
        </form>
    </div>

 <div class="bg-white border border-blue-100 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[620px] text-left text-xs text-gray-600">
                <thead class="bg-gray-50/70 border-b border-gray-100 text-gray-500 font-medium uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="py-3 px-4">Agenda & Lead</th>
                        <th class="py-3 px-4">Waktu</th>
                        <th class="py-3 px-4">Metode</th>
                        <th class="py-3 px-4">Sales PIC</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($followUps as $fu)
                    <tr class="hover:bg-blue-50/30 transition-colors">
                        <td class="py-3 px-4">
                            <div class="font-semibold text-gray-900">{{ $fu->title }}</div>
                            <a href="{{ route('leads.show', $fu->lead) }}" class="text-[11px] text-blue-600 hover:underline">
                                {{ $fu->lead?->name }} ({{ $fu->lead?->lead_code }})
                            </a>
                        </td>
                        <td class="py-3 px-4">
                            <span class="{{ $fu->status === 'pending' && $fu->follow_up_at->isPast() ? 'text-red-600 font-medium' : 'text-gray-700' }}">
                                {{ $fu->follow_up_at->translatedFormat('d M Y, H:i') }}
                            </span>
                        </td>
                        <td class="py-3 px-4 uppercase text-[10px] font-semibold text-gray-500">{{ $fu->method }}</td>
                        <td class="py-3 px-4">{{ $fu->assignedUser?->name ?? '-' }}</td>
                        <td class="py-3 px-4">
 <span class="px-2 py-0.5 text-[10px] font-medium border
                                {{ $fu->status === 'completed' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : ($fu->status === 'cancelled' ? 'bg-gray-100 text-gray-500 border-gray-200' : 'bg-amber-50 text-amber-700 border-amber-200') }}">
                                {{ ucfirst($fu->status) }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-right">
                            <div class="inline-flex items-center gap-1.5">
                                @if($fu->status === 'pending')
                                    <form method="POST" action="{{ route('follow-ups.complete', $fu) }}">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="px-2 py-1 text-[11px] text-emerald-600 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200 rounded">
                                            Selesai
                                        </button>
                                    </form>
                                @endif
                                <a href="{{ route('follow-ups.edit', $fu) }}" class="p-1 text-gray-400 hover:text-blue-600">Edit</a>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-8 text-center text-gray-400">Belum ada agenda follow-up</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($followUps->hasPages())
        <div class="p-3 border-t border-gray-100">{{ $followUps->links() }}</div>
        @endif
    </div>
</div>
@endsection
