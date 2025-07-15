<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ReportController extends Controller
{
    /**
     * Menampilkan halaman laporan transaksi.
     */
    public function index()
    {
        return view('report.index');
    }

    /**
     * Filter transaksi berdasarkan rentang tanggal.
     */
    public function filter(Request $request): View
    {
        // Validasi form
        $request->validate([
            'start_date' => 'required|date',
            'end_date'   => 'required|date|after_or_equal:start_date'
        ]);

        // Ambil data berdasarkan tanggal
        $transactions = Transaction::with(['customer:id,name', 'details.product:id,name,price'])
            ->whereBetween('date', [$request->start_date, $request->end_date])
            ->oldest()
            ->get();

        return view('report.index', compact('transactions'));
    }

    /**
     * Cetak laporan PDF berdasarkan rentang tanggal.
     */
    public function print($startDate, $endDate)
    {   
        $transactions = Transaction::with(['customer:id,name', 'details.product:id,name,price'])
            ->whereBetween('date', [$startDate, $endDate])
            ->oldest()
            ->get();
        return view('report.print', compact('transactions'));
    }
}
