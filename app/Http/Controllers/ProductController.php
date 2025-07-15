<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use App\Models\Unit;
use App\Models\Supplier;  // Pastikan Anda sudah memiliki model Supplier
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    /**
     * Menampilkan daftar produk.
     */
    public function index(Request $request): View
    {
        $pagination = 10; // Tentukan jumlah produk per halaman
    
        // Query untuk mengambil produk dengan pencarian dan relasi yang diperlukan
        $products = Product::select('id', 'category_id', 'unit_id', 'supplier_id', 'name', 'price', 'image', 'expired_date', 'qty')
            ->with(['category:id,name', 'unit:id,name', 'supplier:id,name']) // Pastikan relasi supplier ada
            ->when($request->search, function ($query) use ($request) {
                $query->where('name', 'LIKE', '%' . $request->search . '%')
                    ->orWhere('price', 'LIKE', '%' . $request->search . '%')
                    ->orWhereHas('category', function ($query) use ($request) {
                        $query->where('name', 'LIKE', '%' . $request->search . '%');  // Pencarian berdasarkan nama kategori
                    });
            })
            ->latest()  // Urutkan berdasarkan yang terbaru
            ->paginate($pagination)  // Paginasi dengan jumlah produk per halaman
            ->withQueryString(); // Mempertahankan parameter pencarian di URL
    
        // Menghitung nomor urut untuk setiap halaman
        return view('products.index', compact('products'))
            ->with('i', ($request->input('page', 1) - 1) * $pagination);
    }
    

    /**
     * Menampilkan form untuk menambahkan produk.
     */
    public function create(): View
    {
        $categories = Category::select('id', 'name')->get();
        $units = Unit::select('id', 'name')->get();
        $suppliers = Supplier::select('id', 'name')->get();  // Menambahkan data supplier
        return view('products.create', compact('categories', 'units', 'suppliers'));
    }

    /**
     * Menyimpan produk baru.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'unit_id'     => 'required|exists:units,id',
            'supplier_id' => 'required|exists:suppliers,id',  // Validasi untuk supplier_id
            'name'        => 'required|string',
            'description' => 'required|string',
            'price'       => 'required|string',
            'expired_date'=> 'nullable|date',
            'qty'         => 'required|integer|min:1',
            'image'       => 'nullable|image|mimes:jpeg,jpg,png|max:1024'
        ]);

        // Proses upload image jika ada
        $imageName = $request->hasFile('image') ? $request->file('image')->store('public/products') : null;

        // Menyimpan produk ke database
        Product::create([
            'category_id' => $request->category_id,
            'unit_id'     => $request->unit_id,
            'supplier_id' => $request->supplier_id,  // Menyimpan supplier_id
            'name'        => $request->name,
            'description' => $request->description,
            'price'       => (float) str_replace(['.', ','], ['', '.'], $request->price),
            'image'       => $imageName ? basename($imageName) : null,
            'expired_date'=> $request->expired_date,
            'qty'         => $request->qty,
        ]);

        return redirect()->route('products.index')->with(['success' => 'Product added successfully!']);
    }

    /**
     * Menampilkan form untuk mengedit produk.
     */
    public function edit($id): View
    {
        $product = Product::findOrFail($id);
        $categories = Category::select('id', 'name')->get();
        $units = Unit::select('id', 'name')->get();
        $suppliers = Supplier::select('id', 'name')->get();  // Mengambil data supplier
        return view('products.edit', compact('product', 'categories', 'units', 'suppliers'));
    }

    /**
     * Memperbarui produk yang ada.
     */
    public function update(Request $request, $id): RedirectResponse
    {
        $request->validate([
            'category_id' => 'required|exists:categories,id',
            'unit_id'     => 'required|exists:units,id',
            'supplier_id' => 'required|exists:suppliers,id',  // Validasi untuk supplier_id
            'name'        => 'required',
            'description' => 'required',
            'price'       => 'required|string',
            'expired_date'=> 'nullable|date',
            'image'       => 'nullable|image|mimes:jpeg,jpg,png|max:1024'
        ]);

        $product = Product::findOrFail($id);

        // Cek dan update gambar jika ada yang diunggah
        if ($request->hasFile('image')) {
            $imageName = $request->file('image')->store('public/products');
            if ($product->image) {
                Storage::delete('public/products/' . $product->image); // Hapus gambar lama
            }
            $product->update(['image' => basename($imageName)]);
        }

        // Update data produk
        $product->update([
            'category_id' => $request->category_id,
            'unit_id'     => $request->unit_id,
            'supplier_id' => $request->supplier_id,  // Update supplier_id
            'name'        => $request->name,
            'description' => $request->description,
            'price'       => (float) str_replace(['.', ','], ['', '.'], $request->price),
            'expired_date'=> $request->expired_date,
        ]);

        return redirect()->route('products.index')->with(['success' => 'Product updated successfully!']);
    }

    /**
     * Menghapus produk.
     */
    public function destroy($id): RedirectResponse
    {
        // Ambil data produk berdasarkan ID
        $product = Product::findOrFail($id);

        // Hapus gambar produk jika ada
        Storage::delete('public/products/' . $product->image);

        // Hapus produk dari database
        $product->delete();

        // Redirect kembali ke halaman index dengan pesan sukses
        return redirect()->route('products.index')->with(['success' => 'The product has been deleted!']);
    }

    /**
     * Menampilkan detail produk.
     */
    public function show($id): View
    {
        $product = Product::with('category', 'unit', 'supplier')->findOrFail($id);  // Menambahkan relasi supplier
        return view('products.show', compact('product'));
    }

    public function getBySupplier($supplierId)
    {
        $products = Product::where('supplier_id', $supplierId)->get();
        return response()->json($products);
    }

}
