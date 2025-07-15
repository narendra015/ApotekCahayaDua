<x-app-layout>
    {{-- Judul Halaman --}}
    <x-page-title>Tambah Produk</x-page-title>

    <div class="bg-white rounded-2 shadow-sm p-4 mb-5">
        <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row">
                <!-- Bagian Kiri: Kategori, Satuan, Nama, Deskripsi, Harga -->
                <div class="col-lg-7">
                    <!-- Kategori -->
                    <div class="mb-3">
                        <label class="form-label">Kategori <span class="text-danger">*</span></label>
                        <select name="category_id" class="form-select select2-single @error('category_id') is-invalid @enderror" autocomplete="off">
                            <option selected disabled value="">- Pilih kategori -</option>
                            @foreach ($categories as $category)
                                <option {{ old('category_id') == $category->id ? 'selected' : '' }} value="{{ $category->id }}">{{ $category->name }}</option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <div class="alert alert-danger mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Satuan -->
                    <div class="mb-3">
                        <label class="form-label">Satuan <span class="text-danger">*</span></label>
                        <select name="unit_id" class="form-select select2-single @error('unit_id') is-invalid @enderror" autocomplete="off">
                            <option selected disabled value="">- Pilih satuan -</option>
                            @foreach ($units as $unit)
                                <option {{ old('unit_id') == $unit->id ? 'selected' : '' }} value="{{ $unit->id }}">{{ $unit->name }}</option>
                            @endforeach
                        </select>
                        @error('unit_id')
                            <div class="alert alert-danger mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Nama Produk -->
                    <div class="mb-3">
                        <label class="form-label">Nama <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" autocomplete="off">
                        @error('name')
                            <div class="alert alert-danger mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Deskripsi -->
                    <div class="mb-3">
                        <label class="form-label">Deskripsi <span class="text-danger">*</span></label>
                        <textarea name="description" rows="5" class="form-control @error('description') is-invalid @enderror" autocomplete="off">{{ old('description') }}</textarea>
                        @error('description')
                            <div class="alert alert-danger mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <!-- Harga -->
                    <div class="mb-3">
                        <label class="form-label">Harga <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">Rp</span>
                            <input type="text" name="price" class="form-control mask-number @error('price') is-invalid @enderror" value="{{ old('price') }}" autocomplete="off">
                        </div>
                        @error('price')
                            <div class="alert alert-danger mt-2">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Bagian Kanan: Jumlah, Tanggal Kedaluwarsa, Pemasok, Gambar -->
                <div class="col-lg-5">
                     <!-- Pemasok -->
                     <div class="mb-3">
                        <label class="form-label">Pemasok <span class="text-danger">*</span></label>
                        <select name="supplier_id" class="form-select select2-single @error('supplier_id') is-invalid @enderror" autocomplete="off">
                            <option selected disabled value="">- Pilih pemasok -</option>
                            @foreach ($suppliers as $supplier)
                                <option {{ old('supplier_id') == $supplier->id ? 'selected' : '' }} value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                        @error('supplier_id')
                            <div class="alert alert-danger mt-2">{{ $message }}</div>
                        @enderror
                    </div>
                    
                    <div class="row">
                        <!-- Jumlah dan Tanggal Kedaluwarsa dalam baris yang sama -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Jumlah <span class="text-danger">*</span></label>
                            <input type="number" name="qty" class="form-control @error('qty') is-invalid @enderror" value="{{ old('qty') }}" autocomplete="off">
                            @error('qty')
                                <div class="alert alert-danger mt-2">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="col-md-6 mb-3">
                            <label class="form-label">Tanggal Kedaluwarsa <span class="text-danger">*</span></label>
                            <input type="date" name="expired_date" class="form-control @error('expired_date') is-invalid @enderror" value="{{ old('expired_date') }}" autocomplete="off">
                            @error('expired_date')
                                <div class="alert alert-danger mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>

                    <!-- Gambar Produk -->
                    <div class="mb-3">
                        <label class="form-label">Gambar <span class="text-danger">*</span></label>
                        <input type="file" accept=".jpg, .jpeg, .png" name="image" id="image" class="form-control @error('image') is-invalid @enderror" autocomplete="off">
                        @error('image')
                            <div class="alert alert-danger mt-2">{{ $message }}</div>
                        @enderror

                        <div class="mt-4">
                            <img id="imagePreview" src="{{ asset('images/no-image.svg') }}" class="img-thumbnail rounded-4 shadow-sm" width="47%" alt="Image">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tombol Simpan dan Batal -->
            <div class="pt-4 pb-2 mt-5 border-top">
                <div class="d-grid gap-3 d-sm-flex justify-content-md-start pt-1">
                    <button type="submit" class="btn btn-primary py-2 px-4">Simpan</button>
                    <a href="{{ route('products.index') }}" class="btn btn-secondary py-2 px-3">Kembali</a>
                </div>
            </div>
        </form>
    </div>

    <!-- Skrip Preview Gambar -->
    <script>
        document.getElementById('image').addEventListener('change', function(event) {
            var reader = new FileReader();
            reader.onload = function() {
                var previewImage = document.getElementById('imagePreview');
                previewImage.src = reader.result;
            };
            reader.readAsDataURL(event.target.files[0]);
        });
    </script>
</x-app-layout>
