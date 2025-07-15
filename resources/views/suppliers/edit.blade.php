<x-app-layout>
    {{-- Judul Halaman --}}
    <x-page-title>Ubah Pemasok Produk</x-page-title>

    <div class="bg-white rounded-3 shadow-sm p-4 mb-5">
        {{-- Form untuk Mengubah Pemasok --}}
        <form action="{{ route('suppliers.update', $supplier->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row g-3">
                {{-- Nama Pemasok --}}
                <div class="col-md-6">
                    <label for="name" class="form-label">Nama Pemasok <span class="text-danger">*</span></label>
                    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $supplier->name) }}" autocomplete="off">
                    @error('name')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                {{-- Email --}}
                <div class="col-md-6">
                    <label for="email" class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" name="email" id="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email', $supplier->email) }}" autocomplete="off">
                    @error('email')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                {{-- Telepon --}}
                <div class="col-md-6">
                    <label for="phone" class="form-label">Telepon <span class="text-danger">*</span></label>
                    <input type="text" name="phone" id="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone', $supplier->phone) }}" autocomplete="off">
                    @error('phone')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                {{-- Alamat --}}
                <div class="col-12">
                    <label for="address" class="form-label">Alamat <span class="text-danger">*</span></label>
                    <textarea name="address" id="address" class="form-control @error('address') is-invalid @enderror" rows="3" autocomplete="off">{{ old('address', $supplier->address) }}</textarea>
                    @error('address')
                        <div class="invalid-feedback">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
            </div>

            {{-- Tombol Aksi --}}
            <div class="d-flex justify-content-start gap-2 mt-4 border-top pt-4">
                <button type="submit" class="btn btn-primary px-4">Perbarui</button>
                <a href="{{ route('suppliers.index') }}" class="btn btn-outline-secondary px-4">Kembali</a>
            </div>
        </form>
    </div>
</x-app-layout>
