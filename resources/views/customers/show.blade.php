@extends('layouts.app')

@section('title', 'Detail Customer — ' . $customer->name)

@section('content')
<div class="space-y-4 max-w-4xl mx-auto">
    {{-- Header --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <div class="flex items-center gap-2">
                <h1 class="text-xl font-bold text-gray-900">{{ $customer->name }}</h1>
 <span class="font-mono text-xs text-gray-400 bg-gray-100 px-2 py-0.5 ">{{ $customer->customer_code }}</span>
            </div>
            <p class="text-xs text-gray-500 mt-0.5">Detail data pelanggan dan riwayat pendaftaran pelatihan</p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('customers.index') }}" class="px-3.5 py-2 text-xs font-medium text-gray-700 bg-white border border-gray-200 rounded-lg hover:bg-gray-50">
                Kembali
            </a>
            <a href="{{ route('registrations.create', ['customer_id' => $customer->id]) }}" class="px-3.5 py-2 text-xs font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700">
                + Daftarkan ke Pelatihan
            </a>
            <a href="{{ route('customers.edit', $customer) }}" class="px-3.5 py-2 text-xs font-semibold text-white bg-slate-900 rounded-lg hover:bg-slate-800">
                Edit Data
            </a>
        </div>
    </div>

    {{-- Detail Card --}}
 <div class="bg-white border border-blue-100/70 p-5 shadow-sm space-y-4 text-xs">
        <h2 class="text-sm font-bold text-gray-900 border-b border-gray-100 pb-2">Informasi Profil Customer</h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-4">
            <div>
                <p class="text-gray-400 text-[11px]">Nama Lengkap</p>
                <p class="font-semibold text-gray-900 text-sm">{{ $customer->name }}</p>
            </div>
            <div>
                <p class="text-gray-400 text-[11px]">Status</p>
                <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10.5px] font-semibold
                    {{ $customer->status == 'active' ? 'bg-emerald-50 text-emerald-700' : ($customer->status == 'prospect' ? 'bg-amber-50 text-amber-700' : 'bg-gray-100 text-gray-600') }}">
                    {{ ucfirst($customer->status) }}
                </span>
            </div>
            <div>
                <p class="text-gray-400 text-[11px]">Instansi Asal</p>
                @if($customer->institution)
                    <p class="font-semibold text-gray-800">{{ $customer->institution->name }} <span class="text-gray-400 font-normal">({{ $customer->institution->type ?? 'Instansi' }})</span></p>
                @else
                    <p class="text-gray-400">-</p>
                @endif
            </div>
            <div>
                <p class="text-gray-400 text-[11px]">Email</p>
                <p class="font-semibold text-gray-800">{{ $customer->email ?? '-' }}</p>
            </div>
            <div>
                <p class="text-gray-400 text-[11px]">Nomor Telepon</p>
                <p class="font-semibold text-gray-800">{{ $customer->phone ?? '-' }}</p>
            </div>
            <div>
                <p class="text-gray-400 text-[11px]">Kota</p>
                <p class="font-semibold text-gray-800">{{ $customer->city ?? '-' }}</p>
            </div>
            <div>
                <p class="text-gray-400 text-[11px]">Provinsi</p>
                <p class="font-semibold text-gray-800">{{ $customer->province ?? '-' }}</p>
            </div>
            <div>
                <p class="text-gray-400 text-[11px]">Sumber Data</p>
                <p class="font-semibold text-gray-800 uppercase">{{ $customer->source ?? 'manual' }}</p>
            </div>
        </div>

        @if($customer->notes)
            <div class="pt-2 border-t border-gray-100">
                <p class="text-gray-400 text-[11px]">Catatan</p>
                <p class="text-gray-700 mt-0.5">{{ $customer->notes }}</p>
            </div>
        @endif
    </div>

    {{-- Riwayat Pendaftaran Pelatihan --}}
 <div class="bg-white border border-blue-100/70 overflow-hidden shadow-sm">
        <div class="p-4 border-b border-gray-100 flex items-center justify-between">
            <div>
                <h2 class="text-sm font-bold text-gray-900">Riwayat Pendaftaran Pelatihan</h2>
                <p class="text-[11px] text-gray-500">Kelas dan jadwal yang diikuti oleh customer ini</p>
            </div>
            <a href="{{ route('registrations.create', ['customer_id' => $customer->id]) }}" class="text-xs text-blue-600 hover:underline font-semibold">
                + Daftarkan ke Jadwal Baru
            </a>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full text-left text-xs text-gray-600">
                <thead class="bg-gray-50/70 border-b border-gray-100 text-gray-400 font-semibold uppercase tracking-wider text-[10.5px]">
                    <tr>
                        <th class="py-3 px-4">NO. REGISTRASI</th>
                        <th class="py-3 px-4">PELATIHAN</th>
                        <th class="py-3 px-4">TRAINER</th>
                        <th class="py-3 px-4">TANGGAL</th>
                        <th class="py-3 px-4">BIAYA</th>
                        <th class="py-3 px-4">STATUS</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    @forelse($customer->registrations as $reg)
                    <tr class="hover:bg-blue-50/20">
                        <td class="py-3 px-4 font-mono font-medium text-gray-800">
                            {{ $reg->registration_number }}
                        </td>
                        <td class="py-3 px-4 font-semibold text-gray-900">
                            {{ $reg->product?->name }}
                        </td>
                        <td class="py-3 px-4 text-gray-700">
                            {{ $reg->schedule?->trainer?->name ?? '-' }}
                        </td>
                        <td class="py-3 px-4 text-gray-700 whitespace-nowrap">
                            {{ $reg->training_date ? $reg->training_date->translatedFormat('d M Y') : '-' }}
                        </td>
                        <td class="py-3 px-4 font-semibold text-gray-900">
                            Rp {{ number_format($reg->amount, 0, ',', '.') }}
                        </td>
                        <td class="py-3 px-4">
                            @php
                                $statusStyle = match($reg->status) {
                                    'confirmed'  => 'bg-emerald-50 text-emerald-700',
                                    'attended'   => 'bg-blue-50 text-blue-700',
                                    'registered' => 'bg-amber-50 text-amber-700',
                                    'cancelled'  => 'bg-red-50 text-red-700',
                                    default      => 'bg-gray-50 text-gray-600',
                                };
                            @endphp
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10.5px] font-semibold {{ $statusStyle }}">
                                {{ ucfirst($reg->status) }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="py-8 text-center text-gray-400 text-xs">
                            Customer ini belum didaftarkan ke jadwal pelatihan manapun.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
