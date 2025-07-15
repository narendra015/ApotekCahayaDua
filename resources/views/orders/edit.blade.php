<x-app-layout>
    <x-page-title>Edit Pembelian</x-page-title>

    <div class="bg-white rounded-2 shadow-sm p-4 mb-5">
        <form action="{{ route('orders.update', $order->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-lg-6">
                    <div class="mb-3">
                        <label class="form-label">Tanggal Pesanan <span class="text-danger">*</span></label>
                        <input type="date" name="order_date" class="form-control @error('order_date') is-invalid @enderror"
                            value="{{ old('order_date', $order->order_date) }}" autocomplete="off">
                        @error('order_date')
                            <div class="alert alert-danger mt-2">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Pemasok <span class="text-danger">*</span></label>
                        <select name="supplier_id" class="form-select select2-single @error('supplier_id') is-invalid @enderror">
                            <option></option>
                            @foreach ($suppliers as $supplier)
                                <option value="{{ $supplier->id }}" {{ old('supplier_id', $order->supplier_id) == $supplier->id ? 'selected' : '' }}>
                                    {{ $supplier->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('supplier_id')
                            <div class="alert alert-danger mt-2">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            <hr class="mt-4">

            <table class="table" id="items-table">
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Kuantitas</th>
                        <th>Harga</th>
                        <th>Total</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($order->orderDetails as $index => $detail)
                        <tr>
                            <td>
                                <select name="items[{{ $index }}][product_id]" class="form-select product-select select2-product">
                                    <option></option>
                                    @foreach ($products as $product)
                                        <option value="{{ $product->id }}"
                                            data-price="{{ $product->price }}"
                                            {{ $detail->product_id == $product->id ? 'selected' : '' }}>
                                            {{ $product->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td><input type="number" min="1" name="items[{{ $index }}][quantity]" class="form-control qty-input" value="{{ $detail->quantity }}"></td>
                            <td><input type="text" name="items[{{ $index }}][price]" class="form-control price-input" data-raw="{{ $detail->price }}" value="{{ number_format($detail->price, 0, ',', '.') }}" readonly></td>
                            <td><input type="text" name="items[{{ $index }}][total]" class="form-control total-input" value="{{ number_format($detail->total, 0, ',', '.') }}" readonly></td>
                            <td><button type="button" class="btn btn-danger remove-item">Hapus</button></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="mt-3">
                <table class="table w-auto">
                    <tr>
                        <td><strong>Total Harga</strong></td>
                        <td style="width:250px"><input type="text" id="total-price" class="form-control" readonly></td>
                    </tr>
                </table>
            </div>

            <button type="button" id="add-item" class="btn btn-primary">Tambah Produk</button>

            <div class="pt-4 pb-2 mt-5 border-top">
                <div class="d-grid gap-3 d-sm-flex justify-content-md-start pt-1">
                    <button type="submit" class="btn btn-primary py-2 px-3">Perbarui Pesanan</button>
                    <a href="{{ route('orders.index') }}" class="btn btn-secondary py-2 px-3">Kembali</a>
                </div>
            </div>
        </form>
    </div>

    {{-- Select2 --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <style>
        .select2-container .select2-selection--single {
            height: 38px !important; padding: 6px 12px !important;
        }
    </style>

    <script>
        let itemIndex = {{ count($order->orderDetails) }};

        function formatNumber(val) {
            return new Intl.NumberFormat('id-ID').format(val);
        }

        function initSelect2(scope = document) {
            $(scope).find('.select2-single, .select2-product').select2({
                placeholder: 'Pilih...', allowClear: true, width: '100%',
                language: { noResults: () => 'Tidak ditemukan' }
            });
        }

        initSelect2();

        $(document).on('select2:open', () => {
            setTimeout(() => {
                document.querySelector('.select2-container--open .select2-search__field')?.focus();
            }, 0);
        });

        document.getElementById('add-item').addEventListener('click', function() {
            const row = `
                <tr>
                    <td>
                        <select name="items[${itemIndex}][product_id]" class="form-select product-select select2-product">
                            <option></option>
                            @foreach ($products as $product)
                                <option value="{{ $product->id }}" data-price="{{ $product->price }}">{{ $product->name }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td><input type="number" min="1" name="items[${itemIndex}][quantity]" class="form-control qty-input"></td>
                    <td><input type="text" name="items[${itemIndex}][price]" class="form-control price-input" data-raw="0" readonly></td>
                    <td><input type="text" name="items[${itemIndex}][total]" class="form-control total-input" readonly></td>
                    <td><button type="button" class="btn btn-danger remove-item">Hapus</button></td>
                </tr>`;
            $('#items-table tbody').append(row);
            initSelect2($('#items-table tbody tr:last'));
            itemIndex++;
        });

        $(document).on('click', '.remove-item', function () {
            $(this).closest('tr').remove();
            updateTotalPrice();
        });

        $(document).on('change', '.product-select', function () {
            const row = $(this).closest('tr');
            const selected = this.selectedOptions[0];
            const price = parseInt(selected.dataset.price || 0);
            row.find('.price-input').val(formatNumber(price)).attr('data-raw', price);
            row.find('.qty-input').val('');
            row.find('.total-input').val('');
            updateTotalPrice();
        });

        $(document).on('input', '.qty-input', function () {
            const row = $(this).closest('tr');
            const qty = parseInt(this.value) || 0;
            const price = parseInt(row.find('.price-input').attr('data-raw')) || 0;
            row.find('.total-input').val(qty ? formatNumber(price * qty) : '');
            updateTotalPrice();
        });

        function updateTotalPrice() {
            let total = 0;
            $('.total-input').each(function () {
                total += parseInt(this.value.replace(/\./g, '')) || 0;
            });
            $('#total-price').val(formatNumber(total));
        }

        updateTotalPrice();
    </script>
</x-app-layout>
