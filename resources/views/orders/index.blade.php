<x-app-layout>
    {{-- Judul Halaman --}}
    <x-page-title>Pembelian</x-page-title>

    <div class="bg-white rounded-2 shadow-sm p-4 mb-4">
        <div class="row">
            <div class="d-grid d-lg-block col-lg-5 col-xl-6 mb-4 mb-lg-0">
                {{-- Tombol Form Tambah Data --}}
                {{-- Tombol Tambah Pesanan hanya muncul jika role adalah admin --}}
                @if(Auth::check() && Auth::user()->role == 'admin')
                <a href="{{ route('orders.create') }}" class="btn btn-primary py-2 px-3">
                    <i class="ti ti-plus me-2"></i> Tambah Pembelian
                </a>
            @endif            
            </div>
            <div class="col-lg-7 col-xl-6">
                {{-- Form Pencarian --}}
                <form action="{{ route('orders.index') }}" method="GET">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control form-search py-2" value="{{ request('search') }}" placeholder="Cari pesanan ..." autocomplete="off">
                        <button class="btn btn-primary py-2" type="submit">Cari</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2 shadow-sm pt-4 px-4 pb-3 mb-5">
        {{-- Tabel Menampilkan Data --}}
        <div class="table-responsive mb-3">
            <table class="table table-bordered table-striped table-hover text-center" style="width:100%">
                <thead>
                    <th>No.</th>
                    <th>Tanggal</th>
                    <th>Pemasok</th>
                    <th>Produk</th>
                    <th>Total</th>
                    @if (Auth::user()->role === 'admin')
                            <th>Aksi</th>
                    @endif
                </thead>
                <tbody>
                @forelse ($orders as $order)
                    <tr>
                        <td width="30">{{ ++$i }}</td>
                        <td width="100">{{ \Carbon\Carbon::parse($order->order_date)->locale('id')->isoFormat('D MMMM YYYY') }}</td>
                        <td width="130" >{{ $order->supplier->name }}</td>
                        <td width="250">
                            @foreach ($order->orderDetails as $detail)
                                <div>
                                    <strong>{{ $detail->product->name }}</strong> - 
                                    <span>{{ $detail->quantity }}x</span> @ Rp {{ number_format($detail->price, 0, '', '.') }} 
                                    = <strong>Rp {{ number_format($detail->total, 0, '', '.') }}</strong>
                                </div>
                            @endforeach
                        </td>
                        <td width="100">
                            <strong>Rp {{ number_format($order->orderDetails->sum('total'), 0, '', '.') }}</strong>
                        </td>
                        @if (Auth::user()->role === 'owner')
                            <td width="80" class="text-center">
                                {{-- Tombol Form Edit Data --}}
                                <a href="{{ route('orders.edit', $order->id) }}" class="btn btn-primary btn-sm m-1" data-bs-tooltip="tooltip" data-bs-title="Edit">
                                    <i class="ti ti-edit"></i>
                                </a>
                                <a href="{{ route('orders.show', $order->id) }}" class="btn btn-warning btn-sm m-1" data-bs-tooltip="tooltip" data-bs-title="Lihat">
                                    <i class="ti ti-eye"></i>
                                </a>
                            </td>
                        @endif
                        @if (Auth::user()->role === 'admin')
                            <td width="80" class="text-center">
                                {{-- Tombol Modal Hapus Data --}}
                                <button type="button" class="btn btn-danger btn-sm m-1" data-bs-toggle="modal" data-bs-target="#modalDelete{{ $order->id }}" data-bs-tooltip="tooltip" data-bs-title="Hapus"> 
                                    <i class="ti ti-trash"></i>
                                </button>
                                <a href="{{ route('orders.show', $order->id) }}" class="btn btn-warning btn-sm m-1" data-bs-tooltip="tooltip" data-bs-title="Lihat">
                                    <i class="ti ti-eye"></i>
                                </a>
                            </td>
                        @endif
                    </tr>

                    {{-- Modal Hapus Data --}}
                    <div class="modal fade" id="modalDelete{{ $order->id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="modalDeleteLabel" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h1 class="modal-title fs-5" id="exampleModalLabel">
                                        <i class="ti ti-trash me-2"></i> Hapus Pembelian
                                    </h1>
                                </div>
                                <div class="modal-body">
                                    <p class="mb-2">
                                        Apakah Anda yakin ingin menghapus Pembelian ini?
                                    </p>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary py-2 px-3" data-bs-dismiss="modal">Batal</button>
                                    {{-- Tombol Hapus Data --}}
                                    <form action="{{ route('orders.destroy', $order->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger py-2 px-3"> Ya, hapus! </button>
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
                                <div>Tidak ada data tersedia.</div>
                            </div>
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
        {{-- Pagination --}}
        <div class="pagination-links">{{ $orders->links() }}</div>
    </div>
</x-app-layout>
