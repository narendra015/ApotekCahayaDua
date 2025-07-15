<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Unit;
use App\Models\Supplier;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CategoryController extends Controller
{
    /**
     * Display a listing of the categories.
     */
    public function index(Request $request): View
    {
        $pagination = 10;
        $search = trim($request->input('search', ''));

        $categories = Category::query()
            ->select('id', 'name')
            ->when($search, function ($query, $search) {
                return $query->where('name', 'LIKE', '%' . $search . '%');
            })
            ->latest()
            ->paginate($pagination)
            ->withQueryString();

        return view('categories.index', compact('categories'))
            ->with('i', ($request->input('page', 1) - 1) * $pagination);
    }

    /**
     * Show the form for creating a new category.
     */
    public function create(): View
    {
        return view('categories.create');
    }

    /**
     * Store a newly created category in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:categories,name',
        ]);

        Category::create($validatedData);

        return redirect()->route('categories.index')
            ->with(['success' => 'The new category has been saved.']);
    }

    /**
     * Show the form for editing the specified category.
     */
    public function edit(string $id): View
    {
        $category = Category::findOrFail($id);

        return view('categories.edit', compact('category'));
    }

    /**
     * Update the specified category in storage.
     */
    public function update(Request $request, $id): RedirectResponse
    {
        $validatedData = $request->validate([
            'name' => "required|string|max:255|unique:categories,name,{$id}",
        ]);

        $category = Category::findOrFail($id);
        $category->update($validatedData);

        return redirect()->route('categories.index')
            ->with(['success' => 'The category has been updated.']);
    }

    /**
     * Remove the specified category from storage.
     */
    public function destroy($id): RedirectResponse
    {
        $category = Category::findOrFail($id);
        $category->delete();

        return redirect()->route('categories.index')
            ->with(['success' => 'The category has been deleted!']);
    }

    // CRUD Methods for Units

    public function indexUnits(Request $request): View
    {
        $pagination = 10;
        $search = trim($request->input('search', ''));

        $units = Unit::query()
            ->select('id', 'name')
            ->when($search, function ($query, $search) {
                return $query->where('name', 'LIKE', '%' . $search . '%');
            })
            ->latest()
            ->paginate($pagination)
            ->withQueryString();

        return view('units.index', compact('units'))
            ->with('i', ($request->input('page', 1) - 1) * $pagination);
    }

    public function createUnit(): View
    {
        return view('units.create');
    }

    public function storeUnit(Request $request): RedirectResponse
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:units,name',
        ]);

        Unit::create($validatedData);

        return redirect()->route('units.index')
            ->with(['success' => 'The new unit has been saved.']);
    }

    public function editUnit(string $id): View
    {
        $unit = Unit::findOrFail($id);

        return view('units.edit', compact('unit'));
    }

    public function updateUnit(Request $request, $id): RedirectResponse
    {
        $validatedData = $request->validate([
            'name' => "required|string|max:255|unique:units,name,{$id}",
        ]);

        $unit = Unit::findOrFail($id);
        $unit->update($validatedData);

        return redirect()->route('units.index')
            ->with(['success' => 'The unit has been updated.']);
    }

    public function destroyUnit($id): RedirectResponse
    {
        $unit = Unit::findOrFail($id);
        $unit->delete();

        return redirect()->route('units.index')
            ->with(['success' => 'The unit has been deleted!']);
    }

    // CRUD Methods for Suppliers

    public function indexSuppliers(Request $request): View
    {
        $pagination = 10;
        $search = trim($request->input('search', ''));

        $suppliers = Supplier::query()
            ->select('id', 'name', 'email', 'phone')
            ->when($search, function ($query, $search) {
                return $query->where('name', 'LIKE', '%' . $search . '%');
            })
            ->latest()
            ->paginate($pagination)
            ->withQueryString();

        return view('suppliers.index', compact('suppliers'))
            ->with('i', ($request->input('page', 1) - 1) * $pagination);
    }

    public function createSupplier(): View
    {
        return view('suppliers.create');
    }

    public function storeSupplier(Request $request): RedirectResponse
    {
        $validatedData = $request->validate([
            'name' => 'required|string|max:255|unique:suppliers,name',
            'email' => 'required|email|unique:suppliers,email',
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:500',
        ]);

        Supplier::create($validatedData);

        return redirect()->route('suppliers.index')
            ->with(['success' => 'The new supplier has been saved.']);
    }

    public function editSupplier(string $id): View
    {
        $supplier = Supplier::findOrFail($id);

        return view('suppliers.edit', compact('supplier'));
    }

    public function updateSupplier(Request $request, $id): RedirectResponse
    {
        $validatedData = $request->validate([
            'name' => "required|string|max:255|unique:suppliers,name,{$id}",
            'email' => "required|email|unique:suppliers,email,{$id}",
            'phone' => 'required|string|max:20',
            'address' => 'required|string|max:500',
        ]);

        $supplier = Supplier::findOrFail($id);
        $supplier->update($validatedData);

        return redirect()->route('suppliers.index')
            ->with(['success' => 'The supplier has been updated.']);

    }

    public function destroySupplier($id): RedirectResponse
    {
        $supplier = Supplier::findOrFail($id);
        $supplier->delete();

        return redirect()->route('suppliers.index')
            ->with(['success' => 'The supplier has been deleted!']);
    }
}
