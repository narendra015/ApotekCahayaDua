<x-app-layout>
    {{-- Page Title --}}
    <x-page-title>Kelola User</x-page-title>

    <div class="bg-white rounded-2 shadow-sm p-4 mb-4">
        <div class="row">
            <div class="col-lg-5 col-xl-6 mb-4 mb-lg-0">
                {{-- Tombol Tambah User hanya muncul jika role adalah owner --}}
                @if (Auth::user()->role === 'owner')
                    <a href="{{ route('users.create') }}" class="btn btn-primary py-2 px-3">
                        <i class="ti ti-plus me-2"></i> Tambah User
                    </a>
                @endif
            </div>
        </div>
    </div>

    <div class="bg-white rounded-2 shadow-sm pt-4 px-4 pb-3 mb-5">
        {{-- Tabel tampil data user --}}
        <div class="table-responsive mb-3">
            <table class="table table-bordered table-striped table-hover text-center" style="width:100%">
                <thead>
                    <th>No.</th>
                    <th>Nama</th>
                    <th>Email</th>
                    <th>Role</th>
                    @if (Auth::user()->role === 'owner')
                        <th class="text-center">Aksi</th>
                    @endif
                </thead>
                <tbody>
                    @php $i = 0; @endphp
                    @forelse ($users as $user)
                    <tr>
                        <td>{{ ++$i }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ ucfirst($user->role) }}</td>
                        @if (Auth::user()->role === 'owner')
                            <td class="text-center">
                                {{-- Tombol Edit --}}
                                <a href="{{ route('users.edit', $user->id) }}" class="btn btn-primary btn-sm m-1">
                                    <i class="ti ti-edit"></i> Edit
                                </a>
                                {{-- Tombol Hapus --}}
                                <button type="button" class="btn btn-danger btn-sm m-1" data-bs-toggle="modal" data-bs-target="#modalDelete{{ $user->id }}">
                                    <i class="ti ti-trash"></i> Hapus
                                </button>

                                {{-- Modal Konfirmasi Hapus --}}
                                <div class="modal fade" id="modalDelete{{ $user->id }}" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="modalDeleteLabel" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title" id="modalDeleteLabel">Konfirmasi Hapus</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                Apakah Anda yakin ingin menghapus akun <strong>{{ $user->name }}</strong>? Penghapusan akun ini tidak dapat dibatalkan.
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                <form action="{{ route('users.destroy', $user->id) }}" method="POST">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-danger">Ya, Hapus!</button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        @endif
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
