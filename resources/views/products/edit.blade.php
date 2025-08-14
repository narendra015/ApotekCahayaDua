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

                    <div class="row">
                        {{-- Satuan --}}
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Satuan <span class="text-danger">*</span></label>
                            <select name="unit_id" class="form-select select2-single @error('unit_id') is-invalid @enderror">
                                <option disabled value="">- Pilih satuan -</option>
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

                        <!-- Golongan Obat -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Golongan Obat <span class="text-danger">*</span></label>
                            <select id="drug_class" name="drug_class" class="form-select select2-with-image @error('drug_class') is-invalid @enderror">
                                <option disabled value="">- Pilih golongan obat -</option>
                                <option value="obat_bebas" data-image="{{ asset('images/obat-bebas.png') }}"
                                    {{ old('drug_class', $product->drug_class) == 'obat_bebas' ? 'selected' : '' }}>
                                    Obat Bebas
                                </option>
                                <option value="obat_bebas_terbatas" data-image="{{ asset('images/obat-bebas-terbatas.png') }}"
                                    {{ old('drug_class', $product->drug_class) == 'obat_bebas_terbatas' ? 'selected' : '' }}>
                                    Obat Bebas Terbatas
                                </option>
                                <option value="obat_keras" data-image="{{ asset('images/obat-keras.png') }}"
                                    {{ old('drug_class', $product->drug_class) == 'obat_keras' ? 'selected' : '' }}>
                                    Obat Keras
                                </option>
                                <option value="obat_narkotika" data-image="{{ asset('images/obat-narkotika.png') }}"
                                    {{ old('drug_class', $product->drug_class) == 'obat_narkotika' ? 'selected' : '' }}>
                                    Obat Narkotika
                                </option>
                                <option value="obat_herbal" data-image="{{ asset('images/obat-herbal.png') }}"
                                    {{ old('drug_class', $product->drug_class) == 'obat_herbal' ? 'selected' : '' }}>
                                    Obat herbal
                                </option>
                                <option value="obat_herbal_terstandar" data-image="{{ asset('images/obat-herbal-terstandar.png') }}"
                                    {{ old('drug_class', $product->drug_class) == 'obat_herbal_terstandar' ? 'selected' : '' }}>
                                    Obat Herbal Terstandar
                                </option>
                                <option value="fitofarmaka" data-image="{{ asset('images/obat-fitofarmaka.png') }}"
                                    {{ old('drug_class', $product->drug_class) == 'fitofarmaka' ? 'selected' : '' }}>
                                    Fitofarmaka
                                </option>
                            </select>
                            @error('drug_class')
                                <div class="alert alert-danger mt-2">{{ $message }}</div>
                            @enderror
                        </div>
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
                    {{-- Pemasok --}}
                    <div class="mb-3">
                        <label class="form-label">Pemasok <span class="text-danger">*</span></label>
                        <select name="supplier_id" class="form-select select2-single @error('supplier_id') is-invalid @enderror">
                            <option disabled value="">- Pilih pemasok -</option>
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
                            @php
                                $img = $product->image ? asset('storage/products/'.$product->image) : asset('images/no-image.svg');
                            @endphp
                            <img id="imagePreview"
                                 src="{{ $img }}"
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

    {{-- Script: preview gambar & Select2 dengan ikon --}}
    <script>
        document.getElementById('image').addEventListener('change', function (event) {
            const file = event.target.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = function () {
                document.getElementById('imagePreview').src = reader.result;
            };
            reader.readAsDataURL(file);
        });

        $(function () {
            function formatState(state) {
                if (!state.id) return state.text;
                const img = $(state.element).attr('data-image');
                if (!img) return state.text;
                return $('<span><img src="' + img + '" class="img-fluid me-2" style="width:20px;height:20px;border-radius:50%;"/> ' + state.text + '</span>');
            }
            $('#drug_class').select2({
                templateResult: formatState,
                templateSelection: formatState,
                width: '100%'
            }).trigger('change'); // render ikon untuk nilai awal
        });
    </script>
</x-app-layout>
