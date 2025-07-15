<x-app-layout>
    {{-- Judul Halaman --}}
    <x-page-title>Tambah Pemasok Produk</x-page-title>

    <div class="bg-white rounded-2 shadow-sm p-4 mb-5">
        {{-- Form Tambah Pemasok --}}
        <form action="{{ route('suppliers.store') }}" method="POST">
            @csrf
            <div class="row">
                {{-- Nama Pemasok --}}
                <div class="col-lg-6 mb-3">
                    <label class="form-label">Nama Pemasok <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" autocomplete="off">

                    {{-- Pesan error untuk nama --}}
                    @error('name')
                        <div class="alert alert-danger mt-2">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                {{-- Email --}}
                <div class="col-lg-6 mb-3">
                    <label class="form-label">Email <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{ old('email') }}" autocomplete="off">

                    {{-- Pesan error untuk email --}}
                    @error('email')
                        <div class="alert alert-danger mt-2">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                {{-- Telepon --}}
                <div class="col-lg-6 mb-3">
                    <label class="form-label">Telepon <span class="text-danger">*</span></label>
                    <input type="text" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ old('phone') }}" autocomplete="off">

                    {{-- Pesan error untuk telepon --}}
                    @error('phone')
                        <div class="alert alert-danger mt-2">
                            {{ $message }}
                        </div>
                    @enderror
                </div>

                {{-- Alamat --}}
                <div class="col-lg-12 mb-3">
                    <label class="form-label">Alamat <span class="text-danger">*</span></label>
                    <textarea name="address" class="form-control @error('address') is-invalid @enderror" rows="4" autocomplete="off">{{ old('address') }}</textarea>

                    {{-- Pesan error untuk alamat --}}
                    @error('address')
                        <div class="alert alert-danger mt-2">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
            </div>

            <div class="pt-4 pb-2 mt-4 border-top">
                <div class="d-grid gap-3 d-sm-flex justify-content-md-start pt-1">
                    {{-- Tombol Simpan --}}
                    <button type="submit" class="btn btn-primary py-2 px-4">Simpan</button>
                    {{-- Tombol Batal --}}
                    <a href="{{ route('suppliers.index') }}" class="btn btn-secondary py-2 px-3">Kembali</a>
                </div>
            </div>
        </form>
    </div>
</x-app-layout>
