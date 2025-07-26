<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Customer;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(): View
    {
        // Jumlah data
        $totalCategory     = Category::count();
        $totalProduct      = Product::count();
        $totalCustomer     = Customer::count();
        $totalTransaction  = Transaction::count();

        // 5 produk terlaris
        $transactions = TransactionDetail::select('product_id', DB::raw('SUM(quantity) as transactions_sum_qty'))
            ->with('product:id,name,price,image')
            ->groupBy('product_id')
            ->orderByDesc('transactions_sum_qty')
            ->take(5)
            ->get();

        // Produk dengan total batch qty ≤ 5
        $productsWithLowStock = Product::with('stockHistories')
            ->get()
            ->filter(fn($p) => $p->stockHistories->sum('qty') <= 5);

        // Produk dengan batch qty = 0
        $emptyBatchProducts = Product::with(['stockHistories' => function ($q) {
                $q->where('qty', 0)->orderBy('expired_date');
            }])
            ->whereHas('stockHistories', fn($q) => $q->where('qty', 0))
            ->get();

        // Produk dengan batch expired dalam 15 hari
        $productsExpiringSoon = Product::whereHas('stockHistories', function ($query) {
                $query->whereDate('expired_date', '<=', now()->addDays(15));
            })
            ->with(['stockHistories' => function ($query) {
                $query->whereDate('expired_date', '<=', now()->addDays(15))
                      ->orderBy('expired_date');
            }])
            ->get();

        // Hitung jumlah notifikasi
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
}
