<x-app-layout>
    {{-- Judul Halaman --}}
    <x-page-title>Ubah Satuan Produk</x-page-title>

    <div class="bg-white rounded-2 shadow-sm p-4 mb-5">
        {{-- Form Ubah Data --}}
        <form action="{{ route('units.update', $unit->id) }}" method="POST">
            @csrf
            @method('PUT')
            <div class="row">
                <div class="col-lg-6">
                    <label class="form-label">Nama <span class="text-danger">*</span></label>
                    <input type="text" name="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name', $unit->name) }}" autocomplete="off">
                    
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
                    {{-- Tombol untuk memperbarui data --}}
                    <button type="submit" class="btn btn-primary py-2 px-3">Perbarui</button>
                    {{-- Tombol untuk kembali ke halaman daftar --}}
                    <a href="{{ route('units.index') }}" class="btn btn-secondary py-2 px-3">Kembali</a>
                </div>
            </div>
        </form>
    </div>
</x-app-layout>
