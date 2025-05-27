<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TestController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function testPermission()
    {
        // Simple method to test if authentication works
        return view('dashboard');
    }
    
    public function testViewAnggota()
    {
        // This route will be protected using route middleware
        return view('anggota.index');
    }
}