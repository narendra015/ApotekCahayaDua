<x-app-layout>
    {{-- Judul Halaman --}}
    <x-page-title>Laporan Penjualan</x-page-title>

    <div class="bg-white rounded-2 shadow-sm p-4 mb-4">
        <div class="alert alert-primary mb-5" role="alert">
            <i class="ti ti-calendar-search fs-5 me-2"></i> Filter berdasarkan tanggal penjualan.
        </div>

        <form action="{{ route('report.filter') }}" method="GET">
            <div class="row">
                <div class="col-lg-4 col-xl-3 mb-4">
                    <label class="form-label">Tanggal Mulai <span class="text-danger">*</span></label>
                    <input type="text" name="start_date" class="form-control datepicker @error('start_date') is-invalid @enderror" value="{{ old('start_date', request('start_date')) }}" autocomplete="off">
                    @error('start_date')
                        <div class="alert alert-danger mt-2">{{ $message }}</div>
                    @enderror
                </div>

                <div class="col-lg-4 col-xl-3">
                    <label class="form-label">Tanggal Selesai <span class="text-danger">*</span></label>
                    <input type="text" name="end_date" class="form-control datepicker @error('end_date') is-invalid @enderror" value="{{ old('end_date', request('end_date')) }}" autocomplete="off">
                    @error('end_date')
                        <div class="alert alert-danger mt-2">{{ $message }}</div>
                    @enderror
                </div>
            </div>

            <div class="pt-4 pb-2 mt-5 border-top">
                <button type="submit" class="btn btn-primary py-2 px-4">Tampilkan <i class="ti ti-chevron-right align-middle ms-2"></i></button>
            </div>
        </form>
    </div>

    @if (isset($transactions))
        <div class="bg-white rounded-2 shadow-sm p-4 mb-5">
            <div class="d-flex justify-content-between mb-4">
                <h6><i class="ti ti-file-text fs-5 me-1"></i> Laporan Penjualan</h6>
                <a href="{{ route('report.print', [request('start_date'), request('end_date')]) }}" target="_blank" class="btn btn-warning py-2 px-3">
                    <i class="ti ti-printer me-2"></i> Cetak
                </a>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped">
                    <thead>
                        <tr class="text-center">
                            <th>No.</th>
                            <th>Tanggal</th>
                            <th>Pelanggan</th>
                            <th>Produk</th>
                            <th>Harga</th>
                            <th>Jumlah</th>
                            <th>Total</th>
                        </tr>
                    </thead>
                    <tbody>
                    @php $no = 1; $totalAmount = 0; @endphp
                    @forelse ($transactions as $transaction)
                        @foreach ($transaction->details as $detail)
                            <tr>
                                <td class="text-center">{{ $no++ }}</td>
                                <td>{{ \Carbon\Carbon::parse($transaction->date)->format('d M Y') }}</td>
                                <td>{{ $transaction->customer->name }}</td>
                                <td>{{ $detail->product->name }}</td>
                                <td class="text-end">{{ 'Rp ' . number_format($detail->price, 0, '', '.') }}</td>
                                <td class="text-center">{{ $detail->quantity }}</td>
                                <td class="text-end">{{ 'Rp ' . number_format($detail->total, 0, '', '.') }}</td>
                            </tr>
                            @php $totalAmount += $detail->total; @endphp
                        @endforeach
                    @empty
                        <tr><td colspan="7" class="text-center">Tidak ada data tersedia.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

            <div class="text-end"><h5>Total: {{ 'Rp ' . number_format($totalAmount, 0, '', '.') }}</h5></div>
        </div>
    @endif
</x-app-layout>
