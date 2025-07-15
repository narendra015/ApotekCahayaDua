<div class="d-flex flex-column flex-lg-row mb-2">
    {{-- Page Title --}}
    <div class="flex-grow-1">
        <h5 class="page-title">{{ $slot }}</h5>
    </div>
    {{-- Breadcrumbs --}}
    <div class="pt-lg-1">
        <nav style="--bs-breadcrumb-divider: '>';" aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item">
                    <a href="javascript:void(0);" onclick="goBack()" class="text-breadcrumb text-decoration-none">
                        <i class="ti ti-home fs-6"></i>
                    </a>
                </li>
                <li class="breadcrumb-item" aria-current="page">
                    {{ $slot }}
                </li>
            </ol>
        </nav>
    </div>
</div>

<script>
    function goBack() {
        if (document.referrer) {
            window.location.href = document.referrer; // Kembali ke halaman sebelumnya
        } else {
            window.location.href = "{{ url('/') }}"; // Jika tidak ada halaman sebelumnya, kembali ke dashboard/home
        }
    }
</script>
