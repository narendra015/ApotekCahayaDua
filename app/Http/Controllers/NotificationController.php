<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use Carbon\Carbon;

class NotificationController extends Controller
{
    /**
     * Menampilkan halaman notifikasi produk.
     */
    public function index()
    {
        $lowStockProducts = Product::where('qty', '<=', 5)->get();

        $expiringProducts = Product::whereNotNull('expired_date')
            ->whereDate('expired_date', '<=', now()->addDays(15)) // mencakup produk kadaluarsa & yang akan kadaluarsa
            ->get();

        return view('notifications.index', compact('lowStockProducts', 'expiringProducts'));
    }

}
