@extends('layouts.app')

@section('title', 'Edit Lead: ' . $lead->name)

@section('content')
<div class="max-w-3xl mx-auto space-y-4">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-xl font-semibold text-gray-900">Edit Data Lead</h1>
            <p class="text-xs text-gray-500 mt-0.5">Kode: {{ $lead->lead_code }}</p>
        </div>
        <a href="{{ route('leads.show', $lead) }}" class="text-xs text-gray-600 hover:text-gray-900 font-medium px-3 py-1.5 border border-gray-200 rounded-lg hover:bg-gray-50">
            Kembali
        </a>
    </div>

    <form method="POST" action="{{ route('leads.update', $lead) }}"
          x-data="{
              products: {{ json_encode($products->map(fn($p) => ['id' => $p->id, 'name' => $p->name, 'price' => (float)$p->price])) }},
              selectedProductId: '{{ old('product_id', $lead->product_id) }}',
              dealPrice: '{{ old('deal_price', $lead->deal_price ?: ($lead->product ? round($lead->product->price * 1.10) : '')) }}',
              get selectedProduct() {
                  return this.products.find(p => p.id == this.selectedProductId) || null;
              },
              get basePrice() {
                  return this.selectedProduct ? this.selectedProduct.price : 0;
              },
              get commission() {
                  return Math.round(this.basePrice * 0.10);
              },
              get offerPrice() {
                  return this.basePrice + this.commission;
              },
              onProductChange() {
                  if (this.selectedProduct) {
                      this.dealPrice = this.offerPrice;
                  } else {
                      this.dealPrice = '';
                  }
              },
              formatRupiah(num) {
                  return new Intl.NumberFormat('id-ID').format(num || 0);
              }
          }"
 class="bg-white border border-blue-100 p-5 shadow-sm space-y-4 text-xs">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Nama Prospek <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $lead->name) }}" required
 class="w-full text-xs border border-gray-200 px-3 py-2 text-gray-800 focus:outline-none focus:border-blue-500">
                @error('name')<p class="text-[11px] text-red-500 mt-1">{{ $message }}</p>@enderror
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Email</label>
                <input type="email" name="email" value="{{ old('email', $lead->email) }}"
 class="w-full text-xs border border-gray-200 px-3 py-2 text-gray-800 focus:outline-none focus:border-blue-500">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">WhatsApp</label>
                <input type="text" name="whatsapp" value="{{ old('whatsapp', $lead->whatsapp) }}"
 class="w-full text-xs border border-gray-200 px-3 py-2 text-gray-800 focus:outline-none focus:border-blue-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Telepon</label>
                <input type="text" name="phone" value="{{ old('phone', $lead->phone) }}"
 class="w-full text-xs border border-gray-200 px-3 py-2 text-gray-800 focus:outline-none focus:border-blue-500">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Instansi / Perusahaan</label>
                <input type="text" name="company" value="{{ old('company', $lead->company) }}"
 class="w-full text-xs border border-gray-200 px-3 py-2 text-gray-800 focus:outline-none focus:border-blue-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Jabatan / Profesi</label>
                <input type="text" name="job_title" value="{{ old('job_title', $lead->job_title) }}"
 class="w-full text-xs border border-gray-200 px-3 py-2 text-gray-800 focus:outline-none focus:border-blue-500">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Kota</label>
                <input type="text" name="city" value="{{ old('city', $lead->city) }}"
 class="w-full text-xs border border-gray-200 px-3 py-2 text-gray-800 focus:outline-none focus:border-blue-500">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Provinsi</label>
                <input type="text" name="province" value="{{ old('province', $lead->province) }}"
 class="w-full text-xs border border-gray-200 px-3 py-2 text-gray-800 focus:outline-none focus:border-blue-500">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-2 border-t border-gray-100">
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Minat Pelatihan <span class="text-red-500">*</span></label>
                <select name="product_id"
                        x-model="selectedProductId"
                        @change="onProductChange()"
 class="w-full text-xs border border-gray-200 px-3 py-2 text-gray-700 focus:outline-none focus:border-blue-500">
                    <option value="">-- Pilih Program Pelatihan --</option>
                    @foreach($products as $prod)
                        <option value="{{ $prod->id }}">
                            {{ $prod->name }} (Asli: Rp {{ number_format($prod->price, 0, ',', '.') }})
                        </option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Sumber Lead</label>
 <select name="lead_source_id" class="w-full text-xs border border-gray-200 px-3 py-2 text-gray-700 focus:outline-none focus:border-blue-500">
                    <option value="">-- Pilih Sumber --</option>
                    @foreach($sources as $src)
                        <option value="{{ $src->id }}" {{ old('lead_source_id', $lead->lead_source_id) == $src->id ? 'selected' : '' }}>{{ $src->label }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-700 mb-1">Sales PIC (Eksklusif)</label>
                @if(auth()->user()->isSales())
                    <input type="hidden" name="assigned_to" value="{{ $lead->assigned_to ?: auth()->id() }}">
                    <input type="text" readonly value="{{ $lead->assignedUser?->name ?: auth()->user()->name }} (Sales Penanggung Jawab)"
 class="w-full text-xs border border-gray-200 bg-gray-50 px-3 py-2 text-gray-700 font-medium">
                @else
 <select name="assigned_to" class="w-full text-xs border border-gray-200 px-3 py-2 text-gray-700 focus:outline-none focus:border-orange-500">
                        <option value="">-- Belum Ditugaskan --</option>
                        @foreach($salesUsers as $sales)
                            <option value="{{ $sales->id }}" {{ old('assigned_to', $lead->assigned_to) == $sales->id ? 'selected' : '' }}>{{ $sales->name }}</option>
                        @endforeach
                    </select>
                @endif
            </div>
        </div>

        {{-- Section Negosiasi Harga: Harga Asli + 10% Komisi --}}
 <div class="p-4 bg-gradient-to-br from-orange-50/70 to-amber-50/50 border border-orange-200 shadow-2xs space-y-3">
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div class="flex items-center gap-2">
 <span class="w-6 h-6 bg-orange-500 text-white flex items-center justify-center font-bold text-xs">10%</span>
                    <span class="text-xs font-bold text-gray-900">Perhitungan Penawaran Customer (Harga Asli + 10%)</span>
                </div>
                <span class="text-[10.5px] font-semibold text-orange-800 bg-orange-100 px-2.5 py-0.5 rounded-full border border-orange-200">
                    Sistem Otomatis Affiliate Disty
                </span>
            </div>

            {{-- Breakdown Harga Asli + 10% --}}
            <template x-if="selectedProduct">
 <div class="grid grid-cols-1 sm:grid-cols-3 gap-2.5 bg-white p-3 border border-orange-100 shadow-2xs">
                    <div class="border-b sm:border-b-0 sm:border-r border-gray-100 pb-2 sm:pb-0 sm:pr-2">
                        <span class="text-[10px] text-gray-400 block uppercase font-semibold">1. Harga Asli (Katalog)</span>
                        <span class="text-sm font-bold text-gray-800">Rp <span x-text="formatRupiah(basePrice)"></span></span>
                        <span class="text-[10px] text-gray-400 block mt-0.5">Hak bersih akademi</span>
                    </div>
                    <div class="border-b sm:border-b-0 sm:border-r border-gray-100 pb-2 sm:pb-0 sm:pr-2">
                        <span class="text-[10px] text-emerald-600 block uppercase font-semibold">2. Komisi Sales (10%)</span>
                        <span class="text-sm font-bold text-emerald-600">+ Rp <span x-text="formatRupiah(commission)"></span></span>
                        <span class="text-[10px] text-emerald-500 block mt-0.5">Margin komisi affiliate</span>
                    </div>
                    <div>
                        <span class="text-[10px] text-orange-600 block uppercase font-semibold">3. Harga ke Customer</span>
                        <span class="text-sm font-extrabold text-orange-600">Rp <span x-text="formatRupiah(offerPrice)"></span></span>
                        <span class="text-[10px] text-orange-500 block mt-0.5">Harga Asli + 10%</span>
                    </div>
                </div>
            </template>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-3 pt-1">
                <div>
                    <label class="block text-xs font-semibold text-gray-700 mb-1">
                        Harga Penawaran / Negosiasi Deal (Rp) <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="deal_price"
                           x-model="dealPrice"
 class="w-full text-xs border border-gray-200 px-3 py-2 text-gray-900 font-semibold focus:outline-none focus:border-orange-500 bg-white shadow-2xs"
                           placeholder="Otomatis terisi Harga Asli + 10%">
                    <p class="text-[10px] text-gray-400 mt-1">
                        Default otomatis terisi <strong>Harga Asli + 10%</strong>. Nilai ini yang ditagihkan ke peserta.
                    </p>
                </div>

                <div class="flex flex-col justify-center">
                    <template x-if="selectedProduct && dealPrice">
                        <div>
                            {{-- Status Negosiasi --}}
 <div x-show="parseFloat(dealPrice) >= offerPrice" class="p-2.5 bg-emerald-50 border border-emerald-200 text-emerald-800 text-[11px]">
                                <span class="font-bold">Penawaran Optimal:</span>
                                Customer membayar Rp <span x-text="formatRupiah(dealPrice)"></span>. Hak komisi sales 10% penuh (Rp <span x-text="formatRupiah(commission)"></span>).
                            </div>
 <div x-show="parseFloat(dealPrice) < offerPrice && parseFloat(dealPrice) >= basePrice" class="p-2.5 bg-amber-50 border border-amber-200 text-amber-800 text-[11px]">
                                <span class="font-bold">Ada Potongan Negosiasi:</span>
                                Harga di atas harga dasar (Rp <span x-text="formatRupiah(basePrice)"></span>). Komisi sales disesuaikan selisih deal.
                            </div>
 <div x-show="parseFloat(dealPrice) < basePrice" class="p-2.5 bg-red-50 border border-red-200 text-red-700 text-[11px]">
                                <span class="font-bold">Di Bawah Harga Asli:</span>
                                Harga negosiasi di bawah harga resmi Disty (Rp <span x-text="formatRupiah(basePrice)"></span>). Perlu konfirmasi khusus Admin!
                            </div>
                        </div>
                    </template>
                    <template x-if="!selectedProduct">
                        <p class="text-[11px] text-gray-400 italic">Pilih Program Pelatihan di atas untuk melihat simulasi perhitungan Harga Asli + 10%.</p>
                    </template>
                </div>
            </div>
        </div>

        <div>
            <label class="block text-xs font-semibold text-gray-700 mb-1">Catatan</label>
 <textarea name="notes" rows="2" class="w-full text-xs border border-gray-200 px-3 py-2 text-gray-800 focus:outline-none focus:border-blue-500">{{ old('notes', $lead->notes) }}</textarea>
        </div>

        <div class="flex items-center justify-between pt-3 border-t border-gray-100">
            <button type="button" onclick="if(confirm('Hapus lead ini?')) document.getElementById('delete-lead-form').submit();" class="text-xs text-red-600 hover:text-red-700 font-medium">
                Hapus Lead
            </button>
            <div class="flex items-center gap-2">
                <a href="{{ route('leads.show', $lead) }}" class="px-4 py-2 text-xs font-medium text-gray-600 hover:text-gray-800">Batal</a>
                <button type="submit" class="px-5 py-2 text-xs font-medium text-white bg-blue-600 rounded-lg hover:bg-blue-700 shadow-sm transition-colors">
                    Simpan Perubahan
                </button>
            </div>
        </div>
    </form>

    <form id="delete-lead-form" method="POST" action="{{ route('leads.destroy', $lead) }}" class="hidden">
        @csrf
        @method('DELETE')
    </form>
</div>
@endsection
