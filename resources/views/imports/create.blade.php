@extends('layouts.app')

@section('title', 'Import Data Leads')

@section('content')
<div class="max-w-2xl mx-auto space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Import Leads Masal</h1>
            <p class="text-xs text-gray-500 mt-0.5">Upload file CSV prospek untuk di-assign ke sales</p>
        </div>
        <a href="{{ route('lead-imports.index') }}" class="text-xs text-gray-600 px-3 py-1.5 border border-gray-200 rounded-lg hover:bg-gray-50">Kembali</a>
    </div>

    {{-- Format Guide --}}
 <div class="bg-orange-50 border border-orange-200 p-4 text-xs text-orange-900 space-y-2">
        <div class="flex items-center gap-2 font-semibold text-orange-800">
            <svg class="w-4 h-4 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            Petunjuk Format Kolom File CSV
        </div>
        <p class="text-orange-700 leading-relaxed">
            Pastikan baris pertama file CSV memiliki header kolom berikut (urutan bebas):<br>
 <code class="bg-white/80 px-2 py-0.5 border border-orange-200 text-orange-900 font-mono text-[11px]">name, email, phone, whatsapp, company, city</code>
        </p>
    </div>

 <form method="POST" action="{{ route('lead-imports.store') }}" enctype="multipart/form-data" class="bg-white border border-orange-100 p-5 shadow-sm space-y-4 text-xs">
        @csrf

        <div>
            <label class="block font-semibold text-gray-700 mb-1">Pilih File CSV (.csv / .txt) <span class="text-red-500">*</span></label>
            <input type="file" name="file" accept=".csv,.txt" required
 class="w-full text-xs text-gray-500 file:mr-3 file:py-2 file:px-3 file: file:border-0 file:text-xs file:font-semibold file:bg-orange-50 file:text-orange-700 hover:file:bg-orange-100">
            @error('file')<p class="text-red-500 mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block font-semibold text-gray-700 mb-1">Label Sumber Lead</label>
 <select name="lead_source_id" class="w-full border border-gray-200 px-3 py-2 text-gray-700 focus:outline-none focus:border-orange-500">
                    <option value="">-- Pilih Sumber (Default: Import CSV) --</option>
                    @foreach($sources as $src)
                        <option value="{{ $src->id }}" {{ $src->name === 'import_csv' ? 'selected' : '' }}>{{ $src->label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block font-semibold text-gray-700 mb-1">Auto-Assign ke Sales PIC</label>
 <select name="assigned_to" class="w-full border border-gray-200 px-3 py-2 text-gray-700 focus:outline-none focus:border-orange-500">
                    <option value="">-- Belum Ditugaskan / Bagi Nanti --</option>
                    @foreach($salesUsers as $s)
                        <option value="{{ $s->id }}">{{ $s->name }} ({{ ucfirst($s->role) }})</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-100">
            <a href="{{ route('lead-imports.index') }}" class="px-4 py-2 text-gray-500 hover:text-gray-700">Batal</a>
            <button type="submit" class="px-5 py-2 text-white bg-orange-500 rounded-full hover:bg-orange-600 shadow-sm">
                Mulai Import & Validasi
            </button>
        </div>
    </form>
</div>
@endsection
