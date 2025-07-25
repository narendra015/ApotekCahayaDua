<x-app-layout>
    {{-- Judul Halaman --}}
    <x-page-title>Edit Produk</x-page-title>

    <div class="bg-white rounded-2 shadow-sm p-4 mb-5">
        <form action="{{ route('products.update', $product->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <div class="row">
                <!-- Kolom Kiri -->
                <div class="col-lg-7">
                    {{-- Kategori --}}
                    <div class="mb-3">
                        <label class="form-label">Kategori <span class="text-danger">*</span></label>
                        <select name="category_id" class="form-select @error('category_id') is-invalid @enderror">
                            <option disabled value="">- Pilih kategori -</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}"
                                        {{ old('category_id', $product->category_id) == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <div class="alert alert-danger mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Satuan --}}
                    <div class="mb-3">
                        <label class="form-label">Satuan <span class="text-danger">*</span></label>
                        <select name="unit_id" class="form-select select2-single @error('unit_id') is-invalid @enderror">
                            <option selected disabled value="">- Pilih satuan -</option>
                            @foreach ($units as $unit)
                                <option value="{{ $unit->id }}"
                                        {{ old('unit_id', $product->unit_id) == $unit->id ? 'selected' : '' }}>
                                    {{ $unit->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('unit_id')
                            <div class="alert alert-danger mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Nama --}}
                    <div class="mb-3">
                        <label class="form-label">Nama <span class="text-danger">*</span></label>
                        <input type="text" name="name"
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $product->name) }}">
                        @error('name')
                            <div class="alert alert-danger mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    {{-- Deskripsi --}}
                    <div class="mb-3">
                        <label class="form-label">Deskripsi <span class="text-danger">*</span></label>
                        <textarea name="description" rows="5"
                                  class="form-control @error('description') is-invalid @enderror">{{ old('description', $product->description) }}</textarea>
                        @error('description')
                            <div class="alert alert-danger mt-2">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                <!-- Kolom Kanan -->
                <div class="col-lg-5">
                    {{-- Pemasok & Expired --}}
                        <div class="mb-3">
                            <label class="form-label">Pemasok <span class="text-danger">*</span></label>
                            <select name="supplier_id"
                                    class="form-select select2-single @error('supplier_id') is-invalid @enderror">
                                <option selected disabled value="">- Pilih pemasok -</option>
                                @foreach ($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}"
                                            {{ old('supplier_id', $product->supplier_id) == $supplier->id ? 'selected' : '' }}>
                                        {{ $supplier->name }}
                                    </option>
                                @endforeach
                            </select>
                            @error('supplier_id')
                                <div class="alert alert-danger mt-2">{{ $message }}</div>
                            @enderror
                        </div>
                    {{-- Gambar --}}
                    <div class="mb-3">
                        <label class="form-label">Gambar <span class="text-danger">*</span></label>
                        <input type="file" accept=".jpg,.jpeg,.png"
                               name="image" id="image"
                               class="form-control @error('image') is-invalid @enderror">
                        @error('image')
                            <div class="alert alert-danger mt-2">{{ $message }}</div>
                        @enderror

                        {{-- Preview --}}
                        <div class="mt-4">
                            <img id="imagePreview"
                                 src="{{ asset('storage/products/'.$product->image) }}"
                                 class="img-thumbnail rounded-4 shadow-sm"
                                 width="53%" alt="Gambar">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Tombol --}}
            <div class="pt-4 pb-2 mt-5 border-top">
                <div class="d-grid gap-3 d-sm-flex justify-content-md-start pt-1">
                    <button type="submit" class="btn btn-primary py-2 px-4">Perbarui</button>
                    <a href="{{ route('products.index') }}" class="btn btn-secondary py-2 px-3">Kembali</a>
                </div>
            </div>
        </form>
    </div>

    {{-- Cleave.js untuk format harga --}}
    <script src="https://cdn.jsdelivr.net/npm/cleave.js@1.6.0/dist/cleave.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const cleavePrice = new Cleave('#price_display', {
                numeral: true,
                numeralThousandsGroupStyle: 'thousand',
                delimiter: '.',
                numeralDecimalMark: ',',
                numeralDecimalScale: 0
            });

            const hiddenPrice = document.getElementById('price');
            hiddenPrice.value = cleavePrice.getRawValue(); // Set ulang saat load awal

            document.getElementById('price_display').addEventListener('input', () => {
                hiddenPrice.value = cleavePrice.getRawValue();
            });
        });
    </script>
</x-app-layout>
