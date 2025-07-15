<x-app-layout>
    {{-- Judul Halaman --}}
    <x-page-title>Ubah Penjualan</x-page-title>

    <div class="bg-white rounded-2 shadow-sm p-4 mb-5">
        <form action="{{ route('transactions.update', $transaction->id) }}" method="POST">
            @csrf
            @method('PUT')

            {{-- ========== HEADER ========== --}}
            <div class="row">
                <div class="col-lg-6">
                    {{-- Tanggal --}}
                    <div class="mb-3">
                        <label class="form-label">Tanggal <span class="text-danger">*</span></label>
                        <input type="text" name="date"
                               class="form-control datepicker @error('date') is-invalid @enderror"
                               value="{{ old('date', $transaction->date) }}" autocomplete="off">
                        @error('date')<div class="alert alert-danger mt-2">{{ $message }}</div>@enderror
                    </div>

                    {{-- Pelanggan --}}
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

            {{-- ========== TABEL PRODUK ========== --}}
            <table class="table" id="items-table">
                <thead>
                    <tr>
                        <th>Produk</th><th>Jumlah</th><th>Harga</th><th>Total</th><th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($transaction->details as $i => $d)
                        <tr>
                            <td>
                                <select name="items[{{ $i }}][product_id]" class="form-select product-select select2-product">
                                    <option></option>
                                    @foreach ($products as $p)
                                        <option value="{{ $p->id }}" data-price="{{ $p->price }}"
                                                {{ $d->product_id == $p->id ? 'selected' : '' }}>
                                            {{ $p->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td><input type="number" min="1" name="items[{{ $i }}][quantity]"
                                       class="form-control qty-input" value="{{ $d->quantity }}"></td>
                            <td><input type="text" class="form-control price-input"
                                       value="{{ number_format($d->price,0,',','.') }}" readonly></td>
                            <td><input type="text" class="form-control total-input"
                                       value="{{ number_format($d->total,0,',','.') }}" readonly></td>
                            <td><button type="button" class="btn btn-danger remove-item">Hapus</button></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- ========== GRAND TOTAL ========== --}}
            <div class="mt-3">
                <table class="table w-auto">
                    <tr>
                        <td class="align-middle"><strong>Total Harga</strong></td>
                        <td style="width:250px"><input type="text" id="total-price" class="form-control" readonly></td>
                    </tr>
                </table>
            </div>

            <button type="button" id="add-item" class="btn btn-primary">Tambah Produk</button>

            {{-- ========== ACTION ========== --}}
            <div class="pt-4 pb-2 mt-5 border-top">
                <div class="d-grid gap-3 d-sm-flex">
                    <button type="submit" class="btn btn-primary px-3">Perbarui</button>
                    <a href="{{ route('transactions.index') }}" class="btn btn-secondary px-3">Kembali</a>
                </div>
            </div>
        </form>
    </div>

    {{-- ========== STYLE ========== --}}
    <style>
        .select2-container .select2-selection--single{
            height:38px!important;padding:6px 12px!important;font-size:.85rem!important;line-height:24px!important;
            border:1px solid #ced4da;border-radius:.375rem
        }
        .select2-container .select2-selection__arrow{height:38px!important;top:0!important;right:8px}
        .select2-container--default .select2-results__option{font-size:.9rem;padding:6px 12px}
    </style>

    {{-- ========== SCRIPTS ========== --}}
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <link  href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        /* ---------- Helpers ---------- */
        const toIDR = n => new Intl.NumberFormat('id-ID').format(+n || 0);

        /* ---------- Init Select2 ---------- */
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

        /* Fokus otomatis ke kolom search */
        $(document).on('select2:open',()=>setTimeout(()=>{
            document.querySelector('.select2-container--open .select2-search__field')?.focus();
        },0));

        /* ---------- Dynamic Row ---------- */
        let idx = {{ count($transaction->details) }};
        $('#add-item').click(()=>{
            $('#items-table tbody').append(`
                <tr>
                    <td>
                        <select name="items[${idx}][product_id]" class="form-select product-select select2-product">
                            <option></option>
                            @foreach($products as $p)
                                <option value="{{ $p->id }}" data-price="{{ $p->price }}">{{ $p->name }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td><input type="number" min="1" name="items[${idx}][quantity]" class="form-control qty-input"></td>
                    <td><input type="text" class="form-control price-input" readonly></td>
                    <td><input type="text" class="form-control total-input" readonly></td>
                    <td><button type="button" class="btn btn-danger remove-item">Hapus</button></td>
                </tr>`);
            initSelect2($('#items-table tbody tr:last'));
            idx++;
        });

        /* ---------- Remove Row ---------- */
        $(document).on('click','.remove-item',function(){
            $(this).closest('tr').remove(); updateGrand();
        });

        /* ---------- Pilih Produk ---------- */
        $(document).on('change','.product-select',function(){
            const price = parseFloat(this.selectedOptions[0]?.dataset.price||0);
            const $row  = $(this).closest('tr');
            $row.find('.price-input').val(toIDR(price));
            $row.find('.qty-input').val(1).trigger('input'); // default qty = 1
        });

        /* ---------- Input Qty ---------- */
        $(document).on('input','.qty-input',function(){
            const $row  = $(this).closest('tr');
            const price = parseFloat(($row.find('.price-input').val()||'0').replace(/\./g,''))||0;
            const qty   = +this.value || 0;
            $row.find('.total-input').val(qty ? toIDR(price * qty) : '');
            updateGrand();
        });

        /* ---------- Grand Total ---------- */
        function updateGrand(){
            let sum = 0;
            $('.total-input').each(function(){
                sum += parseFloat((this.value||'0').replace(/\./g,''))||0;
            });
            $('#total-price').val(toIDR(sum));
        }
        updateGrand();
    </script>
</x-app-layout>
