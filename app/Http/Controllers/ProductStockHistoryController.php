<?php

namespace App\Http\Controllers;

use App\Models\ProductStockHistory;
use Illuminate\Http\Request;

class ProductStockHistoryController extends Controller
{
    /**
     * Tampilkan form edit histori stok.
     */
    public function edit(ProductStockHistory $history)
    {
        return view('products.edit-histori', compact('history'));
    }

    /**
     * Simpan perubahan histori stok.
     */
    public function update(Request $request, ProductStockHistory $history)
    {
        $request->validate([
            'price' => 'required|string',
            'expired_date' => 'required|date|after_or_equal:today',
        ]);

        $history->update([
            'price' => (float) str_replace(['.', ','], ['', '.'], $request->price),
            'expired_date' => $request->expired_date,
        ]);

        return redirect()
            ->route('products.show', $history->product_id)
            ->with('success', 'Histori stok berhasil diperbarui.');
    }

    /**
     * Hapus histori stok jika sudah kosong atau kadaluarsa.
     */
    public function destroy($id)
    {
        $history = ProductStockHistory::findOrFail($id);

        // Cegah hapus jika stok masih ada dan belum kadaluarsa
        if ($history->qty > 0 && now()->lte($history->expired_date)) {
            return redirect()->back()->with('error', 'Riwayat ini masih aktif dan tidak bisa dihapus.');
        }

        $history->delete();

        return redirect()->back()->with('success', 'Riwayat stok berhasil dihapus.');
    }
}
