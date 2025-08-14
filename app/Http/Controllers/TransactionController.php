<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\ProductStockHistory;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $pagination = 10;
        $search = $request->search;

        $transactions = Transaction::with(['customer', 'details.product'])
            ->when($search, function ($query) use ($search) {
                $query->where('date', 'LIKE', '%' . $search . '%')
                    ->orWhereHas('customer', fn($q) => $q->where('name', 'LIKE', '%' . $search . '%'))
                    ->orWhereHas('details.product', fn($q) => $q->where('name', 'LIKE', '%' . $search . '%'));
            })
            ->latest()
            ->paginate($pagination);

        return view('transactions.index', compact('transactions'))
            ->with('i', ($request->input('page', 1) - 1) * $pagination);
    }

    public function create()
    {
        $customers = Customer::all(['id', 'name']);
        $products  = $this->getProductsForSale(); // <-- sudah termasuk drug_class, fifo_price, available_qty
        return view('transactions.create', compact('customers', 'products'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'date' => 'required|date',
            'customer_id' => 'required|exists:customers,id',
            'paid_amount' => 'required|numeric|min:0',
            'products' => 'required|array|min:1',
            'products.*.product_id' => 'required|exists:products,id|distinct',
            'products.*.quantity' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            $transaction = Transaction::create([
                'date' => $request->date,
                'customer_id' => $request->customer_id,
                'total_amount' => 0,
                'paid_amount' => $request->paid_amount,
                'change_amount' => 0,
            ]);

            $totalAmount = 0;

            foreach ($request->products as $item) {
                $product   = Product::findOrFail($item['product_id']);
                $qtyNeeded = (int) $item['quantity'];
                $remaining = $qtyNeeded;

                $stockBatches = $product->stockHistories()
                    ->where('qty', '>', 0)
                    ->orderBy('expired_date')
                    ->get();

                foreach ($stockBatches as $batch) {
                    if ($remaining <= 0) break;

                    $usedQty      = min($batch->qty, $remaining);
                    $roundedPrice = $this->roundToNearest100($batch->price);

                    $transaction->details()->create([
                        'product_id' => $product->id,
                        'quantity'   => $usedQty,
                        'price'      => $roundedPrice,
                        'total'      => $usedQty * $roundedPrice,
                    ]);

                    $batch->decrement('qty', $usedQty);
                    $totalAmount += $usedQty * $roundedPrice;
                    $remaining   -= $usedQty;
                }

                if ($remaining > 0) {
                    throw new \Exception("Stok tidak cukup untuk produk: {$product->name}");
                }

                $product->decrement('qty', $qtyNeeded);
                $product->updateProductWithFIFO();
            }

            if ($request->paid_amount < $totalAmount) {
                throw new \Exception("Uang dibayarkan kurang dari total transaksi.");
            }

            $transaction->update([
                'total_amount'  => $totalAmount,
                'change_amount' => $request->paid_amount - $totalAmount,
            ]);

            DB::commit();
            return redirect()->route('transactions.index')->with('success', 'Transaksi berhasil disimpan!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Transaction Store Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menyimpan transaksi: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        // Penting: muat product pada detail agar drug_class bisa dipakai di view
        $transaction = Transaction::with('details.product')->findOrFail($id);

        $customers = Customer::all(['id', 'name']);

        // GANTI: sebelumnya Product::all(['id','name','price','qty'])
        // Sekarang kita ambil produk dengan stok batch + lampirkan drug_class & fifo_price
        $products  = $this->getProductsForSale();

        return view('transactions.edit', compact('transaction', 'customers', 'products'));
    }

    public function update(Request $request, $id): RedirectResponse
    {
        $request->validate([
            'date' => 'required|date',
            'customer_id' => 'required|exists:customers,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => ['required', 'exists:products,id', 'distinct'],
            'items.*.quantity' => 'required|integer|min:1',
            'paid_amount' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $transaction = Transaction::with('details')->findOrFail($id);

            // Kembalikan stok lama
            foreach ($transaction->details as $detail) {
                $product = $detail->product;
                $batch = $product->stockHistories()
                    ->where('price', $detail->price)
                    ->orderBy('created_at', 'desc')
                    ->first();

                if ($batch) $batch->increment('qty', $detail->quantity);

                $product->increment('qty', $detail->quantity);
                $product->updateProductWithFIFO();
            }

            $transaction->details()->delete();

            // Build ulang detail FIFO
            $totalAmount = 0;

            foreach ($request->items as $item) {
                $product   = Product::findOrFail($item['product_id']);
                $qtyNeeded = (int) $item['quantity'];
                $remaining = $qtyNeeded;

                $stockBatches = $product->stockHistories()
                    ->where('qty', '>', 0)
                    ->orderBy('expired_date')
                    ->get();

                foreach ($stockBatches as $batch) {
                    if ($remaining <= 0) break;

                    $usedQty      = min($batch->qty, $remaining);
                    $roundedPrice = $this->roundToNearest100($batch->price);

                    $transaction->details()->create([
                        'product_id' => $product->id,
                        'quantity'   => $usedQty,
                        'price'      => $roundedPrice,
                        'total'      => $usedQty * $roundedPrice,
                    ]);

                    $batch->decrement('qty', $usedQty);
                    $totalAmount += $usedQty * $roundedPrice;
                    $remaining   -= $usedQty;
                }

                if ($remaining > 0) {
                    throw new \Exception("Stok tidak cukup untuk produk: {$product->name}");
                }

                $product->decrement('qty', $qtyNeeded);
                $product->updateProductWithFIFO();
            }

            if ($request->paid_amount < $totalAmount) {
                throw new \Exception("Nominal yang dibayarkan kurang dari total transaksi.");
            }

            $transaction->update([
                'date'          => $request->date,
                'customer_id'   => $request->customer_id,
                'total_amount'  => $totalAmount,
                'paid_amount'   => $request->paid_amount,
                'change_amount' => $request->paid_amount - $totalAmount,
            ]);

            DB::commit();
            return redirect()->route('transactions.index')->with('success', 'Transaction updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Transaction Update Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memperbarui transaksi: ' . $e->getMessage());
        }
    }

    public function destroy($id): RedirectResponse
    {
        DB::beginTransaction();
        try {
            $transaction = Transaction::with('details')->findOrFail($id);

            foreach ($transaction->details as $detail) {
                $product = Product::find($detail->product_id);
                if ($product) {
                    $product->increment('qty', $detail->quantity);

                    $batch = $product->stockHistories()
                        ->orderBy('expired_date')
                        ->orderBy('created_at')
                        ->first();

                    if ($batch) $batch->increment('qty', $detail->quantity);
                    $product->updateProductWithFIFO();
                }
            }

            $transaction->details()->delete();
            $transaction->delete();

            DB::commit();
            return redirect()->route('transactions.index')->with('success', 'Transaksi berhasil dihapus!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menghapus transaksi: ' . $e->getMessage());
        }
    }

    public function print($id)
    {
        $transaction = Transaction::with(['customer', 'details.product'])->findOrFail($id);
        return view('transactions.print', compact('transaction'));
    }

    private function roundToNearest100($value)
    {
        return round($value / 100) * 100;
    }

    /**
     * Ambil produk yang siap dijual:
     * - Punya stok batch (qty>0)
     * - Sertakan drug_class, fifo_price, available_qty
     */
    private function getProductsForSale()
    {
        $products = Product::select('id','name','drug_class') // <-- penting: drug_class ikut
            ->with(['stockHistories' => function ($q) {
                $q->where('qty', '>', 0)->orderBy('expired_date');
            }])
            ->whereHas('stockHistories', fn($q) => $q->where('qty','>',0))
            ->get();

        // hitung fifo_price & available_qty
        foreach ($products as $product) {
            $firstStock           = $product->stockHistories->first();
            $product->fifo_price  = $firstStock?->price ?? 0;
            $product->available_qty = $product->stockHistories->sum('qty');
        }
        return $products;
    }
}
