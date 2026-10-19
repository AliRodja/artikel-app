<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        // Auth::attempt mencari user berdasarkan email lalu mencocokkan password dengan Hash::check().
        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            // Pesan sengaja umum: tidak membocorkan apakah email terdaftar atau passwordnya yang salah.
            throw ValidationException::withMessages([
                'email' => __('auth.failed'),
            ]);
        }

        // Ganti session ID setelah login untuk mencegah session fixation.
        $request->session()->regenerate();

        // intended(): kembali ke halaman yang tadi dicoba dibuka sebelum dipaksa login.
        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::logout();

        // Hapus data session lama & buat CSRF token baru agar session lama tidak bisa dipakai lagi.
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home')->with('success', 'Anda berhasil keluar.');
    }
}
