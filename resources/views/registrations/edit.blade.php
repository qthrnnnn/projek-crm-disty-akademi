@extends('layouts.app')

@section('title', 'Edit Pendaftaran: ' . $registration->registration_number)

@section('content')
<div class="max-w-2xl mx-auto space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Edit Pendaftaran Peserta</h1>
            <p class="text-xs text-gray-500 mt-0.5">No. Reg: {{ $registration->registration_number }}</p>
        </div>
        <a href="{{ route('registrations.index') }}" class="text-xs text-gray-600 px-3 py-1.5 border border-gray-200 rounded-lg hover:bg-gray-50">Kembali</a>
    </div>

 <form method="POST" action="{{ route('registrations.update', $registration) }}" class="bg-white border border-blue-100 p-5 shadow-sm space-y-4 text-xs">
        @csrf
        @method('PUT')

        <div>
            <label class="block font-semibold text-gray-700 mb-1">Customer</label>
 <select name="customer_id" required class="w-full border border-gray-200 px-3 py-2 text-gray-800 focus:outline-none focus:border-blue-500">
                @foreach($customers as $c)
                    <option value="{{ $c->id }}" {{ old('customer_id', $registration->customer_id) == $c->id ? 'selected' : '' }}>
                        {{ $c->name }} ({{ $c->customer_code }})
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block font-semibold text-gray-700 mb-1">Jadwal Pelatihan</label>
 <select name="training_schedule_id" required class="w-full border border-gray-200 px-3 py-2 text-gray-800 focus:outline-none focus:border-blue-500">
                @foreach($schedules as $s)
                    <option value="{{ $s->id }}" {{ old('training_schedule_id', $registration->training_schedule_id) == $s->id ? 'selected' : '' }}>
                        {{ $s->product?->name }} | Tgl: {{ $s->training_date->translatedFormat('d M Y') }} | Trainer: {{ $s->trainer?->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block font-semibold text-gray-700 mb-1">Amount (Rp)</label>
                <input type="number" name="amount" value="{{ old('amount', $registration->amount) }}" required min="0"
 class="w-full border border-gray-200 px-3 py-2 text-gray-800 focus:outline-none focus:border-blue-500">
            </div>
            <div>
                <label class="block font-semibold text-gray-700 mb-1">Status Pendaftaran</label>
 <select name="status" class="w-full border border-gray-200 px-3 py-2 text-gray-700 focus:outline-none focus:border-blue-500">
                    <option value="registered" {{ old('status', $registration->status) == 'registered' ? 'selected' : '' }}>Registered</option>
                    <option value="confirmed" {{ old('status', $registration->status) == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                    <option value="attended" {{ old('status', $registration->status) == 'attended' ? 'selected' : '' }}>Attended</option>
                    <option value="cancelled" {{ old('status', $registration->status) == 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
        </div>

        <div>
            <label class="block font-semibold text-gray-700 mb-1">Catatan</label>
 <textarea name="notes" rows="2" class="w-full border border-gray-200 px-3 py-2 text-gray-800 focus:outline-none focus:border-blue-500">{{ old('notes', $registration->notes) }}</textarea>
        </div>

        <div class="flex items-center justify-between pt-2 border-t border-gray-100">
            <button type="button" onclick="if(confirm('Hapus pendaftaran ini?')) document.getElementById('del-reg').submit();" class="text-red-600 hover:underline">
                Hapus
            </button>
            <div class="flex items-center gap-2">
                <a href="{{ route('registrations.index') }}" class="px-4 py-2 text-gray-500 hover:text-gray-700">Batal</a>
                <button type="submit" class="px-5 py-2 text-white bg-slate-900 rounded-lg hover:bg-slate-800 shadow-sm font-semibold">
                    Simpan Perubahan
                </button>
            </div>
        </div>
    </form>

    <form id="del-reg" method="POST" action="{{ route('registrations.destroy', $registration) }}" class="hidden">
        @csrf @method('DELETE')
    </form>
</div>
@endsection
