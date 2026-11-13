@extends('layouts.app')

@section('title', 'Edit Jadwal Pelatihan')

@section('content')
<div class="max-w-2xl mx-auto space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Edit Jadwal Pelatihan</h1>
            <p class="text-xs text-gray-500 mt-0.5">Perbarui tanggal, instruktur trainer, atau kapasitas kuota</p>
        </div>
        <a href="{{ route('training-schedules.index') }}" class="text-xs text-gray-600 px-3 py-1.5 border border-gray-200 rounded-lg hover:bg-gray-50">Kembali</a>
    </div>

 <form method="POST" action="{{ route('training-schedules.update', $trainingSchedule) }}" class="bg-white border border-blue-100 p-5 shadow-sm space-y-4 text-xs">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block font-semibold text-gray-700 mb-1">Program Pelatihan <span class="text-red-500">*</span></label>
 <select name="product_id" required class="w-full border border-gray-200 px-3 py-2 text-gray-800 focus:outline-none focus:border-blue-500">
                    @foreach($products as $p)
                        <option value="{{ $p->id }}" {{ old('product_id', $trainingSchedule->product_id) == $p->id ? 'selected' : '' }}>
                            {{ $p->name }} (Rp {{ number_format($p->price, 0, ',', '.') }})
                        </option>
                    @endforeach
                </select>
                @error('product_id')<p class="text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block font-semibold text-gray-700 mb-1">Trainer Pengampu <span class="text-red-500">*</span></label>
 <select name="trainer_id" required class="w-full border border-gray-200 px-3 py-2 text-gray-800 focus:outline-none focus:border-blue-500">
                    @foreach($trainers as $t)
                        <option value="{{ $t->id }}" {{ old('trainer_id', $trainingSchedule->trainer_id) == $t->id ? 'selected' : '' }}>
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
                <input type="date" name="training_date" value="{{ old('training_date', $trainingSchedule->training_date->format('Y-m-d')) }}" required
 class="w-full border border-gray-200 px-3 py-2 text-gray-800 focus:outline-none focus:border-blue-500">
            </div>
            <div>
                <label class="block font-semibold text-gray-700 mb-1">Jam Mulai <span class="text-red-500">*</span></label>
                <input type="time" name="start_time" value="{{ old('start_time', substr($trainingSchedule->start_time, 0, 5)) }}" required
 class="w-full border border-gray-200 px-3 py-2 text-gray-800 focus:outline-none focus:border-blue-500">
            </div>
            <div>
                <label class="block font-semibold text-gray-700 mb-1">Jam Selesai <span class="text-red-500">*</span></label>
                <input type="time" name="end_time" value="{{ old('end_time', substr($trainingSchedule->end_time, 0, 5)) }}" required
 class="w-full border border-gray-200 px-3 py-2 text-gray-800 focus:outline-none focus:border-blue-500">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div>
                <label class="block font-semibold text-gray-700 mb-1">Kapasitas Kuota Peserta <span class="text-red-500">*</span></label>
                <input type="number" name="capacity" value="{{ old('capacity', $trainingSchedule->capacity) }}" required min="1"
 class="w-full border border-gray-200 px-3 py-2 text-gray-800 focus:outline-none focus:border-blue-500">
            </div>
            <div>
                <label class="block font-semibold text-gray-700 mb-1">Status Jadwal</label>
 <select name="status" class="w-full border border-gray-200 px-3 py-2 text-gray-700 focus:outline-none focus:border-blue-500">
                    <option value="scheduled" {{ old('status', $trainingSchedule->status) == 'scheduled' ? 'selected' : '' }}>Scheduled (Terbuka)</option>
                    <option value="ongoing" {{ old('status', $trainingSchedule->status) == 'ongoing' ? 'selected' : '' }}>Ongoing (Sedang Jalan)</option>
                    <option value="completed" {{ old('status', $trainingSchedule->status) == 'completed' ? 'selected' : '' }}>Completed (Selesai)</option>
                    <option value="cancelled" {{ old('status', $trainingSchedule->status) == 'cancelled' ? 'selected' : '' }}>Cancelled (Batal)</option>
                </select>
            </div>
            <div>
                <label class="block font-semibold text-gray-700 mb-1">Lokasi / Link Pelatihan</label>
                <input type="text" name="location" value="{{ old('location', $trainingSchedule->location) }}"
 class="w-full border border-gray-200 px-3 py-2 text-gray-800 focus:outline-none focus:border-blue-500">
            </div>
        </div>

        <div>
            <label class="block font-semibold text-gray-700 mb-1">Catatan Tambahan</label>
 <textarea name="notes" rows="2" class="w-full border border-gray-200 px-3 py-2 text-gray-800 focus:outline-none focus:border-blue-500">{{ old('notes', $trainingSchedule->notes) }}</textarea>
        </div>

        <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-100">
            <a href="{{ route('training-schedules.index') }}" class="px-4 py-2 text-gray-500 hover:text-gray-700">Batal</a>
            <button type="submit" class="px-5 py-2 text-white bg-slate-900 rounded-lg hover:bg-slate-800 shadow-sm font-semibold">
                Perbarui Jadwal
            </button>
        </div>
    </form>
</div>
@endsection
