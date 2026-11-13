@extends('layouts.app')

@section('title', 'Daftarkan Customer ke Jadwal')

@section('content')
<div class="max-w-2xl mx-auto space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Daftarkan Customer ke Jadwal</h1>
            <p class="text-xs text-gray-500 mt-0.5">Pilih customer dan pasangkan ke jadwal pelatihan yang telah dibuka</p>
        </div>
        <a href="{{ route('registrations.index') }}" class="text-xs text-gray-600 px-3 py-1.5 border border-gray-200 rounded-lg hover:bg-gray-50">Kembali</a>
    </div>

 <form method="POST" action="{{ route('registrations.store') }}" class="bg-white border border-blue-100 p-5 shadow-sm space-y-4 text-xs">
        @csrf

        <div>
            <label class="block font-semibold text-gray-700 mb-1">Pilih Customer <span class="text-red-500">*</span></label>
 <select name="customer_id" required class="w-full border border-gray-200 px-3 py-2 text-gray-800 focus:outline-none focus:border-blue-500">
                <option value="">-- Pilih Customer --</option>
                @foreach($customers as $c)
                    <option value="{{ $c->id }}" {{ (old('customer_id') == $c->id || ($selectedCustomer && $selectedCustomer->id == $c->id)) ? 'selected' : '' }}>
                        {{ $c->name }} ({{ $c->customer_code }}) {{ $c->institution ? '— ' . $c->institution->name : '' }}
                    </option>
                @endforeach
            </select>
            @error('customer_id')<p class="text-red-500 mt-1">{{ $message }}</p>@enderror
        </div>

        <div>
            <label class="block font-semibold text-gray-700 mb-1">Pilih Jadwal Pelatihan yang Tersedia <span class="text-red-500">*</span></label>
 <select name="training_schedule_id" id="schedule_select" required class="w-full border border-gray-200 px-3 py-2 text-gray-800 focus:outline-none focus:border-blue-500" onchange="updateAmount(this)">
                <option value="">-- Pilih Jadwal & Pelatihan --</option>
                @foreach($schedules as $s)
                    <option value="{{ $s->id }}" data-price="{{ $s->product?->price ?? 0 }}"
                        {{ (old('training_schedule_id') == $s->id || ($selectedSchedule && $selectedSchedule->id == $s->id)) ? 'selected' : '' }}>
                        {{ $s->product?->name }} | Tgl: {{ $s->training_date->translatedFormat('d M Y') }} ({{ substr($s->start_time, 0, 5) }} - {{ substr($s->end_time, 0, 5) }}) | Trainer: {{ $s->trainer?->name }} | Kuota: {{ $s->capacity }}
                    </option>
                @endforeach
            </select>
            @error('training_schedule_id')<p class="text-red-500 mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block font-semibold text-gray-700 mb-1">Biaya / Nilai Pelatihan (Rp) <span class="text-red-500">*</span></label>
                <input type="number" name="amount" id="amount_input" value="{{ old('amount', $selectedSchedule?->product?->price ?? 0) }}" required min="0"
 class="w-full border border-gray-200 px-3 py-2 text-gray-800 focus:outline-none focus:border-blue-500">
            </div>
            <div>
                <label class="block font-semibold text-gray-700 mb-1">Status Pendaftaran</label>
 <select name="status" class="w-full border border-gray-200 px-3 py-2 text-gray-700 focus:outline-none focus:border-blue-500">
                    <option value="registered">Registered</option>
                    <option value="confirmed">Confirmed</option>
                    <option value="attended">Attended</option>
                    <option value="cancelled">Cancelled</option>
                </select>
            </div>
        </div>

        <div>
            <label class="block font-semibold text-gray-700 mb-1">Catatan</label>
 <textarea name="notes" rows="2" class="w-full border border-gray-200 px-3 py-2 text-gray-800 focus:outline-none focus:border-blue-500" placeholder="Keterangan pendaftaran...">{{ old('notes') }}</textarea>
        </div>

        <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-100">
            <a href="{{ route('registrations.index') }}" class="px-4 py-2 text-gray-500 hover:text-gray-700">Batal</a>
            <button type="submit" class="px-5 py-2 text-white bg-slate-900 rounded-lg hover:bg-slate-800 shadow-sm font-semibold">
                Daftarkan Sekarang
            </button>
        </div>
    </form>
</div>

<script>
function updateAmount(select) {
    const selectedOption = select.options[select.selectedIndex];
    const price = selectedOption.getAttribute('data-price');
    if (price) {
        document.getElementById('amount_input').value = price;
    }
}
</script>
@endsection
