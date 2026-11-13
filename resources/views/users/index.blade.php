@extends('layouts.app')

@section('title', 'Manajemen Pengguna & Tim Sales')

@section('content')
<div class="space-y-4">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Pengguna & Akun Tim</h1>
            <p class="text-xs text-gray-500 mt-0.5">Kelola akun Admin dan Sales tim Disty Akademi</p>
        </div>
        <a href="{{ route('users.create') }}" class="inline-flex items-center gap-1.5 px-3.5 py-1.5 text-xs font-medium text-white bg-gray-900 rounded-full hover:bg-black transition-colors shadow-sm">
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Pengguna
        </a>
    </div>

 <div class="bg-white border border-blue-100 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[550px] text-left text-xs text-gray-600">
                <thead class="bg-gray-50/70 border-b border-gray-100 text-gray-500 font-medium uppercase tracking-wider text-[11px]">
                    <tr>
                        <th class="py-3 px-4">Nama & Email</th>
                        <th class="py-3 px-4">Role</th>
                        <th class="py-3 px-4">No. HP / WA</th>
                        <th class="py-3 px-4">Status</th>
                        <th class="py-3 px-4 text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($users as $u)
                    <tr class="hover:bg-blue-50/30 transition-colors">
                        <td class="py-3 px-4">
                            <div class="font-semibold text-gray-900">{{ $u->name }}</div>
                            <div class="text-[11px] text-gray-400">{{ $u->email }}</div>
                        </td>
                        <td class="py-3 px-4">
 <span class="px-2 py-0.5 text-[10px] font-semibold uppercase tracking-wider border
                                {{ $u->role === 'admin' ? 'bg-purple-50 text-purple-700 border-purple-200' : ($u->role === 'manager' ? 'bg-blue-50 text-blue-700 border-blue-200' : 'bg-emerald-50 text-emerald-700 border-emerald-200') }}">
                                {{ $u->role }}
                            </span>
                        </td>
                        <td class="py-3 px-4">{{ $u->phone ?? '-' }}</td>
                        <td class="py-3 px-4">
 <span class="px-2 py-0.5 text-[10px] font-medium border {{ $u->is_active ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-gray-100 text-gray-500 border-gray-200' }}">
                                {{ $u->is_active ? 'Aktif' : 'Non-aktif' }}
                            </span>
                        </td>
                        <td class="py-3 px-4 text-right">
                            <a href="{{ route('users.edit', $u) }}" class="p-1 text-gray-400 hover:text-blue-600">Edit</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="py-8 text-center text-gray-400">Belum ada pengguna</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($users->hasPages())
        <div class="p-3 border-t border-gray-100">{{ $users->links() }}</div>
        @endif
    </div>
</div>
@endsection
