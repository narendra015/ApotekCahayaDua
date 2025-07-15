<x-app-layout>
    <x-page-title>Register</x-page-title>

    <div class="d-flex justify-content-center align-items-center min-vh-10">
        <div class="bg-white rounded-2 shadow-sm p-4 mb-4" style="max-width: 600px; width: 100%;">
            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            <form action="{{ url('/register') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="name" class="form-label">Nama</label>
                    <input type="text" id="name" name="name" class="form-control" value="{{ old('name') }}" required>
                </div>

                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email" class="form-control" value="{{ old('email') }}" required>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label for="password_confirmation" class="form-label">Konfirmasi Password</label>
                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" required>
                </div>

                <div class="mb-3">
                    <button type="submit" class="btn btn-primary py-2 px-4">Register</button>
                </div>
            </form>

            <p>Already have an account? <a href="{{ url('/login') }}">Login</a></p>
        </div>
    </div>
</x-app-layout>
