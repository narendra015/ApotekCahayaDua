<x-app-layout>
    {{-- Judul Halaman --}}
    <x-page-title>Tambah Stok Produk</x-page-title>

    <div class="bg-white rounded-2 shadow-sm p-4 mb-5">
        <form action="{{ route('products.store-stock', $product->id) }}" method="POST">
            @csrf
            <div class="row">
                <!-- Kiri: Info Produk -->
                <div class="col-lg-7">
                    {{-- Nama Produk --}}
                    <div class="mb-3">
                        <label class="form-label">Nama Produk</label>
                        <input type="text" class="form-control" value="{{ $product->name }}" readonly>
                    </div>

                    {{-- Kategori --}}
                    <div class="mb-3">
                        <label class="form-label">Kategori</label>
                        <input type="text" class="form-control" value="{{ $product->category->name }}" readonly>
                    </div>

                    {{-- Satuan --}}
                    <div class="mb-3">
                        <label class="form-label">Satuan</label>
                        <input type="text" class="form-control" value="{{ $product->unit->name ?? '-' }}" readonly>
                    </div>

                    {{-- Deskripsi --}}
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea class="form-control" rows="5" readonly>{{ $product->description }}</textarea>
                    </div>
                    {{-- Harga Baru --}}
                    <div class="mb-3">
                        <label class="form-label">Harga Baru <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" name="price" class="form-control mask-number @error('price') is-invalid @enderror"
                                   value="{{ old('price', number_format($product->price, 0, '', '.')) }}" autocomplete="off" required>
                        </div>
                        @error('price')
                            <div class="alert alert-danger mt-2">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Kanan: Input Stok Baru -->
                <div class="col-lg-5">
                    <div class="row">
                    {{-- Jumlah --}}
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Jumlah <span class="text-danger">*</span></label>
                            <input type="number" name="qty" class="form-control @error('qty') is-invalid @enderror" value="{{ old('qty') }}" min="1" required>
                            @error('qty')
                                <div class="alert alert-danger mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                    {{-- Tanggal Kadaluarsa --}}
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal Kadaluarsa <span class="text-danger">*</span></label>
                            <input type="date" name="expired_date" class="form-control @error('expired_date') is-invalid @enderror"
                                value="{{ old('expired_date') }}" required>
                            @error('expired_date')
                                <div class="alert alert-danger mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    {{-- Pemasok --}}
                    <div class="mb-3">
                        <label class="form-label">Pemasok</label>
                        <input type="text" class="form-control" value="{{ $product->supplier->name ?? '-' }}" readonly>
                    </div>

                    {{-- Gambar Produk --}}
                    <div class="mb-3">
                        <label class="form-label">Gambar Produk <span class="text-danger">*</span></label>
                        <div class="text-center">
                            <img src="{{ asset('storage/products/'.$product->image) }}"
                                class="img-thumbnail rounded-4 shadow-sm mt-2"
                                width="60%" alt="Gambar Produk">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tombol -->
            <div class="pt-4 pb-2 mt-5 border-top">
                <div class="d-grid gap-3 d-sm-flex justify-content-md-start pt-1">
                    <button type="submit" class="btn btn-success py-2 px-4">Tambah Stok</button>
                    <a href="{{ route('products.index') }}" class="btn btn-secondary py-2 px-3">Kembali</a>
                </div>
            </div>
        </form>
    </div>

    @push('scripts')
        {{-- Script untuk format harga --}}
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.mask/1.14.16/jquery.mask.min.js"></script>
        <script>
            $(document).ready(function(){
                $('.mask-number').mask('000.000.000.000', {reverse: true});
            });
        </script>
    @endpush
</x-app-layout>
