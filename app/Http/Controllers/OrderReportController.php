<?php
namespace App\Http\Controllers;

use App\Models\Order;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class OrderReportController extends Controller
{
    /**
     * Menampilkan halaman laporan pembelian.
     */
    public function index()
    {
        return view('order_report.index');
    }

    /**
     * Filter pembelian berdasarkan rentang tanggal.
     */
    public function filter(Request $request)
    {
        // Validasi form
        $request->validate([
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date'
        ]);

        // Ambil data berdasarkan tanggal
        $orders = Order::with(['supplier:id,name', 'orderDetails.product:id,name,price'])
            ->whereBetween('order_date', [$request->start_date, $request->end_date])  // Gunakan order_date sesuai dengan kolom yang ada
            ->oldest()
            ->get();

        // Jika tidak ada data, tampilkan pesan error
        if ($orders->isEmpty()) {
            return back()->with('error', 'Tidak ada data untuk rentang tanggal yang dipilih.');
        }

        // Jika ada data, kirim data ke tampilan
        return view('order_report.index', compact('orders'))->with('success', 'Data laporan berhasil difilter.');
    }

    /**
     * Cetak laporan PDF berdasarkan rentang tanggal.
     */
    public function print($startDate, $endDate)
    {   
        // Ambil data pesanan dengan detail produk dan pemasok
        $orders = Order::with(['supplier:id,name', 'orderDetails.product:id,name,price'])
            ->whereBetween('order_date', [$startDate, $endDate]) // Gunakan order_date di sini
            ->oldest()
            ->get();

        // Jika tidak ada data, tampilkan pesan
        if ($orders->isEmpty()) {
            return back()->with('error', 'Tidak ada data untuk rentang tanggal yang dipilih.');
        }

        // Cetak laporan dalam format PDF
        return view('order_report.print', compact('orders', 'startDate', 'endDate'));
    }
}
