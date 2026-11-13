@extends('layouts.app')

@section('title', 'Detail Batch Import')

@section('content')
<div class="space-y-4">
 <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 bg-white border border-blue-100 p-4 shadow-sm">
        <div>
            <h1 class="text-xl font-bold text-gray-900">{{ $importBatch->file_name }}</h1>
            <p class="text-xs text-gray-400 mt-0.5">Diupload oleh {{ $importBatch->user?->name ?? 'User' }} pada {{ $importBatch->created_at->translatedFormat('d M Y, H:i') }}</p>
        </div>
        <div class="flex items-center gap-3 text-xs">
 <span class="px-2.5 py-1 bg-blue-50 text-blue-700 font-medium">Total: {{ $importBatch->total_rows }}</span>
 <span class="px-2.5 py-1 bg-emerald-50 text-emerald-700 font-medium">Berhasil: {{ $importBatch->success_rows }}</span>
 <span class="px-2.5 py-1 bg-red-50 text-red-700 font-medium">Gagal: {{ $importBatch->failed_rows }}</span>
            <a href="{{ route('lead-imports.index') }}" class="px-3 py-1 border border-gray-200 rounded-lg text-gray-600 hover:bg-gray-50">Kembali</a>
        </div>
    </div>

    {{-- Rows list --}}
 <div class="bg-white border border-blue-100 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-gray-600">
                <thead class="bg-gray-50/70 border-b border-gray-100 text-gray-500 font-medium uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="py-3 px-4">Baris #</th>
                        <th class="py-3 px-4">Data Hasil Baca</th>
                        <th class="py-3 px-4">Status Baris</th>
                        <th class="py-3 px-4">Lead Terkait</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($rows as $row)
                    <tr class="hover:bg-blue-50/30 transition-colors">
                        <td class="py-3 px-4 font-mono font-medium text-gray-500">{{ $row->row_number }}</td>
                        <td class="py-3 px-4">
                            <div class="font-medium text-gray-900">{{ $row->data['name'] ?? ($row->data['Name'] ?? '-') }}</div>
                            <div class="text-[11px] text-gray-400">
                                {{ $row->data['email'] ?? ($row->data['Email'] ?? '') }}
                                {{ isset($row->data['phone']) ? ' • ' . $row->data['phone'] : '' }}
                            </div>
                        </td>
                        <td class="py-3 px-4">
 <span class="px-2 py-0.5 text-[10px] font-medium border
                                {{ $row->status === 'imported' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : ($row->status === 'invalid' ? 'bg-red-50 text-red-700 border-red-200' : 'bg-gray-50 text-gray-600 border-gray-200') }}">
                                {{ ucfirst($row->status) }}
                            </span>
                        </td>
                        <td class="py-3 px-4">
                            @if($row->lead)
                                <a href="{{ route('leads.show', $row->lead) }}" class="text-blue-600 hover:underline font-mono">
                                    {{ $row->lead->lead_code }}
                                </a>
                            @else
                                <span class="text-gray-400">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="py-8 text-center text-gray-400">Tidak ada rincian baris data</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($rows->hasPages())
        <div class="p-3 border-t border-gray-100">{{ $rows->links() }}</div>
        @endif
    </div>
</div>
@endsection
