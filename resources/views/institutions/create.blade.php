@extends('layouts.app')

@section('title', 'Tambah Instansi')

@section('content')
<div class="max-w-xl mx-auto space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Tambah Instansi Baru</h1>
            <p class="text-xs text-gray-500 mt-0.5">Daftarkan lembaga, universitas, instansi pemerintah, atau swasta</p>
        </div>
        <a href="{{ route('institutions.index') }}" class="text-xs text-gray-600 px-3 py-1.5 border border-gray-200 rounded-lg hover:bg-gray-50">Kembali</a>
    </div>

 <form method="POST" action="{{ route('institutions.store') }}" class="bg-white border border-blue-100 p-5 shadow-sm space-y-4 text-xs">
        @csrf

        <div>
            <label class="block font-semibold text-gray-700 mb-1">Nama Instansi <span class="text-red-500">*</span></label>
            <input type="text" name="name" value="{{ old('name') }}" required
                   placeholder="Contoh: Universitas Airlangga / PT Telkom"
 class="w-full border border-gray-200 px-3 py-2 text-gray-800 focus:outline-none focus:border-blue-500">
            @error('name')<p class="text-red-500 mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block font-semibold text-gray-700 mb-1">Jenis Instansi</label>
 <select name="type" class="w-full border border-gray-200 px-3 py-2 text-gray-700 focus:outline-none focus:border-blue-500">
                    <option value="Perguruan Tinggi" {{ old('type') == 'Perguruan Tinggi' ? 'selected' : '' }}>Perguruan Tinggi</option>
                    <option value="Pemerintahan" {{ old('type') == 'Pemerintahan' ? 'selected' : '' }}>Pemerintahan</option>
                    <option value="BUMN" {{ old('type') == 'BUMN' ? 'selected' : '' }}>BUMN</option>
                    <option value="Swasta / Corporate" {{ old('type') == 'Swasta / Corporate' ? 'selected' : '' }}>Swasta / Corporate</option>
                    <option value="Sekolah / SMK" {{ old('type') == 'Sekolah / SMK' ? 'selected' : '' }}>Sekolah / SMK</option>
                    <option value="Lainnya" {{ old('type') == 'Lainnya' ? 'selected' : '' }}>Lainnya</option>
                </select>
            </div>
            <div>
                <label class="block font-semibold text-gray-700 mb-1">Bidang / Industri</label>
                <input type="text" name="industry" value="{{ old('industry') }}"
                       placeholder="Contoh: Pendidikan, IT, Keuangan"
 class="w-full border border-gray-200 px-3 py-2 text-gray-800 focus:outline-none focus:border-blue-500">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block font-semibold text-gray-700 mb-1">Email Resmi</label>
                <input type="email" name="email" value="{{ old('email') }}"
                       placeholder="info@instansi.ac.id"
 class="w-full border border-gray-200 px-3 py-2 text-gray-800 focus:outline-none focus:border-blue-500">
            </div>
            <div>
                <label class="block font-semibold text-gray-700 mb-1">Nomor Telepon</label>
                <input type="text" name="phone" value="{{ old('phone') }}"
                       placeholder="021-xxx / 0812xxx"
 class="w-full border border-gray-200 px-3 py-2 text-gray-800 focus:outline-none focus:border-blue-500">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block font-semibold text-gray-700 mb-1">Kota</label>
                <input type="text" name="city" value="{{ old('city') }}"
                       placeholder="Contoh: Surabaya"
 class="w-full border border-gray-200 px-3 py-2 text-gray-800 focus:outline-none focus:border-blue-500">
            </div>
            <div>
                <label class="block font-semibold text-gray-700 mb-1">Provinsi</label>
                <input type="text" name="province" value="{{ old('province') }}"
                       placeholder="Contoh: Jawa Timur"
 class="w-full border border-gray-200 px-3 py-2 text-gray-800 focus:outline-none focus:border-blue-500">
            </div>
        </div>

        <div>
            <label class="block font-semibold text-gray-700 mb-1">Alamat Lengkap</label>
 <textarea name="address" rows="2" class="w-full border border-gray-200 px-3 py-2 text-gray-800 focus:outline-none focus:border-blue-500" placeholder="Alamat kantor / kampus...">{{ old('address') }}</textarea>
        </div>

        <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-100">
            <a href="{{ route('institutions.index') }}" class="px-4 py-2 text-gray-500 hover:text-gray-700">Batal</a>
            <button type="submit" class="px-5 py-2 text-white bg-slate-900 rounded-lg hover:bg-slate-800 shadow-sm font-semibold">
                Simpan Instansi
            </button>
        </div>
    </form>
</div>
@endsection
