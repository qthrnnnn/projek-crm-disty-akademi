@extends('layouts.app')

@section('title', 'Jadwal yang Tersedia')

@section('content')
<div class="space-y-4">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Jadwal yang Tersedia</h1>
            <p class="text-xs text-gray-500 mt-0.5">Setiap jadwal memiliki trainer, waktu, lokasi, dan kapasitas.</p>
        </div>
        <div class="flex items-center gap-2">
            @if(auth()->user()->isAdmin())
                <a href="{{ route('trainers.index') }}" class="px-3.5 py-2 text-xs font-medium text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50 shadow-sm transition-colors">
                    Kelola Trainer
                </a>
                <a href="{{ route('training-schedules.create') }}" class="px-4 py-2 text-xs font-semibold text-white bg-gray-900 rounded-full hover:bg-black shadow-sm transition-colors">
                    + Buat Jadwal
                </a>
            @endif
        </div>
    </div>

    {{-- Filter Card --}}
 <div class="bg-white border border-blue-100/70 p-3.5 shadow-sm">
        <form method="GET" action="{{ route('training-schedules.index') }}" class="flex flex-col sm:flex-row items-center gap-2.5">
            <div class="relative flex-1 w-full">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Cari pelatihan, trainer, atau lokasi..."
 class="w-full text-xs border border-gray-200 px-3 py-2 text-gray-800 placeholder-gray-400 focus:outline-none focus:border-blue-500">
            </div>
            <div class="w-full sm:w-48">
 <select name="status" class="w-full text-xs border border-gray-200 px-3 py-2 text-gray-700 focus:outline-none focus:border-blue-500">
                    <option value="">Semua status</option>
                    <option value="scheduled" {{ request('status') == 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                    <option value="ongoing" {{ request('status') == 'ongoing' ? 'selected' : '' }}>Ongoing</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                    <option value="cancelled" {{ request('status') == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <button type="submit" class="w-full sm:w-auto px-5 py-2 text-xs font-semibold text-white bg-slate-900 rounded-lg hover:bg-slate-800 shadow-sm transition-colors">
                Cari
            </button>
            @if(request()->anyFilled(['search', 'status']))
                <a href="{{ route('training-schedules.index') }}" class="px-3 py-2 text-xs text-gray-500 hover:text-gray-700 border border-gray-200 rounded-lg">Reset</a>
            @endif
        </form>
    </div>

    {{-- Table Card --}}
 <div class="bg-white border border-blue-100/70 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[700px] text-left text-xs text-gray-600">
                <thead class="bg-gray-50/70 border-b border-gray-100 text-gray-400 font-semibold uppercase tracking-wider text-[10.5px]">
                    <tr>
                        <th class="py-3.5 px-4">PELATIHAN</th>
                        <th class="py-3.5 px-4">TRAINER</th>
                        <th class="py-3.5 px-4">TANGGAL</th>
                        <th class="py-3.5 px-4">JAM MULAI</th>
                        <th class="py-3.5 px-4">JAM SELESAI</th>
                        <th class="py-3.5 px-4">STATUS</th>
                        <th class="py-3.5 px-4">PESERTA</th>
                        <th class="py-3.5 px-4 text-right">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($schedules as $schedule)
                    <tr class="hover:bg-blue-50/20 transition-colors">
                        <td class="py-3.5 px-4 font-semibold text-gray-900">
                            {{ $schedule->product?->name }}
                            @if($schedule->location)
                                <div class="text-[11px] text-gray-400 font-normal">{{ $schedule->location }}</div>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-gray-800 font-medium">
                            {{ $schedule->trainer?->name ?? '-' }}
                        </td>
                        <td class="py-3.5 px-4 text-gray-900 font-medium whitespace-nowrap">
                            {{ $schedule->training_date->translatedFormat('d M Y') }}
                        </td>
                        <td class="py-3.5 px-4 text-gray-600 whitespace-nowrap">
                            {{ substr($schedule->start_time, 0, 5) }}
                        </td>
                        <td class="py-3.5 px-4 text-gray-600 whitespace-nowrap">
                            {{ substr($schedule->end_time, 0, 5) }}
                        </td>
                        <td class="py-3.5 px-4">
                            @php
                                $scStyles = [
                                    'scheduled' => 'bg-blue-50 text-blue-700 border-blue-200',
                                    'ongoing'   => 'bg-amber-50 text-amber-700 border-amber-200',
                                    'completed' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                    'cancelled' => 'bg-gray-100 text-gray-600 border-gray-200',
                                ];
                            @endphp
 <span class="inline-block px-2 py-0.5 border text-[10px] font-semibold uppercase tracking-wider {{ $scStyles[$schedule->status] ?? 'bg-gray-50 border-gray-200' }}">
                                {{ ucfirst($schedule->status) }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 font-medium">
                            <span class="text-blue-600 font-bold">{{ $schedule->registrations_count }}</span> / {{ $schedule->capacity }}
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <div class="inline-flex items-center gap-1.5">
                                <a href="{{ route('registrations.create') }}?schedule_id={{ $schedule->id }}" class="text-[11px] text-emerald-600 hover:text-emerald-800 font-medium bg-emerald-50 px-2 py-1 rounded border border-emerald-200">
                                    + Daftar
                                </a>
                                @if(auth()->user()->isAdmin())
                                    <a href="{{ route('training-schedules.edit', $schedule) }}" class="text-xs text-blue-600 hover:text-blue-800 font-medium p-1">Edit</a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-12 text-center text-gray-400 text-sm">
                            Belum ada jadwal pelatihan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Footer --}}
        <div class="p-4 border-t border-gray-100 bg-gray-50/40 flex flex-col sm:flex-row items-center justify-between gap-3">
            <div class="flex items-center gap-2 text-xs text-gray-500">
                <span>Tampilkan</span>
 <span class=" border border-gray-200 px-2.5 py-1 text-xs text-gray-700 bg-white">50</span>
            </div>
            @if($schedules->hasPages())
                <div>{{ $schedules->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
