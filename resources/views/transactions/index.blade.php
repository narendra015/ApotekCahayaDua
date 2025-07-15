<x-app-layout>
    {{-- Judul Halaman --}}
    <x-page-title>Penjualan</x-page-title>

    <div class="bg-white rounded-2 shadow-sm p-4 mb-4">
        <div class="row">
            <div class="d-grid d-lg-block col-lg-5 col-xl-6 mb-4 mb-lg-0">
                {{-- Tombol Tambah Transaksi hanya muncul jika role adalah admin --}}
                @if (Auth::user()->role === 'admin')
                <a href="{{ route('transactions.create') }}" class="btn btn-primary py-2 px-3">
                    <i class="ti ti-plus me-2"></i> Tambah Penjualan
                </a>
                @endif
            </div>
            <div class="col-lg-7 col-xl-6">
                {{-- Form Pencarian --}}
                <form action="{{ route('transactions.index') }}" method="GET">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control form-search py-2" value="{{ request('search') }}" placeholder="Cari transaksi ..." autocomplete="off">
                        <button class="btn btn-primary py-2" type="submit">Cari</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2 shadow-sm pt-4 px-4 pb-3 mb-5">
        {{-- Tabel Data Transaksi --}}
        <div class="table-responsive mb-3">
            <table class="table table-bordered table-striped table-hover text-center" style="width:100%">
                <thead>
                    <tr class="text-center">
                        <th>No.</th>
                        <th>Tanggal</th>
                        <th>Pelanggan</th>
                        <th>Produk</th>
                        <th>Total</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                @forelse ($transactions as $transaction)
                    <tr>
                        <td width="30">{{ ++$i }}</td>
                        <td width="100">{{ \Carbon\Carbon::parse($transaction->date)->locale('id')->isoFormat('D MMMM YYYY') }}</td>
                        <td width="130">{{ $transaction->customer->name }}</td>
                        <td width="250">
                            @foreach ($transaction->details as $detail)
                                <div>
                                    <strong>{{ $detail->product->name }}</strong> - 
                                    <span>{{ $detail->quantity }}x</span> @ Rp {{ number_format($detail->price, 0, '', '.') }} 
                                    = <strong>Rp {{ number_format($detail->total, 0, '', '.') }}</strong>
                                </div>
                            @endforeach
                        </td>
                        <td width="100">
                            <strong>Rp {{ number_format($transaction->details->sum('total'), 0, '', '.') }}</strong>
                        </td>
                        @if (Auth::user()->role === 'owner')
                            <td class="text-center" width="80">
                                {{-- Tombol Edit --}}
                                <a href="{{ route('transactions.edit', $transaction->id) }}" class="btn btn-primary btn-sm m-1" data-bs-tooltip="tooltip" data-bs-title="Ubah">
                                    <i class="ti ti-edit"></i>
                                </a>
                            </td>
                        @endif
                        @if (Auth::user()->role === 'admin')
                            <td class="text-center" width="80">
                                {{-- Tombol Hapus (Modal) --}}
                                <button type="button" class="btn btn-danger btn-sm m-1" data-bs-toggle="modal" data-bs-target="#modalDelete{{ $transaction->id }}" data-bs-tooltip="tooltip" data-bs-title="Hapus"> 
                                    <i class="ti ti-trash"></i>
                                </button>
                                <a href="{{ route('transactions.print', $transaction->id) }}" class="btn btn-warning btn-sm m-1" target="_blank" data-bs-tooltip="tooltip" data-bs-title="Nota Penjualan">
                                    <i class="ti ti-printer"></i>
                                </a>
                            </td>
                        @endif
                    </tr>

                    {{-- Modal Hapus Data --}}
                    <div class="modal fade" id="modalDelete{{ $transaction->id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="modalDeleteLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h1 class="modal-title fs-5" id="exampleModalLabel">
                                        <i class="ti ti-trash me-2"></i> Hapus Transaksi
                                    </h1>
                                </div>
                                <div class="modal-body">
                                    <p class="mb-2">
                                        Apakah Anda yakin ingin menghapus transaksi ini?
                                    </p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary py-2 px-3" data-bs-dismiss="modal">Batal</button>
                                    <form action="{{ route('transactions.destroy', $transaction->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger py-2 px-3">Ya, hapus!</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <tr>
                        <td colspan="6">
                            <div class="d-flex justify-content-center align-items-center">
                                <i class="ti ti-info-circle fs-5 me-2"></i>
                                <div>Tidak ada data transaksi.</div>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{-- Navigasi Halaman --}}
        <div class="pagination-links">{{ $transactions->links() }}</div>
    </div>
</x-app-layout>
