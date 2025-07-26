<x-app-layout>
    <x-page-title>Tambah Penjualan</x-page-title>

    <div class="bg-white rounded-2 shadow-sm p-4 mb-5">
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        <form action="{{ route('transactions.store') }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-lg-6">
                    <div class="mb-3">
                        <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                        <input type="date" name="date" class="form-control" value="{{ now()->format('Y-m-d') }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Pelanggan <span class="text-danger">*</span></label>
                        <select name="customer_id" class="form-control select2-customer @error('customer_id') is-invalid @enderror" required>
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
                            <select name="products[0][product_id]" class="form-control product-select select2-product" required>
                                <option></option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}"
                                            data-price="{{ $product->fifo_price }}"
                                            data-qty="{{ $product->stockHistories->sum('qty') }}">
                                        {{ $product->name }}
                                    </option>
                                @endforeach
                            </select>
                        </td>
                        <td><input type="number" name="products[0][quantity]" class="form-control qty-input" required></td>
                        <td>
                            <input type="text" class="form-control price-input" readonly>
                            <input type="hidden" name="products[0][price]" class="hidden-price">
                        </td>
                        <td><input type="text" name="products[0][total]" class="form-control total-input" readonly></td>
                        <td><button type="button" class="btn btn-danger remove-item">Hapus</button></td>
                    </tr>
                </tbody>
            </table>

            <div class="mt-3">
                <table class="table w-auto">
                    <tr>
                        <td class="align-middle"><strong>Total Harga</strong></td>
                        <td style="width:250px">
                            <input type="text" id="total-price" class="form-control" readonly>
                        </td>
                        <td class="align-middle"><strong>Uang Dibayar</strong></td>
                        <td>
                            <input type="text" id="paid-amount-display" class="form-control" required value="{{ number_format(old('paid_amount'), 0, ',', '.') }}">
                            <input type="hidden" name="paid_amount" id="paid-amount" value="{{ old('paid_amount') }}">
                        </td>
                        <td class="align-middle"><strong>Kembalian</strong></td>
                        <td>
                            <input type="text" id="change-amount" class="form-control" readonly>
                        </td>
                    </tr>
                </table>
            </div>

            <button type="button" id="add-item" class="btn btn-primary me-2">Tambah Barang</button>
            <button type="submit" class="btn btn-success">Simpan Transaksi</button>
        </form>
    </div>

    <!-- Select2 -->
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <style>
        .select2-container .select2-selection--single {
            font-size: clamp(12px, 1.4vw, 14px);
            min-height: 32px;
            padding: 1px
        }
        .select2-container--default .select2-selection__rendered {
            font-size: clamp(12px, 1.4vw, 14px);
            line-height: 1.4;
        }
        .select2-results__option {
            font-size: clamp(12px, 1.3vw, 14px);
        }
        .select2-search__field {
            font-size: clamp(12px, 1.3vw, 14px);
        }
    </style>

    <script>
        const formatNumber = n => new Intl.NumberFormat('id-ID').format(Number(n) || 0);
        const parseNumber = s => parseFloat((s || '0').toString().replace(/\./g,'')) || 0;
        const roundToNearest = (value, nearest = 100) => Math.round(value / nearest) * nearest;
        const formatRibuan = angka => new Intl.NumberFormat('id-ID').format(angka || 0);
        const cleanAngka = str => parseFloat((str || '').toString().replace(/\./g,'')) || 0;

        function initSelect2(scope = document) {
            $(scope).find('.select2-customer').select2({
                placeholder: 'Pilih pelanggan', allowClear: true, width: '100%',
                language: { noResults: () => "Tidak ditemukan" }
            });
            $(scope).find('.select2-product').select2({
                placeholder: 'Pilih produk', allowClear: true, width: '100%',
                language: { noResults: () => "Tidak ditemukan" }
            });
        }

        initSelect2();

        $(document).on('select2:open', function () {
            setTimeout(() => {
                document.querySelector('.select2-container--open .select2-search__field')?.focus();
            }, 0);
        });

        let itemIndex = 1;
        $('#add-item').on('click', () => {
            const row = `
            <tr>
                <td>
                    <select name="products[${itemIndex}][product_id]" class="form-control product-select select2-product" required>
                        <option></option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}"
                                    data-price="{{ $product->fifo_price }}"
                                    data-qty="{{ $product->stockHistories->sum('qty') }}">
                                {{ $product->name }}
                            </option>
                        @endforeach
                    </select>
                </td>
                <td><input type="number" name="products[${itemIndex}][quantity]" class="form-control qty-input" required></td>
                <td>
                    <input type="text" class="form-control price-input" readonly>
                    <input type="hidden" name="products[${itemIndex}][price]" class="hidden-price">
                </td>
                <td><input type="text" name="products[${itemIndex}][total]" class="form-control total-input" readonly></td>
                <td><button type="button" class="btn btn-danger remove-item">Hapus</button></td>
            </tr>`;
            $('#items-table tbody').append(row);
            initSelect2($('#items-table tbody tr:last'));
            itemIndex++;
        });

        $(document).on('click', '.remove-item', function () {
            $(this).closest('tr').remove();
            calculateTotalPrice();
        });

        $(document).on('change', '.product-select', function () {
            const opt = this.selectedOptions[0];
            const $row = $(this).closest('tr');
            const priceRaw = parseNumber(opt?.dataset.price ?? 0);
            const stock = parseInt(opt?.dataset.qty ?? 0);

            const roundedPrice = roundToNearest(priceRaw, 100);
            $row.find('.price-input').val(formatNumber(roundedPrice));
            $row.find('.hidden-price').val(roundedPrice);
            $row.find('.qty-input').attr('max', stock).val('');
            $row.find('.total-input').val('');
        });

        $(document).on('input', '.qty-input', function () {
            const $row = $(this).closest('tr');
            const price = parseNumber($row.find('.hidden-price').val());
            const qty = parseInt(this.value) || 0;

            $row.find('.total-input').val(qty ? formatNumber(price * qty) : '');
            calculateTotalPrice();
        });

        function calculateTotalPrice() {
            let total = 0;
            $('.total-input').each(function () {
                total += parseNumber(this.value);
            });
            $('#total-price').val(formatNumber(total));
            calculateChange();
        }

        function calculateChange() {
            const total = parseNumber($('#total-price').val());
            const paid = parseNumber($('#paid-amount').val());
            const change = paid - total;
            $('#change-amount').val(change >= 0 ? formatNumber(change) : '');
        }

        $('#paid-amount-display').on('input', function () {
            const raw = cleanAngka($(this).val());
            $('#paid-amount').val(raw);
            $(this).val(formatRibuan(raw));
            calculateChange();
        });

        $(document).ready(function () {
            const awal = cleanAngka($('#paid-amount-display').val());
            $('#paid-amount-display').val(formatRibuan(awal));
            $('#paid-amount').val(awal);
        });
    </script>
</x-app-layout>
