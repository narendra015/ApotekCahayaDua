<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Unit;
use App\Models\Supplier;
use App\Models\ProductStockHistory;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProductController extends Controller
{
    // daftar nilai yang sah untuk drug_class
    private array $drugClasses = [
        'obat_bebas',
        'obat_bebas_terbatas',
        'obat_keras',
        'obat_narkotika',
        'obat_herbal',
        'obat_herbal_terstandar',
        'fitofarmaka',
    ];

    public function index(Request $request): View
    {
        $products = Product::with([
                'category:id,name',
                'unit:id,name',
                'supplier:id,name',
                'stockHistories' => function ($query) {
                    $query->orderBy('expired_date');
                }
            ])
            ->when($request->search, function ($query) use ($request) {
                $search = $request->search;
                $query->where('name', 'LIKE', "%{$search}%")
                    ->orWhereHas('category', fn($q) => $q->where('name', 'LIKE', "%{$search}%"))
                    // opsional: ikutkan pencarian berdasarkan golongan obat
                    ->orWhere('drug_class', 'LIKE', "%{$search}%");
            })
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('products.index', compact('products'))
            ->with('i', ($request->input('page', 1) - 1) * 10);
    }

    public function create(): View
    {
        $categories = Category::select('id', 'name')->get();
        $units = Unit::select('id', 'name')->get();
        $suppliers = Supplier::select('id', 'name')->get();

        // kirim daftar kelas obat ke view jika ingin dipakai untuk select
        $drugClasses = $this->drugClasses;

        return view('products.create', compact('categories', 'units', 'suppliers', 'drugClasses'));
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'category_id'   => 'required|exists:categories,id',
            'unit_id'       => 'required|exists:units,id',
            'supplier_id'   => 'required|exists:suppliers,id',
            'name'          => 'required|string',
            'description'   => 'required|string',
            'price'         => 'required|string',
            'qty'           => 'required|integer|min:1',
            'expired_date'  => 'required|date|after_or_equal:today',
            'image'         => 'nullable|image|mimes:jpeg,jpg,png|max:1024',
            'drug_class'    => ['required', Rule::in($this->drugClasses)],
        ]);

        $imageName = $request->hasFile('image')
            ? $request->file('image')->store('public/products')
            : null;

        // Simpan produk utama
        $product = Product::create([
            'category_id' => $request->category_id,
            'unit_id'     => $request->unit_id,
            'supplier_id' => $request->supplier_id,
            'name'        => $request->name,
            'description' => $request->description,
            'image'       => $imageName ? basename($imageName) : null,
            'drug_class'  => $request->drug_class, // <— simpan golongan obat
            // nilai default; akan diperbarui via FIFO
            'price'       => 0,
            'qty'         => 0,
            'expired_date'=> null,
        ]);

        // Konversi harga Indonesia ke float
        $parsedPrice = (float) str_replace(['.', ','], ['', '.'], $request->price);

        // Simpan batch pertama ke riwayat stok
        ProductStockHistory::create([
            'product_id'   => $product->id,
            'price'        => $parsedPrice,
            'qty'          => $request->qty,
            'expired_date' => $request->expired_date,
        ]);

        // Perbarui produk utama mengikuti batch tertua (FIFO)
        $product->updateProductWithFIFO();

        return redirect()->route('products.index')->with('success', 'Produk berhasil ditambahkan.');
    }

    public function edit($id): View
    {
        $product = Product::findOrFail($id);
        $categories = Category::select('id', 'name')->get();
        $units = Unit::select('id', 'name')->get();
        $suppliers = Supplier::select('id', 'name')->get();
        $drugClasses = $this->drugClasses;

        return view('products.edit', compact('product', 'categories', 'units', 'suppliers', 'drugClasses'));
    }

    public function update(Request $request, $id): RedirectResponse
    {
        $request->validate([
            'category_id'  => 'required|exists:categories,id',
            'unit_id'      => 'required|exists:units,id',
            'supplier_id'  => 'required|exists:suppliers,id',
            'name'         => 'required',
            'description'  => 'required',
            'expired_date' => 'nullable|date',
            'image'        => 'nullable|image|mimes:jpeg,jpg,png|max:1024',
            'drug_class'   => ['required', Rule::in($this->drugClasses)],
        ]);

        $product = Product::findOrFail($id);

        if ($request->hasFile('image')) {
            $imageName = $request->file('image')->store('public/products');
            if ($product->image) {
                Storage::delete('public/products/' . $product->image);
            }
            $product->image = basename($imageName);
        }

        $product->update([
            'category_id' => $request->category_id,
            'unit_id'     => $request->unit_id,
            'supplier_id' => $request->supplier_id,
            'name'        => $request->name,
            'description' => $request->description,
            'drug_class'  => $request->drug_class, // <— update golongan obat
        ]);

        return redirect()->route('products.index')->with('success', 'Produk berhasil diperbarui.');
    }

    public function destroy($id): RedirectResponse
    {
        $product = Product::findOrFail($id);
        if ($product->image) {
            Storage::delete('public/products/' . $product->image);
        }
        $product->stockHistories()->delete();
        $product->delete();

        return redirect()->route('products.index')->with('success', 'Produk berhasil dihapus.');
    }

    public function show($id): View
    {
        $product = Product::with(['category', 'unit', 'supplier', 'stockHistories'])->findOrFail($id);
        $oldestStock = $product->oldestStock();

        return view('products.show', compact('product', 'oldestStock'));
    }

    public function getBySupplier($supplierId)
    {
        $products = Product::where('supplier_id', $supplierId)->get();
        return response()->json($products);
    }

    public function addStock(Product $product): View
    {
        return view('products.add-stock', compact('product'));
    }

    public function storeStock(Request $request, Product $product): RedirectResponse
    {
        $request->validate([
            'price'         => 'required|string',
            'qty'           => 'required|integer|min:1',
            'expired_date'  => 'required|date|after_or_equal:today',
        ]);

        $parsedPrice = (float) str_replace(['.', ','], ['', '.'], $request->price);

        ProductStockHistory::create([
            'product_id'   => $product->id,
            'price'        => $parsedPrice,
            'qty'          => $request->qty,
            'expired_date' => $request->expired_date,
        ]);

        $product->updateProductWithFIFO();

        return redirect()->route('products.show', $product->id)->with('success', 'Stok berhasil ditambahkan.');
    }
}
