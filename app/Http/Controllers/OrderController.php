<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Supplier;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderController extends Controller
{
    /** -------------------------------------------
     *  LIST PESANAN
     * ------------------------------------------ */
    public function index(Request $request)
    {
        $pagination = 10;
        $search     = $request->search;

        $orders = Order::with(['supplier', 'orderDetails.product'])
            ->when($search, function ($q) use ($search) {
                $q->where('order_date',   'LIKE', "%{$search}%")
                  ->orWhereHas('supplier', fn($s) =>
                        $s->where('name', 'LIKE', "%{$search}%"))
                  ->orWhereHas('orderDetails.product', fn($p) =>
                        $p->where('name', 'LIKE', "%{$search}%"));
            })
            ->latest()
            ->paginate($pagination);

        return view('orders.index', compact('orders'))
            ->with('i', ($request->input('page', 1) - 1) * $pagination);
    }

    /** -------------------------------------------
     *  FORM TAMBAH
     * ------------------------------------------ */
    public function create()
    {
        $suppliers = Supplier::all(['id', 'name']);
        // Tampilkan semua produk, tak peduli stok
        $products  = Product::all(['id', 'name', 'price', 'qty']);

        return view('orders.create', compact('suppliers', 'products'));
    }

    /** -------------------------------------------
     *  SIMPAN PESANAN BARU
     * ------------------------------------------ */
    public function store(Request $request)
    {
        $request->validate([
            'order_date'                 => 'required|date',
            'supplier_id'                => 'required|exists:suppliers,id',
            'order_details'              => 'required|array|min:1',
            'order_details.*.product_id' => 'required|exists:products,id',
            'order_details.*.quantity'   => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            $productIds   = collect($request->order_details)->pluck('product_id');
            $productData  = Product::whereIn('id', $productIds)->get()->keyBy('id');

            // --- BUAT ORDER ---
            $order = Order::create([
                'order_date'   => $request->order_date,
                'supplier_id'  => $request->supplier_id,
                'total_amount' => 0,
            ]);

            $totalAmount = 0;
            foreach ($request->order_details as $detail) {
                $product  = $productData[$detail['product_id']]
                    ?? throw new \Exception("Produk tidak ditemukan.");

                $subtotal = $product->price * $detail['quantity'];

                $order->orderDetails()->create([
                    'product_id' => $product->id,
                    'quantity'   => $detail['quantity'],
                    'price'      => $product->price,
                    'total'      => $subtotal,
                ]);

                $totalAmount += $subtotal;
            }

            $order->update(['total_amount' => $totalAmount]);

            DB::commit();
            return redirect()
                ->route('orders.index')
                ->with('success', 'Pesanan berhasil disimpan!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Pesanan Error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Gagal menyimpan pesanan: ' . $e->getMessage());
        }
    }

    /** -------------------------------------------
     *  FORM EDIT
     * ------------------------------------------ */
    public function edit($id)
    {
        $order     = Order::with('orderDetails.product')->findOrFail($id);
        $suppliers = Supplier::all(['id', 'name']);
        $products  = Product::all(['id', 'name', 'price', 'qty']);

        return view('orders.edit', compact('order', 'suppliers', 'products'));
    }

    /** -------------------------------------------
     *  UPDATE PESANAN
     * ------------------------------------------ */
    public function update(Request $request, $id)
    {
        $request->validate([
            'order_date'            => 'required|date',
            'supplier_id'           => 'required|exists:suppliers,id',
            'items'                 => 'required|array|min:1',
            'items.*.product_id'    => 'required|exists:products,id',
            'items.*.quantity'      => 'required|integer|min:1',
            'items.*.id'            => 'nullable|exists:order_details,id',
        ]);

        DB::beginTransaction();
        try {
            $order = Order::findOrFail($id);
            $order->orderDetails()->delete();   // hapus detail lama

            $productIds  = collect($request->items)->pluck('product_id');
            $productData = Product::whereIn('id', $productIds)->get()->keyBy('id');

            $totalAmount = 0;
            foreach ($request->items as $detail) {
                $product  = $productData[$detail['product_id']]
                    ?? throw new \Exception("Produk tidak ditemukan.");

                $subtotal = $product->price * $detail['quantity'];

                $order->orderDetails()->create([
                    'product_id' => $product->id,
                    'quantity'   => $detail['quantity'],
                    'price'      => $product->price,
                    'total'      => $subtotal,
                ]);

                $totalAmount += $subtotal;
            }

            $order->update([
                'order_date'   => $request->order_date,
                'supplier_id'  => $request->supplier_id,
                'total_amount' => $totalAmount,
            ]);

            DB::commit();
            return redirect()
                ->route('orders.index')
                ->with('success', 'Pesanan berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Pesanan Update Error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Gagal memperbarui pesanan: ' . $e->getMessage());
        }
    }

    /** -------------------------------------------
     *  HAPUS
     * ------------------------------------------ */
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $order = Order::findOrFail($id);
            $order->orderDetails()->delete();
            $order->delete();

            DB::commit();
            return redirect()->route('orders.index')
                ->with('success', 'Pesanan berhasil dihapus!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()
                ->with('error', 'Gagal menghapus pesanan: ' . $e->getMessage());
        }
    }

    /** -------------------------------------------
     *  SHOW
     * ------------------------------------------ */
    public function show($id)
    {
        $order = Order::with('orderDetails.product')->findOrFail($id);
        return view('orders.show', compact('order'));
    }

    /** -------------------------------------------
     *  CETAK PDF
     * ------------------------------------------ */
    public function printOrder($id)
    {
        $order = Order::with(['supplier', 'orderDetails.product'])->findOrFail($id);
        return view('orders.print', compact('order'));
    }
}
