<x-app-layout>
    {{-- Judul Halaman --}}
    <x-page-title>Dashboard</x-page-title>

    {{-- Kartu Statistik --}}
    <div class="row mb-3">
        @php
            $cards = [
                ['title' => 'Kategori', 'count' => $totalCategory, 'icon' => 'ti-category', 'color' => 'primary-2'],
                ['title' => 'Produk',   'count' => $totalProduct,  'icon' => 'ti-copy',    'color' => 'success'],
                ['title' => 'Pelanggan','count' => $totalCustomer, 'icon' => 'ti-users',   'color' => 'warning'],
                ['title' => 'Transaksi','count' => $totalTransaction,'icon'=>'ti-folders','color' => 'info'],
            ];
        @endphp

        @foreach ($cards as $card)
            <div class="col-lg-6 col-xl-3">
                <div class="bg-white rounded-2 shadow-sm p-4 mb-4">
                    <div class="d-flex align-items-center">
                        <div class="me-4">
                            <i class="ti {{ $card['icon'] }} fs-1 bg-{{ $card['color'] }} text-white rounded-2 p-2"></i>
                        </div>
                        <div>
                            <p class="text-muted mb-1">{{ $card['title'] }}</p>
                            <h5 class="fw-bold mb-0">{{ $card['count'] }}</h5>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    {{-- 5 Produk Terlaris --}}
    <div class="bg-white rounded-2 shadow-sm p-4 mb-5">
        <h6 class="mb-3 text-center">
            <i class="ti ti-folder-star fs-5 me-1"></i> 5 Produk Terlaris
        </h6>
        <div class="table-responsive">
            <table class="table table-bordered table-striped text-center">
                <thead><tr><th>Gambar</th><th>Nama</th><th>Harga</th><th>Terjual</th></tr></thead>
                <tbody>
                    @forelse ($transactions as $t)
                        <tr>
                            <td><img src="{{ asset('/storage/products/' . $t->product->image) }}" width="80" class="img-thumbnail"></td>
                            <td>{{ $t->product->name }}</td>
                            <td>Rp {{ number_format($t->product->price, 0, '', '.') }}</td>
                            <td>{{ $t->transactions_sum_qty }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center">Tidak ada data tersedia.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    {{-- Analitik Penjualan (total) --}}
    <div class="bg-white rounded-2 shadow-sm p-4 mb-5">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0"><i class="ti ti-chart-line fs-5 me-1"></i> Analitik Penjualan</h6>
            <div class="d-flex gap-2">
                <select id="rangeSelect" class="form-select form-select-sm">
                    <option value="7d">7 Hari</option>
                    <option value="30d" selected>30 Hari</option>
                    <option value="90d">90 Hari</option>
                    <option value="ytd">Sejak Awal Tahun</option>
                </select>
                <select id="metricSelect" class="form-select form-select-sm">
                    <option value="amount" selected>Omzet (Rp)</option>
                    <option value="qty">Kuantitas (Qty)</option>
                </select>
            </div>
        </div>

        <div class="row text-center mb-3" id="kpiRow">
            <div class="col-12 col-md-4">
                <div class="small text-muted">Total Omzet</div>
                <div class="fw-bold" id="kpiAmount">-</div>
            </div>
            <div class="col-12 col-md-4">
                <div class="small text-muted">Total Qty</div>
                <div class="fw-bold" id="kpiQty">-</div>
            </div>
            <div class="col-12 col-md-4">
                <div class="small text-muted">Jumlah Transaksi</div>
                <div class="fw-bold" id="kpiOrders">-</div>
            </div>
        </div>

        <canvas id="salesLine"></canvas>
    </div>

    {{-- Produk Terlaris (Bar Chart Horizontal) --}}
    <div class="bg-white rounded-2 shadow-sm p-4 mb-5">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="mb-0">
                <i class="ti ti-bar-chart fs-5 me-1"></i>
                <span id="topProdukTitle">Produk Terlaris dalam</span>
            </h6>
        </div>
        <div class="chart-box">
            <canvas id="topProductsBar"></canvas>
        </div>
    </div>

        {{-- Stok Produk yang Hampir Habis --}}
    <div class="bg-white rounded-2 shadow-sm p-4 mb-5">
        <h6 class="mb-3 text-center">
            <i class="ti ti-box fs-5 me-1"></i> Stok Produk yang Hampir Habis
        </h6>
        <div class="table-responsive">
            <table class="table table-bordered table-striped text-center">
                <thead><tr><th>Gambar</th><th>Nama</th><th>Harga</th><th>Total Stok</th></tr></thead>
                <tbody>
                    @forelse ($productsWithLowStock as $p)
                        <tr>
                            <td><img src="{{ asset('/storage/products/' . $p->image) }}" width="80" class="img-thumbnail"></td>
                            <td>{{ $p->name }}</td>
                            <td>Rp {{ number_format($p->price, 0, '', '.') }}</td>
                            <td>{{ $p->stockHistories->sum('qty') }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center">Tidak ada stok produk yang hampir habis.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Produk dengan Batch Kosong --}}
    <div class="bg-white rounded-2 shadow-sm p-4 mb-5">
        <h6 class="mb-3 text-center">
            <i class="ti ti-box-off fs-5 me-1"></i> Produk dengan Batch Kosong (Qty = 0)
        </h6>
        <div class="table-responsive">
            <table class="table table-bordered table-striped text-center">
                <thead><tr><th>Gambar</th><th>Nama Produk</th><th>Harga Batch</th><th>Tgl Expired</th></tr></thead>
                <tbody>
                    @forelse ($emptyBatchProducts as $product)
                        @foreach ($product->stockHistories as $batch)
                            <tr>
                                <td><img src="{{ asset('/storage/products/' . $product->image) }}" width="80" class="img-thumbnail"></td>
                                <td>{{ $product->name }}</td>
                                <td>Rp {{ number_format($batch->price, 0, '', '.') }}</td>
                                <td>{{ \Carbon\Carbon::parse($batch->expired_date)->locale('id')->isoFormat('D MMMM YYYY') }}</td>
                            </tr>
                        @endforeach
                    @empty
                        <tr><td colspan="4" class="text-center">Tidak ada produk dengan batch kosong.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Produk yang Akan Kedaluwarsa --}}
    <div class="bg-white rounded-2 shadow-sm p-4 mb-5">
        <h6 class="mb-3 text-center">
            <i class="ti ti-calendar-time fs-5 me-1"></i> Produk yang Akan Kedaluwarsa dalam 1-15 Hari
        </h6>
        <div class="table-responsive">
            <table class="table table-bordered table-striped text-center">
                <thead>
                    <tr>
                        <th>Gambar</th>
                        <th>Nama Produk</th>
                        <th>Harga Batch</th>
                        <th>Tgl Expired</th>
                        <th>Sisa Hari</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($productsExpiringSoon as $product)
                        @foreach ($product->stockHistories as $batch)
                            @php
                                $exp     = \Carbon\Carbon::parse($batch->expired_date)->startOfDay();
                                $today   = \Carbon\Carbon::now()->startOfDay();
                                $daysLeft = $today->diffInDays($exp, false);
                            @endphp
                            <tr>
                                <td><img src="{{ asset('/storage/products/' . $product->image) }}" width="80" class="img-thumbnail"></td>
                                <td>{{ $product->name }}</td>
                                <td>Rp {{ number_format($batch->price, 0, '', '.') }}</td>
                                <td>
                                    <span class="badge {{ $daysLeft < 0 ? 'bg-danger' : 'bg-warning' }}">
                                        {{ $exp->locale('id')->isoFormat('D MMMM YYYY') }}
                                    </span>
                                </td>
                                <td>{{ $daysLeft < 0 ? 'Sudah lewat' : $daysLeft . ' hari' }}</td>
                            </tr>
                        @endforeach
                    @empty
                        <tr><td colspan="5" class="text-center">Tidak ada produk yang mendekati kadaluarsa.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- CSS khusus chart --}}
    <style>
        .chart-box {
            height: 420px;
            position: relative;
        }
        .chart-box canvas {
            height: 100% !important;
            width: 100% !important;
        }
    </style>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
    document.addEventListener('DOMContentLoaded', () => {
        const rangeSel  = document.getElementById('rangeSelect');
        const metricSel = document.getElementById('metricSelect');

        const salesCtx  = document.getElementById('salesLine').getContext('2d');
        const topCtx    = document.getElementById('topProductsBar').getContext('2d');

        let salesChart = null;
        let topChart   = null;

        const humanRange = (v) => ({
            '7d':'7 Hari','30d':'30 Hari','90d':'90 Hari','ytd':'Sejak Awal Tahun'
        })[v] || 'Periode';

        // -------- Grafik Analitik Penjualan --------
        async function loadSalesData() {
            const range  = rangeSel.value;
            const metric = metricSel.value;

            const res  = await fetch(`{{ route('dashboard.analytics.timeseries') }}?range=${range}&metric=${metric}`, {
                headers: { 'X-Requested-With':'XMLHttpRequest' },
                credentials: 'same-origin'
            });
            const json = await res.json();

            document.getElementById('kpiAmount').textContent =
                new Intl.NumberFormat('id-ID', {style:'currency', currency:'IDR'}).format(json.kpi.total_amount || 0);
            document.getElementById('kpiQty').textContent    = (json.kpi.total_qty ?? 0).toLocaleString('id-ID');
            document.getElementById('kpiOrders').textContent = (json.kpi.orders ?? 0).toLocaleString('id-ID');

            const datasets = [
                { label: metric==='qty' ? 'Qty Harian' : 'Omzet Harian', data: json.series.main, tension: 0.3, fill: false },
                { label: 'Rata-rata 7 Hari', data: json.series.ma7, borderDash: [6,6], tension: 0.3, fill: false }
            ];

            if (salesChart) salesChart.destroy();
            salesChart = new Chart(salesCtx, {
                type: 'line',
                data: { labels: json.labels, datasets },
                options: {
                    responsive: true,
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: { display: true },
                        tooltip: {
                            callbacks: {
                                label: (ctx) => {
                                    const v = ctx.parsed.y ?? 0;
                                    return metric==='qty'
                                        ? `${ctx.dataset.label}: ${v.toLocaleString('id-ID')}`
                                        : `${ctx.dataset.label}: ` + new Intl.NumberFormat('id-ID',{style:'currency',currency:'IDR'}).format(v);
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            ticks: {
                                callback: (v) => metric==='qty'
                                    ? v.toLocaleString('id-ID')
                                    : new Intl.NumberFormat('id-ID',{notation:'compact',maximumFractionDigits:1}).format(v)
                            }
                        }
                    }
                }
            });
        }

        // -------- Grafik Top Produk (horizontal) --------
        async function loadTopProducts() {
            const range  = rangeSel.value;
            const metric = metricSel.value;

            const res  = await fetch(`{{ route('dashboard.analytics.top_products') }}?range=${range}&metric=${metric}`, {
                headers: { 'X-Requested-With':'XMLHttpRequest' }, credentials: 'same-origin'
            });
            const json = await res.json();

            document.getElementById('topProdukTitle').textContent = `Produk Terlaris dalam ${humanRange(range)}`;

            const labels = json.items.map(i => i.name);
            const data   = json.items.map(i => metric==='qty' ? i.total_qty : i.total_amt);

            if (topChart) { topChart.destroy(); topChart = null; }

            topChart = new Chart(topCtx, {
                type: 'bar',
                data: {
                    labels,
                    datasets: [{
                        label: metric==='qty' ? 'Qty' : 'Omzet',
                        data,
                        borderWidth: 1,
                        barPercentage: 0.7,
                        categoryPercentage: 0.7
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    indexAxis: 'y',
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: (ctx) => {
                                    const v = ctx.parsed.x ?? 0;
                                    return metric==='qty'
                                        ? `Qty: ${v.toLocaleString('id-ID')}`
                                        : `Omzet: ` + new Intl.NumberFormat('id-ID',{style:'currency',currency:'IDR'}).format(v);
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            beginAtZero: true,
                            ticks: {
                                callback: (v) => metric==='qty'
                                    ? v.toLocaleString('id-ID')
                                    : new Intl.NumberFormat('id-ID',{notation:'compact',maximumFractionDigits:1}).format(v)
                            },
                            grid: { drawBorder: false }
                        },
                        y: {
                            ticks: {
                                autoSkip: false,
                                callback: (val, idx) => {
                                    const t = labels[idx] ?? '';
                                    return t.length > 28 ? t.slice(0, 28) + '…' : t;
                                }
                            },
                            grid: { drawBorder: false }
                        }
                    }
                }
            });
        }

        function reloadAll() {
            loadSalesData();
            loadTopProducts();
        }

        rangeSel.addEventListener('change', reloadAll);
        metricSel.addEventListener('change', reloadAll);

        reloadAll();
    });
    </script>
    @endpush
</x-app-layout>
