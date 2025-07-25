<x-app-layout>
    {{-- Judul Halaman --}}
    <x-page-title>Pemasok Produk</x-page-title>

    <div class="bg-white rounded-2 shadow-sm p-4 mb-4">
        <div class="row">
            <div class="d-grid d-lg-block col-lg-5 col-xl-6 mb-4 mb-lg-0">
                @if (Auth::user()->role === 'admin')
                <a href="{{ route('categories.create') }}" class="btn btn-primary btn-sm py-2 px-2">
                    <i class="ti ti-plus me-1"></i> Tambah Kategori 
                </a>
                <a href="{{ route('units.create') }}" class="btn btn-primary btn-sm py-2 px-2">
                    <i class="ti ti-plus me-1"></i> Tambah Satuan 
                </a>
                <a href="{{ route('suppliers.create') }}" class="btn btn-primary btn-sm py-2 px-2">
                    <i class="ti ti-plus me-1"></i> Tambah Pemasok
                </a>
                @endif
            </div>
            <div class="col-lg-7 col-xl-6">
                {{-- Form Pencarian --}}
                <form action="{{ route('suppliers.index') }}" method="GET">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control form-search py-2" value="{{ request('search') }}" placeholder="Cari pemasok ..." autocomplete="off">
                        <button class="btn btn-primary py-2" type="submit">Cari</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2 shadow-sm pt-4 px-4 pb-3 mb-5">
        <ul class="nav nav-pills mb-4">
            <li class="nav-item">
                <a class="nav-link" href="{{ route('categories.index') }}">Kategori Produk</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('units.index') }}">Satuan Produk</a>
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="{{ route('suppliers.index') }}">Pemasok Produk</a>
            </li>
        </ul>

        {{-- Tabel Pemasok --}}
        <div class="table-responsive mb-3">
            <table class="table table-bordered table-striped table-hover text-center" style="width:100%">
                <thead>
                    <tr>
                        <th>No.</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Telepon</th>
                        @if (Auth::user()->role === 'admin')
                            <th>Aksi</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                @php $i = 0; @endphp
                @forelse ($suppliers as $supplier)
                    <tr>
                        <td>{{ ++$i }}</td>
                        <td>{{ $supplier->name }}</td>
                        <td>{{ $supplier->email }}</td>
                        <td>{{ $supplier->phone }}</td>
                        @if (Auth::user()->role === 'admin')
                            <td class="text-center">
                                <a href="{{ route('suppliers.edit', $supplier->id) }}" class="btn btn-primary btn-sm m-1" data-bs-tooltip="tooltip" data-bs-title="Ubah">
                                    <i class="ti ti-edit"></i>
                                </a>
                                {{-- <button type="button" class="btn btn-danger btn-sm m-1" data-bs-toggle="modal" data-bs-target="#modalDeleteSupplier{{ $supplier->id }}" data-bs-tooltip="tooltip" data-bs-title="Hapus">
                                    <i class="ti ti-trash"></i>
                                </button> --}}
                            </td>
                        @endif
                    </tr>

                    {{-- Modal Konfirmasi Hapus --}}
                    <div class="modal fade" id="modalDeleteSupplier{{ $supplier->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h1 class="modal-title fs-5">
                                        <i class="ti ti-trash me-2"></i> Hapus Pemasok
                                    </h1>
                                </div>
                                <div class="modal-body">
                                    Apakah Anda yakin ingin menghapus <span class="fw-bold">{{ $supplier->name }}</span>?
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                    <form action="{{ route('suppliers.destroy', $supplier->id) }}" method="POST">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger">Ya, hapus!</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">Tidak ada pemasok tersedia.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        <div class="pagination-links">{{ $suppliers->links() }}</div>
    </div>
</x-app-layout>
