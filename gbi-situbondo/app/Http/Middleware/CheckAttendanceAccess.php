<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Anggota;
use App\Models\Komsel;

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
        if ($request->routeIs('kehadiran.personal-report') && !$user->id_anggota) {
            return redirect()->route('dashboard')->with('error', 'Profil anggota tidak lengkap.');
        }
        
        // Check if route is for komsel report and user is komsel leader
        if ($request->routeIs('kehadiran.komsel-report')) {
            if (!$user->id_anggota) {
                return redirect()->route('dashboard')->with('error', 'Profil anggota tidak lengkap.');
            }
            
            $anggota = Anggota::find($user->id_anggota);
            $isKomselLeader = Komsel::where('id_pemimpin', $anggota->id_anggota)->exists();
            
            if (!$isKomselLeader) {
                return redirect()->route('dashboard')->with('error', 'Anda bukan pemimpin komsel.');
            }
        }
        
        return $next($request);
    }
}