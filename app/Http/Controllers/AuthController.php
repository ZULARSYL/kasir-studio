<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return $this->redirectToDashboard(Auth::user()->role);
        }

        return view('login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'username' => 'required|string',
            'password' => 'required|string',
        ], [
            'username.required' => 'Username tidak boleh kosong.',
            'password.required' => 'Password tidak boleh kosong.',
        ]);

        $cleanUsername = strtolower(trim((string) $request->input('username', '')));
        $credentials = [
            'username' => $cleanUsername,
            'password' => (string) $request->input('password', ''),
        ];

        $remember = $request->boolean('remember');

        if (Auth::attempt($credentials, $remember)) {
            $request->session()->regenerate();
            $user = Auth::user();

            $message = 'Selamat datang kembali, ' . $user->name . '! Anda berhasil masuk ke Sistem.';

            return redirect()->intended(route($this->dashboardRouteName($user->role)))
                ->with('success', $message);
        }

        return back()->withErrors([
            'username' => 'Username atau kata sandi yang Anda masukkan salah.',
        ])->onlyInput('username');
    }

    private function redirectToDashboard(string $role)
    {
        return redirect()->route($this->dashboardRouteName($role));
    }

    private function dashboardRouteName(string $role): string
    {
        return match ($role) {
            'admin' => 'admin.dashboard',
            'kasir' => 'kasir.index',
        };
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login')->with('success', 'Anda telah berhasil keluar.');
    }
}
