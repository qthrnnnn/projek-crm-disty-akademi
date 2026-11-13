@extends('layouts.app')

@section('title', 'Edit Akun Pengguna')

@section('content')
<div class="max-w-xl mx-auto space-y-4">
    <div class="flex items-center justify-between">
        <h1 class="text-xl font-semibold text-gray-900">Edit Akun Pengguna</h1>
        <a href="{{ route('users.index') }}" class="text-xs text-gray-600 px-3 py-1.5 border border-gray-200 rounded-lg hover:bg-gray-50">Kembali</a>
    </div>

 <form method="POST" action="{{ route('users.update', $user) }}" class="bg-white border border-blue-100 p-5 shadow-sm space-y-4 text-xs">
        @csrf
        @method('PUT')

        <div>
            <label class="block font-semibold text-gray-700 mb-1">Nama Lengkap <span class="text-red-500">*</span></label>
            <input type="text" name="name" value="{{ old('name', $user->name) }}" required
 class="w-full border border-gray-200 px-3 py-2 text-gray-800 focus:outline-none focus:border-blue-500">
            @error('name')<p class="text-red-500 mt-1">{{ $message }}</p>@enderror
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block font-semibold text-gray-700 mb-1">Email Login <span class="text-red-500">*</span></label>
                <input type="email" name="email" value="{{ old('email', $user->email) }}" required
 class="w-full border border-gray-200 px-3 py-2 text-gray-800 focus:outline-none focus:border-blue-500">
                @error('email')<p class="text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block font-semibold text-gray-700 mb-1">No. WhatsApp / HP</label>
                <input type="text" name="phone" value="{{ old('phone', $user->phone) }}"
 class="w-full border border-gray-200 px-3 py-2 text-gray-800 focus:outline-none focus:border-blue-500">
            </div>
        </div>

        <div>
            <label class="block font-semibold text-gray-700 mb-1">Role Akun <span class="text-red-500">*</span></label>
 <select name="role" required class="w-full border border-gray-200 px-3 py-2 text-gray-700 focus:outline-none focus:border-blue-500">
                <option value="sales" {{ $user->role == 'sales' ? 'selected' : '' }}>Sales</option>
                <option value="admin" {{ $user->role == 'admin' ? 'selected' : '' }}>Admin</option>
            </select>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block font-semibold text-gray-700 mb-1">Password Baru (Kosongkan jika tidak diubah)</label>
                <input type="password" name="password" minlength="8"
 class="w-full border border-gray-200 px-3 py-2 text-gray-800 focus:outline-none focus:border-blue-500">
            </div>
            <div>
                <label class="block font-semibold text-gray-700 mb-1">Ulangi Password Baru</label>
                <input type="password" name="password_confirmation"
 class="w-full border border-gray-200 px-3 py-2 text-gray-800 focus:outline-none focus:border-blue-500">
            </div>
        </div>

        <div class="flex items-center gap-2 pt-1">
 <input type="checkbox" name="is_active" id="is_active" value="1" {{ $user->is_active ? 'checked' : '' }} class=" border-gray-300 text-blue-600 focus:ring-blue-500">
            <label for="is_active" class="font-medium text-gray-700">Akun Aktif</label>
        </div>

        <div class="flex items-center justify-between pt-2 border-t border-gray-100">
            @if($user->id !== auth()->id())
                <button type="button" onclick="if(confirm('Hapus pengguna ini?')) document.getElementById('del-u').submit();" class="text-red-600 hover:underline">
                    Hapus
                </button>
            @else
                <div></div>
            @endif
            <div class="flex items-center gap-2">
                <a href="{{ route('users.index') }}" class="px-4 py-2 text-gray-500 hover:text-gray-700">Batal</a>
                <button type="submit" class="px-5 py-2 text-white bg-blue-600 rounded-lg hover:bg-blue-700 shadow-sm">Simpan Perubahan</button>
            </div>
        </div>
    </form>

    @if($user->id !== auth()->id())
        <form id="del-u" method="POST" action="{{ route('users.destroy', $user) }}" class="hidden">
            @csrf @method('DELETE')
        </form>
    @endif
</div>
@endsection
