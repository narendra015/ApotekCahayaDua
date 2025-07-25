<x-app-layout>
    <x-page-title>Edit Histori Stok</x-page-title>

    <div class="bg-white p-4 rounded shadow-sm">
        <form action="{{ route('stock-histories.update', $history->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label">Harga <span class="text-danger">*</span></label>
                <div class="input-group">
                    <span class="input-group-text">Rp</span>
                    <input type="text" name="price" class="form-control @error('price') is-invalid @enderror"
                        value="{{ old('price', number_format($history->price, 0, '', '.')) }}">
                </div>
                @error('price')
                    <div class="alert alert-danger mt-2">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-3">
                <label class="form-label">Tanggal Kadaluarsa <span class="text-danger">*</span></label>
                <input type="date" name="expired_date" class="form-control @error('expired_date') is-invalid @enderror"
                    value="{{ old('expired_date', \Carbon\Carbon::parse($history->expired_date)->format('Y-m-d')) }}">
                @error('expired_date')
                    <div class="alert alert-danger mt-2">{{ $message }}</div>
                @enderror
            </div>

            <div class="d-flex gap-3">
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                <a href="{{ route('products.show', $history->product_id) }}" class="btn btn-secondary">Batal</a>
            </div>
        </form>
    </div>
</x-app-layout>
