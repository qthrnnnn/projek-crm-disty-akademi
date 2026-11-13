@extends('layouts.app')

@section('title', 'Log Aktivitas Interaksi')

@section('content')
<div class="space-y-4">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Log Aktivitas & Riwayat Interaksi</h1>
            <p class="text-xs text-gray-500 mt-0.5">Semua jejak kontak prospek, telepon, whatsapp, & status change</p>
        </div>
    </div>

    {{-- Filter --}}
 <div class="bg-white border border-blue-100 p-3.5 shadow-sm">
        <form method="GET" action="{{ route('activities.index') }}" class="flex flex-wrap items-center gap-2.5">
 <select name="type" class="text-xs border border-gray-200 px-3 py-2 text-gray-700 focus:outline-none focus:border-blue-500">
                <option value="">Semua Jenis Aktivitas</option>
                @foreach(\App\Models\Activity::TYPE_LABELS as $tk => $tl)
                    <option value="{{ $tk }}" {{ request('type') == $tk ? 'selected' : '' }}>{{ $tl }}</option>
                @endforeach
            </select>
            <button type="submit" class="text-xs bg-gray-900 text-white font-medium py-2 px-3 rounded-lg hover:bg-gray-800">Filter</button>
            @if(request()->filled('type'))
                <a href="{{ route('activities.index') }}" class="text-xs text-gray-500 hover:text-gray-700 px-2 py-1">Reset</a>
            @endif
        </form>
    </div>

    {{-- Activities Table --}}
 <div class="bg-white border border-blue-100 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[620px] text-left text-xs text-gray-600">
                <thead class="bg-gray-50/70 border-b border-gray-100 text-gray-500 font-medium uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="py-3 px-4">Waktu</th>
                        <th class="py-3 px-4">Tipe</th>
                        <th class="py-3 px-4">Prospek</th>
                        <th class="py-3 px-4">Subjek / Detail</th>
                        <th class="py-3 px-4">Sales User</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($activities as $act)
                    <tr class="hover:bg-blue-50/30 transition-colors">
                        <td class="py-3 px-4 whitespace-nowrap text-gray-500">
                            {{ $act->activity_at->translatedFormat('d M Y, H:i') }}
                        </td>
                        <td class="py-3 px-4">
 <span class="px-2 py-0.5 text-[10px] font-medium border bg-gray-50 border-gray-200">
                                {{ \App\Models\Activity::TYPE_LABELS[$act->type] ?? ucfirst($act->type) }}
                            </span>
                        </td>
                        <td class="py-3 px-4">
                            <a href="{{ route('leads.show', $act->lead) }}" class="font-semibold text-blue-600 hover:underline">
                                {{ $act->lead?->name ?? '-' }}
                            </a>
                        </td>
                        <td class="py-3 px-4">
                            <div class="font-medium text-gray-900">{{ $act->subject ?? '-' }}</div>
                            @if($act->description)
                                <div class="text-[11px] text-gray-500 mt-0.5">{{ $act->description }}</div>
                            @endif
                        </td>
                        <td class="py-3 px-4">{{ $act->user?->name ?? 'System' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-8 text-center text-gray-400">Belum ada riwayat aktivitas</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($activities->hasPages())
        <div class="p-3 border-t border-gray-100">{{ $activities->links() }}</div>
        @endif
    </div>
</div>
@endsection
