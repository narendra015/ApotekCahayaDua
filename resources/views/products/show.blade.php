<x-app-layout>
    {{-- Judul Halaman --}}
    <x-page-title>Detail Produk</x-page-title>

    <div class="bg-white rounded-2 shadow-sm p-4 mb-5">
        {{-- Menampilkan detail produk --}}
        <div class="row flex-lg-row align-items-center g-5">
            <div class="col-lg-3">
                <img src="{{ asset('storage/products/'.$product->image) }}" class="d-block mx-lg-auto img-thumbnail rounded-4 shadow-sm" alt="Gambar" loading="lazy">
            </div>
            <div class="col-lg-9">
                <h4>{{ $product->name }}</h4>
                <p class="text-muted"><i class="ti ti-tag me-1"></i> {{ $product->category->name }}</p>
                <p style="text-align: justify">{{ $product->description }}.</p>
                <p class="text-success fw-bold"> {{ 'Rp ' . number_format($product->price, 0, '', '.') . ' / ' . ($product->unit ? $product->unit->name : 'Tidak ada satuan') }}</p>
                
                {{-- Menambahkan Tanggal Kedaluwarsa --}}
                <p><strong>Tanggal Kedaluwarsa:</strong> {{ \Carbon\Carbon::parse($product->expired_date)->format('d M Y') }}</p>
                
                {{-- Menambahkan Jumlah --}}
                <p><strong>Jumlah:</strong> {{ $product->qty }}</p>
                
                {{-- Menambahkan Nama Pemasok --}}
                <p><strong>Pemasok:</strong> {{ $product->supplier ? $product->supplier->name : 'Tidak ada pemasok' }}</p> <!-- Menampilkan Nama Pemasok -->
            </div>
        </div>

        <div class="pt-4 pb-2 mt-5 border-top">
            <div class="d-grid gap-3 d-sm-flex justify-content-md-start pt-1">
                <!-- Tombol untuk kembali ke halaman daftar produk -->
                <a href="{{ route('products.index') }}" class="btn btn-secondary py-2 px-4">Tutup</a>
            </div>
        </div>
    </div>
</x-app-layout>
