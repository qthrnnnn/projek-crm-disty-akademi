@extends('layouts.app')

@section('title', 'Tambah Trainer')

@section('content')
<div class="max-w-xl mx-auto space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Tambah Trainer Baru</h1>
            <p class="text-xs text-gray-500 mt-0.5">Input instruktur untuk pengampu jadwal kelas akademi</p>
        </div>
        <a href="{{ route('trainers.index') }}" class="text-xs text-gray-600 px-3 py-1.5 border border-gray-200 rounded-lg hover:bg-gray-50">Kembali</a>
    </div>

 <form method="POST" action="{{ route('trainers.store') }}" class="bg-white border border-blue-100 p-5 shadow-sm space-y-4 text-xs">
        @csrf

        <div>
            <label class="block font-semibold text-gray-700 mb-1">Nama Lengkap Trainer <span class="text-red-500">*</span></label>
            <input type="text" name="name" value="{{ old('name') }}" required placeholder="e.g. Ir. Sandika Galih, M.Kom"
 class="w-full border border-gray-200 px-3 py-2 text-gray-800 focus:outline-none focus:border-blue-500">
            @error('name')<p class="text-red-500 mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block font-semibold text-gray-700 mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email') }}" placeholder="trainer@distyakademi.com"
 class="w-full border border-gray-200 px-3 py-2 text-gray-800 focus:outline-none focus:border-blue-500">
            </div>
            <div>
                <label class="block font-semibold text-gray-700 mb-1">No. Telepon / WhatsApp</label>
                <input type="text" name="phone" value="{{ old('phone') }}" placeholder="08xxxxxxxxxx"
 class="w-full border border-gray-200 px-3 py-2 text-gray-800 focus:outline-none focus:border-blue-500">
            </div>
        </div>

        <div>
            <label class="block font-semibold text-gray-700 mb-1">Keahlian & Catatan</label>
 <textarea name="notes" rows="3" class="w-full border border-gray-200 px-3 py-2 text-gray-800 focus:outline-none focus:border-blue-500" placeholder="Spesialisasi materi, portofolio, atau bio singkat...">{{ old('notes') }}</textarea>
        </div>

        <div class="flex items-center gap-2 pt-1">
 <input type="checkbox" name="is_active" id="is_active" value="1" checked class=" border-gray-300 text-blue-600 focus:ring-blue-500">
            <label for="is_active" class="font-medium text-gray-700">Status Aktif (Tersedia mengajar)</label>
        </div>

        <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-100">
            <a href="{{ route('trainers.index') }}" class="px-4 py-2 text-gray-500 hover:text-gray-700">Batal</a>
            <button type="submit" class="px-5 py-2 text-white bg-slate-900 rounded-lg hover:bg-slate-800 shadow-sm font-semibold">
                Simpan Trainer
            </button>
        </div>
    </form>
</div>
@endsection
