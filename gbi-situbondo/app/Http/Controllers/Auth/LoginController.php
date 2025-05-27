<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $redirectTo = RouteServiceProvider::HOME;

    public function __construct()
    {
        $this->middleware('guest')->except('logout');
    }

    // Override metode username() untuk login dengan email
    public function username()
    {
        return 'email';
    }

    // Tambahkan validasi login untuk memeriksa status aktif
    protected function credentials(Request $request)
    {
        return $request->only($this->username(), 'password');
    }

    // Pesan setelah login
    protected function authenticated(Request $request, $user)
    {
        if ($user->anggota) {
            return redirect()->route('dashboard')->with('success', 'Selamat datang, ' . $user->anggota->nama . '!');
        }
        
        return redirect()->route('dashboard')->with('success', 'Selamat datang!');
    }
}
