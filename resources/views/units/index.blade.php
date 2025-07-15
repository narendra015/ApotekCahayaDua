<x-app-layout>
    {{-- Judul Halaman --}}
    <x-page-title>Satuan Produk</x-page-title>

    <div class="bg-white rounded-2 shadow-sm p-4 mb-4">
        <div class="row">
            <div class="d-grid d-lg-block col-lg-5 col-xl-6 mb-4 mb-lg-0">
                {{-- Tombol Tambah Data hanya muncul jika role adalah admin --}}
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
                <form action="{{ route('units.index') }}" method="GET">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control form-search py-2" value="{{ request('search') }}" placeholder="Cari satuan ..." autocomplete="off">
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
                <a class="nav-link active" href="{{ route('units.index') }}">Satuan Produk</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="{{ route('suppliers.index') }}">Pemasok Produk</a>
            </li>
        </ul>
        
        {{-- Tabel Satuan --}}
        <div class="table-responsive mb-3">
            <table class="table table-bordered table-striped table-hover" style="width:100%">
                <thead>
                    <th class="text-center">No.</th>
                    <th class="text-center">Nama</th>
                    @if (Auth::user()->role === 'admin')
                        <th class="text-center">Aksi</th>
                    @endif
                </thead>
                <tbody>
                @php $i = 0; @endphp
                @forelse ($units as $unit)
                    <tr>
                        <td width="30" class="text-center">{{ ++$i }}</td>
                        <td width="200">{{ $unit->name }}</td>
                        @if (Auth::user()->role === 'admin')
                            <td width="70" class="text-center">
                                <a href="{{ route('units.edit', $unit->id) }}" class="btn btn-primary btn-sm m-1" data-bs-tooltip="tooltip" data-bs-title="Ubah">
                                    <i class="ti ti-edit"></i>
                                </a>
                                <button type="button" class="btn btn-danger btn-sm m-1" data-bs-toggle="modal" data-bs-target="#modalDeleteUnit{{ $unit->id }}" data-bs-tooltip="tooltip" data-bs-title="Hapus">
                                    <i class="ti ti-trash"></i>
                                </button>
                            </td>
                        @endif
                    </tr>

                    {{-- Modal Hapus --}}
                    <div class="modal fade" id="modalDeleteUnit{{ $unit->id }}" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <h1 class="modal-title fs-5">
                                        <i class="ti ti-trash me-2"></i> Hapus Satuan
                                    </h1>
                                </div>
                                <div class="modal-body">
                                    Apakah Anda yakin ingin menghapus <span class="fw-bold">{{ $unit->name }}</span>?
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                    <form action="{{ route('units.destroy', $unit->id) }}" method="POST">
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
                        <td colspan="3" class="text-center">Tidak ada Satuan tersedia.</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        {{-- Navigasi Halaman --}}
        <div class="pagination-links">{{ $units->links() }}</div>
    </div>
</x-app-layout>
