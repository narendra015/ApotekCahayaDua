<x-app-layout>
    {{-- Judul Halaman --}}
    <x-page-title>Tambah Penjualan</x-page-title>

    <div class="bg-white rounded-2 shadow-sm p-4 mb-5">
        {{-- Pesan Error --}}
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        {{-- Form Tambah Transaksi --}}
        <form action="{{ route('transactions.store') }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-lg-6">
                    {{-- Tanggal --}}
                    <div class="mb-3">
                        <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                        <input type="date" name="date" class="form-control"
                               value="{{ now()->format('Y-m-d') }}" required>
                    </div>

                    {{-- Pelanggan --}}
                    <div class="mb-3">
                        <label class="form-label">Pelanggan <span class="text-danger">*</span></label>
                        <select name="customer_id"
                                class="form-control select2-customer @error('customer_id') is-invalid @enderror"
                                required>
                            <option></option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                            @endforeach
                        </select>
                        @error('customer_id')
                            <div class="alert alert-danger mt-2">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Tabel Item Produk --}}
            <table class="table" id="items-table">
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Jumlah</th>
                        <th>Harga</th>
                        <th>Total</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>
                            <select name="products[0][product_id]"
                                    class="form-control product-select select2-product" required>
                                <option></option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}"
                                            data-price="{{ $product->price }}"
                                            data-qty="{{ $product->qty }}">
                                        {{ $product->name }}
                                    </option>
                                @endforeach
                            </select>
                        </td>
                        <td><input type="number" name="products[0][quantity]" class="form-control qty-input" required></td>
                        <td><input type="text"   name="products[0][price]"   class="form-control price-input"  readonly></td>
                        <td><input type="text"   name="products[0][total]"   class="form-control total-input"  readonly></td>
                        <td><button type="button" class="btn btn-danger remove-item">Hapus</button></td>
                    </tr>
                </tbody>
            </table>

            {{-- Grand Total --}}
            <div class="mt-3">
                <table class="table w-auto">
                    <tr>
                        <td class="align-middle"><strong>Total Harga</strong></td>
                        <td style="width:250px">
                            <input type="text" id="total-price" class="form-control" readonly>
                        </td>
                    </tr>
                </table>
            </div>

            <button type="button" id="add-item" class="btn btn-primary me-2">Tambah Barang</button>
            <button type="submit" class="btn btn-success">Simpan Transaksi</button>
        </form>
    </div>

    {{-- ==================== RESOURCES ==================== --}}
    {{-- Select2 CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>

    {{-- Custom styling untuk Select2 --}}
    <style>
        .select2-container .select2-selection--single {
            height: 35px !important;
            padding: 6px 8px !important;
            font-size: 0.95rem !important;
            border-radius: 8px;
        }
        .select2-container--default .select2-selection__arrow {
            height: 35px !important;
            top: 0 !important;
        }
        .select2-container--default .select2-results__option {
            font-size: 12px;
            padding: 10px 12px;
        }
        .select2-container--default .select2-selection--single .select2-selection__rendered {
            color: #6c757d;
            line-height: 24px !important;
        }
        .select2-container--default .select2-selection--single .select2-selection__placeholder {
            color: #999;
            font-style: italic;
        }
    </style>

    {{-- jQuery & Select2 JS --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        // Format angka
        const formatNumber = n => new Intl.NumberFormat('id-ID').format(Number(n) || 0);

        // Inisialisasi Select2
        function initSelect2(scope = document) {
            $(scope).find('.select2-customer').select2({
                placeholder: 'Pilih pelanggan',
                allowClear: true,
                width: '100%',
                language: { noResults: () => "Tidak ditemukan" }
            });

            $(scope).find('.select2-product').select2({
                placeholder: 'Pilih produk',
                allowClear: true,
                width: '100%',
                language: { noResults: () => "Tidak ditemukan" }
            });
        }

        initSelect2();

        // Fokus otomatis ke kolom pencarian saat select2 dibuka
        $(document).on('select2:open', function () {
            setTimeout(() => {
                document.querySelector('.select2-container--open .select2-search__field')?.focus();
            }, 0);
        });

        // Tambah baris produk
        let itemIndex = 1;
        $('#add-item').on('click', () => {
            const row = `
            <tr>
                <td>
                    <select name="products[${itemIndex}][product_id]"
                            class="form-control product-select select2-product" required>
                        <option></option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}"
                                    data-price="{{ $product->price }}"
                                    data-qty="{{ $product->qty }}">
                                {{ $product->name }}
                            </option>
                        @endforeach
                    </select>
                </td>
                <td><input type="number" name="products[${itemIndex}][quantity]" class="form-control qty-input" required></td>
                <td><input type="text"   name="products[${itemIndex}][price]"   class="form-control price-input"  readonly></td>
                <td><input type="text"   name="products[${itemIndex}][total]"   class="form-control total-input"  readonly></td>
                <td><button type="button" class="btn btn-danger remove-item">Hapus</button></td>
            </tr>`;
            $('#items-table tbody').append(row);
            initSelect2($('#items-table tbody tr:last'));
            itemIndex++;
        });

        // Hapus baris produk
        $(document).on('click', '.remove-item', function () {
            $(this).closest('tr').remove();
            calculateTotalPrice();
        });

        // Saat produk dipilih
        $(document).on('change', '.product-select', function () {
            const opt = this.selectedOptions[0];
            const $row = $(this).closest('tr');
            const price = opt?.dataset.price ?? 0;
            const stock = opt?.dataset.qty ?? 0;

            $row.find('.price-input').val(formatNumber(price));
            $row.find('.qty-input').attr('max', stock).val('');
            $row.find('.total-input').val('');
        });

        // Saat jumlah berubah
        $(document).on('input', '.qty-input', function () {
            const $row = $(this).closest('tr');
            const price = parseFloat(($row.find('.price-input').val() || '0').replace(/\./g,'')) || 0;
            const qty   = parseInt(this.value) || 0;

            $row.find('.total-input').val(qty ? formatNumber(price * qty) : '');
            calculateTotalPrice();
        });

        function calculateTotalPrice() {
            let total = 0;
            $('.total-input').each(function () {
                total += parseFloat((this.value || '0').replace(/\./g,'')) || 0;
            });
            $('#total-price').val(formatNumber(total));
        }
    </script>
</x-app-layout>
