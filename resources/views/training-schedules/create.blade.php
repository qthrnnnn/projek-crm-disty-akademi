@extends('layouts.app')

@section('title', 'Buat Jadwal Pelatihan')

@section('content')
<div class="max-w-2xl mx-auto space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Buat Jadwal Pelatihan Baru</h1>
            <p class="text-xs text-gray-500 mt-0.5">Tentukan materi program, instruktur/trainer, kuota peserta, dan jam</p>
        </div>
        <a href="{{ route('training-schedules.index') }}" class="text-xs text-gray-600 px-3 py-1.5 border border-gray-200 rounded-lg hover:bg-gray-50">Kembali</a>
    </div>

 <form method="POST" action="{{ route('training-schedules.store') }}" class="bg-white border border-blue-100 p-5 shadow-sm space-y-4 text-xs">
        @csrf

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block font-semibold text-gray-700 mb-1">Program Pelatihan <span class="text-red-500">*</span></label>
 <select name="product_id" required class="w-full border border-gray-200 px-3 py-2 text-gray-800 focus:outline-none focus:border-blue-500">
                    <option value="">-- Pilih Program --</option>
                    @foreach($products as $p)
                        <option value="{{ $p->id }}" {{ old('product_id') == $p->id ? 'selected' : '' }}>
                            {{ $p->name }} (Rp {{ number_format($p->price, 0, ',', '.') }})
                        </option>
                    @endforeach
                </select>
                @error('product_id')<p class="text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block font-semibold text-gray-700 mb-1">Trainer Pengampu <span class="text-red-500">*</span></label>
 <select name="trainer_id" required class="w-full border border-gray-200 px-3 py-2 text-gray-800 focus:outline-none focus:border-blue-500">
                    <option value="">-- Pilih Trainer --</option>
                    @foreach($trainers as $t)
                        <option value="{{ $t->id }}" {{ old('trainer_id') == $t->id ? 'selected' : '' }}>
                            {{ $t->name }}
                        </option>
                    @endforeach
                </select>
                @error('trainer_id')<p class="text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="block font-semibold text-gray-700 mb-1">Tanggal Pelatihan <span class="text-red-500">*</span></label>
                <input type="date" name="training_date" value="{{ old('training_date', now()->addDays(3)->format('Y-m-d')) }}" required
 class="w-full border border-gray-200 px-3 py-2 text-gray-800 focus:outline-none focus:border-blue-500">
            </div>
            <div>
                <label class="block font-semibold text-gray-700 mb-1">Jam Mulai <span class="text-red-500">*</span></label>
                <input type="time" name="start_time" value="{{ old('start_time', '09:00') }}" required
 class="w-full border border-gray-200 px-3 py-2 text-gray-800 focus:outline-none focus:border-blue-500">
            </div>
            <div>
                <label class="block font-semibold text-gray-700 mb-1">Jam Selesai <span class="text-red-500">*</span></label>
                <input type="time" name="end_time" value="{{ old('end_time', '16:00') }}" required
 class="w-full border border-gray-200 px-3 py-2 text-gray-800 focus:outline-none focus:border-blue-500">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="block font-semibold text-gray-700 mb-1">Kapasitas Kuota Peserta <span class="text-red-500">*</span></label>
                <input type="number" name="capacity" value="{{ old('capacity', 25) }}" required min="1"
 class="w-full border border-gray-200 px-3 py-2 text-gray-800 focus:outline-none focus:border-blue-500">
            </div>
            <div>
                <label class="block font-semibold text-gray-700 mb-1">Status Jadwal</label>
 <select name="status" class="w-full border border-gray-200 px-3 py-2 text-gray-700 focus:outline-none focus:border-blue-500">
                    <option value="scheduled">Scheduled (Terbuka)</option>
                    <option value="ongoing">Ongoing (Sedang Jalan)</option>
                    <option value="completed">Completed (Selesai)</option>
                    <option value="cancelled">Cancelled (Batal)</option>
                </select>
            </div>
            <div>
                <label class="block font-semibold text-gray-700 mb-1">Lokasi / Link Pelatihan</label>
                <input type="text" name="location" value="{{ old('location', 'Disty Academy Hall / Zoom Meeting') }}"
 class="w-full border border-gray-200 px-3 py-2 text-gray-800 focus:outline-none focus:border-blue-500">
            </div>
        </div>

        <div>
            <label class="block font-semibold text-gray-700 mb-1">Catatan Tambahan</label>
 <textarea name="notes" rows="2" class="w-full border border-gray-200 px-3 py-2 text-gray-800 focus:outline-none focus:border-blue-500" placeholder="Kebutuhan software, persiapan kelas, dll...">{{ old('notes') }}</textarea>
        </div>

        <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-100">
            <a href="{{ route('training-schedules.index') }}" class="px-4 py-2 text-gray-500 hover:text-gray-700">Batal</a>
            <button type="submit" class="px-5 py-2 text-white bg-slate-900 rounded-lg hover:bg-slate-800 shadow-sm font-semibold">
                Simpan Jadwal
            </button>
        </div>
    </form>
</div>
@endsection
