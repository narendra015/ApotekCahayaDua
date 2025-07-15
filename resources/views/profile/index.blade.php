<x-app-layout>
    <x-page-title>Profile</x-page-title>

    <div class="container">
        <div class="card shadow-sm mx-auto" style="max-width: 800px;">
            <div class="card-body">
                {{-- Notifikasi sukses atau error --}}
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @elseif(session('error'))
                    <div class="alert alert-danger">{{ session('error') }}</div>
                @endif

                {{-- Tab Navigation --}}
                <ul class="nav nav-pills mb-4">
                    <li class="nav-item">
                        <a class="nav-link active" data-bs-toggle="pill" href="#profile">Informasi Profile</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-bs-toggle="pill" href="#password">Perbarui Kata Sandi</a>
                    </li>
                </ul>

                <div class="tab-content">
                    {{-- Informasi Profile --}}
                    <div class="tab-pane fade show active" id="profile">
                        <h4>Informasi Profile</h4>
                        <p>Perbarui informasi profil dan alamat email Anda.</p>

                        <form action="{{ route('profile.update') }}" method="POST">
                            @csrf
                            @method('PUT')
                        
                            <div class="mb-3">
                                <label for="name" class="form-label">Nama</label>
                                <input type="text" id="name" name="name" class="form-control @error('name') is-invalid @enderror"
                                    value="{{ old('name', $user->name) }}" required>
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" id="email" name="email" class="form-control @error('email') is-invalid @enderror"
                                    value="{{ old('email', $user->email) }}" autocomplete="email" required>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            
                            <div class="mb-3">
                                <label for="role" class="form-label">Role</label>
                                <input type="text" id="role" name="role" class="form-control" value="{{ old('role', $user->role) }}" disabled>
                            </div>
                        
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </form>
                        
                    </div>

                    {{-- Perbarui Kata Sandi --}}
                    <div class="tab-pane fade" id="password">
                        <h4>Perbarui Kata Sandi</h4>
                        <p>Gunakan kata sandi yang kuat dan unik untuk keamanan akun Anda.</p>

                        <form action="{{ route('profile.update-password') }}" method="POST">
                            @csrf
                            @method('PUT')
                        
                            <div class="mb-3">
                                <label for="current_password" class="form-label">Kata Sandi Saat Ini</label>
                                <input type="password" id="current_password" name="current_password" class="form-control @error('current_password') is-invalid @enderror" required>
                                @error('current_password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        
                            <div class="mb-3">
                                <label for="password" class="form-label">Kata Sandi Baru</label>
                                <input type="password" id="password" name="password" class="form-control @error('password') is-invalid @enderror" required>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        
                            <div class="mb-3">
                                <label for="password_confirmation" class="form-label">Konfirmasi Kata Sandi</label>
                                <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required>
                            </div>
                        
                            <button type="submit" class="btn btn-primary">Simpan</button>
                        </form>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Script untuk Konfirmasi Penghapusan Akun --}}
    <script>
        function confirmDelete() {
            return confirm("Apakah Anda yakin ingin menghapus akun ini secara permanen?");
        }
    </script>
</x-app-layout>
