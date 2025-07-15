<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
  /**
     * Menampilkan halaman profil.
     */
    public function index()
    {
        $user = Auth::user();

        if (!$user instanceof User) {
            abort(403, 'Aksi tidak sah.');
        }

        return view('profile.index', compact('user'));
    }

    /**
     * Mengupdate informasi profil (nama & email).
     */
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        if (!$user instanceof User) {
            return redirect()->route('profile.index')->with('error', 'Pengguna tidak ditemukan.');
        }

        // Validasi input
        $validated = $request->validate([
            'name'  => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', Rule::unique('users', 'email')->ignore($user->id)],
        ]);

        try {
            // Update nama dan email
            $user->update([
                'name'  => $validated['name'],
                'email' => $validated['email'],
            ]);

            return redirect()->route('profile.index')->with('success', 'Profil berhasil diperbarui.');
        } catch (\Exception $e) {
            return redirect()->route('profile.index')->with('error', 'Gagal memperbarui profil. ' . $e->getMessage());
        }
    }

    /**
     * Mengupdate kata sandi pengguna.
     */
    public function updatePassword(Request $request)
    {
        $user = Auth::user();

        if (!$user instanceof User) {
            return redirect()->route('profile.index')->with('error', 'Pengguna tidak ditemukan.');
        }

        // Validasi input
        $validated = $request->validate([
            'current_password' => ['required', 'min:6'],
            'password'         => ['required', 'min:6', 'confirmed'],
        ]);

        // Cek apakah password lama sesuai
        if (!Hash::check($validated['current_password'], $user->password)) {
            return redirect()->route('profile.index')->with('error', 'Password lama salah.');
        }

        try {
            // Simpan password baru
            $user->update([
                'password' => Hash::make($validated['password']),
            ]);

            return redirect()->route('profile.index')->with('success', 'Password berhasil diperbarui.');
        } catch (\Exception $e) {
            return redirect()->route('profile.index')->with('error', 'Gagal memperbarui password. ' . $e->getMessage());
        }
    }
}
