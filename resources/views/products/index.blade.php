@extends('layouts.app')

@section('title', 'Katalog Program Pelatihan & Sertifikasi')

@section('content')
<div class="space-y-4">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-xl font-bold text-gray-900">Katalog Program Pelatihan & Sertifikasi</h1>
            <p class="text-xs text-gray-500 mt-0.5">Pelatihan, uji sertifikasi BNSP, dan paket bundling dipisahkan</p>
        </div>
        @if(auth()->user()->isAdmin())
        <a href="{{ route('products.create') }}" class="px-4 py-2 text-xs font-semibold text-white bg-gray-900 rounded-full hover:bg-black shadow-sm transition-colors">
            + Tambah Program
        </a>
        @endif
    </div>

    {{-- Filter Kategori & Pencarian --}}
 <div class="bg-white border border-blue-100/70 p-3.5 shadow-sm space-y-3">
        {{-- Category Pills --}}
        <div class="flex flex-wrap items-center gap-1.5 border-b border-gray-100 pb-3 text-xs">
            <a href="{{ route('products.index', array_merge(request()->except('category', 'page'))) }}"
               class="px-3 py-1.5 rounded-lg font-medium transition-colors {{ !request('category') ? 'bg-slate-900 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                Semua Program
            </a>
            <a href="{{ route('products.index', array_merge(request()->except('page', 'product_type'), ['category' => 'Pelatihan / Bootcamp'])) }}"
               class="px-3 py-1.5 rounded-lg font-medium transition-colors {{ request('category') == 'Pelatihan / Bootcamp' ? 'bg-slate-900 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                Pelatihan / Bootcamp
            </a>
            <a href="{{ route('products.index', array_merge(request()->except('page', 'product_type'), ['category' => 'Sertifikasi BNSP'])) }}"
               class="px-3 py-1.5 rounded-lg font-medium transition-colors {{ request('category') == 'Sertifikasi BNSP' ? 'bg-slate-900 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                Sertifikasi BNSP
            </a>
            @foreach(['training' => 'Pelatihan', 'certification' => 'Uji Sertifikasi', 'bundle' => 'Paket Bundling'] as $type => $label)
            <a href="{{ route('products.index', array_merge(request()->except('page', 'category'), ['product_type' => $type])) }}"
               class="px-3 py-1.5 rounded-lg font-medium transition-colors {{ request('product_type') == $type ? 'bg-slate-900 text-white' : 'bg-gray-100 text-gray-600 hover:bg-gray-200' }}">
                {{ $label }}
            </a>
            @endforeach
        </div>

        {{-- Search Input --}}
        <form method="GET" action="{{ route('products.index') }}" class="flex items-center gap-2">
            @if(request('category'))
                <input type="hidden" name="category" value="{{ request('category') }}">
            @endif
            @if(request('product_type'))
                <input type="hidden" name="product_type" value="{{ request('product_type') }}">
            @endif
            <div class="relative flex-1">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Cari nama program, kode, atau materi..."
 class="w-full text-xs border border-gray-200 px-3 py-2 text-gray-800 placeholder-gray-400 focus:outline-none focus:border-blue-500">
            </div>
            <button type="submit" class="px-5 py-2 text-xs font-semibold text-white bg-slate-900 rounded-lg hover:bg-slate-800 shadow-sm transition-colors">
                Cari
            </button>
            @if(request()->anyFilled(['search', 'category', 'product_type']))
                <a href="{{ route('products.index') }}" class="px-3 py-2 text-xs text-gray-500 hover:text-gray-700 border border-gray-200 rounded-lg">Reset</a>
            @endif
        </form>
    </div>

    {{-- Table Card --}}
 <div class="bg-white border border-blue-100/70 overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[620px] text-left text-xs text-gray-600">
                <thead class="bg-gray-50/70 border-b border-gray-100 text-gray-400 font-semibold uppercase tracking-wider text-[10.5px]">
                    <tr>
                        <th class="py-3.5 px-4">KODE & NAMA PROGRAM</th>
                        <th class="py-3.5 px-4">TIPE LAYANAN</th>
                        <th class="py-3.5 px-4">KATEGORI</th>
                        <th class="py-3.5 px-4">TIPE BIAYA</th>
                        <th class="py-3.5 px-4">HARGA</th>
                        <th class="py-3.5 px-4">DURASI</th>
                        <th class="py-3.5 px-4">STATUS</th>
                        @if(auth()->user()->isAdmin())
                        <th class="py-3.5 px-4 text-right">AKSI</th>
                        @endif
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($products as $prod)
                    <tr class="hover:bg-blue-50/20 transition-colors">
                        <td class="py-3.5 px-4">
                            <div class="font-semibold text-gray-900 text-sm">{{ $prod->name }}</div>
                            <div class="text-[11px] text-gray-400 font-mono mt-0.5">{{ $prod->code }}</div>
                            @if($prod->description)
                                <div class="text-[11px] text-gray-500 line-clamp-1 mt-0.5">{{ $prod->description }}</div>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2 py-0.5 text-[10.5px] font-semibold {{ $prod->product_type === 'certification' ? 'bg-red-50 text-red-700 border-red-200' : ($prod->product_type === 'bundle' ? 'bg-amber-50 text-amber-700 border-amber-200' : 'bg-blue-50 text-blue-700 border-blue-200') }} border">
                                {{ $prod->productTypeLabel() }}
                            </span>
                        </td>
                        <td class="py-3.5 px-4 whitespace-nowrap">
                            @if($prod->category == 'Sertifikasi BNSP')
 <span class="inline-flex items-center px-2 py-0.5 text-[10.5px] font-semibold bg-red-50 text-red-700 border border-red-200">
                                    Sertifikasi BNSP
                                </span>
                            @else
 <span class="inline-flex items-center px-2 py-0.5 text-[10.5px] font-semibold bg-blue-50 text-blue-700 border border-blue-200">
                                    {{ $prod->category ?? 'Pelatihan / Bootcamp' }}
                                </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 whitespace-nowrap">
                            @if($prod->price == 0)
 <span class="inline-flex items-center px-2 py-0.5 text-[10.5px] font-bold bg-slate-900 text-white">
                                    Gratis
                                </span>
                            @else
 <span class="inline-flex items-center px-2 py-0.5 text-[10.5px] font-bold bg-blue-900 text-white">
                                    Berbayar
                                </span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 font-semibold text-gray-900 whitespace-nowrap">
                            @if($prod->price == 0)
                                <span class="text-emerald-700 font-bold">Gratis (Rp 0)</span>
                            @else
                                <span>Rp {{ number_format($prod->price, 0, ',', '.') }}</span>
                            @endif
                        </td>
                        <td class="py-3.5 px-4 text-gray-700 whitespace-nowrap">
                            {{ $prod->duration_hours ? $prod->duration_hours . ' Jam' : '-' }}
                        </td>
                        <td class="py-3.5 px-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10.5px] font-medium border {{ $prod->is_active ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-gray-100 text-gray-500 border-gray-200' }}">
                                {{ $prod->is_active ? 'Aktif' : 'Non-aktif' }}
                            </span>
                        </td>
                        @if(auth()->user()->isAdmin())
                        <td class="py-3.5 px-4 text-right whitespace-nowrap">
                            <div class="inline-flex items-center gap-1">
                                <a href="{{ route('products.edit', $prod) }}" class="p-1.5 text-gray-400 hover:text-blue-600 rounded hover:bg-blue-50" title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                <form method="POST" action="{{ route('products.destroy', $prod) }}" class="inline" onsubmit="return confirm('Hapus program ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1.5 text-gray-400 hover:text-red-600 rounded hover:bg-red-50" title="Hapus">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </td>
                        @endif
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-gray-400 text-xs">
                            Tidak ada program pelatihan atau sertifikasi ditemukan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($products->hasPages())
            <div class="px-4 py-3 border-t border-gray-100 bg-gray-50/50">
                {{ $products->links() }}
            </div>
        @endif
    </div>
</div>
@endsection
