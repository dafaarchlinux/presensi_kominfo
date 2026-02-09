<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\PNS;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class PNSAuthController extends Controller
{
    /**
     * TAMPILKAN FORM LOGIN PNS
     */
    public function showLoginForm()
    {
        return view('auth.pns-login');
    }

    /**
     * PROSES LOGIN PNS
     */
    public function login(Request $request)
    {
        $request->validate([
            'nip'      => 'required|string',
            'password' => 'required|string',
        ]);

        // Cari PNS berdasarkan NIP
        $pns = PNS::where('nip', $request->nip)->first();

        if (!$pns || !$pns->password) {
            return back()->withErrors([
                'nip' => 'NIP atau password salah',
            ]);
        }

        // Cek password hash
        if (!Hash::check($request->password, $pns->password)) {
            return back()->withErrors([
                'nip' => 'NIP atau password salah',
            ]);
        }

        // Login pakai guard pns
        Auth::guard('pns')->login($pns);

        // NANTI kita arahkan ke presensi wajah
        return redirect()->intended('/pns/presensi-wajah');
    }

    /**
     * LOGOUT PNS
     */
    public function logout(Request $request)
    {
        Auth::guard('pns')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/login');
    }
}
