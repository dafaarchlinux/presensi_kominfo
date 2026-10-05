<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthPnsController extends Controller
{
    public function showLogin()
    {
        return view('auth.pns-login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'nip'      => 'required|string',
            'password' => 'required|string',
        ]);

        if (Auth::guard('pns')->attempt($credentials)) {
            $request->session()->regenerate();

            // ✅ ARAHKAN KE HALAMAN PRESENSI PNS
            return redirect()->route('pns.presensi.wajah');
        }

        return back()
            ->withErrors([
                'nip' => 'NIP atau password salah',
            ])
            ->withInput();
    }

    public function logout(Request $request)
    {
        Auth::guard('pns')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('pns.login');
    }
}
