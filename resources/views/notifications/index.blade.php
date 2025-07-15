<x-app-layout>
    <x-page-title>Notifikasi Produk</x-page-title>

    {{-- 📦 Produk dengan Stok Menipis --}}
    <div class="bg-white p-4 rounded shadow-sm mb-4">
        <h4 class="mb-3">📦 Produk dengan Stok Menipis</h4>
        @if($lowStockProducts->isEmpty())
            <p class="text-muted">Tidak ada produk dengan stok rendah.</p>
        @else
            <div class="row">
                @foreach($lowStockProducts as $product)
                <div class="col-md-4 mb-3">
                   <div class="card h-100 shadow-sm text-center">
                        <img src="{{ asset('/storage/products/' . $product->image) }}" alt="{{ $product->name }}"
                            class="card-img-top mx-auto d-block mt-3"
                            style="width: 80px; height: 80px; object-fit: cover;">
                        <div class="card-body">
                            <h6 class="card-title">{{ $product->name }}</h6>
                            <p class="text-danger mb-0">Stok tersisa: <strong>{{ $product->qty }}</strong></p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- ⏳ Produk Kadaluarsa dan Akan Kadaluarsa --}}
    <div class="bg-white p-4 rounded shadow-sm">
        <h4 class="mb-3">⏳ Produk Kadaluarsa & Akan Kadaluarsa (≤ 15 hari)</h4>
        @if($expiringProducts->isEmpty())
            <p class="text-muted">Tidak ada produk yang kadaluarsa atau mendekati kadaluarsa.</p>
        @else
            <div class="row">
                @foreach($expiringProducts as $product)
                @php
                    $expiredDate = \Carbon\Carbon::parse($product->expired_date)->locale('id');
                    $daysRemaining = now()->diffInDays($expiredDate, false);
                    $isExpired = $daysRemaining < 0;
                @endphp
                <div class="col-md-4 mb-3">
                    <div class="card h-100 shadow-sm text-center">
                        <img src="{{ asset('/storage/products/' . $product->image) }}" alt="{{ $product->name }}"
                            class="card-img-top mx-auto d-block mt-3"
                            style="width: 80px; height: 80px; object-fit: cover;">
                        <div class="card-body">
                            <h6 class="card-title">{{ $product->name }}</h6>
                            <p class="mb-1">
                                Tanggal Exp: 
                                <strong>{{ $expiredDate->translatedFormat('d F Y') }}</strong>
                            </p>
                            <span class="badge {{ $isExpired ? 'bg-danger' : 'bg-warning' }}">
                                {{ $isExpired ? 'Sudah Kadaluarsa' : 'Tersisa ' . $daysRemaining . ' Hari' }}
                            </span>
                            <p class="text-muted mt-2 mb-0">Stok: {{ $product->qty }}</p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        @endif
    </div>
</x-app-layout>
