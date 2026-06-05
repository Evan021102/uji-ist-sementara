<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AksesPin;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (session()->has('role_akses')) {
            return redirect()->route('dashboard.index');
        }
        return view('dashboard.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'pin' => 'required|string',
        ]);

        $akses = AksesPin::where('pin', $request->pin)->first();

        if ($akses) {
            session(['role_akses' => $akses->role]);
            return redirect()->route('dashboard.index');
        }

        return redirect()->back()->with('error', 'PIN yang Anda masukkan salah!');
    }

    public function logout()
    {
        session()->forget('role_akses');
        return redirect()->route('login');
    }
}
