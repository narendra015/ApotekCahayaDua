<x-app-layout>
    {{-- Judul Halaman --}}
    <x-page-title>Tambah Satuan Produk</x-page-title>

    <div class="bg-white rounded-2 shadow-sm p-4 mb-5">
        {{-- Form Tambah Satuan --}}
        <form action="{{ route('units.store') }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-lg-6">
                    <label class="form-label">Nama Satuan <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" autocomplete="off">
                    
                    {{-- Pesan error untuk nama --}}
                    @error('name')
                        <div class="alert alert-danger mt-2">
                            {{ $message }}
                        </div>
                    @enderror
                </div>
            </div>
    
            <div class="pt-4 pb-2 mt-5 border-top">
                <div class="d-grid gap-3 d-sm-flex justify-content-md-start pt-1">
                    {{-- Tombol simpan data --}}
                    <button type="submit" class="btn btn-primary py-2 px-4">Simpan</button>
                    {{-- Tombol kembali ke halaman index --}}
                    <a href="{{ route('units.index') }}" class="btn btn-secondary py-2 px-3">Kembali</a>
                </div>
            </div>
        </form>
    </div>
</x-app-layout>
