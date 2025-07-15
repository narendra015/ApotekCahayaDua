<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{

    public function index()
    {
        $users = User::all(); // Fetch all users
        return view('users.index', compact('users'));
    }

    public function create()
    {
        return view('users.create');
    }

    public function store(Request $request)
    {
        // Validate user input
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'role' => 'required|in:admin,owner,user',
            'password' => 'required|string|min:8', // Password must be at least 8 characters
        ]);

        // Check for validation errors
        if ($validator->fails()) {
            return redirect()->route('users.create')
                             ->withErrors($validator)
                             ->withInput();
        }

        // Create a new user
        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password), // Hash password securely
            'role' => $request->role,
        ]);

        return redirect()->route('users.index')->with('success', 'User berhasil ditambahkan');
    }

    public function edit($id)
    {
        $user = User::findOrFail($id);
        return view('users.edit', compact('user'));
    }

    public function update(Request $request, $id)
    {
        // Validate the data
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $id, // Unique email validation with exception for current user
            'role' => 'required|in:admin,owner,user',
            'password' => 'nullable|string|min:8', // Password is optional for update
        ]);

        // Check for validation errors
        if ($validator->fails()) {
            return redirect()->route('users.edit', $id)
                             ->withErrors($validator)
                             ->withInput();
        }

        $user = User::findOrFail($id);
        
        // Update the user details
        $userData = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
        ];

        // If password is provided, hash and update it
        if ($request->filled('password')) {
            $userData['password'] = Hash::make($request->password);
        }

        $user->update($userData);

        return redirect()->route('users.index')->with('success', 'User berhasil diperbarui');
    }

    public function destroy($id)
    {
        $user = User::findOrFail($id);
    
        // Prevent deletion of the last owner user (important for system security)
        if ($user->role === 'owner' && User::where('role', 'owner')->count() === 1) {
            return redirect()->route('users.index')->with('error', 'Tidak dapat menghapus satu-satunya owner');
        }
    
        // Use forceDelete to permanently remove the user from the database
        $user->forceDelete();
    
        return redirect()->route('users.index')->with('success', 'User berhasil dihapus');
    }
    
}
