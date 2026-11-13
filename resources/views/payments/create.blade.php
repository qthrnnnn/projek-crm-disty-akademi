@extends('layouts.app')

@section('title', 'Input Bukti Pembayaran')

@section('content')
<div class="max-w-2xl mx-auto space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Input Bukti Pembayaran</h1>
            <p class="text-xs text-gray-500 mt-0.5">Catat bukti transfer atau pembayaran dari peserta</p>
        </div>
        <a href="{{ route('payments.index') }}" class="text-xs text-gray-600 px-3 py-1.5 border border-gray-200 rounded-lg hover:bg-gray-50">Kembali</a>
    </div>

    <form method="POST" action="{{ route('payments.store') }}" enctype="multipart/form-data"
          x-data="{
              onLeadChange(e) {
                  const opt = e.target.selectedOptions[0];
                  if (opt && opt.dataset.price) {
                      const amountInput = document.getElementById('payment-amount-input');
                      if (amountInput) {
                          amountInput.value = opt.dataset.price;
                      }
                  }
              }
          }"
 class="bg-white border border-blue-100 p-5 shadow-sm space-y-4 text-xs">
        @csrf

        <div>
            <label class="block font-semibold text-gray-700 mb-1">Pilih Lead / Calon Peserta <span class="text-red-500">*</span></label>
 <select name="lead_id" required @change="onLeadChange($event)" class="w-full border border-gray-200 px-3 py-2 text-gray-800 focus:outline-none focus:border-blue-500">
                <option value="">-- Pilih Peserta --</option>
                @foreach($leads as $l)
                    <option value="{{ $l->id }}" data-price="{{ (int)$l->effectivePrice() }}" {{ (old('lead_id') == $l->id || request('lead_id') == $l->id) ? 'selected' : '' }}>
                        {{ $l->name }} ({{ $l->lead_code }}) — {{ $l->product?->name ?? 'Belum ada produk' }} (Deal: Rp {{ number_format($l->effectivePrice(), 0, ',', '.') }})
                    </option>
                @endforeach
            </select>
        </div>

 <div class="p-3 bg-emerald-50 border border-emerald-200 text-emerald-800 text-[11.5px] flex flex-wrap items-center justify-between gap-2">
            <span><strong>Komisi Affiliate (10%)</strong>: Sales yang membawa peserta ini otomatis mendapatkan komisi 10% setelah pembayaran diverifikasi Admin.</span>
 <span class="font-bold text-emerald-900 bg-white px-2 py-0.5 border border-emerald-300 text-xs">Rate: 10%</span>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block font-semibold text-gray-700 mb-1">Program Pelatihan</label>
 <select name="product_id" class="w-full border border-gray-200 px-3 py-2 text-gray-700 focus:outline-none focus:border-blue-500">
                    <option value="">-- Sesuai Minat Lead / Pilih Baru --</option>
                    @foreach($products as $prod)
                        <option value="{{ $prod->id }}" {{ old('product_id') == $prod->id ? 'selected' : '' }}>
                            {{ $prod->name }} (Rp {{ number_format($prod->price, 0, ',', '.') }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block font-semibold text-gray-700 mb-1">Nominal Pembayaran Deal (Rp) <span class="text-red-500">*</span></label>
                <input id="payment-amount-input" type="number" name="amount" value="{{ old('amount') }}" required min="0" placeholder="Otomatis terisi nilai deal"
 class="w-full border border-gray-200 px-3 py-2 text-gray-800 focus:outline-none focus:border-blue-500 font-semibold">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block font-semibold text-gray-700 mb-1">Metode Pembayaran</label>
 <select name="payment_method" class="w-full border border-gray-200 px-3 py-2 text-gray-700 focus:outline-none focus:border-blue-500">
                    <option value="transfer_bank">Transfer Bank</option>
                    <option value="qris">QRIS / E-Wallet</option>
                    <option value="cash">Tunai / Cash</option>
                </select>
            </div>
            <div>
                <label class="block font-semibold text-gray-700 mb-1">Nama Bank / Channel</label>
                <input type="text" name="bank_name" value="{{ old('bank_name') }}" placeholder="BCA / Mandiri / BNI / dsb"
 class="w-full border border-gray-200 px-3 py-2 text-gray-800 focus:outline-none focus:border-blue-500">
            </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block font-semibold text-gray-700 mb-1">Nama Pengirim Rekening</label>
                <input type="text" name="account_name" value="{{ old('account_name') }}" placeholder="a.n Pengirim"
 class="w-full border border-gray-200 px-3 py-2 text-gray-800 focus:outline-none focus:border-blue-500">
            </div>
            <div>
                <label class="block font-semibold text-gray-700 mb-1">Tanggal Transfer</label>
                <input type="datetime-local" name="payment_date" value="{{ old('payment_date', now()->format('Y-m-d\TH:i')) }}"
 class="w-full border border-gray-200 px-3 py-2 text-gray-800 focus:outline-none focus:border-blue-500">
            </div>
        </div>

        <div>
            <label class="block font-semibold text-gray-700 mb-1">Upload Foto / Bukti Struk Transfer</label>
            <input type="file" name="proof_file" accept="image/*,.pdf"
 class="w-full text-xs text-gray-500 file:mr-3 file:py-2 file:px-3 file: file:border-0 file:text-xs file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
            <span class="text-[10px] text-gray-400 mt-1 block">Format: JPG, PNG, atau PDF (Maks. 5MB)</span>
        </div>

        <div>
            <label class="block font-semibold text-gray-700 mb-1">Catatan Tambahan</label>
 <textarea name="notes" rows="2" class="w-full border border-gray-200 px-3 py-2 text-gray-800 focus:outline-none focus:border-blue-500" placeholder="Keterangan transaksi...">{{ old('notes') }}</textarea>
        </div>

        <div class="flex items-center justify-end gap-2 pt-2 border-t border-gray-100">
            <a href="{{ route('payments.index') }}" class="px-4 py-2 text-gray-500 hover:text-gray-700">Batal</a>
            <button type="submit" class="px-5 py-2 text-white bg-blue-600 rounded-lg hover:bg-blue-700 shadow-sm">Simpan Bukti Pembayaran</button>
        </div>
    </form>
</div>
@endsection
