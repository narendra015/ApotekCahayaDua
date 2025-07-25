<x-app-layout>
    <x-page-title>Notifikasi Produk</x-page-title>

    {{-- 📦 Produk dengan Stok Menipis --}}
    <div class="bg-white p-4 rounded shadow-sm mb-4">
        <h4 class="mb-3">📦 Produk dengan Stok Menipis (Total ≤ 5)</h4>
        @if($lowStockProducts->isEmpty())
            <p class="text-muted">Tidak ada produk dengan stok rendah.</p>
        @else
            <div class="row">
                @foreach($lowStockProducts as $product)
                    @php $totalQty = $product->stockHistories->sum('qty'); @endphp
                    <div class="col-md-4 mb-3">
                        <div class="card h-100 shadow-sm text-center">
                            <img src="{{ asset('/storage/products/' . $product->image) }}"
                                 alt="{{ $product->name }}"
                                 class="card-img-top mx-auto d-block mt-3"
                                 style="width: 80px; height: 80px; object-fit: cover;">
                            <div class="card-body">
                                <h6 class="card-title">{{ $product->name }}</h6>
                                <p class="mb-1">Total Stok: <strong>{{ $totalQty }}</strong></p>
                                <ul class="text-muted small list-unstyled mt-2">
                                    @foreach($product->stockHistories as $batch)
                                        <li>Batch: exp {{ \Carbon\Carbon::parse($batch->expired_date)->format('d/m/Y') }},
                                            qty: {{ $batch->qty }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    {{-- 🛑 Produk dengan Batch Kosong --}}
    <div class="bg-white p-4 rounded shadow-sm mb-4">
        <h4 class="mb-3">🛑 Produk dengan Batch Kosong (Qty = 0)</h4>
        @if($emptyBatchProducts->isEmpty())
            <p class="text-muted">Tidak ada batch dengan stok 0.</p>
        @else
            <div class="row">
                @foreach($emptyBatchProducts as $product)
                    @foreach($product->stockHistories as $batch)
                        @if($batch->qty == 0)
                        <div class="col-md-4 mb-3">
                            <div class="card h-100 shadow-sm text-center">
                                <img src="{{ asset('/storage/products/' . $product->image) }}"
                                     alt="{{ $product->name }}"
                                     class="card-img-top mx-auto d-block mt-3"
                                     style="width: 80px; height: 80px; object-fit: cover;">
                                <div class="card-body">
                                    <h6 class="card-title">{{ $product->name }}</h6>
                                    <p class="mb-1">Batch Expired:
                                        <strong>{{ \Carbon\Carbon::parse($batch->expired_date)->translatedFormat('d F Y') }}</strong></p>
                                    <p class="text-danger mb-0">Stok: 0</p>
                                </div>
                            </div>
                        </div>
                        @endif
                    @endforeach
                @endforeach
            </div>
        @endif
    </div>

    {{-- ⏳ Produk Kadaluarsa dan Akan Kadaluarsa --}}
    <div class="bg-white p-4 rounded shadow-sm">
        <h4 class="mb-3">⏳ Produk Kadaluarsa & Akan Kadaluarsa (≤ 15 hari)</h4>

        @php $foundExpiring = false; @endphp

        <div class="row">
            @foreach($expiringProducts as $product)
                @foreach($product->stockHistories as $batch)
                    @php
                        $expiredDate = \Carbon\Carbon::parse($batch->expired_date)->startOfDay();
                        $daysRemaining = now()->startOfDay()->diffInDays($expiredDate, false);
                        $isExpired = $daysRemaining < 0;
                    @endphp

                    @if($daysRemaining <= 15)
                        @php $foundExpiring = true; @endphp

                        <div class="col-md-4 mb-3">
                            <div class="card h-100 shadow-sm text-center">
                                <img src="{{ asset('/storage/products/' . $product->image) }}"
                                     alt="{{ $product->name }}"
                                     class="card-img-top mx-auto d-block mt-3"
                                     style="width: 80px; height: 80px; object-fit: cover;">
                                <div class="card-body">
                                    <h6 class="card-title">{{ $product->name }}</h6>
                                    <p class="mb-1">
                                        Tanggal Exp:
                                        <strong>{{ $expiredDate->locale('id')->translatedFormat('d F Y') }}</strong>
                                    </p>
                                    <span class="badge {{ $isExpired ? 'bg-danger' : 'bg-warning text-dark' }}">
                                        {{ $isExpired ? 'Sudah Kadaluarsa' : 'Tersisa ' . abs($daysRemaining) . ' Hari' }}
                                    </span>
                                    <p class="text-muted mt-2 mb-0">Stok batch ini: {{ $batch->qty }}</p>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            @endforeach
        </div>

        @if(!$foundExpiring)
            <p class="text-muted">Tidak ada produk yang kadaluarsa atau mendekati kadaluarsa.</p>
        @endif
    </div>
</x-app-layout>
