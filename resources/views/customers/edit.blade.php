@extends('layouts.app')

@section('title', 'Edit Customer')

@section('content')
<div class="max-w-2xl mx-auto space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Edit Customer</h1>
            <p class="text-xs text-gray-500 mt-0.5">Perbarui informasi profil customer: {{ $customer->customer_code }}</p>
        </div>
        <a href="{{ route('customers.index') }}" class="text-xs text-gray-600 px-3 py-1.5 border border-gray-200 rounded-lg hover:bg-gray-50">Kembali</a>
    </div>

 <form method="POST" action="{{ route('customers.update', $customer) }}" class="bg-white border border-blue-100 p-5 shadow-sm space-y-4 text-xs">
        @csrf
        @method('PUT')

        <div>
            <label class="block font-semibold text-gray-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
            <input type="text" name="name" value="{{ old('name', $customer->name) }}" required
 class="w-full border border-gray-200 px-3 py-2 text-gray-800 focus:outline-none focus:border-blue-500">
            @error('name')<p class="text-red-500 mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block font-semibold text-gray-700 mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email', $customer->email) }}"
 class="w-full border border-gray-200 px-3 py-2 text-gray-800 focus:outline-none focus:border-blue-500">
            </div>
            <div>
                <label class="block font-semibold text-gray-700 mb-1">Nomor Telepon / WhatsApp</label>
                <input type="text" name="phone" value="{{ old('phone', $customer->phone) }}"
 class="w-full border border-gray-200 px-3 py-2 text-gray-800 focus:outline-none focus:border-blue-500">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block font-semibold text-gray-700 mb-1">Instansi Asal</label>
 <select name="institution_id" class="w-full border border-gray-200 px-3 py-2 text-gray-700 focus:outline-none focus:border-blue-500">
                    <option value="">-- Tanpa / Belum Ditentukan --</option>
                    @foreach($institutions as $inst)
                        <option value="{{ $inst->id }}" {{ old('institution_id', $customer->institution_id) == $inst->id ? 'selected' : '' }}>
                            {{ $inst->name }} ({{ $inst->type ?? 'Instansi' }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block font-semibold text-gray-700 mb-1">Status Customer <span class="text-red-500">*</span></label>
 <select name="status" required class="w-full border border-gray-200 px-3 py-2 text-gray-700 focus:outline-none focus:border-blue-500">
                    <option value="active" selected>Active (Aktif)</option>
                </select>
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block font-semibold text-gray-700 mb-1">Kota</label>
                <input type="text" name="city" value="{{ old('city', $customer->city) }}"
 class="w-full border border-gray-200 px-3 py-2 text-gray-800 focus:outline-none focus:border-blue-500">
            </div>
            <div>
                <label class="block font-semibold text-gray-700 mb-1">Provinsi</label>
                <input type="text" name="province" value="{{ old('province', $customer->province) }}"
 class="w-full border border-gray-200 px-3 py-2 text-gray-800 focus:outline-none focus:border-blue-500">
            </div>
        </div>

        <div>
            <label class="block font-semibold text-gray-700 mb-1">Catatan Tambahan</label>
 <textarea name="notes" rows="2" class="w-full border border-gray-200 px-3 py-2 text-gray-800 focus:outline-none focus:border-blue-500">{{ old('notes', $customer->notes) }}</textarea>
        </div>

        <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-100">
            <a href="{{ route('customers.index') }}" class="px-4 py-2 text-gray-500 hover:text-gray-700">Batal</a>
            <button type="submit" class="px-5 py-2 text-white bg-slate-900 rounded-lg hover:bg-slate-800 shadow-sm font-semibold">
                Perbarui Customer
            </button>
        </div>
    </form>
</div>
@endsection
