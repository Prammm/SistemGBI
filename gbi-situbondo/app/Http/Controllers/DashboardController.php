<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Anggota;
use App\Models\Kegiatan;
use App\Models\PelaksanaanKegiatan;
use App\Models\Komsel;
use App\Models\JadwalPelayanan;
use App\Models\Kehadiran;
use App\Models\User;
use App\Models\Keluarga;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

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

        switch ($user->id_role) {
            case 1: // Admin
                $data = $this->getAdminDashboardData($user);
                break;
            case 2: // Pengurus Gereja
                $data = $this->getPengurusDashboardData($user);
                break;
            case 3: // Petugas Pelayanan
                $data = $this->getPetugasDashboardData($user);
                break;
            case 4: // Anggota Jemaat
                $data = $this->getAnggotaDashboardData($user);
                break;
            default:
                $data = $this->getBasicDashboardData();
        }

        return view('dashboard', compact('data', 'user'));
    }

    /**
     * ADMIN Dashboard - System Oversight
     */
    private function getAdminDashboardData($user)
    {
        return [
            // System Overview
            'total_users' => User::count(),
            'total_anggota' => Anggota::count(),
            'total_keluarga' => Keluarga::count(),
            'total_komsel' => Komsel::count(),
            
            // User Activity
            'new_users_this_month' => User::whereMonth('created_at', Carbon::now()->month)
                ->whereYear('created_at', Carbon::now()->year)
                ->count(),
            'new_anggota_this_month' => Anggota::whereMonth('created_at', Carbon::now()->month)
                ->whereYear('created_at', Carbon::now()->year)
                ->count(),
            
            // Recent Activities
            'recent_users' => User::with('anggota', 'role')
                ->orderBy('created_at', 'desc')
                ->limit(5)
                ->get(),
            'recent_anggota' => Anggota::orderBy('created_at', 'desc')
                ->limit(5)
                ->get(),
            
            // System Alerts
            'users_without_anggota' => User::whereNull('id_anggota')
                ->where('id_role', '>', 1)
                ->count(),
            'anggota_without_komsel' => Anggota::whereDoesntHave('komsel')->count(),
            
            // Upcoming Critical Events
            'upcoming_events' => PelaksanaanKegiatan::with('kegiatan')
                ->where('tanggal_kegiatan', '>=', Carbon::now()->format('Y-m-d'))
                ->orderBy('tanggal_kegiatan')
                ->limit(5)
                ->get(),
                
            // Quick Stats
            'active_this_week' => Kehadiran::whereBetween('waktu_absensi', [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek()
            ])->distinct('id_anggota')->count('id_anggota'),
            
            'role_name' => 'Administrator Sistem'
        ];
    }

    /**
     * PENGURUS GEREJA Dashboard - Operational Management
     */
    private function getPengurusDashboardData($user)
    {
        return [
            // Church Overview
            'total_anggota' => Anggota::count(),
            'total_keluarga' => Keluarga::count(),
            'total_komsel' => Komsel::count(),
            'average_komsel_size' => Komsel::withCount('anggota')->avg('anggota_count'),
            
            // Growth Metrics
            'new_anggota_this_month' => Anggota::whereMonth('created_at', Carbon::now()->month)
                ->whereYear('created_at', Carbon::now()->year)
                ->count(),
            'new_families_this_month' => Keluarga::whereMonth('created_at', Carbon::now()->month)
                ->whereYear('created_at', Carbon::now()->year)
                ->count(),
            
            // Recent New Members
            'recent_anggota' => Anggota::with('keluarga')
                ->orderBy('created_at', 'desc')
                ->limit(8)
                ->get(),
            
            // Upcoming Events Need Attention
            'upcoming_events' => PelaksanaanKegiatan::with('kegiatan')
                ->where('tanggal_kegiatan', '>=', Carbon::now()->format('Y-m-d'))
                ->orderBy('tanggal_kegiatan')
                ->limit(6)
                ->get(),
            
            // Events This Week
            'events_this_week' => PelaksanaanKegiatan::with('kegiatan')
                ->whereBetween('tanggal_kegiatan', [
                    Carbon::now()->startOfWeek(),
                    Carbon::now()->endOfWeek()
                ])
                ->orderBy('tanggal_kegiatan')
                ->get(),
                
            // Attendance Overview
            'attendance_this_week' => Kehadiran::whereBetween('waktu_absensi', [
                Carbon::now()->startOfWeek(),
                Carbon::now()->endOfWeek()
            ])->count(),
            
            'active_members_this_month' => Kehadiran::whereMonth('waktu_absensi', Carbon::now()->month)
                ->whereYear('waktu_absensi', Carbon::now()->year)
                ->distinct('id_anggota')
                ->count('id_anggota'),
            
            // Komsel Status
            'komsel_with_recent_activity' => Komsel::whereHas('anggota.kehadiran', function($q) {
                $q->where('waktu_absensi', '>=', Carbon::now()->subDays(30));
            })->count(),
            
            'role_name' => 'Pengurus Gereja'
        ];
    }

    /**
     * PETUGAS PELAYANAN Dashboard - Service Operations
     */
    private function getPetugasDashboardData($user)
    {
        return [
            // Service Overview
            'upcoming_services' => JadwalPelayanan::with(['anggota', 'pelaksanaan.kegiatan'])
                ->whereHas('pelaksanaan', function($q) {
                    $q->where('tanggal_kegiatan', '>=', Carbon::now()->format('Y-m-d'));
                })
                ->orderBy('tanggal_pelayanan')
                ->limit(10)
                ->get(),
            
            // Confirmations Needed
            'pending_confirmations' => JadwalPelayanan::with(['anggota', 'pelaksanaan.kegiatan'])
                ->where('status_konfirmasi', 'belum')
                ->whereHas('pelaksanaan', function($q) {
                    $q->where('tanggal_kegiatan', '>=', Carbon::now()->format('Y-m-d'));
                })
                ->count(),
            
            'pending_services_detail' => JadwalPelayanan::with(['anggota', 'pelaksanaan.kegiatan'])
                ->where('status_konfirmasi', 'belum')
                ->whereHas('pelaksanaan', function($q) {
                    $q->where('tanggal_kegiatan', '>=', Carbon::now()->format('Y-m-d'))
                        ->where('tanggal_kegiatan', '<=', Carbon::now()->addDays(7)->format('Y-m-d'));
                })
                ->orderBy('tanggal_pelayanan')
                ->limit(5)
                ->get(),
            
            // Today's Activities
            'todays_events' => PelaksanaanKegiatan::with('kegiatan')
                ->where('tanggal_kegiatan', Carbon::now()->format('Y-m-d'))
                ->orderBy('jam_mulai')
                ->get(),
            
            // Attendance Today
            'attendance_today' => Kehadiran::whereDate('waktu_absensi', Carbon::now())
                ->count(),
            
            // This Week's Events Need Attention
            'events_this_week' => PelaksanaanKegiatan::with(['kegiatan', 'kehadiran'])
                ->whereBetween('tanggal_kegiatan', [
                    Carbon::now()->format('Y-m-d'),
                    Carbon::now()->addDays(7)->format('Y-m-d')
                ])
                ->orderBy('tanggal_kegiatan')
                ->get(),
            
            // Service Statistics
            'total_services_this_month' => JadwalPelayanan::whereMonth('tanggal_pelayanan', Carbon::now()->month)
                ->whereYear('tanggal_pelayanan', Carbon::now()->year)
                ->count(),
            
            'active_servants' => JadwalPelayanan::whereMonth('tanggal_pelayanan', Carbon::now()->month)
                ->whereYear('tanggal_pelayanan', Carbon::now()->year)
                ->distinct('id_anggota')
                ->count('id_anggota'),
            
            // Members needing scheduling attention
            'members_need_scheduling' => Anggota::whereHas('spesialisasi')
                ->whereDoesntHave('jadwalPelayanan', function($q) {
                    $q->where('tanggal_pelayanan', '>=', Carbon::now()->format('Y-m-d'));
                })
                ->count(),
            
            'role_name' => 'Petugas Pelayanan'
        ];
    }

    /**
     * ANGGOTA JEMAAT Dashboard - Personal View
     */
    private function getAnggotaDashboardData($user)
    {
        $anggota = $user->anggota;
        
        if (!$anggota) {
            return [
                'role_name' => 'Anggota Jemaat',
                'profile_incomplete' => true,
                'message' => 'Profil anggota belum lengkap. Silakan hubungi admin untuk melengkapi profil Anda.'
            ];
        }

        return [
            // My Services
            'my_upcoming_services' => JadwalPelayanan::with(['pelaksanaan.kegiatan'])
                ->where('id_anggota', $anggota->id_anggota)
                ->whereHas('pelaksanaan', function($q) {
                    $q->where('tanggal_kegiatan', '>=', Carbon::now()->format('Y-m-d'));
                })
                ->orderBy('tanggal_pelayanan')
                ->limit(5)
                ->get(),
            
            // Confirmations Needed
            'my_pending_confirmations' => JadwalPelayanan::with(['pelaksanaan.kegiatan'])
                ->where('id_anggota', $anggota->id_anggota)
                ->where('status_konfirmasi', 'belum')
                ->whereHas('pelaksanaan', function($q) {
                    $q->where('tanggal_kegiatan', '>=', Carbon::now()->format('Y-m-d'));
                })
                ->orderBy('tanggal_pelayanan')
                ->get(),
            
            // My Komsel
            'my_komsel' => $anggota->komsel()->with('pemimpin')->get(),
            
            // Upcoming Komsel Meetings
            'upcoming_komsel_meetings' => $this->getUpcomingKomselMeetings($anggota),
            
            // Today's Events I Can Attend
            'todays_events' => $this->getTodaysEventsForMember($anggota),
            
            // My Recent Attendance
            'my_recent_attendance' => Kehadiran::with(['pelaksanaan.kegiatan'])
                ->where('id_anggota', $anggota->id_anggota)
                ->orderBy('waktu_absensi', 'desc')
                ->limit(5)
                ->get(),
            
            // Personal Statistics
            'my_attendance_this_month' => Kehadiran::where('id_anggota', $anggota->id_anggota)
                ->whereMonth('waktu_absensi', Carbon::now()->month)
                ->whereYear('waktu_absensi', Carbon::now()->year)
                ->count(),
            
            'my_services_this_month' => JadwalPelayanan::where('id_anggota', $anggota->id_anggota)
                ->whereMonth('tanggal_pelayanan', Carbon::now()->month)
                ->whereYear('tanggal_pelayanan', Carbon::now()->year)
                ->count(),
            
            // Family Info
            'my_family' => $anggota->id_keluarga ? 
                Anggota::where('id_keluarga', $anggota->id_keluarga)
                    ->where('id_anggota', '!=', $anggota->id_anggota)
                    ->limit(5)
                    ->get() : collect(),
            
            'role_name' => 'Anggota Jemaat',
            'anggota' => $anggota
        ];
    }

    /**
     * Get upcoming komsel meetings for member
     */
    private function getUpcomingKomselMeetings($anggota)
    {
        $komselNames = $anggota->komsel->pluck('nama_komsel')->toArray();
        $komselActivityPatterns = array_map(function($name) {
            return 'Komsel - ' . $name;
        }, $komselNames);
        
        if (empty($komselActivityPatterns)) {
            return collect();
        }
        
        return PelaksanaanKegiatan::with('kegiatan')
            ->whereHas('kegiatan', function($query) use ($komselActivityPatterns) {
                $query->where('tipe_kegiatan', 'komsel')
                    ->whereIn('nama_kegiatan', $komselActivityPatterns);
            })
            ->where('tanggal_kegiatan', '>=', Carbon::now()->format('Y-m-d'))
            ->orderBy('tanggal_kegiatan')
            ->limit(3)
            ->get();
    }

    /**
     * Get today's events member can attend
     */
    private function getTodaysEventsForMember($anggota)
    {
        $today = Carbon::now()->format('Y-m-d');
        
        // Get user's komsel activity patterns
        $komselNames = $anggota->komsel->pluck('nama_komsel')->toArray();
        $komselActivityPatterns = array_map(function($name) {
            return 'Komsel - ' . $name;
        }, $komselNames);
        
        return PelaksanaanKegiatan::with('kegiatan')
            ->where('tanggal_kegiatan', $today)
            ->where(function($query) use ($komselActivityPatterns) {
                // Include user's komsel activities
                $query->whereHas('kegiatan', function($subquery) use ($komselActivityPatterns) {
                    $subquery->where('tipe_kegiatan', 'komsel')
                        ->whereIn('nama_kegiatan', $komselActivityPatterns);
                })
                // Include non-komsel activities (church-wide)
                ->orWhereHas('kegiatan', function($subquery) {
                    $subquery->where('tipe_kegiatan', '!=', 'komsel');
                });
            })
            ->orderBy('jam_mulai')
            ->get();
    }

    /**
     * Fallback basic dashboard data
     */
    private function getBasicDashboardData()
    {
        return [
            'total_anggota' => Anggota::count(),
            'total_komsel' => Komsel::count(),
            'upcoming_events' => PelaksanaanKegiatan::with('kegiatan')
                ->where('tanggal_kegiatan', '>=', Carbon::now()->format('Y-m-d'))
                ->orderBy('tanggal_kegiatan')
                ->limit(5)
                ->get(),
            'role_name' => 'User'
        ];
    }
}