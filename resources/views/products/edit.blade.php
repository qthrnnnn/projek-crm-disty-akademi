@extends('layouts.app')

@section('title', 'Edit Program Pelatihan & Sertifikasi')

@section('content')
<div class="max-w-xl mx-auto space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Edit Program</h1>
            <p class="text-xs text-gray-500 mt-0.5">Perbarui informasi biaya, kategori, atau materi program</p>
        </div>
        <a href="{{ route('products.index') }}" class="text-xs text-gray-600 px-3 py-1.5 border border-gray-200 rounded-lg hover:bg-gray-50">Kembali</a>
    </div>

 <form method="POST" action="{{ route('products.update', $product) }}" class="bg-white border border-blue-100 p-5 shadow-sm space-y-4 text-xs">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block font-semibold text-gray-700 mb-1">Kode Program <span class="text-red-500">*</span></label>
                <input type="text" name="code" value="{{ old('code', $product->code) }}" required
 class="w-full border border-gray-200 px-3 py-2 text-gray-800 focus:outline-none focus:border-blue-500">
                @error('code')<p class="text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block font-semibold text-gray-700 mb-1">Tipe Layanan <span class="text-red-500">*</span></label>
                <select name="product_type" required class="w-full border border-gray-200 px-3 py-2 text-gray-800 focus:outline-none focus:border-blue-500">
                    <option value="training" {{ old('product_type', $product->product_type) == 'training' ? 'selected' : '' }}>Pelatihan / Kelas Belajar</option>
                    <option value="certification" {{ old('product_type', $product->product_type) == 'certification' ? 'selected' : '' }}>Uji Sertifikasi BNSP Saja</option>
                    <option value="bundle" {{ old('product_type', $product->product_type) == 'bundle' ? 'selected' : '' }}>Paket Pelatihan + Sertifikasi BNSP</option>
                </select>
                @error('product_type')<p class="text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block font-semibold text-gray-700 mb-1">Kategori Produk <span class="text-red-500">*</span></label>
 <select name="category" required class="w-full border border-gray-200 px-3 py-2 text-gray-800 focus:outline-none focus:border-blue-500">
                    <option value="Pelatihan / Bootcamp" {{ old('category', $product->category) == 'Pelatihan / Bootcamp' ? 'selected' : '' }}>Pelatihan / Bootcamp</option>
                    <option value="Sertifikasi BNSP" {{ old('category', $product->category) == 'Sertifikasi BNSP' ? 'selected' : '' }}>Sertifikasi BNSP</option>
                    <option value="Workshop & Seminar" {{ old('category', $product->category) == 'Workshop & Seminar' ? 'selected' : '' }}>Workshop & Seminar</option>
                </select>
                @error('category')<p class="text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>
        </div>

        <div>
            <label class="block font-semibold text-gray-700 mb-1">Nama Program <span class="text-red-500">*</span></label>
            <input type="text" name="name" value="{{ old('name', $product->name) }}" required
 class="w-full border border-gray-200 px-3 py-2 text-gray-800 focus:outline-none focus:border-blue-500">
            @error('name')<p class="text-red-500 mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block font-semibold text-gray-700 mb-1">Biaya / Harga (Rp) <span class="text-red-500">*</span></label>
                <input type="number" name="price" value="{{ old('price', (int)$product->price) }}" required min="0"
 class="w-full border border-gray-200 px-3 py-2 text-gray-800 focus:outline-none focus:border-blue-500">
                <p class="text-[10.5px] text-gray-400 mt-0.5">Isi angka 0 jika program ini Gratis</p>
            </div>
            <div>
                <label class="block font-semibold text-gray-700 mb-1">Durasi Pelatihan (Jam)</label>
                <input type="number" name="duration_hours" value="{{ old('duration_hours', $product->duration_hours) }}" min="1"
 class="w-full border border-gray-200 px-3 py-2 text-gray-800 focus:outline-none focus:border-blue-500">
            </div>
        </div>

        <div>
            <label class="block font-semibold text-gray-700 mb-1">Deskripsi & Benefit Program</label>
 <textarea name="description" rows="3" class="w-full border border-gray-200 px-3 py-2 text-gray-800 focus:outline-none focus:border-blue-500">{{ old('description', $product->description) }}</textarea>
        </div>

        <div class="flex items-center gap-2 pt-1">
 <input type="checkbox" name="is_active" id="is_active" value="1" {{ $product->is_active ? 'checked' : '' }} class=" border-gray-300 text-blue-600 focus:ring-blue-500">
            <label for="is_active" class="font-medium text-gray-700">Status Aktif (Tersedia untuk pendaftaran & jadwal)</label>
        </div>

        <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-100">
            <a href="{{ route('products.index') }}" class="px-4 py-2 text-gray-500 hover:text-gray-700">Batal</a>
            <button type="submit" class="px-5 py-2 text-white bg-slate-900 rounded-lg hover:bg-slate-800 shadow-sm font-semibold">
                Perbarui Program
            </button>
        </div>
    </form>
</div>
@endsection
