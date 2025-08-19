<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;   // <— tambahkan
use Illuminate\View\View;

class DashboardController extends Controller
{
    /** Kandidat nama kolom agar tahan perbedaan skema */
    private const DETAIL_QTY_CANDIDATES   = ['quantity', 'qty'];
    private const DETAIL_PRICE_CANDIDATES = ['price', 'unit_price', 'selling_price'];
    private const TRX_TOTAL_CANDIDATES    = ['total_amount', 'total', 'grand_total'];

    /** Helper: pilih kolom pertama yang tersedia pada sebuah tabel */
    private static function pickColumn(string $table, array $candidates): ?string
    {
        foreach ($candidates as $col) {
            if (Schema::hasColumn($table, $col)) return $col;
        }
        return null;
    }

    /** Helper: buat URL absolut ke gambar produk (storage/public/products) */
    private static function productImageUrl(?string $filename): string
    {
        $filename = $filename ?: 'default.png';
        return URL::asset('storage/products/' . $filename);
    }

    /**
     * Halaman dashboard utama.
     */
    public function index(): View
    {
        // 1) Ringkasan jumlah data
        $totalCategory    = Category::count();
        $totalProduct     = Product::count();
        $totalCustomer    = Customer::count();
        $totalTransaction = Transaction::count();

        // 2) 5 produk terlaris (berdasarkan total qty terjual)
        $tdTable = 'transaction_details';
        $qtyCol  = self::pickColumn($tdTable, self::DETAIL_QTY_CANDIDATES) ?? 'quantity'; // fallback

        $transactions = TransactionDetail::select('product_id', DB::raw("SUM({$qtyCol}) as transactions_sum_qty"))
            ->with('product:id,name,price,image')
            ->groupBy('product_id')
            ->orderByDesc('transactions_sum_qty')
            ->take(5)
            ->get();

        // 3) Produk dengan total batch qty ≤ 5 (gunakan relasi batch)
        $productsWithLowStock = Product::with('stockHistories')
            ->get()
            ->filter(fn ($p) => $p->stockHistories->sum('qty') <= 5);

        // 4) Produk dengan batch qty = 0
        $emptyBatchProducts = Product::with(['stockHistories' => function ($q) {
                $q->where('qty', 0)->orderBy('expired_date');
            }])
            ->whereHas('stockHistories', fn ($q) => $q->where('qty', 0))
            ->get();

        // 5) Produk dengan batch expired dalam 15 hari ke depan
        $productsExpiringSoon = Product::whereHas('stockHistories', function ($q) {
                $q->whereDate('expired_date', '<=', now()->addDays(15));
            })
            ->with(['stockHistories' => function ($q) {
                $q->whereDate('expired_date', '<=', now()->addDays(15))
                  ->orderBy('expired_date');
            }])
            ->get();

        // 6) Hitung jumlah notifikasi
        $lowStockCount = $productsWithLowStock->count();

        $expiredSoonCount = $productsExpiringSoon->reduce(function ($carry, $product) {
            $count = $product->stockHistories->filter(function ($batch) {
                return Carbon::parse($batch->expired_date)->isBefore(now()->addDays(15)->endOfDay());
            })->count();
            return $carry + $count;
        }, 0);

        $emptyBatchCount = $emptyBatchProducts->reduce(function ($carry, $product) {
            return $carry + $product->stockHistories->where('qty', 0)->count();
        }, 0);

        // 7) Kirim ke view
        return view('dashboard.index', compact(
            'totalCategory',
            'totalProduct',
            'totalCustomer',
            'totalTransaction',
            'transactions',
            'productsWithLowStock',
            'productsExpiringSoon',
            'emptyBatchProducts',
            'lowStockCount',
            'expiredSoonCount',
            'emptyBatchCount'
        ));
    }

    /**
     * Endpoint JSON untuk grafik time series analitik penjualan.
     * Query param:
     *   - range: 7d|30d|90d|ytd (default 30d)
     *   - metric: amount|qty (default amount)
     */
    public function timeseries(Request $req)
    {
        $range  = $req->get('range', '30d');      // 7d|30d|90d|ytd
        $metric = $req->get('metric', 'amount');  // amount|qty

        // Rentang tanggal (harian)
        $start = match ($range) {
            '7d'  => now()->startOfDay()->subDays(6),
            '30d' => now()->startOfDay()->subDays(29),
            '90d' => now()->startOfDay()->subDays(89),
            'ytd' => now()->startOfYear(),
            default => now()->startOfDay()->subDays(29),
        };
        $end = now()->endOfDay();

        $userId   = optional($req->user())->id ?? 'guest';
        $cacheKey = "dash:ts:v4:{$userId}:{$range}:{$metric}";

        // >>> Perbaikan penting: bawa $metric ke closure!
        return Cache::remember($cacheKey, 600, function () use ($start, $end, $metric) {
            $trxTable = 'transactions';
            $tdTable  = 'transaction_details';

            // 1) Tentukan kolom tanggal utama: pakai 'date' jika ada, else 'created_at'
            $dateCol = Schema::hasColumn($trxTable, 'date') ? 'date' : 'created_at';
            $useDateOnly = $dateCol === 'date'; // kolom bertipe DATE (tanpa waktu)

            // 2) Deteksi kolom amount dan kolom detail qty/price
            $totalCol = null;
            foreach (self::TRX_TOTAL_CANDIDATES as $c) {
                if (Schema::hasColumn($trxTable, $c)) { $totalCol = $c; break; }
            }
            $hasTotalAmount = $totalCol !== null;

            $qtyColDetail   = self::pickColumn($tdTable, self::DETAIL_QTY_CANDIDATES);
            $priceColDetail = self::pickColumn($tdTable, self::DETAIL_PRICE_CANDIDATES);
            $hasQtyDetail   = $qtyColDetail   !== null;
            $hasPriceDetail = $priceColDetail !== null;

            // Helper where range
            $applyRange = function ($q) use ($dateCol, $useDateOnly, $start, $end) {
                return $useDateOnly
                    ? $q->whereDate($dateCol, '>=', $start->toDateString())
                        ->whereDate($dateCol, '<=', $end->toDateString())
                    : $q->whereBetween($dateCol, [$start, $end]);
            };

            // 3) Ambil AMOUNT per hari
            if ($hasTotalAmount) {
                $amountRows = $applyRange(DB::table($trxTable))
                    ->selectRaw("DATE($dateCol) as d, SUM($totalCol) as total_amount")
                    ->groupBy('d')->orderBy('d')->get();
            } else {
                // Fallback: hitung amount dari detail (qty * price)
                $query = $applyRange(DB::table("$trxTable as t")->leftJoin("$tdTable as td", 'td.transaction_id', '=', 't.id'))
                    ->selectRaw("DATE(t.$dateCol) as d");

                if ($hasQtyDetail && $hasPriceDetail) {
                    $query->selectRaw("COALESCE(SUM(td.$qtyColDetail * td.$priceColDetail),0) as total_amount");
                } else {
                    $query->selectRaw("0 as total_amount");
                }

                $amountRows = $query->groupBy('d')->orderBy('d')->get();
            }

            // 4) Ambil QTY per hari
            if ($hasQtyDetail) {
                $qtyRows = $applyRange(DB::table("$trxTable as t")->join("$tdTable as td", 'td.transaction_id', '=', 't.id'))
                    ->selectRaw("DATE(t.$dateCol) as d, SUM(td.$qtyColDetail) as total_qty")
                    ->groupBy('d')->orderBy('d')->get();
            } else {
                $qtyRows = collect();
            }

            // 5) Gabungkan ke map tanggal
            $map = [];
            foreach ($amountRows as $r) {
                $map[$r->d] = [
                    'amount' => (float) ($r->total_amount ?? 0),
                    'qty'    => 0.0,
                ];
            }
            foreach ($qtyRows as $r) {
                $map[$r->d]['qty'] = (float) ($r->total_qty ?? 0);
                if (!isset($map[$r->d]['amount'])) $map[$r->d]['amount'] = 0.0;
            }

            // 6) Zero-fill + MA7
            $period = CarbonPeriod::create($start->copy()->startOfDay(), $end->copy()->startOfDay());
            $labels = [];
            $main   = [];
            $ma7    = [];
            $buffer = [];

            foreach ($period as $day) {
                $key = $day->toDateString();
                $labels[] = $day->locale('id')->isoFormat('D MMM');

                $valAmount = $map[$key]['amount'] ?? 0.0;
                $valQty    = $map[$key]['qty'] ?? 0.0;

                $val = $metric === 'qty' ? $valQty : $valAmount;

                $main[] = $val;

                $buffer[] = $val;
                if (count($buffer) > 7) array_shift($buffer);
                $ma7[] = count($buffer) ? array_sum($buffer)/count($buffer) : 0.0;
            }

            // 7) KPI konsisten (sum map)
            $ordersQuery = DB::table($trxTable);
            $ordersQuery = $useDateOnly
                ? $ordersQuery->whereDate($dateCol, '>=', $start->toDateString())->whereDate($dateCol, '<=', $end->toDateString())
                : $ordersQuery->whereBetween($dateCol, [$start, $end]);

            $orders = (int) $ordersQuery->count();

            $total_amount = array_sum(array_map(fn($x) => $x['amount'], $map ?: []));
            $total_qty    = array_sum(array_map(fn($x) => $x['qty'], $map ?: []));

            return response()->json([
                'labels' => $labels,
                'series' => [
                    'main' => array_map('floatval', $main),
                    'ma7'  => array_map('floatval', $ma7),
                ],
                'kpi' => [
                    'total_amount' => (float) $total_amount,
                    'total_qty'    => (float) $total_qty,
                    'orders'       => $orders,
                ],
            ]);
        });
    }

    /**
     * Endpoint Top Produk (untuk bar chart)
     * Mengembalikan image_url absolut agar bisa digambar di kanvas Chart.js
     */
    public function topProducts(Request $req)
    {
        $range  = $req->get('range', '30d');     // 7d|30d|90d|ytd
        $metric = $req->get('metric', 'amount'); // amount|qty
        $limit  = (int) $req->get('limit', 10);

        $trxTable = 'transactions';
        $tdTable  = 'transaction_details';

        // Pakai 'date' kalau ada, else 'created_at'
        $dateCol = Schema::hasColumn($trxTable, 'date') ? 'date' : 'created_at';

        $start = match ($range) {
            '7d'  => now()->startOfDay()->subDays(6),
            '30d' => now()->startOfDay()->subDays(29),
            '90d' => now()->startOfDay()->subDays(89),
            'ytd' => now()->startOfYear(),
            default => now()->startOfDay()->subDays(29),
        };
        $end = now()->endOfDay();

        // Kolom qty/harga detail (pakai helper pickColumn)
        $qtyCol   = self::pickColumn($tdTable, self::DETAIL_QTY_CANDIDATES)   ?? 'quantity';
        $priceCol = self::pickColumn($tdTable, self::DETAIL_PRICE_CANDIDATES) ?? 'price';

        $q = DB::table("$trxTable as t")
            ->join("$tdTable as d", 'd.transaction_id', '=', 't.id')
            ->join('products as p', 'p.id', '=', 'd.product_id')
            ->when(
                $dateCol === 'date',
                fn($qq)=>$qq->whereDate("t.$dateCol", '>=', $start->toDateString())
                            ->whereDate("t.$dateCol", '<=', $end->toDateString()),
                fn($qq)=>$qq->whereBetween("t.$dateCol", [$start, $end])
            )
            ->groupBy('p.id','p.name','p.image','p.price')
            ->orderByDesc(
                $metric === 'qty'
                    ? DB::raw("SUM(d.$qtyCol)")
                    : DB::raw("SUM(d.$qtyCol * d.$priceCol)")
            )
            ->limit($limit)
            ->selectRaw('p.id, p.name, p.image, p.price,
                SUM(d.'.$qtyCol.') as total_qty,
                SUM(d.'.$qtyCol.' * d.'.$priceCol.') as total_amount');

        $rows = $q->get();

        return response()->json([
            'items' => $rows->map(function ($r) {
                return [
                    'product_id' => $r->id,
                    'name'       => $r->name,
                    'image'      => $r->image,                               // tetap kirim jika Anda butuh
                    'image_url'  => self::productImageUrl($r->image),        // <— URL absolut untuk Chart.js
                    'price'      => (float) $r->price,
                    'total_qty'  => (float) $r->total_qty,
                    'total_amt'  => (float) $r->total_amount,
                ];
            }),
        ]);
    }
}
