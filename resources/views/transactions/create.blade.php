<x-app-layout>
    <x-page-title>Tambah Penjualan</x-page-title>

    <div class="bg-white rounded-2 shadow-sm p-4 mb-5">
        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        {{-- PERINGATAN REGULASI (dinamis, warning-only) --}}
        <div id="legal-warning" class="alert alert-warning d-none" role="alert" aria-live="polite">
            <div class="d-flex align-items-start">
                <div class="me-2">⚠️</div>
                <div>
                    <strong>Peringatan Regulasi:</strong> Terdapat item dengan golongan obat yang
                    <em>tidak boleh dijual bebas</em>. Penyerahan wajib melalui fasilitas kefarmasian
                    dan <em>berdasarkan resep</em> serta pencatatan sesuai ketentuan.
                    <ul id="legal-warning-list" class="mb-0 mt-2"></ul>
                </div>
            </div>
        </div>

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
                        <td class="product-cell">
                            <select name="products[0][product_id]" class="form-control product-select select2-product" required>
                                <option></option>
                                @foreach($products as $product)
                                    <option value="{{ $product->id }}"
                                            data-price="{{ $product->fifo_price }}"
                                            data-qty="{{ $product->stockHistories->sum('qty') }}"
                                            data-drug-class="{{ $product->drug_class }}"
                                            data-name="{{ $product->name }}">
                                        {{ $product->name }}
                                    </option>
                                @endforeach
                            </select>
                            {{-- Catatan regulasi per baris (muncul hanya jika produk terbatas) --}}
                            <div class="small text-danger fw-semibold legal-note d-none"></div>
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
        .select2-results__option { font-size: clamp(12px, 1.3vw, 14px); }
        .select2-search__field   { font-size: clamp(12px, 1.3vw, 14px); }

        /* Agar daftar peringatan rapi */
        #legal-warning-list li { margin-left: 1rem; }

        /* ===== Transformasi baris: default tengah, kalau ada catatan -> top aligned ===== */
        #items-table tbody td { vertical-align: middle; } /* default rapi saat TIDAK ada catatan */

        /* Baris yang punya catatan (JS menambah .has-legal-note ke <tr>) */
        #items-table tbody tr.has-legal-note td {
            vertical-align: top;
            padding-top: .25rem;
        }

        /* Catatan hanya muncul di baris yang ada kelasnya */
        .legal-note { display: none; margin-top: .25rem; }
        #items-table tbody tr.has-legal-note .legal-note {
            display: block;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
    </style>

    <script>
        // ===== Util Rupiah =====
        const formatNumber   = n => new Intl.NumberFormat('id-ID').format(Number(n) || 0);
        const parseNumber    = s => parseFloat((s || '0').toString().replace(/\./g,'')) || 0;
        const roundToNearest = (value, nearest = 100) => Math.round(value / nearest) * nearest;
        const formatRibuan   = angka => new Intl.NumberFormat('id-ID').format(angka || 0);
        const cleanAngka     = str => parseFloat((str || '').toString().replace(/\./g,'')) || 0;

        // ===== Golongan obat & label (dengan normalisasi) =====
        const DRUG_CLASS_LABELS = {
            'obat_bebas': 'Obat Bebas',
            'obat_bebas_terbatas': 'Obat Bebas Terbatas',
            'obat_keras': 'Obat Keras',
            'psikotropika': 'Psikotropika',
            'obat_narkotika': 'Narkotika',
            'obat_herbal': 'Obat Herbal',
            'obat_herbal_terstandar': 'Obat Herbal Terstandar',
            'fitofarmaka': 'Fitofarmaka',
        };
        const RESTRICTED = new Set(['obat_keras', 'psikotropika', 'obat_narkotika']);
        const norm = k => (k || '').toString().trim().toLowerCase().replace(/[\s-]+/g,'_');
        const formatDrugClass = key => DRUG_CLASS_LABELS[norm(key)] || key || '-';

        // ===== Select2 init =====
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

        // Pastikan event change terpicu saat memilih item di Select2
        $(document).on('select2:select', '.product-select', function () { $(this).trigger('change'); });
        $(document).on('select2:open', function () {
            setTimeout(() => {
                document.querySelector('.select2-container--open .select2-search__field')?.focus();
            }, 0);
        });

        // ===== Baris dinamis =====
        let itemIndex = 1;
        $('#add-item').on('click', () => {
            const row = `
            <tr>
                <td class="product-cell">
                    <select name="products[${itemIndex}][product_id]" class="form-control product-select select2-product" required>
                        <option></option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}"
                                    data-price="{{ $product->fifo_price }}"
                                    data-qty="{{ $product->stockHistories->sum('qty') }}"
                                    data-drug-class="{{ $product->drug_class }}"
                                    data-name="{{ $product->name }}">
                                {{ $product->name }}
                            </option>
                        @endforeach
                    </select>
                    <div class="small text-danger fw-semibold legal-note d-none"></div>
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

        // ===== Hapus baris =====
        $(document).on('click', '.remove-item', function () {
            $(this).closest('tr').remove();
            calculateTotalPrice();
            updateLegalWarning();
        });

        // ===== Saat pilih produk =====
        $(document).on('change', '.product-select', function () {
            const opt  = this.selectedOptions[0];
            const $row = $(this).closest('tr');

            if (!opt) return;

            const priceRaw = parseNumber(opt.dataset.price || 0);
            const stock    = parseInt(opt.dataset.qty || 0);
            const dcNorm   = norm(opt.dataset.drugClass || '');

            // harga (dibulatkan per Rp100 jika perlu)
            const roundedPrice = roundToNearest(priceRaw, 100);
            $row.find('.price-input').val(formatNumber(roundedPrice));
            $row.find('.hidden-price').val(roundedPrice);
            $row.find('.qty-input').attr('max', stock).val('');
            $row.find('.total-input').val('');

            // catatan regulasi per baris + transformasi tampilan baris
            const $note    = $row.find('.legal-note');
            if (RESTRICTED.has(dcNorm)) {
                const msg = `Perhatian: ${formatDrugClass(dcNorm)} — wajib resep & pencatatan.`;
                $note.text(msg).attr('title', msg).removeClass('d-none');
                $row.addClass('has-legal-note');   // aktifkan top-aligned
            } else {
                $note.text('').attr('title','').addClass('d-none');
                $row.removeClass('has-legal-note'); // kembali align tengah
            }

            updateLegalWarning();
        });

        // ===== Hitung total & kembalian =====
        $(document).on('input', '.qty-input', function () {
            const $row = $(this).closest('tr');
            const price = parseNumber($row.find('.hidden-price').val());
            const qty   = parseInt(this.value) || 0;

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
            const paid  = parseNumber($('#paid-amount').val());
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
            updateLegalWarning(); // evaluasi awal (jika ada preselect)
        });

        // ===== Peringatan global (atas) =====
        function updateLegalWarning() {
            let restrictedItems = [];
            $('#items-table tbody tr').each(function () {
                const opt = $(this).find('.product-select')[0]?.selectedOptions[0];
                if (!opt) return;
                const dcNorm = norm(opt.dataset.drugClass || '');
                const name   = (opt.dataset.name || opt.textContent || '').trim();
                if (RESTRICTED.has(dcNorm)) restrictedItems.push({ name, dc: dcNorm });
            });

            if (restrictedItems.length > 0) {
                const listHtml = restrictedItems.map(i =>
                    `<li><strong>${i.name}</strong> — ${formatDrugClass(i.dc)} (wajib resep & pencatatan)</li>`
                ).join('');
                $('#legal-warning-list').html(listHtml);
                $('#legal-warning').removeClass('d-none');
            } else {
                $('#legal-warning-list').empty();
                $('#legal-warning').addClass('d-none');
            }
        }
    </script>
</x-app-layout>
