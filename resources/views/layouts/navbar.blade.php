{{-- Navbar Top --}}
<nav class="navbar navbar-expand-lg navbar-light bg-light fixed-top shadow">
    <div class="container">
        {{-- Brand --}}
        <a class="navbar-brand d-flex align-items-center" href="{{ route('dashboard') }}">
            <img src="{{ asset('images/logo_apotek.png') }}" alt="Logo" width="100" class="me-2">
            {{-- <span class="fw-bold text-uppercase">Apotek Cahaya Dua</span> --}}
        </a>

        {{-- Navbar Menu (Hanya Muncul di Mobile) --}}
        <div class="d-flex ms-auto">
            <div class="collapse navbar-collapse d-lg-none" id="navbarNav">
                <ul class="navbar-nav">
                    @auth
                        {{-- Dashboard --}}
                        <li class="nav-item">
                            <x-navbar-link href="{{ route('dashboard') }}" :active="request()->routeIs('dashboard')">
                                <i class="ti ti-home me-1"></i> Dashboard
                            </x-navbar-link>
                        </li>

                        {{-- Kategori --}}
                        <li class="nav-item">
                            <x-navbar-link href="{{ route('categories.index') }}" :active="request()->routeIs('categories.*')">
                                <i class="ti ti-category me-1"></i> Kategori
                            </x-navbar-link>
                        </li>

                        {{-- Produk --}}
                        <li class="nav-item">
                            <x-navbar-link href="{{ route('products.index') }}" :active="request()->routeIs('products.*')">
                                <i class="ti ti-copy me-1"></i> Produk
                            </x-navbar-link>
                        </li>

                        {{-- Pelanggan --}}
                        <li class="nav-item">
                            <x-navbar-link href="{{ route('customers.index') }}" :active="request()->routeIs('customers.*')">
                                <i class="ti ti-users me-1"></i> Pelanggan
                            </x-navbar-link>
                        </li>

                        {{-- Dropdown Transaksi --}}
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="transactionsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="ti ti-folders me-1"></i> Transaksi
                            </a>
                            <ul class="dropdown-menu text-center" aria-labelledby="transactionsDropdown">
                                <li>
                                    <a class="dropdown-item" href="{{ route('transactions.index') }}" :active="request()->routeIs('transactions.*')">
                                        Penjualan
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item" href="{{ route('orders.index') }}" :active="request()->routeIs('orders.*')">
                                        Pembelian
                                    </a>
                                </li>
                            </ul>
                        </li>

                        {{-- Laporan --}}
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="reportsDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="ti ti-file-text me-1"></i> Laporan
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="reportsDropdown">
                                <li>
                                    <x-navbar-link href="{{ route('report.index') }}" :active="request()->routeIs('report.index')">
                                        <i class="ti ti-file-text me-1"></i> Laporan Penjualan
                                    </x-navbar-link> 
                                </li>
                                <li>
                                    <x-navbar-link href="{{ route('order_report.index') }}" :active="request()->routeIs('order_report.index')">
                                        <i class="ti ti-file-text me-1"></i> Laporan Pembelian
                                    </x-navbar-link> 
                                </li> 
                            </ul>
                        </li>   
                                            
                        <li class="nav-item me-3">
                            <a href="{{ route('notifications.index') }}" class="nav-link position-relative">
                                <i class="ti ti-bell fs-5"></i>
                                @if(($totalNotification ?? 0) > 0)
                                    <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                        {{ $totalNotification }}
                                    </span>
                                @endif
                            </a>
                        </li>
                        
                        {{-- User --}}
                        <li class="nav-item">
                            @auth
                                @if (Auth::user()->role === 'owner')
                                    <x-navbar-link href="{{ route('users.index') }}" :active="request()->routeIs('users.*')">
                                        <i class="ti ti-users me-1"></i> User
                                    </x-navbar-link>
                                @endif
                            @endauth
                        </li>                                            
                    @endauth
                </ul>
            </div>

            {{-- Menu Autentikasi --}}
            @auth
                {{-- Dropdown Pengguna --}}
                <div class="dropdown">
                    <button class="btn btn-light dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                        👤 {{ Auth::user()->name }}
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end text-center" aria-labelledby="userDropdown">
                        <li>
                            <a class="dropdown-item" href="{{ route('profile.index') }}">
                                <i class="ti ti-user me-2"></i> Profile
                            </a>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-item">
                                    <i class="ti ti-power-off me-2"></i> Logout
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            @else
                {{-- Tombol Login dan Daftar --}}
                <a href="{{ route('login') }}" class="btn btn-outline-primary btn-sm me-2">Login</a>
            @endauth
        </div>

        {{-- Tombol Toggler (Untuk Mobile) --}}
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
    </div>
</nav>
