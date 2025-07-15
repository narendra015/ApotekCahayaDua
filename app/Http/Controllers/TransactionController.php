<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class TransactionController extends Controller
{
    /**
     * Menampilkan daftar transaksi.
     */
    public function index(Request $request)
    {
        $pagination = 10;
        $search = $request->search;

        $transactions = Transaction::with(['customer', 'details.product'])
            ->when($search, function ($query) use ($search) {
                $query->where('date', 'LIKE', '%' . $search . '%')
                    ->orWhereHas('customer', function ($q) use ($search) {
                        $q->where('name', 'LIKE', '%' . $search . '%');
                    })
                    ->orWhereHas('details.product', function ($q) use ($search) {
                        $q->where('name', 'LIKE', '%' . $search . '%');
                    });
            })
            ->latest()
            ->paginate($pagination);

        return view('transactions.index', compact('transactions'))
            ->with('i', ($request->input('page', 1) - 1) * $pagination);
    }

    /**
     * Menampilkan form tambah transaksi.
     */
    public function create()
    {
        $customers = Customer::all(['id', 'name']);
        $products = Product::where('qty', '>', 0)->get(['id', 'name', 'price', 'qty']);

        return view('transactions.create', compact('customers', 'products'));
    }

    /**
     * Menyimpan transaksi baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'date' => 'required|date',
            'customer_id' => 'required|exists:customers,id',
            'products' => 'required|array|min:1',
            'products.*.product_id' => ['required', 'exists:products,id', 'distinct'],
            'products.*.quantity' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            $productIds = collect($request->products)->pluck('product_id')->toArray();
            $productData = Product::whereIn('id', $productIds)->get()->keyBy('id');

            foreach ($request->products as $product) {
                if (!isset($productData[$product['product_id']])) {
                    throw new \Exception("Produk tidak ditemukan.");
                }
                if ($productData[$product['product_id']]->qty < $product['quantity']) {
                    throw new \Exception("Stok produk '{$productData[$product['product_id']]->name}' tidak cukup!");
                }
            }

            $transaction = Transaction::create([
                'date' => $request->date,
                'customer_id' => $request->customer_id,
                'total_amount' => 0,
            ]);

            $totalAmount = 0;
            foreach ($request->products as $product) {
                $productInstance = $productData[$product['product_id']];
                $subtotal = $productInstance->price * $product['quantity'];

                $transaction->details()->create([
                    'product_id' => $productInstance->id,
                    'quantity' => $product['quantity'],
                    'price' => $productInstance->price,
                    'total' => $subtotal,
                ]);

                $productInstance->decrement('qty', $product['quantity']);
                $totalAmount += $subtotal;
            }

            $transaction->update(['total_amount' => $totalAmount]);

            DB::commit();
            return redirect()->route('transactions.index')->with('success', 'Transaction saved successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Transaction Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menyimpan transaksi: ' . $e->getMessage());
        }
    }

    /**
     * Menampilkan form edit transaksi.
     */
    public function edit($id)
    {
        $transaction = Transaction::with('details.product')->findOrFail($id);
        $customers = Customer::all(['id', 'name']);
        $products = Product::all(['id', 'name', 'price', 'qty']);

        return view('transactions.edit', compact('transaction', 'customers', 'products'));
    }

    /**
     * Memperbarui transaksi.
     */
    public function update(Request $request, $id): RedirectResponse
    {
        $request->validate([
            'date' => 'required|date',
            'customer_id' => 'required|exists:customers,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => ['required', 'exists:products,id', 'distinct'],
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        DB::beginTransaction();
        try {
            $transaction = Transaction::findOrFail($id);

            // Kembalikan stok sebelum menghapus detail transaksi
            foreach ($transaction->details as $detail) {
                Product::where('id', $detail->product_id)->increment('qty', $detail->quantity);
            }

            $transaction->details()->delete();

            $totalAmount = 0;
            $productIds = collect($request->items)->pluck('product_id')->toArray();
            $productData = Product::whereIn('id', $productIds)->get()->keyBy('id');

            foreach ($request->items as $item) {
                if (!isset($productData[$item['product_id']])) {
                    throw new \Exception("Produk tidak ditemukan.");
                }
                if ($productData[$item['product_id']]->qty < $item['quantity']) {
                    throw new \Exception("Stok produk '{$productData[$item['product_id']]->name}' tidak cukup!");
                }
            }

            $transaction->update([
                'date' => $request->date,
                'customer_id' => $request->customer_id,
            ]);

            foreach ($request->items as $item) {
                $product = $productData[$item['product_id']];
                $subtotal = $product->price * $item['quantity'];

                $transaction->details()->create([
                    'product_id' => $product->id,
                    'quantity' => $item['quantity'],
                    'price' => $product->price,
                    'total' => $subtotal,
                ]);

                $product->decrement('qty', $item['quantity']);
                $totalAmount += $subtotal;
            }

            $transaction->update(['total_amount' => $totalAmount]);

            DB::commit();
            return redirect()->route('transactions.index')->with('success', 'Transaction updated successfully!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Transaction Update Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memperbarui transaksi: ' . $e->getMessage());
        }
    }

    /**
     * Menghapus transaksi.
     */
    public function destroy($id): RedirectResponse
    {
        DB::beginTransaction();
        try {
            $transaction = Transaction::findOrFail($id);

            foreach ($transaction->details as $detail) {
                Product::where('id', $detail->product_id)->increment('qty', $detail->quantity);
            }

            $transaction->details()->delete();
            $transaction->delete();

            DB::commit();
            return redirect()->route('transactions.index')->with('success', 'Transaction deleted successfully!');
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


}
