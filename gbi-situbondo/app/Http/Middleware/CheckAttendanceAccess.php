<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Anggota;
use App\Models\Komsel;
use App\Models\JadwalPelayanan;

class CheckAttendanceAccess
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next)
    {
        $user = Auth::user();
        
        if (!$user) {
            return redirect()->route('login');
        }
        
        // Check if route is for personal report and user has anggota record
        if ($request->routeIs('laporan.personal-report') && !$user->id_anggota) {
            return redirect()->route('laporan.index')->with('error', 'Profil anggota tidak lengkap.');
        }
        
        // Check if route is for komsel report and user is komsel leader
        if ($request->routeIs('laporan.komsel-report')) {
            if (!$user->id_anggota) {
                return redirect()->route('laporan.index')->with('error', 'Profil anggota tidak lengkap.');
            }
            
            $anggota = Anggota::find($user->id_anggota);
            $isKomselLeader = Komsel::where('id_pemimpin', $anggota->id_anggota)->exists();
            
            if (!$isKomselLeader) {
                return redirect()->route('laporan.index')->with('error', 'Anda bukan pemimpin komsel.');
            }
        }
        
        // Check if route is for personal service report and user has service history
        if ($request->routeIs('laporan.personal-service-report')) {
            if (!$user->id_anggota) {
                return redirect()->route('laporan.index')->with('error', 'Profil anggota tidak lengkap.');
            }
            
            $anggota = Anggota::find($user->id_anggota);
            $hasServiceHistory = JadwalPelayanan::where('id_anggota', $anggota->id_anggota)->exists();
            
            if (!$hasServiceHistory) {
                return redirect()->route('laporan.index')->with('error', 'Anda belum memiliki riwayat pelayanan.');
            }
        }
        
        return $next($request);
    }
}