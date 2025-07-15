<!DOCTYPE html>
<html lang="id" class="h-100">

<head>
    {{-- Required meta tags --}}
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Aplikasi POS Apotek dengan Laravel 11">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    {{-- Title --}}
    <title>Aplikasi POS Apotek dengan Laravel 11</title>

    {{-- Favicon icon --}}
    <link rel="shortcut icon" href="{{ asset('images/favicon.ico') }}" type="image/x-icon">

    {{-- Bootstrap CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    {{-- Tabler Icons CSS --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css" />

    {{-- Google Fonts --}}
    <link href="https://fonts.googleapis.com/css?family=Nunito:300,400,600,700,800,900" rel="stylesheet">

    {{-- Flatpickr CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.css" rel="stylesheet">

    {{-- Select2 CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

    {{-- Custom Template CSS --}}
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">

    {{-- jQuery --}}
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>
</head>

<body class="d-flex flex-column h-100">
    {{-- Header --}}
    <header>
        @include('layouts.navbar')
    </header>

    {{-- Main Content --}}
    <main class="flex-shrink-0 py-4">
        <div class="container">
            <div class="page-content">
                {{ $slot }}
            </div>
        </div>
    </main>

    {{-- Footer --}}
    <footer class="footer bg-white shadow mt-auto py-3">
        <div class="container">
            <div class="text-center">
                &copy; 2025. All rights reserved.
            </div>
        </div>
    </footer>

    {{-- Bootstrap JS --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>

    {{-- Flatpickr JS + Bahasa Indonesia --}}
    <script src="https://cdn.jsdelivr.net/npm/flatpickr@4.6.13/dist/flatpickr.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/id.js"></script>

    {{-- Select2 JS --}}
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    {{-- jQuery Mask Plugin --}}
    <script src="https://cdn.jsdelivr.net/npm/jquery-mask-plugin@1.14.16/dist/jquery.mask.min.js"></script>

    {{-- SweetAlert2 --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    {{-- Bootstrap Notify --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap-notify@3.1.3/bootstrap-notify.min.js"></script>

    {{-- Custom Scripts --}}
    <script src="{{ asset('js/plugins.js') }}"></script>
    <script src="{{ asset('js/image-preview.js') }}"></script>

    {{-- Init Plugins --}}
    <script>
        $(document).ready(function () {
            // SweetAlert messages
            @if (session('success'))
                Swal.fire({
                    icon: "success",
                    title: "Sukses!",
                    text: "{{ session('success') }}",
                    showConfirmButton: false,
                    timer: 2000
                });
            @elseif (session('error'))
                Swal.fire({
                    icon: "error",
                    title: "Gagal!",
                    text: "{{ session('error') }}",
                    showConfirmButton: false,
                    timer: 2000
                });
            @endif

            // Flatpickr tunggal (tanggal biasa)
            flatpickr(".datepicker", {
                altInput: true,
                altFormat: "j F Y",
                dateFormat: "Y-m-d",
                locale: "id",
                disableMobile: true
            });

            // Flatpickr range mode
            flatpickr(".datepicker-range", {
                mode: "range",
                altInput: true,
                altFormat: "j F Y",
                dateFormat: "Y-m-d",
                locale: "id",
                disableMobile: true,
                defaultDate: [new Date(), new Date()]
            });

            // Select2
            $('.select2-single').each(function () {
                $(this).select2({
                    dropdownParent: $(this).parent()
                });
            });

            // Fix scroll bug on modal
            $(document).on('select2:close', '.select2-single', function (e) {
                var evt = "scroll.select2";
                $(e.target).parents().off(evt);
                $(window).off(evt);
            });

            // Format angka
            $('.mask-number').mask('#.##0', { reverse: true });
        });
    </script>
</body>
</html>
