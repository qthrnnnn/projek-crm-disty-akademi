@extends('layouts.app')

@section('title', 'Daftar Trainer')

@section('content')
<div class="space-y-4">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Daftar Trainer</h1>
            <p class="text-xs text-gray-500 mt-0.5">Trainer digunakan saat membuat jadwal pelatihan.</p>
        </div>
        <div>
            <a href="{{ route('trainers.create') }}" class="px-4 py-2 text-xs font-semibold text-white bg-gray-900 rounded-full hover:bg-black shadow-sm transition-colors">
                + Tambah Trainer
            </a>
        </div>
    </div>

    {{-- Filter Card --}}
 <div class="bg-white border border-blue-100/70 p-3.5 shadow-sm">
        <form method="GET" action="{{ route('trainers.index') }}" class="flex flex-col sm:flex-row items-center gap-2.5">
            <div class="relative flex-1 w-full">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Cari trainer, email, atau telepon..."
 class="w-full text-xs border border-gray-200 px-3 py-2 text-gray-800 placeholder-gray-400 focus:outline-none focus:border-blue-500">
            </div>
            <button type="submit" class="w-full sm:w-auto px-5 py-2 text-xs font-semibold text-white bg-slate-900 rounded-lg hover:bg-slate-800 shadow-sm transition-colors">
                Cari
            </button>
            @if(request()->filled('search'))
                <a href="{{ route('trainers.index') }}" class="px-3 py-2 text-xs text-gray-500 hover:text-gray-700 border border-gray-200 rounded-lg">Reset</a>
            @endif
        </form>
    </div>

    {{-- Table Card --}}
 <div class="bg-white border border-blue-100/70 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[550px] text-left text-xs text-gray-600">
                <thead class="bg-gray-50/70 border-b border-gray-100 text-gray-400 font-semibold uppercase tracking-wider text-[10.5px]">
                    <tr>
                        <th class="py-3.5 px-4">NAMA ▲</th>
                        <th class="py-3.5 px-4">EMAIL</th>
                        <th class="py-3.5 px-4">TELEPON</th>
                        <th class="py-3.5 px-4">STATUS</th>
                        <th class="py-3.5 px-4 text-right">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($trainers as $trainer)
                    <tr class="hover:bg-blue-50/20 transition-colors">
                        <td class="py-3.5 px-4 font-semibold text-gray-900">
                            {{ $trainer->name }}
                        </td>
                        <td class="py-3.5 px-4 text-gray-600">
                            {{ $trainer->email ?? '-' }}
                        </td>
                        <td class="py-3.5 px-4 text-gray-600">
                            {{ $trainer->phone ?? '-' }}
                        </td>
                        <td class="py-3.5 px-4">
 <span class="inline-block px-2 py-0.5 border text-[10px] font-semibold uppercase tracking-wider {{ $trainer->is_active ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-gray-100 text-gray-600 border-gray-200' }}">
                                {{ $trainer->is_active ? 'Aktif' : 'Non-aktif' }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-right">
                            <a href="{{ route('trainers.edit', $trainer) }}" class="text-xs text-blue-600 hover:text-blue-800 font-medium p-1">Edit</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-12 text-center text-gray-400 text-sm">
                            Belum ada trainer.
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
            @if($trainers->hasPages())
                <div>{{ $trainers->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
