@extends('layouts.app')

@section('title', 'Tambah Jadwal Follow-up')

@section('content')
<div class="max-w-2xl mx-auto space-y-4">
    <div class="flex items-center justify-between">
        <h1 class="text-xl font-semibold text-gray-900">Buat Jadwal Follow-up</h1>
        <a href="{{ route('follow-ups.index') }}" class="text-xs text-gray-600 px-3 py-1.5 border border-gray-200 rounded-lg hover:bg-gray-50">Kembali</a>
    </div>

 <form method="POST" action="{{ route('follow-ups.store') }}" class="bg-white border border-blue-100 p-5 shadow-sm space-y-4 text-xs">
        @csrf

        <div>
            <label class="block font-semibold text-gray-700 mb-1">Pilih Lead Prospek <span class="text-red-500">*</span></label>
 <select name="lead_id" required class="w-full border border-gray-200 px-3 py-2 text-gray-800 focus:outline-none focus:border-blue-500">
                <option value="">-- Pilih Prospek --</option>
                @foreach($leads as $l)
                    <option value="{{ $l->id }}" {{ (old('lead_id') == $l->id || ($selectedLead && $selectedLead->id == $l->id)) ? 'selected' : '' }}>
                        {{ $l->name }} ({{ $l->lead_code }}) - {{ $l->phone ?? $l->email }}
                    </option>
                @endforeach
            </select>
            @error('lead_id')<p class="text-red-500 mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block font-semibold text-gray-700 mb-1">Judul / Agenda Follow-up <span class="text-red-500">*</span></label>
            <input type="text" name="title" value="{{ old('title') }}" required placeholder="Contoh: Konfirmasi bukti transfer pembayaran"
 class="w-full border border-gray-200 px-3 py-2 text-gray-800 focus:outline-none focus:border-blue-500">
            @error('title')<p class="text-red-500 mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block font-semibold text-gray-700 mb-1">Waktu Pelaksanaan <span class="text-red-500">*</span></label>
                <input type="datetime-local" name="follow_up_at" value="{{ old('follow_up_at', now()->addDay()->format('Y-m-d\T10:00')) }}" required
 class="w-full border border-gray-200 px-3 py-2 text-gray-800 focus:outline-none focus:border-blue-500">
            </div>
            <div>
                <label class="block font-semibold text-gray-700 mb-1">Metode Kontak</label>
 <select name="method" class="w-full border border-gray-200 px-3 py-2 text-gray-700 focus:outline-none focus:border-blue-500">
                    <option value="whatsapp">WhatsApp</option>
                    <option value="call">Telepon Langsung</option>
                    <option value="email">Email</option>
                    <option value="meeting">Meeting / Offline</option>
                </select>
            </div>
        </div>

        <div>
            <label class="block font-semibold text-gray-700 mb-1">Sales PIC</label>
            @if(auth()->user()->isSales())
                <input type="hidden" name="assigned_to" value="{{ auth()->id() }}">
                <input type="text" readonly value="{{ auth()->user()->name }} (Sales PIC)"
 class="w-full border border-gray-200 bg-gray-50 px-3 py-2 text-gray-700 font-medium">
            @else
 <select name="assigned_to" class="w-full border border-gray-200 px-3 py-2 text-gray-700 focus:outline-none focus:border-blue-500">
                    <option value="">-- Pilih Sales PIC --</option>
                    @foreach($salesUsers as $s)
                        <option value="{{ $s->id }}" {{ old('assigned_to') == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                    @endforeach
                </select>
            @endif
        </div>

        <div>
            <label class="block font-semibold text-gray-700 mb-1">Catatan Persiapan</label>
 <textarea name="description" rows="2" class="w-full border border-gray-200 px-3 py-2 text-gray-800 focus:outline-none focus:border-blue-500" placeholder="Poin apa yang ingin dibahas...">{{ old('description') }}</textarea>
        </div>

        <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-100">
            <a href="{{ route('follow-ups.index') }}" class="px-4 py-2 text-gray-500 hover:text-gray-700">Batal</a>
            <button type="submit" class="px-5 py-2 text-white bg-blue-600 rounded-lg hover:bg-blue-700 shadow-sm">Simpan Jadwal</button>
        </div>
    </form>
</div>
@endsection
