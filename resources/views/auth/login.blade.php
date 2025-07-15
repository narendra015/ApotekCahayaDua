<x-app-layout>
    <x-page-title>Login</x-page-title>

    <div class="d-flex justify-content-center align-items-center min-vh-10">
        <div class="bg-white rounded-2 shadow-sm p-4 mb-4" style="max-width: 600px; width: 100%;">
            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif

            @if ($errors->any())
                <div class="alert alert-danger">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <form action="{{ url('/login') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" id="email" name="email" class="form-control" value="{{ old('email') }}" required>
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>

                <div class="mb-3">
                    <button type="submit" class="btn btn-primary py-2 px-4">Login</button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
