<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Carbon\Carbon;

class NotificationController extends Controller
{
    public function index()
    {
        // Produk dengan total batch stok ≤ 5
        $lowStockProducts = Product::with('stockHistories')
            ->get()
            ->filter(function ($product) {
                return $product->stockHistories->sum('qty') <= 5;
            });

        // Produk yang memiliki batch dengan qty = 0
        $emptyBatchProducts = Product::with(['stockHistories' => function ($q) {
                $q->where('qty', '=', 0)->orderBy('expired_date');
            }])
            ->whereHas('stockHistories', function ($q) {
                $q->where('qty', '=', 0);
            })
            ->get();

        // Produk dengan batch kadaluarsa atau akan kadaluarsa
        $expiringProducts = Product::whereHas('stockHistories', function ($query) {
                $query->whereDate('expired_date', '<=', now()->addDays(15));
            })
            ->with(['stockHistories' => function ($query) {
                $query->whereDate('expired_date', '<=', now()->addDays(15))
                      ->orderBy('expired_date');
            }])
            ->get();

        return view('notifications.index', compact(
            'lowStockProducts',
            'emptyBatchProducts',
            'expiringProducts'
        ));
    }

    public function count()
    {
        // Total stok ≤ 5
        $lowStock = Product::with('stockHistories')
            ->get()
            ->filter(fn($p) => $p->stockHistories->sum('qty') <= 5)
            ->count();

        // Batch qty = 0
        $emptyBatch = Product::with('stockHistories')
            ->get()
            ->reduce(function ($carry, $product) {
                $count = $product->stockHistories->where('qty', 0)->count();
                return $carry + $count;
            }, 0);

        // Batch expired ≤ 15 hari lagi
        $expiring = Product::with('stockHistories')
            ->get()
            ->reduce(function ($carry, $product) {
                $count = $product->stockHistories->filter(function ($batch) {
                    return Carbon::parse($batch->expired_date)->isBefore(now()->addDays(15)->endOfDay());
                })->count();
                return $carry + $count;
            }, 0);

        $total = $lowStock + $emptyBatch + $expiring;

        return response()->json(['total' => $total]);
    }
}
