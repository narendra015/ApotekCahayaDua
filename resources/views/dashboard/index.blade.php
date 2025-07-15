<x-app-layout>
    {{-- Page Title --}}
    <x-page-title>Dashboard</x-page-title>

    <div class="row mb-3">
        {{-- Menampilkan informasi jumlah data --}}
        <div class="col-lg-6 col-xl-3">
            <div class="bg-white rounded-2 shadow-sm p-4 mb-4">
                <div class="d-flex align-items-center">
                    <div class="me-4">
                        <i class="ti ti-category fs-1 bg-primary-2 text-white rounded-2 p-2"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-1">Kategori</p>
                        <h5 class="fw-bold mb-0">{{ $totalCategory }}</h5>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 col-xl-3">
            <div class="bg-white rounded-2 shadow-sm p-4 mb-4">
                <div class="d-flex align-items-center">
                    <div class="me-4">
                        <i class="ti ti-copy fs-1 bg-success text-white rounded-2 p-2"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-1">Produk</p>
                        <h5 class="fw-bold mb-0">{{ $totalProduct }}</h5>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 col-xl-3">
            <div class="bg-white rounded-2 shadow-sm p-4 mb-4">
                <div class="d-flex align-items-center">
                    <div class="me-4">
                        <i class="ti ti-users fs-1 bg-warning text-white rounded-2 p-2"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-1">Pelanggan</p>
                        <h5 class="fw-bold mb-0">{{ $totalCustomer }}</h5>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 col-xl-3">
            <div class="bg-white rounded-2 shadow-sm p-4 mb-4">
                <div class="d-flex align-items-center">
                    <div class="me-4">
                        <i class="ti ti-folders fs-1 bg-info text-white rounded-2 p-2"></i>
                    </div>
                    <div>
                        <p class="text-muted mb-1">Transaksi</p>
                        <h5 class="fw-bold mb-0">{{ $totalTransaction }}</h5>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2 shadow-sm p-4 mb-5">
        <h6 class="mb-3 text-center">
            <i class="ti ti-folder-star fs-5 me-1"></i> 5 Produk Terlaris
        </h6>
        <div class="table-responsive">
            <table class="table table-bordered table-striped text-center">
                <thead>
                    <th>Gambar</th>
                    <th>Nama</th>
                    <th>Harga</th>
                    <th>Terjual</th>
                </thead>
                <tbody>
                    @forelse ($transactions as $transaction)
                        <tr>
                            <td><img src="{{ asset('/storage/products/'.$transaction->product->image) }}" class="img-thumbnail" width="80"></td>
                            <td>{{ $transaction->product->name }}</td>
                            <td>{{ 'Rp ' . number_format($transaction->product->price, 0, '', '.') }}</td>
                            <td>{{ $transaction->transactions_sum_qty }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4">Tidak ada data tersedia.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    {{-- Stok Produk yang Hampir Habis --}}
    <div class="bg-white rounded-2 shadow-sm p-4 mb-5">
        <h6 class="mb-3 text-center">
            <i class="ti ti-folder-star fs-5 me-1"></i> Stok Produk yang Hampir Habis
        </h6>
        <div class="table-responsive">
            <table class="table table-bordered table-striped text-center">
                <thead>
                    <th>Gambar</th>
                    <th>Nama</th>
                    <th>Harga</th>
                    <th>Stok Tersisa</th>
                </thead>
                <tbody>
                    @forelse ($productsWithLowStock as $product)
                        <tr>
                            <td><img src="{{ asset('/storage/products/'.$product->image) }}" class="img-thumbnail" width="80"></td>
                            <td>{{ $product->name }}</td>
                            <td>{{ 'Rp ' . number_format($product->price, 0, '', '.') }}</td>
                            <td>{{ $product->qty }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4">Tidak ada Stok Produk yang Hampir Habis.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    {{-- Produk yang akan kedaluwarsa dalam 1-15 hari --}}
    <div class="bg-white rounded-2 shadow-sm p-4 mb-5">
        <h6 class="mb-3 text-center">
            <i class="ti ti-calendar-time fs-5 me-1"></i> Produk yang Akan Kedaluwarsa dalam 1 hingga 15 Hari
        </h6>
        <div class="table-responsive">
            <table class="table table-bordered table-striped text-center">
                <thead>
                    <th>Gambar</th>
                    <th>Nama</th>
                    <th>Harga</th>
                    <th>Tanggal Kedaluwarsa</th>
                </thead>
                <tbody>
                    @forelse ($productsExpiringSoon as $product)
                        @php
                            // Menghitung selisih hari antara tanggal kadaluarsa dan hari ini
                            $daysRemaining = now()->diffInDays($product->expired_date);
                        @endphp
                        <tr>
                            <td><img src="{{ asset('/storage/products/'.$product->image) }}" class="img-thumbnail" width="80"></td>
                            <td>{{ $product->name }}</td>
                            <td>{{ 'Rp ' . number_format($product->price, 0, '', '.') }}</td>
                            <td>
                                <span class="badge 
                                    @if ($daysRemaining <= 15 && $daysRemaining > 0)
                                        bg-warning  {{-- Kuning jika 1-15 hari --}}
                                    @elseif ($daysRemaining <= 0)
                                        bg-danger   {{-- Merah jika sudah lewat --}}
                                    @endif
                                ">
                                    {{ \Carbon\Carbon::parse($product->expired_date)->locale('id')->isoFormat('D MMMM YYYY') }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center">Tidak ada produk yang akan kedaluwarsa dalam 1 hingga 15 hari.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

</x-app-layout>
