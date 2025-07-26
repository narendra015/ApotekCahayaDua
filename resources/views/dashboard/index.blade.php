<x-app-layout>
    {{-- Judul Halaman --}}
    <x-page-title>Dashboard</x-page-title>

    {{-- Kartu Statistik --}}
    <div class="row mb-3">
        @php
            $cards = [
                ['title' => 'Kategori', 'count' => $totalCategory, 'icon' => 'ti-category', 'color' => 'primary-2'],
                ['title' => 'Produk', 'count' => $totalProduct, 'icon' => 'ti-copy', 'color' => 'success'],
                ['title' => 'Pelanggan', 'count' => $totalCustomer, 'icon' => 'ti-users', 'color' => 'warning'],
                ['title' => 'Transaksi', 'count' => $totalTransaction, 'icon' => 'ti-folders', 'color' => 'info'],
            ];
        @endphp

        @foreach ($cards as $card)
            <div class="col-lg-6 col-xl-3">
                <div class="bg-white rounded-2 shadow-sm p-4 mb-4">
                    <div class="d-flex align-items-center">
                        <div class="me-4">
                            <i class="ti {{ $card['icon'] }} fs-1 bg-{{ $card['color'] }} text-white rounded-2 p-2"></i>
                        </div>
                        <div>
                            <p class="text-muted mb-1">{{ $card['title'] }}</p>
                            <h5 class="fw-bold mb-0">{{ $card['count'] }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- 5 Produk Terlaris --}}
    <div class="bg-white rounded-2 shadow-sm p-4 mb-5">
        <h6 class="mb-3 text-center">
            <i class="ti ti-folder-star fs-5 me-1"></i> 5 Produk Terlaris
        </h6>
        <div class="table-responsive">
            <table class="table table-bordered table-striped text-center">
                <thead><tr><th>Gambar</th><th>Nama</th><th>Harga</th><th>Terjual</th></tr></thead>
                <tbody>
                    @forelse ($transactions as $t)
                        <tr>
                            <td><img src="{{ asset('/storage/products/' . $t->product->image) }}" width="80" class="img-thumbnail"></td>
                            <td>{{ $t->product->name }}</td>
                            <td>Rp {{ number_format($t->product->price, 0, '', '.') }}</td>
                            <td>{{ $t->transactions_sum_qty }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center">Tidak ada data tersedia.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Stok Produk yang Hampir Habis --}}
    <div class="bg-white rounded-2 shadow-sm p-4 mb-5">
        <h6 class="mb-3 text-center">
            <i class="ti ti-box fs-5 me-1"></i> Stok Produk yang Hampir Habis
        </h6>
        <div class="table-responsive">
            <table class="table table-bordered table-striped text-center">
                <thead><tr><th>Gambar</th><th>Nama</th><th>Harga</th><th>Total Stok</th></tr></thead>
                <tbody>
                    @forelse ($productsWithLowStock as $p)
                        <tr>
                            <td><img src="{{ asset('/storage/products/' . $p->image) }}" width="80" class="img-thumbnail"></td>
                            <td>{{ $p->name }}</td>
                            <td>Rp {{ number_format($p->price, 0, '', '.') }}</td>
                            <td>{{ $p->stockHistories->sum('qty') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center">Tidak ada stok produk yang hampir habis.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Produk dengan Batch Kosong --}}
    <div class="bg-white rounded-2 shadow-sm p-4 mb-5">
        <h6 class="mb-3 text-center">
            <i class="ti ti-box-off fs-5 me-1"></i> Produk dengan Batch Kosong (Qty = 0)
        </h6>
        <div class="table-responsive">
            <table class="table table-bordered table-striped text-center">
                <thead><tr><th>Gambar</th><th>Nama Produk</th><th>Harga Batch</th><th>Tgl Expired</th></tr></thead>
                <tbody>
                    @forelse ($emptyBatchProducts as $product)
                        @foreach ($product->stockHistories as $batch)
                            <tr>
                                <td><img src="{{ asset('/storage/products/' . $product->image) }}" width="80" class="img-thumbnail"></td>
                                <td>{{ $product->name }}</td>
                                <td>Rp {{ number_format($batch->price, 0, '', '.') }}</td>
                                <td>{{ \Carbon\Carbon::parse($batch->expired_date)->locale('id')->isoFormat('D MMMM YYYY') }}</td>
                            </tr>
                        @endforeach
                    @empty
                        <tr><td colspan="4" class="text-center">Tidak ada produk dengan batch kosong.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Produk dengan Batch yang akan Kedaluwarsa --}}
    <div class="bg-white rounded-2 shadow-sm p-4 mb-5">
        <h6 class="mb-3 text-center">
            <i class="ti ti-calendar-time fs-5 me-1"></i> Produk yang Akan Kedaluwarsa dalam 1-15 Hari
        </h6>
        <div class="table-responsive">
            <table class="table table-bordered table-striped text-center">
                <thead>
                    <tr>
                        <th>Gambar</th>
                        <th>Nama Produk</th>
                        <th>Harga Batch</th>
                        <th>Tgl Expired</th>
                        <th>Sisa Hari</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($productsExpiringSoon as $product)
                        @foreach ($product->stockHistories as $batch)
                            @php
                                $exp = \Carbon\Carbon::parse($batch->expired_date)->startOfDay();
                                $today = \Carbon\Carbon::now()->startOfDay();
                                $daysLeft = $today->diffInDays($exp, false);
                            @endphp
                            <tr>
                                <td>
                                    <img src="{{ asset('/storage/products/' . $product->image) }}" width="80" class="img-thumbnail">
                                </td>
                                <td>{{ $product->name }}</td>
                                <td>Rp {{ number_format($batch->price, 0, '', '.') }}</td>
                                <td>
                                    <span class="badge {{ $daysLeft < 0 ? 'bg-danger' : 'bg-warning' }}">
                                        {{ $exp->locale('id')->isoFormat('D MMMM YYYY') }}
                                    </span>
                                </td>
                                <td>
                                    {{ $daysLeft < 0 ? 'Sudah lewat' : $daysLeft . ' hari' }}
                                </td>
                            </tr>
                        @endforeach
                    @empty
                        <tr><td colspan="5" class="text-center">Tidak ada produk yang mendekati kadaluarsa.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
