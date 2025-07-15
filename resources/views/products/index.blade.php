<x-app-layout>
    {{-- Judul Halaman --}}
    <x-page-title>Produk</x-page-title>

    {{-- Penjelasan Warna Badge --}}
    <div class="mb-3">
        <p><strong>Penjelasan Status Kedaluwarsa:</strong></p>
        <table style="margin: auto; border-collapse: collapse;">
            <thead>
                <tr>
                    <th style="text-align: center; padding: 10px; border-right: 2px solid black; border-left: 2px solid black;">
                        <span class="badge bg-success me-2">Masih Aman</span>
                    </th>
                    <th style="text-align: center; padding: 10px; border-right: 2px solid black;">
                        <span class="badge bg-warning me-2">Hati-Hati!</span>
                    </th>
                    <th style="text-align: center; padding: 10px; border-right: 2px solid black;">
                        <span class="badge bg-danger me-2">Kadaluarsa</span>
                    </th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="text-align: center; padding: 10px; border-right: 2px solid black; border-left: 2px solid black;">
                        Produk belum mendekati kadaluarsa (lebih dari 15 hari).
                    </td>
                    <td style="text-align: center; padding: 10px; border-right: 2px solid black;">
                        Produk akan kadaluarsa dalam 1-15 hari.
                    </td>
                    <td style="text-align: center; padding: 10px; border-right: 2px solid black;">
                        Produk sudah melewati tanggal kadaluarsa.
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <div class="bg-white rounded-2 shadow-sm p-4 mb-4">
        <div class="row">
            <div class="d-grid d-lg-block col-lg-5 col-xl-6 mb-4 mb-lg-0">
                {{-- Tombol Tambah Produk hanya muncul jika role adalah admin --}}
                @if (Auth::user()->role === 'admin')
                    <a href="{{ route('products.create') }}" class="btn btn-primary py-2 px-3">
                        <i class="ti ti-plus me-2"></i> Tambah Produk
                    </a>
                @endif
            </div>
            <div class="col-lg-7 col-xl-6">
                {{-- Form Pencarian --}}
                <form action="{{ route('products.index') }}" method="GET">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control form-search py-2" value="{{ request('search') }}" placeholder="Cari produk ..." autocomplete="off">
                        <button class="btn btn-primary py-2" type="submit">Cari</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2 shadow-sm pt-4 px-4 pb-3 mb-5">
        {{-- Tabel Produk --}}
        <div class="table-responsive mb-3">
            <table class="table table-bordered table-striped table-hover text-center">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Gambar</th>
                        <th>Nama</th>
                        <th>Harga</th>
                        <th>Kategori</th>
                        <th>Tanggal Kedaluwarsa</th>
                        <th>Jumlah</th>
                        @if (Auth::user()->role === 'admin')
                            <th>Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $index => $product)
                        @php
                            $expiredDate = \Carbon\Carbon::parse($product->expired_date);
                            $daysRemaining = now()->diffInDays($expiredDate, false); // false untuk menghitung mundur (bisa negatif)
                        @endphp
                        <tr id="product-row-{{ $product->id }}">
                            <td>{{ $loop->iteration }}</td>
                            <td>
                                <img src="{{ asset('storage/products/'.$product->image) }}" class="img-thumbnail rounded-4 img-fluid" width="80" alt="Gambar Produk">
                            </td>
                            <td>{{ $product->name }}</td>
                            <td class="text-end">
                                {{ 'Rp ' . number_format($product->price, 0, '', '.') . ' / ' . ($product->unit ? $product->unit->name : 'Tidak ada satuan') }}
                            </td>
                            <td>{{ $product->category->name }}</td>
                            <td>
                                <span class="badge 
                                    @if ($daysRemaining < 0)
                                        bg-danger       {{-- Sudah kadaluarsa --}}
                                    @elseif ($daysRemaining <= 15)
                                        bg-warning      {{-- Akan kadaluarsa dalam 1-15 hari --}}
                                    @else
                                        bg-success      {{-- Masih aman --}}
                                    @endif
                                ">
                                    {{ $expiredDate->locale('id')->isoFormat('D MMMM YYYY') }}
                                </span>
                            </td>
                            <td class="text-center {{ $product->qty <= 5 ? 'text-danger fw-bold' : '' }}">
                                {{ $product->qty }}
                            </td>
                            @if (Auth::user()->role === 'admin')
                                <td class="text-center">
                                    <a href="{{ route('products.show', $product->id) }}" class="btn btn-warning btn-sm" data-bs-tooltip="tooltip" data-bs-title="Detail">
                                        <i class="ti ti-list"></i>
                                    </a>
                                    <a href="{{ route('products.edit', $product->id) }}" class="btn btn-primary btn-sm" data-bs-tooltip="tooltip" data-bs-title="Edit">
                                        <i class="ti ti-edit"></i>
                                    </a>
                                    {{-- Tombol modal hapus data --}}
                                    <button type="button" class="btn btn-danger btn-sm m-1" data-bs-toggle="modal" data-bs-target="#modalDelete{{ $product->id }}" data-bs-tooltip="tooltip" data-bs-title="Hapus"> 
                                        <i class="ti ti-trash"></i>
                                    </button>                             
                                </td>
                            @endif
                        </tr>

                        {{-- Modal hapus data --}}
                        <div class="modal fade" id="modalDelete{{ $product->id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="modalDeleteLabel" aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h1 class="modal-title fs-5">
                                            <i class="ti ti-trash me-2"></i> Hapus Produk
                                        </h1>
                                    </div>
                                    <div class="modal-body">
                                        {{-- Informasi data yang akan dihapus --}}
                                        <p class="mb-2">
                                            Apakah Anda yakin ingin menghapus <span class="fw-bold">{{ $product->name }}</span>?
                                        </p>
                                    </div>
                                    <div class="modal-footer">
                                        <button type="button" class="btn btn-secondary py-2 px-3" data-bs-dismiss="modal">Batal</button>
                                        {{-- Tombol hapus data --}}
                                        <form action="{{ route('products.destroy', $product->id) }}" method="POST">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger py-2 px-3"> Ya, hapus! </button>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>

                    @empty
                        <tr>
                            <td colspan="8" class="text-center">Tidak ada data tersedia.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        {{-- Pagination --}}
        <div class="d-flex justify-content-center mt-3">
            {{ $products->links() }}
        </div>
    </div>
</x-app-layout>
