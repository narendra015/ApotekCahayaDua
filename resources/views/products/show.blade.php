<x-app-layout>
    {{-- Judul Halaman --}}
    <x-page-title>Detail Produk</x-page-title>

    <div class="bg-white rounded-2 shadow-sm p-4 mb-5">
        {{-- Menampilkan detail produk --}}
        <div class="row flex-lg-row align-items-center g-5">
            <div class="col-lg-3">
                @php
                    $productImage = $product->image
                        ? asset('storage/products/' . $product->image)
                        : asset('images/no-image.svg');
                @endphp
                <img src="{{ $productImage }}"
                     class="d-block mx-lg-auto img-thumbnail rounded-4 shadow-sm"
                     alt="Gambar Produk" loading="lazy">
            </div>

            <div class="col-lg-9">
                <h4>{{ $product->name }}</h4>

                <p class="text-muted mb-2">
                    <i class="ti ti-tag me-1"></i> {{ $product->category->name ?? 'Tidak ada kategori' }}
                </p>

                <p style="text-align: justify">{{ $product->description }}</p>

                {{-- FIFO Harga & Tanggal Kedaluwarsa --}}
                @php
                    $oldestStock = $product->stockHistories->where('qty', '>', 0)->sortBy('expired_date')->first();
                    $totalQty    = $product->stockHistories->sum('qty');

                    $drugMeta = [
                        'obat_bebas' => [
                            'label' => 'Obat Bebas',
                            'icon'  => asset('images/obat-bebas.png'),
                        ],
                        'obat_bebas_terbatas' => [
                            'label' => 'Obat Bebas Terbatas',
                            'icon'  => asset('images/obat-bebas-terbatas.png'),
                        ],
                        'obat_keras' => [
                            'label' => 'Obat Keras',
                            'icon'  => asset('images/obat-keras.png'),
                        ],
                        'obat_narkotika' => [
                            'label' => 'Obat Narkotika',
                            'icon'  => asset('images/obat-narkotika.png'),
                        ],
                        'obat_herbal' => [
                            'label' => 'Obat Herbal',
                            'icon'  => asset('images/obat-herbal.png'),
                        ],
                        'obat_herbal_terstandar' => [
                            'label' => 'Obat Herbal Terstandar',
                            'icon'  => asset('images/obat-herbal-terstandar.png'),
                        ],
                        'fitofarmaka' => [
                            'label' => 'Fitofarmaka',
                            'icon'  => asset('images/obat-fitofarmaka.png'),
                        ],
                    ];

                    $drugKey = trim((string) $product->drug_class);
                    $meta    = $drugMeta[$drugKey] ?? null;
                @endphp

                <p class="text-success fw-bold mb-2">
                    {{ 'Rp ' . number_format($oldestStock?->price ?? 0, 0, '', '.') . ' / ' . ($product->unit?->name ?? 'Tidak ada satuan') }}
                </p>

                <p class="mb-1"><strong>Tanggal Kedaluwarsa:</strong>
                    {{ $oldestStock?->expired_date
                        ? \Carbon\Carbon::parse($oldestStock->expired_date)->locale('id')->translatedFormat('d F Y')
                        : '-' }}
                </p>

                <p class="mb-1"><strong>Jumlah Total Stok:</strong> {{ $totalQty }}</p>

                <p class="mb-1"><strong>Pemasok:</strong> {{ $product->supplier->name ?? 'Tidak ada pemasok' }}</p>

                {{-- Golongan Obat (dengan ikon jika tersedia) --}}
                <p class="mb-0"><strong>Golongan Obat:</strong>
                    @if($meta)
                        {{ $meta['label'] }}
                        @if(!empty($meta['icon']))
                            <img src="{{ $meta['icon'] }}" alt="{{ $meta['label'] }}" width="20" class="me-2 align-text-bottom">
                        @endif
                    @else
                        -
                    @endif
                </p>
            </div>
        </div>

        {{-- Tabel Riwayat Penambahan Stok --}}
        <div class="pt-5 mt-5 border-top">
            <h5 class="mb-3">Riwayat Penambahan Stok</h5>
            @if($product->stockHistories->count())
                <div class="table-responsive">
                    <table class="table table-bordered text-center">
                        <thead class="table-light">
                            <tr>
                                <th>No.</th>
                                <th>Harga</th>
                                <th>Jumlah</th>
                                <th>Tanggal Kadaluarsa</th>
                                <th>Waktu Ditambahkan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($product->stockHistories->sortBy('created_at') as $index => $history)
                                @php
                                    $expired  = \Carbon\Carbon::parse($history->expired_date)->isPast();
                                    $emptyQty = $history->qty <= 0;
                                @endphp
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>Rp {{ number_format($history->price, 0, '', '.') }}</td>
                                    <td>{{ $history->qty }}</td>
                                    <td>{{ \Carbon\Carbon::parse($history->expired_date)->locale('id')->translatedFormat('d F Y') }}</td>
                                    <td>{{ $history->created_at->locale('id')->translatedFormat('d F Y H:i') }}</td>
                                    <td>
                                        <a href="{{ route('stock-histories.edit', $history->id) }}" class="btn btn-sm btn-primary me-1">
                                            <i class="ti ti-edit"></i> Edit
                                        </a>

                                        @if($expired || $emptyQty)
                                            <!-- Tombol Trigger Modal -->
                                            <button type="button" class="btn btn-sm btn-danger" data-bs-toggle="modal" data-bs-target="#modalDeleteStockHistory{{ $history->id }}">
                                                <i class="ti ti-trash"></i> Hapus
                                            </button>

                                            <!-- Modal Hapus Batch Stok -->
                                            <div class="modal fade" id="modalDeleteStockHistory{{ $history->id }}" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h1 class="modal-title fs-5">
                                                                <i class="ti ti-trash me-2"></i> Hapus Riwayat Stok
                                                            </h1>
                                                        </div>
                                                        <div class="modal-body">
                                                            Apakah Anda yakin ingin menghapus batch stok dari produk
                                                            <span class="fw-bold">{{ $product->name }}</span>
                                                            dengan ID batch <span class="fw-bold">#{{ $history->id }}</span>?
                                                        </div>
                                                        <div class="modal-footer">
                                                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                            <form action="{{ route('stock-histories.destroy', $history->id) }}" method="POST">
                                                                @csrf
                                                                @method('DELETE')
                                                                <button type="submit" class="btn btn-danger">Ya, hapus!</button>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="alert alert-warning text-center mb-0">
                    Belum ada data penambahan stok untuk produk ini.
                </div>
            @endif
        </div>

        {{-- Tombol Kembali --}}
        <div class="pt-4 pb-2 mt-5 border-top">
            <div class="d-grid gap-3 d-sm-flex justify-content-md-start pt-1">
                <a href="{{ route('products.index') }}" class="btn btn-secondary py-2 px-4">Tutup</a>
            </div>
        </div>
    </div>
</x-app-layout>
