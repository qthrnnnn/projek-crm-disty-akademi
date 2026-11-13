@extends('layouts.app')

@section('title', 'Edit Jadwal Follow-up')

@section('content')
<div class="max-w-2xl mx-auto space-y-4">
    <div class="flex items-center justify-between">
        <h1 class="text-xl font-semibold text-gray-900">Edit Jadwal Follow-up</h1>
        <a href="{{ route('follow-ups.index') }}" class="text-xs text-gray-600 px-3 py-1.5 border border-gray-200 rounded-lg hover:bg-gray-50">Kembali</a>
    </div>

 <form method="POST" action="{{ route('follow-ups.update', $followUp) }}" class="bg-white border border-blue-100 p-5 shadow-sm space-y-4 text-xs">
        @csrf
        @method('PUT')

        <div>
            <label class="block font-semibold text-gray-700 mb-1">Lead Prospek</label>
 <select name="lead_id" required class="w-full border border-gray-200 px-3 py-2 text-gray-800 focus:outline-none focus:border-blue-500">
                @foreach($leads as $l)
                    <option value="{{ $l->id }}" {{ old('lead_id', $followUp->lead_id) == $l->id ? 'selected' : '' }}>
                        {{ $l->name }} ({{ $l->lead_code }})
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label class="block font-semibold text-gray-700 mb-1">Judul Agenda <span class="text-red-500">*</span></label>
            <input type="text" name="title" value="{{ old('title', $followUp->title) }}" required
 class="w-full border border-gray-200 px-3 py-2 text-gray-800 focus:outline-none focus:border-blue-500">
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block font-semibold text-gray-700 mb-1">Waktu Pelaksanaan</label>
                <input type="datetime-local" name="follow_up_at" value="{{ old('follow_up_at', $followUp->follow_up_at->format('Y-m-d\TH:i')) }}" required
 class="w-full border border-gray-200 px-3 py-2 text-gray-800 focus:outline-none focus:border-blue-500">
            </div>
            <div>
                <label class="block font-semibold text-gray-700 mb-1">Metode</label>
 <select name="method" class="w-full border border-gray-200 px-3 py-2 text-gray-700 focus:outline-none focus:border-blue-500">
                    <option value="whatsapp" {{ old('method', $followUp->method) == 'whatsapp' ? 'selected' : '' }}>WhatsApp</option>
                    <option value="call" {{ old('method', $followUp->method) == 'call' ? 'selected' : '' }}>Telepon</option>
                    <option value="email" {{ old('method', $followUp->method) == 'email' ? 'selected' : '' }}>Email</option>
                    <option value="meeting" {{ old('method', $followUp->method) == 'meeting' ? 'selected' : '' }}>Meeting</option>
                </select>
            </div>
        </div>

        <div>
            <label class="block font-semibold text-gray-700 mb-1">Sales PIC</label>
            @if(auth()->user()->isSales())
                <input type="hidden" name="assigned_to" value="{{ $followUp->assigned_to ?: auth()->id() }}">
                <input type="text" readonly value="{{ $followUp->assignedUser?->name ?: auth()->user()->name }} (Sales PIC)"
 class="w-full border border-gray-200 bg-gray-50 px-3 py-2 text-gray-700 font-medium">
            @else
 <select name="assigned_to" class="w-full border border-gray-200 px-3 py-2 text-gray-700 focus:outline-none focus:border-blue-500">
                    <option value="">-- Pilih Sales --</option>
                    @foreach($salesUsers as $s)
                        <option value="{{ $s->id }}" {{ old('assigned_to', $followUp->assigned_to) == $s->id ? 'selected' : '' }}>{{ $s->name }}</option>
                    @endforeach
                </select>
            @endif
        </div>

        <div>
            <label class="block font-semibold text-gray-700 mb-1">Catatan</label>
 <textarea name="description" rows="2" class="w-full border border-gray-200 px-3 py-2 text-gray-800 focus:outline-none focus:border-blue-500">{{ old('description', $followUp->description) }}</textarea>
        </div>

        <div class="flex items-center justify-between pt-2 border-t border-gray-100">
            <button type="button" onclick="if(confirm('Hapus jadwal ini?')) document.getElementById('delete-fu').submit();" class="text-red-600 hover:underline">
                Hapus
            </button>
            <div class="flex items-center gap-2">
                <a href="{{ route('follow-ups.index') }}" class="px-4 py-2 text-gray-500 hover:text-gray-700">Batal</a>
                <button type="submit" class="px-5 py-2 text-white bg-blue-600 rounded-lg hover:bg-blue-700">Simpan Perubahan</button>
            </div>
        </div>
    </form>
    <form id="delete-fu" method="POST" action="{{ route('follow-ups.destroy', $followUp) }}" class="hidden">
        @csrf @method('DELETE')
    </form>
</div>
@endsection
