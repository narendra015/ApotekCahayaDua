<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): View
    {
        // jumlah data yang ditampilkan per paginasi halaman
        $pagination = 10;

        if ($request->search) {
            // menampilkan pencarian data
            $customers = Customer::select('id', 'name', 'address', 'phone')
                ->whereAny(['name', 'address', 'phone'], 'LIKE', '%' . $request->search . '%')
                ->paginate($pagination)
                ->withQueryString();
        } else {
            // menampilkan semua data
            $customers = Customer::select('id', 'name', 'address', 'phone')
                ->latest()
                ->paginate($pagination);
        }

        // tampilkan data ke view
        return view('customers.index', compact('customers'))->with('i', ($request->input('page', 1) - 1) * $pagination);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        // tampilkan form add data
        return view('customers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        // validasi form
        $request->validate([
            'name'    => 'required',
            'address' => 'required',
            'phone'   => 'required|max:13|unique:customers'
        ]);

        // create data
        Customer::create([
            'name'    => $request->name,
            'address' => $request->address,
            'phone'   => $request->phone
        ]);

        // redirect ke halaman index dan tampilkan pesan berhasil simpan data
        return redirect()->route('customers.index')->with(['success' => 'The new customer has been saved.']);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): View
    {
        // get data by ID
        $customer = Customer::findOrFail($id);

        // tampilkan form edit data
        return view('customers.edit', compact('customer'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id): RedirectResponse
    {
        // validasi form
        $request->validate([
            'name'    => 'required',
            'address' => 'required',
            'phone'   => 'required|max:13|unique:customers,phone,' . $id
        ]);

        // get data by ID
        $customer = Customer::findOrFail($id);

        // update data
        $customer->update([
            'name'    => $request->name,
            'address' => $request->address,
            'phone'   => $request->phone
        ]);

        // redirect ke halaman index dan tampilkan pesan berhasil ubah data
        return redirect()->route('customers.index')->with(['success' => 'The customer has been updated.']);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id): RedirectResponse
    {
        // get data by ID
        $customer = Customer::findOrFail($id);

        // delete data
        $customer->delete();

        // redirect ke halaman index dan tampilkan pesan berhasil hapus data
        return redirect()->route('customers.index')->with(['success' => 'The customer has been deleted!']);
    }
}