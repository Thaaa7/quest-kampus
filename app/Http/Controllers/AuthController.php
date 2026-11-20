<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function show()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'identifier' => ['required', 'string', 'max:30'],
            'password' => ['required', 'string'],
        ], [], ['identifier' => 'NIM atau NIP', 'password' => 'kata sandi']);

        if (! Auth::attempt($credentials, $request->boolean('remember'))) {
            return back()
                ->withErrors(['password' => 'NIM atau kata sandi salah. Coba lagi.'])
                ->onlyInput('identifier');
        }

        $request->session()->regenerate();

        // Kalau sebelumnya membuka QR ruangan (atau halaman lain) sebelum login, kembali ke sana.
        return redirect()->intended($request->user()->homeRoute());
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
