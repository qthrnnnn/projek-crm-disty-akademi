@extends('layouts.app')

@section('title', 'Riwayat Import Leads')

@section('content')
<div class="space-y-4">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Riwayat Import Lead (CSV / Excel)</h1>
            <p class="text-xs text-gray-500 mt-0.5">Daftar batch upload prospek masal dari CSV atau Excel</p>
        </div>
        <a href="{{ route('lead-imports.create') }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 transition-colors shadow-sm">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/></svg>
            Upload File Baru
        </a>
    </div>

 <div class="bg-white border border-blue-100 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-gray-600">
                <thead class="bg-gray-50/70 border-b border-gray-100 text-gray-500 font-medium uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="py-3 px-4">Waktu & File</th>
                        <th class="py-3 px-4">Pengunggah</th>
                        <th class="py-3 px-4">Total Baris</th>
                        <th class="py-3 px-4">Berhasil</th>
                        <th class="py-3 px-4">Gagal</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($batches as $batch)
                    <tr class="hover:bg-blue-50/30 transition-colors">
                        <td class="py-3 px-4">
                            <div class="font-semibold text-gray-900">{{ $batch->file_name }}</div>
                            <div class="text-[11px] text-gray-400">{{ $batch->created_at->translatedFormat('d M Y, H:i') }}</div>
                        </td>
                        <td class="py-3 px-4">{{ $batch->user?->name ?? '-' }}</td>
                        <td class="py-3 px-4 font-medium">{{ $batch->total_rows }}</td>
                        <td class="py-3 px-4 text-emerald-600 font-bold">{{ $batch->success_rows }}</td>
                        <td class="py-3 px-4 text-red-600 font-medium">{{ $batch->failed_rows }}</td>
                        <td class="py-3 px-4">
 <span class="px-2 py-0.5 text-[10px] font-medium border bg-blue-50 text-blue-700 border-blue-200">
                                {{ ucfirst($batch->status) }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-right">
                            <a href="{{ route('lead-imports.show', $batch) }}" class="text-xs text-blue-600 hover:underline font-medium">
                                Detail Baris
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-gray-400">Belum ada riwayat import data</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($batches->hasPages())
        <div class="p-3 border-t border-gray-100">{{ $batches->links() }}</div>
        @endif
    </div>
</div>
@endsection
