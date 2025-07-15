<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    // Menampilkan form login
    public function showLoginForm()
    {
        return view('auth.login');
    }

    // Menampilkan form registrasi
    public function showRegisterForm()
    {
        return view('auth.register');
    }

    // Register user
    public function register(Request $request)
    {
        // Validasi input
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed', // pastikan ada input 'password_confirmation' pada form
        ]);

        // Membuat user baru
        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'admin', // Semua user otomatis menjadi admin (bisa disesuaikan)
        ]);

        // Redirect ke login dengan pesan sukses
        return redirect('/login')->with('success', 'Registration successful! You can now log in.');
    }

    // Login user
    public function login(Request $request)
    {
        // Validasi kredensial
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        // Cek kredensial
        if (Auth::attempt($credentials)) {
            $request->session()->regenerate(); // Melakukan regenerasi session untuk mencegah session fixation

            // Arahkan ke dashboard umum setelah login
            return redirect()->route('dashboard'); // Ganti dengan rute dashboard yang sesuai
        }

        // Jika kredensial salah
        return back()->withErrors(['email' => 'Invalid credentials'])->onlyInput('email');
    }

    // Logout user
    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login')->with('success', 'You have been logged out.');
    }
}
