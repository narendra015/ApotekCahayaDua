<x-app-layout>
    <x-page-title>Edit Penjualan</x-page-title>

    <div class="bg-white rounded-2 shadow-sm p-4 mb-5">
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

        <form action="{{ route('transactions.update', $transaction->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="row">
                <div class="col-lg-6">
                    <div class="mb-3">
                        <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                        <input type="text" name="date"
                               class="form-control datepicker @error('date') is-invalid @enderror"
                               value="{{ old('date', $transaction->date) }}" autocomplete="off">
                        @error('date')<div class="alert alert-danger mt-2">{{ $message }}</div>@enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Pelanggan <span class="text-danger">*</span></label>
                        <select name="customer_id"
                                class="form-select select2-customer @error('customer_id') is-invalid @enderror">
                            <option></option>
                            @foreach ($customers as $customer)
                                <option value="{{ $customer->id }}"
                                    {{ old('customer_id', $transaction->customer_id) == $customer->id ? 'selected' : '' }}>
                                    {{ $customer->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('customer_id')<div class="alert alert-danger mt-2">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

            <hr class="mt-4">

            <table class="table" id="items-table">
                <thead>
                    <tr>
                        <th>Produk</th><th>Jumlah</th><th>Harga</th><th>Total</th><th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($transaction->details as $i => $d)
                        <tr>
                            <td class="product-cell">
                                <select name="items[{{ $i }}][product_id]" class="form-select product-select select2-product">
                                    <option></option>
                                    @foreach ($products as $p)
                                        <option value="{{ $p->id }}"
                                            data-price="{{ $p->price ?? $p->fifo_price ?? 0 }}"
                                            data-drug-class="{{ $p->drug_class }}"
                                            data-name="{{ $p->name }}"
                                            {{ $d->product_id == $p->id ? 'selected' : '' }}>
                                            {{ $p->name }}
                                        </option>
                                    @endforeach
                                </select>
                                {{-- Catatan regulasi (muncul hanya bila terbatas) --}}
                                <div class="small text-danger fw-semibold legal-note d-none"></div>
                            </td>
                            <td>
                                <input type="number" min="1" name="items[{{ $i }}][quantity]"
                                       class="form-control qty-input" value="{{ $d->quantity }}">
                            </td>
                            <td>
                                <input type="text" class="form-control price-input"
                                       value="{{ number_format($d->price,0,',','.') }}" readonly>
                            </td>
                            <td>
                                <input type="text" class="form-control total-input"
                                       value="{{ number_format($d->total,0,',','.') }}" readonly>
                            </td>
                            <td><button type="button" class="btn btn-danger remove-item">Hapus</button></td>
                        </tr>
                    @endforeach
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
                            <input type="text" id="paid-amount-display" class="form-control" required
                                   value="{{ number_format(old('paid_amount', $transaction->paid_amount ?? 0), 0, ',', '.') }}">
                            <input type="hidden" name="paid_amount" id="paid-amount"
                                   value="{{ old('paid_amount', $transaction->paid_amount ?? 0) }}">
                        </td>
                        <td class="align-middle"><strong>Kembalian</strong></td>
                        <td>
                            <input type="text" id="change-amount" class="form-control" readonly
                                   value="{{ number_format($transaction->change_amount ?? 0, 0, ',', '.') }}">
                        </td>
                    </tr>
                </table>
            </div>

            <button type="button" id="add-item" class="btn btn-primary">Tambah Produk</button>

            <div class="pt-4 pb-2 mt-5 border-top">
                <div class="d-grid gap-3 d-sm-flex">
                    <button type="submit" class="btn btn-primary px-3">Perbarui</button>
                    <a href="{{ route('transactions.index') }}" class="btn btn-secondary px-3">Kembali</a>
                </div>
            </div>
        </form>
    </div>

    {{-- STYLE --}}
    <style>
        .select2-container .select2-selection--single {
            height:38px!important;padding:6px 12px!important;font-size:.85rem!important;line-height:24px!important;
            border:1px solid #ced4da;border-radius:.375rem
        }
        .select2-container .select2-selection__arrow { height:38px!important;top:0!important;right:8px }
        .select2-container--default .select2-results__option { font-size:.9rem;padding:6px 12px }

        /* Default sejajar tengah; jika ada catatan -> top aligned */
        #items-table tbody td { vertical-align: middle; }
        #items-table tbody tr.has-legal-note td { vertical-align: top; padding-top: .25rem; }

        /* Catatan hanya tampil saat terbatas; tinggi baris stabil */
        .legal-note { display:none; margin-top:.25rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
        #items-table tbody tr.has-legal-note .legal-note { display:block; }
    </style>

    {{-- SCRIPTS --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link  href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        // ===== Util Rupiah =====
        const toIDR = n => new Intl.NumberFormat('id-ID').format(+n || 0);
        const parseNumber = s => parseFloat((s || '0').replace(/\./g,'')) || 0;
        const roundToNearest = (value, nearest = 100) => Math.round(value / nearest) * nearest;
        const formatRibuan = angka => new Intl.NumberFormat('id-ID').format(angka || 0);
        const cleanAngka = str => parseFloat((str || '').replace(/\./g,'')) || 0;

        // ===== Golongan obat (SATU definisi saja) =====
        const RESTRICTED = new Set(['obat_keras','psikotropika','obat_narkotika']);
        const norm = k => (k || '').toString().trim().toLowerCase().replace(/[\s-]+/g,'_');
        const DRUG_CLASS_LABELS = {
            'obat_bebas':'Obat Bebas',
            'obat_bebas_terbatas':'Obat Bebas Terbatas',
            'obat_keras':'Obat Keras',
            'psikotropika':'Psikotropika',
            'obat_narkotika':'Narkotika',
            'obat_herbal':'Obat Herbal',
            'obat_herbal_terstandar':'Obat Herbal Terstandar',
            'fitofarmaka':'Fitofarmaka',
        };
        const labelDrug = k => DRUG_CLASS_LABELS[norm(k)] || k || '-';

        // ===== Select2 =====
        function initSelect2(target=document){
            $(target).find('.select2-customer').select2({
                placeholder:'Pilih pelanggan', allowClear:true, width:'100%',
                language:{ noResults:()=> 'Tidak ditemukan' }
            });
            $(target).find('.select2-product').select2({
                placeholder:'Pilih produk', allowClear:true, width:'100%',
                language:{ noResults:()=> 'Tidak ditemukan' }
            });
        }
        initSelect2();

        // Pastikan select2 memicu handler change
        $(document).on('select2:select', '.product-select', function(){ $(this).trigger('change'); });
        $(document).on('select2:open',()=>setTimeout(()=>{
            document.querySelector('.select2-container--open .select2-search__field')?.focus();
        },0));

        // ===== Tambah baris =====
        let idx = {{ count($transaction->details) }};
        $('#add-item').click(()=>{
            $('#items-table tbody').append(`
                <tr>
                    <td class="product-cell">
                        <select name="items[${idx}][product_id]" class="form-select product-select select2-product">
                            <option></option>
                            @foreach($products as $p)
                                <option value="{{ $p->id }}"
                                        data-price="{{ $p->price ?? $p->fifo_price ?? 0 }}"
                                        data-drug-class="{{ $p->drug_class }}"
                                        data-name="{{ $p->name }}">
                                    {{ $p->name }}
                                </option>
                            @endforeach
                        </select>
                        <div class="small text-danger fw-semibold legal-note d-none"></div>
                    </td>
                    <td><input type="number" min="1" name="items[${idx}][quantity]" class="form-control qty-input"></td>
                    <td><input type="text" class="form-control price-input" readonly></td>
                    <td><input type="text" class="form-control total-input" readonly></td>
                    <td><button type="button" class="btn btn-danger remove-item">Hapus</button></td>
                </tr>`);
            initSelect2($('#items-table tbody tr:last'));
            idx++;
        });

        // ===== Hapus baris =====
        $(document).on('click','.remove-item',function(){
            $(this).closest('tr').remove();
            updateGrand(); updateLegalWarning();
        });

        // ===== Terapkan pilihan produk ke baris =====
        function applySelection($row, opt, {preserveInputs=false}={}){
            if (!opt) return;

            const priceRaw = parseFloat(opt.dataset.price || 0);
            const rounded  = roundToNearest(priceRaw, 100);
            const dcNorm   = norm(opt.dataset.drugClass);

            // Harga & total
            if (!preserveInputs || !$row.find('.price-input').val()) {
                $row.find('.price-input').val(toIDR(rounded));
            }
            const qty = parseFloat($row.find('.qty-input').val()) || 0;
            if (!preserveInputs || !$row.find('.total-input').val()) {
                $row.find('.total-input').val(qty ? toIDR(rounded * qty) : '');
            }

            // Catatan & transformasi baris
            const $note = $row.find('.legal-note');
            if (RESTRICTED.has(dcNorm)) {
                const message = `Perhatian: ${labelDrug(dcNorm)} — wajib resep & pencatatan.`;
                $note.text(message).attr('title', message).removeClass('d-none');
                $row.addClass('has-legal-note');
            } else {
                $note.text('').attr('title','').addClass('d-none');
                $row.removeClass('has-legal-note');
            }
        }

        // ===== Saat pilih produk =====
        $(document).on('change','.product-select',function(){
            const $row = $(this).closest('tr');
            const opt  = this.selectedOptions[0];
            applySelection($row, opt, {preserveInputs:false});
            updateGrand(); updateLegalWarning();
        });

        // ===== Qty berubah =====
        $(document).on('input','.qty-input',function(){
            const $row  = $(this).closest('tr');
            const price = parseNumber($row.find('.price-input').val());
            const qty   = +this.value || 0;
            $row.find('.total-input').val(qty ? toIDR(price * qty) : '');
            updateGrand();
        });

        // ===== Grand total & kembalian =====
        function updateGrand(){
            let sum = 0;
            $('.total-input').each(function(){ sum += parseNumber(this.value); });
            $('#total-price').val(toIDR(sum));
            updateChange();
        }

        function updateChange() {
            const total = parseNumber($('#total-price').val());
            const paid  = parseNumber($('#paid-amount').val());
            const change = paid - total;
            $('#change-amount').val(change >= 0 ? toIDR(change) : '0');
        }

        // Format Uang Dibayar
        $('#paid-amount-display').on('input', function () {
            const raw = cleanAngka($(this).val());
            $('#paid-amount').val(raw);
            $(this).val(formatRibuan(raw));
            updateChange();
        });
        $(document).ready(function () {
            const awal = cleanAngka($('#paid-amount-display').val());
            $('#paid-amount-display').val(formatRibuan(awal));
            $('#paid-amount').val(awal);
        });

        // ===== Peringatan global (atas) =====
        function updateLegalWarning() {
            const items = [];
            $('#items-table tbody tr').each(function () {
                const opt = $(this).find('.product-select')[0]?.selectedOptions[0];
                if (!opt) return;
                const dcNorm = norm(opt.dataset.drugClass);
                const name   = (opt.dataset.name || opt.textContent || '').trim();
                if (RESTRICTED.has(dcNorm)) items.push({ name, dc: dcNorm });
            });

            if (items.length > 0) {
                const listHtml = items.map(i =>
                    `<li><strong>${i.name}</strong> — ${labelDrug(i.dc)} (wajib resep & pencatatan)</li>`
                ).join('');
                $('#legal-warning-list').html(listHtml);
                $('#legal-warning').removeClass('d-none');
            } else {
                $('#legal-warning-list').empty();
                $('#legal-warning').addClass('d-none');
            }
        }

        // ===== Inisialisasi baris existing =====
        $(document).ready(function(){
            $('#items-table tbody tr').each(function(){
                const sel = $(this).find('.product-select')[0];
                const opt = sel?.selectedOptions[0];
                applySelection($(this), opt, {preserveInputs:true});
            });
            updateGrand();
            updateLegalWarning();
        });
    </script>
</x-app-layout>
