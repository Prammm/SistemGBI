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
        
        // Check if route is for personal report
        if ($request->routeIs('laporan.personal-report')) {
            // Admin dan Pengurus Gereja (role 1,2) bisa akses semua laporan pribadi
            if ($user->id_role <= 2) {
                return $next($request);
            }
            
            // Petugas Pelayanan (role 3) TIDAK BISA akses personal-report
            if ($user->id_role == 3) {
                return redirect()->route('laporan.index')
                    ->with('error', 'Anda tidak memiliki akses untuk melihat laporan kehadiran pribadi.');
            }
            
            // Anggota Jemaat (role 4) hanya bisa akses laporan pribadi sendiri
            if ($user->id_role == 4) {
                if (!$user->id_anggota) {
                    return redirect()->route('laporan.index')->with('error', 'Profil anggota tidak lengkap.');
                }
                
                // Pastikan tidak ada parameter user_id untuk anggota biasa
                if ($request->has('user_id') && $request->user_id != $user->id) {
                    return redirect()->route('laporan.personal-report')
                        ->with('error', 'Anda hanya dapat melihat laporan pribadi sendiri.');
                }
            }
        }
        
        // Check if route is for komsel report
        if ($request->routeIs('laporan.komsel-report')) {
            // Admin dan Pengurus Gereja (role 1,2) bisa akses semua
            if ($user->id_role <= 2) {
                return $next($request);
            }
            
            // Petugas Pelayanan (role 3) bisa akses dengan user selection
            if ($user->id_role == 3) {
                return $next($request);
            }
            
            // Anggota Jemaat (role 4) hanya jika pemimpin komsel
            if ($user->id_role == 4) {
                if (!$user->id_anggota) {
                    return redirect()->route('laporan.index')->with('error', 'Profil anggota tidak lengkap.');
                }
                
                $anggota = Anggota::find($user->id_anggota);
                $isKomselLeader = Komsel::where('id_pemimpin', $anggota->id_anggota)->exists();
                
                if (!$isKomselLeader) {
                    return redirect()->route('laporan.index')->with('error', 'Anda bukan pemimpin komsel.');
                }
                
                // Pastikan tidak ada parameter user_id untuk anggota biasa
                if ($request->has('user_id') && $request->user_id != $user->id) {
                    return redirect()->route('laporan.komsel-report')
                        ->with('error', 'Anda hanya dapat melihat laporan komsel yang Anda pimpin.');
                }
            }
        }
        
        // Check if route is for personal service report
        if ($request->routeIs('laporan.personal-service-report')) {
            // Admin, Pengurus Gereja, dan Petugas Pelayanan (role 1,2,3) bisa akses dengan user selection
            if ($user->id_role <= 3) {
                return $next($request);
            }
            
            // Anggota Jemaat (role 4) hanya bisa akses jika punya riwayat pelayanan
            if ($user->id_role == 4) {
                if (!$user->id_anggota) {
                    return redirect()->route('laporan.index')->with('error', 'Profil anggota tidak lengkap.');
                }
                
                $anggota = Anggota::find($user->id_anggota);
                $hasServiceHistory = JadwalPelayanan::where('id_anggota', $anggota->id_anggota)->exists();
                
                if (!$hasServiceHistory) {
                    return redirect()->route('laporan.index')->with('error', 'Anda belum memiliki riwayat pelayanan.');
                }
                
                // Pastikan tidak ada parameter user_id untuk anggota biasa
                if ($request->has('user_id') && $request->user_id != $user->id) {
                    return redirect()->route('laporan.personal-service-report')
                        ->with('error', 'Anda hanya dapat melihat riwayat pelayanan sendiri.');
                }
            }
        }
        
        return $next($request);
    }
}