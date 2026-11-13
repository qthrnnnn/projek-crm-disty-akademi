@extends('layouts.app')

@section('title', 'Verifikasi Pembayaran')

@section('content')
<div class="max-w-3xl mx-auto space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Verifikasi Bukti Transfer</h1>
            <p class="text-xs text-gray-500 mt-0.5">Validasi pembayaran peserta untuk penerbitan akses kelas & komisi sales</p>
        </div>
        <a href="{{ route('payments.index') }}" class="text-xs text-gray-600 px-3 py-1.5 border border-gray-200 rounded-lg hover:bg-gray-50">Kembali</a>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        {{-- Detail transaksi --}}
 <div class="bg-white border border-blue-100 p-5 shadow-sm space-y-3 text-xs">
            <h3 class="font-semibold text-gray-900 text-sm border-b border-gray-100 pb-2">Detail Transaksi</h3>
            <div class="space-y-2.5">
                <div>
                    <span class="text-gray-400 block text-[11px]">Nama Peserta</span>
                    <a href="{{ route('leads.show', $payment->lead) }}" class="font-bold text-blue-600 hover:underline">
                        {{ $payment->lead?->name }} ({{ $payment->lead?->lead_code }})
                    </a>
                </div>
                <div>
                    <span class="text-gray-400 block text-[11px]">Program Pelatihan</span>
                    <span class="font-semibold text-gray-800">{{ $payment->product?->name ?? ($payment->lead?->product?->name ?? '-') }}</span>
                </div>
                <div>
                    <span class="text-gray-400 block text-[11px]">Nominal Transfer</span>
                    <span class="text-base font-bold text-emerald-600">Rp {{ number_format($payment->amount, 0, ',', '.') }}</span>
                </div>
                <div>
                    <span class="text-gray-400 block text-[11px]">Metode / Bank / Rekening</span>
                    <span class="font-medium text-gray-800">{{ $payment->bank_name ?? $payment->payment_method }} {{ $payment->account_name ? ' — a.n ' . $payment->account_name : '' }}</span>
                </div>
                <div>
                    <span class="text-gray-400 block text-[11px]">Sales yang Menangani (PIC)</span>
                    <span class="font-medium text-gray-800">{{ $payment->lead?->assignedUser?->name ?? 'Belum ada PIC' }}</span>
                </div>
                <div>
                    <span class="text-gray-400 block text-[11px]">Status Saat Ini</span>
 <span class="inline-block px-2 py-0.5 text-[10px] font-bold border uppercase
                        {{ $payment->status === 'valid' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' : 'bg-amber-50 text-amber-700 border-amber-200' }}">
                        {{ $payment->status }}
                    </span>
                </div>
                @if($payment->verifiedByUser)
                    <div class="pt-2 border-t border-gray-100 text-[11px] text-gray-500">
                        Diverifikasi oleh: <strong class="text-gray-700">{{ $payment->verifiedByUser->name }}</strong>
                        pada {{ $payment->verified_at?->translatedFormat('d M Y, H:i') }}
                    </div>
                @endif
            </div>
        </div>

        {{-- Bukti Transfer & Form Verifikasi --}}
        <div class="space-y-4">
 <div class="bg-white border border-blue-100 p-5 shadow-sm space-y-3 text-xs">
                <h3 class="font-semibold text-gray-900 text-sm border-b border-gray-100 pb-2">Bukti Struk Transfer</h3>
                @if($payment->proof_file)
 <div class="border border-gray-100 overflow-hidden bg-gray-50">
                        <img src="{{ asset('storage/' . $payment->proof_file) }}" alt="Bukti Transfer" class="w-full h-48 object-contain">
                    </div>
                @else
 <div class="h-32 bg-gray-50 flex items-center justify-center text-gray-400 text-xs">
                        Tidak ada lampiran file struk
                    </div>
                @endif
            </div>

            {{-- Form Verifikasi --}}
 <div class="bg-white border border-blue-100 p-5 shadow-sm space-y-3 text-xs">
                <h3 class="font-semibold text-gray-900 text-sm">Status Verifikasi</h3>
                @if(auth()->user()->isAdmin())
                <form method="POST" action="{{ route('payments.verify', $payment) }}" class="space-y-3">
                    @csrf
                    @method('PATCH')
                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">Keputusan Verifikasi</label>
 <select name="status" required class="w-full border border-gray-200 px-3 py-2 text-gray-800 focus:outline-none focus:border-blue-500">
                            <option value="valid" {{ $payment->status == 'valid' ? 'selected' : '' }}>Valid (Dana Masuk - Terbitkan Komisi Sales & Deal)</option>
                            <option value="recheck" {{ $payment->status == 'recheck' ? 'selected' : '' }}>Perlu Cek Ulang (Minta sales cross-check)</option>
                            <option value="invalid" {{ $payment->status == 'invalid' ? 'selected' : '' }}>Tidak Valid (Tolak Pembayaran)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-semibold text-gray-700 mb-1">Catatan Verifikasi</label>
 <textarea name="verification_notes" rows="2" class="w-full border border-gray-200 px-3 py-2 text-gray-800" placeholder="Catatan audit...">{{ $payment->verification_notes }}</textarea>
                    </div>
                    <button type="submit" class="w-full py-2 bg-blue-600 hover:bg-blue-700 text-white font-medium rounded-lg shadow-sm transition-colors">
                        Simpan Hasil Verifikasi
                    </button>
                </form>
                @else
 <div class="p-3 bg-gray-50 text-gray-600 space-y-2">
                    <p>Status: <strong class="uppercase text-gray-900">{{ $payment->status }}</strong></p>
                    @if($payment->verification_notes)
                        <p class="text-[11px] text-gray-500">Catatan Admin: "{{ $payment->verification_notes }}"</p>
                    @endif
                    <p class="text-[10.5px] text-gray-400 border-t border-gray-200 pt-1.5">
                        Verifikasi pembayaran dilakukan secara terpusat oleh Admin/Keuangan. Begitu diverifikasi valid, komisi affiliate 10% otomatis diterbitkan untuk Anda.
                    </p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
