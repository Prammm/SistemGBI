<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Anggota;
use App\Models\Kegiatan;
use App\Models\PelaksanaanKegiatan;
use App\Models\Komsel;
use App\Models\JadwalPelayanan;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = Auth::user();
        $data = [];

        // Data untuk semua role
        $data['total_anggota'] = Anggota::count();
        $data['total_komsel'] = Komsel::count();
        $data['kegiatan_mendatang'] = PelaksanaanKegiatan::with('kegiatan')
            ->where('tanggal_kegiatan', '>=', now()->format('Y-m-d'))
            ->orderBy('tanggal_kegiatan')
            ->limit(5)
            ->get();

        // Data spesifik untuk role tertentu
        if ($user->id_role == 1 || $user->id_role == 2) { // Admin atau Pengurus Gereja
            $data['anggota_baru'] = Anggota::orderBy('created_at', 'desc')
                ->limit(5)
                ->get();
        }

        if ($user->id_role == 3) { // Pengurus Pelayanan
            $data['jadwal_pelayanan'] = JadwalPelayanan::where('tanggal_pelayanan', '>=', now()->format('Y-m-d'))
                ->orderBy('tanggal_pelayanan')
                ->limit(5)
                ->get();
        }

        if ($user->id_role == 4) { // Anggota Jemaat
            if ($user->id_anggota) {
                $data['jadwal_pelayanan_saya'] = JadwalPelayanan::where('id_anggota', $user->id_anggota)
                    ->where('tanggal_pelayanan', '>=', now()->format('Y-m-d'))
                    ->orderBy('tanggal_pelayanan')
                    ->limit(5)
                    ->get();

                $data['komsel_saya'] = Komsel::whereHas('anggota', function($query) use ($user) {
                    $query->where('anggota.id_anggota', $user->id_anggota);
                })->get();
            }
        }

        return view('dashboard', compact('data', 'user'));
    }
}