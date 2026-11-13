@extends('layouts.app')

@section('title', 'Daftar Instansi')

@section('content')
<div class="space-y-4">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Daftar Instansi</h1>
            <p class="text-xs text-gray-500 mt-0.5">Kelola data instansi, perusahaan, kampus, atau organisasi mitra.</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('institutions.create') }}" class="px-4 py-2 text-xs font-semibold text-white bg-gray-900 rounded-full hover:bg-black shadow-sm transition-colors">
                + Tambah Instansi
            </a>
        </div>
    </div>

    {{-- Filter Card --}}
 <div class="bg-white border border-blue-100/70 p-3.5 shadow-sm">
        <form method="GET" action="{{ route('institutions.index') }}" class="flex flex-col sm:flex-row items-center gap-2.5">
            <div class="relative flex-1 w-full">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Cari instansi, kota, atau jenis instansi..."
 class="w-full text-xs border border-gray-200 px-3 py-2 text-gray-800 placeholder-gray-400 focus:outline-none focus:border-blue-500">
            </div>
            <button type="submit" class="w-full sm:w-auto px-5 py-2 text-xs font-semibold text-white bg-slate-900 rounded-lg hover:bg-slate-800 shadow-sm transition-colors">
                Cari
            </button>
            @if(request()->filled('search'))
                <a href="{{ route('institutions.index') }}" class="px-3 py-2 text-xs text-gray-500 hover:text-gray-700 border border-gray-200 rounded-lg">Reset</a>
            @endif
        </form>
    </div>

    {{-- Table Card --}}
 <div class="bg-white border border-blue-100/70 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[550px] text-left text-xs text-gray-600">
                <thead class="bg-gray-50/70 border-b border-gray-100 text-gray-400 font-semibold uppercase tracking-wider text-[10.5px]">
                    <tr>
                        <th class="py-3.5 px-4">INSTANSI</th>
                        <th class="py-3.5 px-4">JENIS</th>
                        <th class="py-3.5 px-4">KOTA</th>
                        <th class="py-3.5 px-4 text-center">CUSTOMER</th>
                        <th class="py-3.5 px-4 text-right">AKSI</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($institutions as $inst)
                    <tr class="hover:bg-blue-50/20 transition-colors">
                        <td class="py-3.5 px-4">
                            <div class="font-semibold text-gray-900">{{ $inst->name }}</div>
                            @if($inst->email || $inst->phone)
                                <div class="text-[11px] text-gray-400">{{ $inst->email ?? '' }} {{ $inst->email && $inst->phone ? '·' : '' }} {{ $inst->phone ?? '' }}</div>
                            @endif
                        </td>
                        <td class="py-3.5 px-4">
 <span class="inline-flex items-center px-2 py-0.5 text-[11px] font-medium bg-gray-100 text-gray-700">
                                {{ $inst->type ?? 'Umum' }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-gray-700 font-medium">
                            {{ $inst->city ?? '-' }}
                            @if($inst->province)
                                <span class="text-gray-400 text-[11px]">, {{ $inst->province }}</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-center">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold bg-blue-50 text-blue-700">
                                {{ $inst->customers_count ?? 0 }} customer
                            </span>
                        </td>
                        <td class="py-3.5 px-4 text-right whitespace-nowrap">
                            <div class="inline-flex items-center gap-1">
                                <a href="{{ route('institutions.edit', $inst) }}" class="p-1.5 text-gray-400 hover:text-blue-600 rounded hover:bg-blue-50" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                @if(auth()->user()->isAdmin())
                                <form method="POST" action="{{ route('institutions.destroy', $inst) }}" class="inline" onsubmit="return confirm('Hapus instansi ini?')">
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
                        <td colspan="5" class="py-8 text-center text-gray-400 text-xs">
                            Belum ada instansi yang terdaftar.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($institutions->hasPages())
            <div class="px-4 py-3 border-t border-gray-100 bg-gray-50/50">
                {{ $institutions->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
