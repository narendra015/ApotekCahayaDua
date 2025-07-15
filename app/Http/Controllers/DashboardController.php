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
    /**
     * Menampilkan halaman dashboard utama.
     */
    public function index(): View
    {
        // Jumlah data
        $totalCategory     = Category::count();
        $totalProduct      = Product::count();
        $totalCustomer     = Customer::count();
        $totalTransaction  = Transaction::count();

        // 5 produk terlaris berdasarkan total quantity
        $transactions = TransactionDetail::select('product_id', DB::raw('SUM(quantity) as transactions_sum_qty'))
            ->with('product:id,name,price,image')
            ->groupBy('product_id')
            ->orderBy('transactions_sum_qty', 'desc')
            ->take(5)
            ->get();

        // Produk dengan stok rendah (≤ 5)
        $productsWithLowStock = Product::where('qty', '<=', 5)->get();

        // Produk yang kadaluarsa atau akan kadaluarsa dalam 15 hari
        $productsExpiringSoon = Product::whereNotNull('expired_date')
            ->whereDate('expired_date', '<=', now()->addDays(15))
            ->get();

        // Jumlah notifikasi (untuk badge di ikon lonceng)
        $lowStockCount    = $productsWithLowStock->count();
        $expiredSoonCount = $productsExpiringSoon->count();

        return view('dashboard.index', compact(
            'totalCategory',
            'totalProduct',
            'totalCustomer',
            'totalTransaction',
            'transactions',
            'productsWithLowStock',
            'productsExpiringSoon',
            'lowStockCount',
            'expiredSoonCount'
        ));
    }
}
