<x-app-layout>
    {{-- Judul Halaman --}}
    <x-page-title>Detail Produk</x-page-title>

    <div class="bg-white rounded-2 shadow-sm p-4 mb-5">
        {{-- Menampilkan detail produk --}}
        <div class="row flex-lg-row align-items-center g-5">
            <div class="col-lg-3">
                <img src="{{ asset('storage/products/' . $product->image) }}"
                     class="d-block mx-lg-auto img-thumbnail rounded-4 shadow-sm"
                     alt="Gambar Produk" loading="lazy">
            </div>
            <div class="col-lg-9">
                <h4>{{ $product->name }}</h4>
                <p class="text-muted">
                    <i class="ti ti-tag me-1"></i> {{ $product->category->name }}
                </p>
                <p style="text-align: justify">{{ $product->description }}</p>

                {{-- FIFO Harga & Tanggal Kadaluarsa --}}
                @php
                    $oldestStock = $product->stockHistories->where('qty', '>', 0)->sortBy('expired_date')->first();
                    $totalQty = $product->stockHistories->sum('qty');
                @endphp

                <p class="text-success fw-bold">
                    {{ 'Rp ' . number_format($oldestStock?->price ?? 0, 0, '', '.') . ' / ' . ($product->unit?->name ?? 'Tidak ada satuan') }}
                </p>

                <p><strong>Tanggal Kedaluwarsa:</strong>
                    {{ $oldestStock?->expired_date 
                        ? \Carbon\Carbon::parse($oldestStock->expired_date)->locale('id')->translatedFormat('d F Y') 
                        : '-' }}
                </p>

                <p><strong>Jumlah Total Stok:</strong> {{ $totalQty }}</p>
                <p><strong>Pemasok:</strong> {{ $product->supplier->name ?? 'Tidak ada pemasok' }}</p>
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
                                    $expired = \Carbon\Carbon::parse($history->expired_date)->isPast();
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
                                            <form action="{{ route('stock-histories.destroy', $history->id) }}"
                                                  method="POST" class="d-inline"
                                                  onsubmit="return confirm('Yakin ingin menghapus batch stok ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="ti ti-trash"></i> Hapus
                                                </button>
                                            </form>
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
