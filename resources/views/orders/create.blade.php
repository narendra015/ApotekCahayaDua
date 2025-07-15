<x-app-layout>
    <x-page-title>Tambah Pembelian</x-page-title>

    <div class="bg-white rounded-2 shadow-sm p-4 mb-5">
        {{-- Menampilkan pesan error --}}
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        {{-- Form Tambah Pesanan --}}
        <form action="{{ route('orders.store') }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-lg-6">
                    {{-- Tanggal --}}
                    <div class="mb-3">
                        <label class="form-label">Tanggal Pembelian <span class="text-danger">*</span></label>
                        <input type="date" name="order_date" class="form-control"
                               value="{{ now()->format('Y-m-d') }}" required>
                    </div>

                    {{-- Pemasok --}}
                    <div class="mb-3">
                        <label class="form-label">Pemasok <span class="text-danger">*</span></label>
                        <select name="supplier_id" id="supplierSelect"
                                class="form-control select2-supplier @error('supplier_id') is-invalid @enderror"
                                required>
                            <option></option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                        @error('supplier_id')
                        <div class="alert alert-danger mt-2">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Tabel Produk --}}
            <table class="table" id="items-table">
                <thead>
                <tr>
                    <th style="width:35%">Produk</th>
                    <th style="width:15%">Kuantitas</th>
                    <th style="width:20%">Harga</th>
                    <th style="width:20%">Total</th>
                    <th style="width:10%">Aksi</th>
                </tr>
                </thead>
                <tbody>
                {{-- Baris pertama --}}
                <tr>
                    <td>
                        <select name="order_details[0][product_id]"
                                class="form-control product-select select2-product" required>
                            <option></option>
                            {{-- opsi produk akan di-inject via JS --}}
                        </select>
                    </td>
                    <td><input type="number" name="order_details[0][quantity]" class="form-control qty-input"
                               min="1" required></td>
                    <td><input type="text" name="order_details[0][price]"
                               class="form-control price-input" data-raw="0" readonly required></td>
                    <td><input type="text" name="order_details[0][total]"
                               class="form-control total-input" readonly required></td>
                    <td><button type="button" class="btn btn-danger remove-item">Hapus</button></td>
                </tr>
                </tbody>
            </table>

            {{-- Total Harga --}}
            <div class="mt-3">
                <table class="table w-auto">
                    <tr>
                        <td><strong>Total Harga</strong></td>
                        <td style="width:250px"><input type="text" id="total-price" class="form-control" readonly></td>
                    </tr>
                </table>
            </div>

            <button type="button" id="add-item" class="btn btn-primary">Tambah Produk</button>
            <button type="submit" class="btn btn-success">Simpan Pesanan</button>
        </form>
    </div>

    {{-- Select2 style --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
    <style>
        .select2-container .select2-selection--single {
            height: 38px !important; padding: 6px 12px !important;
            font-size: 0.9rem !important; border: 1px solid #ced4da; border-radius: .375rem;
        }
        .select2-container--default .select2-selection__arrow { height: 38px !important; top: 0 !important; right: 8px }
        .select2-container--default .select2-selection__rendered { color: #6c757d }
    </style>

    {{-- Scripts --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        /** ----------  GLOBAL STATE  ---------- **/
        let itemIndex = 1;                // untuk nama input dinamis
        let productsBySupplier = [];      // cache produk dari supplier terpilih

        /** ----------  HELPER  ---------- **/
        const formatNumber = n => new Intl.NumberFormat('id-ID').format(n);

        /** ----------  INIT SELECT2  ---------- **/
        function initSelect2(scope = document) {
            $(scope).find('.select2-supplier').select2({
                placeholder: 'Pilih pemasok', allowClear: true, width: '100%',
                language: {noResults: () => 'Tidak ditemukan'}
            });
            $(scope).find('.select2-product').select2({
                placeholder: 'Pilih produk', allowClear: true, width: '100%',
                language: {noResults: () => 'Tidak ditemukan'}
            });
        }
        initSelect2();

        // Auto-focus kolom pencarian ketika select2 dibuka
        $(document).on('select2:open', () => {
            setTimeout(() => {
                document.querySelector('.select2-container--open .select2-search__field')?.focus();
            }, 0);
        });

        /** ----------  SUPPLIER CHANGE  ---------- **/
        $('#supplierSelect').on('change', function () {
            const supplierId = $(this).val();

            // kosongkan cache & dropdown produk
            productsBySupplier = [];
            refreshAllProductSelect([]);

            // reset price/total
            $('.price-input').val('').attr('data-raw', 0);
            $('.qty-input, .total-input').val('');
            calculateTotalPrice();

            if (!supplierId) return;

            // Ambil produk via AJAX
            $.get(`/products/by-supplier/${supplierId}`, function (data) {
                productsBySupplier = data || [];
                refreshAllProductSelect(productsBySupplier);
            });
        });

        /** ----------  REFRESH PRODUCT SELECT  ---------- **/
        function optionHtml(list) {
            return list.map(p =>
                `<option value="${p.id}" data-price="${p.price}">${p.name}</option>`
            ).join('');
        }
        function refreshAllProductSelect(list) {
            $('.product-select').each(function () {
                // simpan pilihan lama untuk dibandingkan
                const prevVal = $(this).val();
                // destroy select2 sebelum mengubah option
                $(this).select2('destroy');
                $(this).html('<option></option>' + optionHtml(list));
                // kembalikan value jika masih ada di list
                if (list.find(p => p.id == prevVal)) $(this).val(prevVal);
                $(this).select2({
                    placeholder: 'Pilih produk', allowClear: true, width: '100%',
                    language: {noResults: () => 'Tidak ditemukan'}
                }).trigger('change'); // agar event price refresh jalan
            });
        }

        /** ----------  ADD ROW  ---------- **/
        $('#add-item').on('click', function () {
            const row = `
                <tr>
                    <td>
                        <select name="order_details[${itemIndex}][product_id]"
                                class="form-control product-select select2-product" required>
                            <option></option>
                            ${optionHtml(productsBySupplier)}
                        </select>
                    </td>
                    <td><input type="number" name="order_details[${itemIndex}][quantity]"
                               class="form-control qty-input" min="1" required></td>
                    <td><input type="text" name="order_details[${itemIndex}][price]"
                               class="form-control price-input" data-raw="0" readonly required></td>
                    <td><input type="text" name="order_details[${itemIndex}][total]"
                               class="form-control total-input" readonly required></td>
                    <td><button type="button" class="btn btn-danger remove-item">Hapus</button></td>
                </tr>`;
            $('#items-table tbody').append(row);
            initSelect2($('#items-table tbody tr:last')); // aktifkan select2 utk baris baru
            itemIndex++;
        });

        /** ----------  REMOVE ROW  ---------- **/
        $(document).on('click', '.remove-item', function () {
            $(this).closest('tr').remove();
            calculateTotalPrice();
        });

        /** ----------  PRODUCT CHANGE  ---------- **/
        $(document).on('change', '.product-select', function () {
            const row   = $(this).closest('tr');
            const price = parseInt(this.selectedOptions[0]?.dataset.price || 0);

            row.find('.price-input').val(formatNumber(price)).attr('data-raw', price);
            row.find('.qty-input').val('');
            row.find('.total-input').val('');
            calculateTotalPrice();
        });

        /** ----------  QTY INPUT  ---------- **/
        $(document).on('input', '.qty-input', function () {
            const row   = $(this).closest('tr');
            const qty   = parseInt(this.value) || 0;
            const price = parseInt(row.find('.price-input').data('raw')) || 0;
            const total = qty * price;

            row.find('.total-input').val(qty ? formatNumber(total) : '');
            calculateTotalPrice();
        });

        /** ----------  CALCULATE GRAND TOTAL  ---------- **/
        function calculateTotalPrice() {
            let grand = 0;
            $('.total-input').each(function () {
                grand += parseInt(this.value.replace(/\./g, '')) || 0;
            });
            $('#total-price').val(formatNumber(grand));
        }
    </script>
</x-app-layout>
