<?php
// app/Http/Controllers/LaporanController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Anggota;
use App\Models\Kehadiran;
use App\Models\JadwalPelayanan;
use App\Models\PelaksanaanKegiatan;
use App\Models\Kegiatan;
use App\Models\Komsel;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Services\ExcelExportService;

class LaporanController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:view_laporan')->only(['index']);
    }

    public function index()
    {
        $user = Auth::user();
        
        // Determine which reports the user can access based on their role
        $availableReports = $this->getAvailableReports($user);
        
        return view('laporan.index', compact('availableReports'));
    }

    /**
     * Determine which reports are available for the user based on their role
     */
    private function getAvailableReports($user)
    {
        $reports = [];
        
        // Admin and Pengurus Gereja can access all reports + personal reports
        if ($user->id_role <= 2) {
            // System-wide reports
            $reports['kehadiran'] = [
                'title' => 'Laporan Kehadiran',
                'description' => 'Melihat statistik kehadiran jemaat pada kegiatan gereja dalam periode tertentu.',
                'route' => 'laporan.kehadiran',
                'icon' => 'fa-clipboard-check',
                'color' => 'kehadiran'
            ];
            
            $reports['pelayanan'] = [
                'title' => 'Laporan Pelayanan',
                'description' => 'Melihat statistik pelayanan dan aktivitas pelayan dalam periode tertentu.',
                'route' => 'laporan.pelayanan',
                'icon' => 'fa-hands-helping',
                'color' => 'pelayanan'
            ];
            
            $reports['komsel'] = [
                'title' => 'Laporan Komsel',
                'description' => 'Melihat statistik kelompok sel, anggota, dan kegiatan komsel dalam periode tertentu.',
                'route' => 'laporan.komsel',
                'icon' => 'fa-users',
                'color' => 'komsel'
            ];
            
            $reports['anggota'] = [
                'title' => 'Laporan Anggota',
                'description' => 'Melihat statistik anggota jemaat, demografi, dan pertumbuhan jemaat.',
                'route' => 'laporan.anggota',
                'icon' => 'fa-user-friends',
                'color' => 'anggota'
            ];
            
            // Personal reports with user selection capability for Admin & Pengurus
            $reports['kehadiran-personal'] = [
                'title' => 'Kehadiran Pribadi',
                'description' => 'Melihat riwayat kehadiran pribadi (dapat memilih anggota lain untuk supervisori).',
                'route' => 'laporan.personal-report',
                'icon' => 'fa-user-check',
                'color' => 'kehadiran',
                'can_select_user' => true
            ];
            
            $reports['pelayanan-personal'] = [
                'title' => 'Riwayat Pelayanan Pribadi',
                'description' => 'Melihat riwayat pelayanan pribadi (dapat memilih anggota lain untuk supervisori).',
                'route' => 'laporan.personal-service-report',
                'icon' => 'fa-hand-holding-heart',
                'color' => 'pelayanan',
                'can_select_user' => true
            ];
            
            $reports['komsel-leader'] = [
                'title' => 'Laporan Komsel (Pemimpin)',
                'description' => 'Melihat laporan komsel sebagai pemimpin (dapat memilih pemimpin komsel lain).',
                'route' => 'laporan.komsel-report',
                'icon' => 'fa-users-cog',
                'color' => 'komsel',
                'can_select_user' => true
            ];
        }
        // Petugas Pelayanan
        elseif ($user->id_role == 3) {
            // General reports they can access
            $reports['kehadiran'] = [
                'title' => 'Laporan Kehadiran',
                'description' => 'Melihat statistik kehadiran jemaat pada kegiatan gereja dalam periode tertentu.',
                'route' => 'laporan.kehadiran',
                'icon' => 'fa-clipboard-check',
                'color' => 'kehadiran'
            ];
            
            $reports['pelayanan'] = [
                'title' => 'Laporan Pelayanan',
                'description' => 'Melihat statistik pelayanan dan aktivitas pelayan.',
                'route' => 'laporan.pelayanan',
                'icon' => 'fa-hands-helping',
                'color' => 'pelayanan'
            ];
            
            // Service reports with user selection - Petugas Pelayanan bisa pilih anggota lain
            $reports['pelayanan-personal'] = [
                'title' => 'Riwayat Pelayanan',
                'description' => 'Melihat riwayat pelayanan (dapat memilih anggota lain untuk supervisori).',
                'route' => 'laporan.personal-service-report',
                'icon' => 'fa-hand-holding-heart',
                'color' => 'pelayanan',
                'can_select_user' => true
            ];
            
            // Check if user is a komsel leader (same as regular member logic)
            if ($user->id_anggota) {
                $anggota = Anggota::find($user->id_anggota);
                $isKomselLeader = Komsel::where('id_pemimpin', $anggota->id_anggota)->exists();
                if ($isKomselLeader) {
                    $reports['komsel-leader'] = [
                        'title' => 'Laporan Komsel',
                        'description' => 'Melihat statistik kehadiran dan aktivitas komsel yang Anda pimpin.',
                        'route' => 'laporan.komsel-report',
                        'icon' => 'fa-users',
                        'color' => 'komsel'
                    ];
                }
            }
        }
        // Anggota Jemaat
        elseif ($user->id_role == 4) {
            $reports['kehadiran-personal'] = [
                'title' => 'Kehadiran Pribadi',
                'description' => 'Melihat riwayat kehadiran Anda pada ibadah, komsel, dan pelayanan.',
                'route' => 'laporan.personal-report',
                'icon' => 'fa-user-check',
                'color' => 'kehadiran'
            ];
            
            if ($user->id_anggota) {
                $anggota = Anggota::find($user->id_anggota);
                
                // Check if user is a komsel leader
                $isKomselLeader = Komsel::where('id_pemimpin', $anggota->id_anggota)->exists();
                if ($isKomselLeader) {
                    $reports['komsel-leader'] = [
                        'title' => 'Laporan Komsel',
                        'description' => 'Melihat statistik kehadiran dan aktivitas komsel yang Anda pimpin.',
                        'route' => 'laporan.komsel-report',
                        'icon' => 'fa-users',
                        'color' => 'komsel'
                    ];
                }
                
                // Check if user has service activities
                $hasServiceHistory = JadwalPelayanan::where('id_anggota', $anggota->id_anggota)->exists();
                if ($hasServiceHistory) {
                    $reports['pelayanan-personal'] = [
                        'title' => 'Riwayat Pelayanan',
                        'description' => 'Melihat riwayat dan statistik pelayanan pribadi Anda.',
                        'route' => 'laporan.personal-service-report',
                        'icon' => 'fa-hand-holding-heart',
                        'color' => 'pelayanan'
                    ];
                }
            }
        }
        
        return $reports;
    }

    public function kehadiran(Request $request)
    {
        $user = Auth::user();
        
        // Only admin, pengurus, and petugas pelayanan can access general attendance reports
        if ($user->id_role > 3) {
            return redirect()->route('laporan.index')
                ->with('error', 'Anda tidak memiliki akses untuk melihat laporan ini.');
        }

        // Get filter parameters
        $period = $request->input('period', 'custom');
        $bulan = $request->input('bulan', Carbon::now()->month);
        $tahun = $request->input('tahun', Carbon::now()->year);
        $kegiatan_id = $request->input('kegiatan_id');
        $pelaksanaan_id = $request->input('pelaksanaan_id');
        $status_filter = $request->input('status_filter'); // New filter
        
        // Handle period-based filtering
        if (in_array($period, [1, 3, 6, 12])) {
            $endDate = Carbon::now();
            $startDate = Carbon::now()->subMonths($period);
            $bulan = $endDate->month;
            $tahun = $endDate->year;
        }
        
        // Data untuk filter
        $bulanList = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        
        $tahunList = range(Carbon::now()->year - 5, Carbon::now()->year);
        
        // Get all kegiatan for filter
        $kegiatanList = Kegiatan::orderBy('nama_kegiatan')->get();
        
        // Get pelaksanaan for selected kegiatan
        $pelaksanaanList = collect();
        if ($kegiatan_id) {
            if (in_array($period, [1, 3, 6, 12])) {
                $pelaksanaanList = PelaksanaanKegiatan::where('id_kegiatan', $kegiatan_id)
                    ->whereBetween('tanggal_kegiatan', [$startDate, $endDate])
                    ->orderBy('tanggal_kegiatan')
                    ->get();
            } else {
                $pelaksanaanList = PelaksanaanKegiatan::where('id_kegiatan', $kegiatan_id)
                    ->whereMonth('tanggal_kegiatan', $bulan)
                    ->whereYear('tanggal_kegiatan', $tahun)
                    ->orderBy('tanggal_kegiatan')
                    ->get();
            }
        }
        
        // Get pelaksanaan kegiatan untuk periode yang dipilih
        if (in_array($period, [1, 3, 6, 12])) {
            $pelaksanaanQuery = PelaksanaanKegiatan::with('kegiatan')
                ->whereBetween('tanggal_kegiatan', [$startDate, $endDate]);
        } else {
            $pelaksanaanQuery = PelaksanaanKegiatan::with('kegiatan')
                ->whereMonth('tanggal_kegiatan', $bulan)
                ->whereYear('tanggal_kegiatan', $tahun);
        }
        
        if ($kegiatan_id) {
            $pelaksanaanQuery->where('id_kegiatan', $kegiatan_id);
        }
        
        if ($pelaksanaan_id) {
            $pelaksanaanQuery->where('id_pelaksanaan', $pelaksanaan_id);
        }
        
        $pelaksanaanEvents = $pelaksanaanQuery->get();
        
        // Generate comprehensive attendance records with auto-assignment logic
        $attendanceRecords = $this->generateAttendanceRecords($pelaksanaanEvents, $status_filter);
        
        // Calculate statistics
        $totalAnggota = Anggota::count();
        $totalHadir = $attendanceRecords->where('status', 'hadir')->count();
        $totalTidakHadir = $attendanceRecords->whereIn('status', ['izin', 'sakit', 'alfa', 'tidak_hadir'])->count();
        $totalExpectedAttendance = $attendanceRecords->count();
        
        // Status breakdown for pie chart
        $statusBreakdown = [
            'hadir' => $attendanceRecords->where('status', 'hadir')->count(),
            'izin' => $attendanceRecords->where('status', 'izin')->count(),
            'sakit' => $attendanceRecords->where('status', 'sakit')->count(),
            'alfa' => $attendanceRecords->where('status', 'alfa')->count(),
            'tidak_hadir' => $attendanceRecords->where('status', 'tidak_hadir')->count(),
        ];
        
        // Kehadiran per kegiatan
        $kehadiranPerKegiatan = $attendanceRecords->groupBy('nama_kegiatan')
            ->map(function ($items, $key) {
                return [
                    'kegiatan' => $key,
                    'jumlah' => $items->where('status', 'hadir')->count()
                ];
            })
            ->sortByDesc('jumlah')
            ->values();

        // NEW: Kehadiran per minggu untuk chart
        $kehadiranPerMinggu = $this->getKehadiranPerMinggu($pelaksanaanEvents, $bulan, $tahun);
        
        return view('laporan.kehadiran', compact(
            'attendanceRecords',
            'bulanList', 
            'tahunList', 
            'bulan', 
            'tahun', 
            'period',
            'totalAnggota', 
            'totalHadir',
            'totalTidakHadir',
            'totalExpectedAttendance',
            'statusBreakdown',
            'kehadiranPerKegiatan',
            'kehadiranPerMinggu', // NEW
            'kegiatanList',
            'pelaksanaanList',
            'kegiatan_id',
            'pelaksanaan_id'
        ));
    }

    /**
     * NEW: Generate kehadiran per minggu data
     */
    private function getKehadiranPerMinggu($pelaksanaanEvents, $bulan, $tahun)
    {
        $kehadiranPerMinggu = [];
        $startDate = Carbon::createFromDate($tahun, $bulan, 1);
        $endDate = $startDate->copy()->endOfMonth();
        
        for ($week = 1; $week <= 5; $week++) {
            $weekStart = ($week - 1) * 7 + 1;
            $weekStartDate = Carbon::createFromDate($tahun, $bulan, $weekStart)->startOfDay();
            $weekEndDate = $weekStartDate->copy()->addDays(6)->endOfDay();
            
            if ($weekStartDate->gt($endDate)) {
                break;
            }
            
            // Count attendance within this week
            $weeklyCount = 0;
            foreach ($pelaksanaanEvents as $pelaksanaan) {
                $eventDate = Carbon::parse($pelaksanaan->tanggal_kegiatan);
                if ($eventDate->between($weekStartDate, $weekEndDate)) {
                    $weeklyCount += Kehadiran::where('id_pelaksanaan', $pelaksanaan->id_pelaksanaan)
                        ->where('status', 'hadir')
                        ->count();
                }
            }
            
            $kehadiranPerMinggu[] = [
                'minggu' => "Minggu $week",
                'jumlah' => $weeklyCount
            ];
        }
        
        return $kehadiranPerMinggu;
    }

    /**
     * Generate comprehensive attendance records with auto-assignment logic
     */
    private function generateAttendanceRecords($pelaksanaanEvents, $statusFilter = null)
    {
        $attendanceRecords = collect();
        
        foreach ($pelaksanaanEvents as $pelaksanaan) {
            // Get actual attendance records
            $actualAttendance = Kehadiran::where('id_pelaksanaan', $pelaksanaan->id_pelaksanaan)
                ->with('anggota.keluarga')
                ->get();
            
            // Determine who should attend this event
            $expectedAttendees = $this->getExpectedAttendees($pelaksanaan);
            
            foreach ($expectedAttendees as $anggota) {
                // Check if there's actual attendance record
                $actualRecord = $actualAttendance->where('id_anggota', $anggota->id_anggota)->first();
                
                if ($actualRecord) {
                    // Use actual attendance record
                    $record = [
                        'tanggal' => $pelaksanaan->tanggal_kegiatan,
                        'nama_anggota' => $anggota->nama,
                        'nama_keluarga' => $anggota->keluarga->nama_keluarga ?? null,
                        'nama_kegiatan' => $pelaksanaan->kegiatan->nama_kegiatan,
                        'tipe_kegiatan' => $pelaksanaan->kegiatan->tipe_kegiatan,
                        'waktu_absensi' => $actualRecord->waktu_absensi,
                        'status' => $actualRecord->status,
                        'lokasi' => $pelaksanaan->lokasi
                    ];
                } else {
                    // PERBAIKAN: Auto-assign sebagai "tidak_hadir" untuk event yang sudah lewat
                    $eventDate = Carbon::parse($pelaksanaan->tanggal_kegiatan);
                    if ($eventDate->isPast()) {
                        $record = [
                            'tanggal' => $pelaksanaan->tanggal_kegiatan,
                            'nama_anggota' => $anggota->nama,
                            'nama_keluarga' => $anggota->keluarga->nama_keluarga ?? null,
                            'nama_kegiatan' => $pelaksanaan->kegiatan->nama_kegiatan,
                            'tipe_kegiatan' => $pelaksanaan->kegiatan->tipe_kegiatan,
                            'waktu_absensi' => null,
                            'status' => 'tidak_hadir',
                            'lokasi' => $pelaksanaan->lokasi
                        ];
                    } else {
                        // Skip future events without attendance
                        continue;
                    }
                }
                
                // PERBAIKAN: Apply status filter yang benar
                if ($statusFilter) {
                    if ($statusFilter === 'hadir' && $record['status'] !== 'hadir') {
                        continue;
                    }
                    if ($statusFilter === 'tidak_hadir' && $record['status'] === 'hadir') {
                        continue;
                    }
                }
                
                $attendanceRecords->push($record);
            }
        }
        
        return $attendanceRecords;
    }

    /**
     * Determine who should attend a specific event
     */
    private function getExpectedAttendees($pelaksanaan)
    {
        $tipeKegiatan = $pelaksanaan->kegiatan->tipe_kegiatan;
        
        if ($tipeKegiatan === 'komsel') {
            // For komsel, only komsel members are expected
            $namaKomsel = str_replace('Komsel - ', '', $pelaksanaan->kegiatan->nama_kegiatan);
            $komsel = Komsel::where('nama_komsel', $namaKomsel)->first();
            
            if ($komsel) {
                return $komsel->anggota()->with('keluarga')->get();
            } else {
                return collect();
            }
        } else {
            // For other events (ibadah, etc.), all active members are expected
            return Anggota::with('keluarga')->get();
        }
    }

    public function pelayanan(Request $request)
    {
        $user = Auth::user();
        
        // Auto-reject expired schedules when viewing pelayanan reports
        $this->autoRejectExpiredSchedules();
        
        // Get filter parameters
        $period = $request->input('period', 'custom');
        $bulan = $request->input('bulan', Carbon::now()->month);
        $tahun = $request->input('tahun', Carbon::now()->year);
        $kegiatan_id = $request->input('kegiatan_id');
        $pelaksanaan_id = $request->input('pelaksanaan_id');
        $status_filter = $request->input('status_filter'); // New filter
        
        // Handle period-based filtering
        if (in_array($period, [1, 3, 6, 12])) {
            $endDate = Carbon::now();
            $startDate = Carbon::now()->subMonths($period);
            $bulan = $endDate->month;
            $tahun = $endDate->year;
        }
        
        // Data untuk filter
        $bulanList = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        
        $tahunList = range(Carbon::now()->year - 5, Carbon::now()->year);
        
        // Get all kegiatan for filter
        $kegiatanList = Kegiatan::orderBy('nama_kegiatan')->get();
        
        // Get pelaksanaan for selected kegiatan
        $pelaksanaanList = collect();
        if ($kegiatan_id) {
            if (in_array($period, [1, 3, 6, 12])) {
                $pelaksanaanList = PelaksanaanKegiatan::where('id_kegiatan', $kegiatan_id)
                    ->whereBetween('tanggal_kegiatan', [$startDate, $endDate])
                    ->orderBy('tanggal_kegiatan')
                    ->get();
            } else {
                $pelaksanaanList = PelaksanaanKegiatan::where('id_kegiatan', $kegiatan_id)
                    ->whereMonth('tanggal_kegiatan', $bulan)
                    ->whereYear('tanggal_kegiatan', $tahun)
                    ->orderBy('tanggal_kegiatan')
                    ->get();
            }
        }
        
        // Query data pelayanan berdasarkan role dan filter
        if (in_array($period, [1, 3, 6, 12])) {
            $query = JadwalPelayanan::whereBetween('tanggal_pelayanan', [$startDate, $endDate])
                ->with(['anggota', 'kegiatan', 'pelaksanaan']);
        } else {
            $query = JadwalPelayanan::whereMonth('tanggal_pelayanan', $bulan)
                ->whereYear('tanggal_pelayanan', $tahun)
                ->with(['anggota', 'kegiatan', 'pelaksanaan']);
        }
        
        if ($kegiatan_id) {
            $query->where('id_kegiatan', $kegiatan_id);
        }
        
        if ($pelaksanaan_id) {
            $query->where('id_pelaksanaan', $pelaksanaan_id);
        }
        
        // Apply status filter
        if ($status_filter) {
            if ($status_filter === 'terima') {
                $query->where('status_konfirmasi', 'terima');
            } elseif ($status_filter === 'tolak') {
                $query->where('status_konfirmasi', 'tolak');
            } elseif ($status_filter === 'belum') {
                $query->where('status_konfirmasi', 'belum');
            }
        }
        
        // Filter based on user role
        if ($user->id_role == 3) {
            $jadwalPelayanan = $query->get();
        } elseif ($user->id_role == 4) {
            if (!$user->id_anggota) {
                return redirect()->route('laporan.index')
                    ->with('error', 'Profil anggota tidak lengkap.');
            }
            $jadwalPelayanan = $query->where('id_anggota', $user->id_anggota)->get();
        } else {
            $jadwalPelayanan = $query->get();
        }
        
        // Statistik pelayanan - hanya yang status_konfirmasi = 'terima'
        $jadwalPelayananTerima = $jadwalPelayanan->where('status_konfirmasi', 'terima');
        $totalPelayanan = $jadwalPelayananTerima->count();
        $totalPelayan = $jadwalPelayananTerima->groupBy('id_anggota')->count();
        
        // Status breakdown
        $statusBreakdown = [
            'terima' => $jadwalPelayanan->where('status_konfirmasi', 'terima')->count(),
            'tolak' => $jadwalPelayanan->where('status_konfirmasi', 'tolak')->count(),
            'belum' => $jadwalPelayanan->where('status_konfirmasi', 'belum')->count(),
        ];
        
        // Pelayanan per posisi - hanya yang diterima
        $pelayananPerPosisi = $jadwalPelayananTerima->groupBy('posisi')
            ->map(function ($items, $key) {
                return [
                    'posisi' => $key ?: 'Tidak Diketahui',
                    'jumlah' => $items->count()
                ];
            })
            ->sortByDesc('jumlah')
            ->values();
        
        // Pelayan paling aktif - hanya yang diterima
        $pelayanAktif = $jadwalPelayananTerima->groupBy('id_anggota')
            ->map(function ($items, $key) {
                return [
                    'anggota' => $items->first()->anggota->nama ?? 'Tidak Diketahui',
                    'jumlah' => $items->count()
                ];
            })
            ->sortByDesc('jumlah')
            ->take(10)
            ->values();
        
        return view('laporan.pelayanan', compact(
            'jadwalPelayanan', 
            'bulanList', 
            'tahunList', 
            'bulan', 
            'tahun', 
            'period',
            'totalPelayanan', 
            'totalPelayan',
            'statusBreakdown',
            'pelayananPerPosisi',
            'pelayanAktif',
            'kegiatanList',
            'pelaksanaanList',
            'kegiatan_id',
            'pelaksanaan_id'
        ));
    }

    /**
     * Auto-reject expired schedules
     */
    private function autoRejectExpiredSchedules()
    {
        $expiredSchedules = JadwalPelayanan::where('status_konfirmasi', 'belum')
            ->where('tanggal_pelayanan', '<', Carbon::now()->format('Y-m-d'))
            ->get();
        
        foreach ($expiredSchedules as $jadwal) {
            $jadwal->update(['status_konfirmasi' => 'tolak']);
        }
    }

    public function komsel(Request $request)
    {
        $user = Auth::user();
        
        // Only admin, pengurus can access general komsel reports
        if ($user->id_role > 2) {
            return redirect()->route('laporan.index')
                ->with('error', 'Anda tidak memiliki akses untuk melihat laporan ini.');
        }

        $bulan = $request->input('bulan', Carbon::now()->month);
        $tahun = $request->input('tahun', Carbon::now()->year);
        
        // Data untuk filter
        $bulanList = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        
        $tahunList = range(Carbon::now()->year - 5, Carbon::now()->year);
        
        // Query data komsel
        $komsel = Komsel::with('anggota', 'pemimpin')->get();
        
        // Kegiatan komsel pada bulan dan tahun tertentu
        $kegiatanKomsel = PelaksanaanKegiatan::whereHas('kegiatan', function($query) {
            $query->where('tipe_kegiatan', 'komsel');
        })
        ->whereMonth('tanggal_kegiatan', $bulan)
        ->whereYear('tanggal_kegiatan', $tahun)
        ->with('kegiatan')
        ->get();
        
        // Statistik komsel
        $totalKomsel = $komsel->count();
        $totalAnggotaKomsel = DB::table('anggota_komsel')->count();
        $rataRataAnggota = $totalKomsel > 0 ? $totalAnggotaKomsel / $totalKomsel : 0;
        
        // Komsel dengan anggota terbanyak
        $komselTerbanyak = $komsel->sortByDesc(function($k) {
            return $k->anggota->count();
        })->take(5)->map(function($k) {
            return [
                'nama' => $k->nama_komsel,
                'jumlah' => $k->anggota->count()
            ];
        })->values();
        
        // Kehadiran komsel per minggu
        $kehadiranPerMinggu = [];
        $startDate = Carbon::createFromDate($tahun, $bulan, 1);
        $endDate = $startDate->copy()->endOfMonth();
        
        for ($week = 1; $week <= 5; $week++) {
            $weekStart = ($week - 1) * 7 + 1;
            $weekStartDate = Carbon::createFromDate($tahun, $bulan, $weekStart)->startOfDay();
            $weekEndDate = $weekStartDate->copy()->addDays(6)->endOfDay();
            
            if ($weekStartDate->gt($endDate)) {
                break;
            }
            
            $weeklyCount = Kehadiran::whereHas('pelaksanaan.kegiatan', function($query) {
                $query->where('tipe_kegiatan', 'komsel');
            })
            ->whereBetween('waktu_absensi', [$weekStartDate, $weekEndDate])
            ->count();
            
            $kehadiranPerMinggu[] = [
                'minggu' => "Minggu $week",
                'jumlah' => $weeklyCount
            ];
        }
        
        return view('laporan.komsel', compact(
            'komsel', 
            'kegiatanKomsel', 
            'bulanList', 
            'tahunList', 
            'bulan', 
            'tahun', 
            'totalKomsel', 
            'totalAnggotaKomsel', 
            'rataRataAnggota',
            'komselTerbanyak',
            'kehadiranPerMinggu'
        ));
    }

    public function anggota(Request $request)
    {
        $user = Auth::user();
        
        // Only admin and pengurus can access anggota reports
        if ($user->id_role > 2) {
            return redirect()->route('laporan.index')
                ->with('error', 'Anda tidak memiliki akses untuk melihat laporan ini.');
        }

        // Data anggota dan statistik
        $anggota = Anggota::with('user')->get();
        
        // Statistik dasar
        $totalAnggota = $anggota->count();
        $anggotaAktif = Anggota::whereHas('kehadiran', function($query) {
            $query->where('waktu_absensi', '>=', Carbon::now()->subMonths(3));
        })->count();
        $anggotaTidakAktif = $totalAnggota - $anggotaAktif;
        
        // Anggota per gender
        $anggotaPerGender = $anggota->groupBy('jenis_kelamin')
            ->map(function ($items, $key) {
                return [
                    'gender' => $key == 'L' ? 'Laki-laki' : ($key == 'P' ? 'Perempuan' : 'Lainnya'),
                    'jumlah' => $items->count()
                ];
            })
            ->values();
        
        // Anggota per kelompok umur
        $anggotaPerUmur = [
            ['kelompok' => '< 18 tahun', 'jumlah' => 0],
            ['kelompok' => '18-25 tahun', 'jumlah' => 0],
            ['kelompok' => '26-35 tahun', 'jumlah' => 0],
            ['kelompok' => '36-50 tahun', 'jumlah' => 0],
            ['kelompok' => '> 50 tahun', 'jumlah' => 0],
        ];
        
        foreach ($anggota as $a) {
            if (!$a->tanggal_lahir) continue;
            
            $umur = Carbon::parse($a->tanggal_lahir)->age;
            
            if ($umur < 18) {
                $anggotaPerUmur[0]['jumlah']++;
            } elseif ($umur >= 18 && $umur <= 25) {
                $anggotaPerUmur[1]['jumlah']++;
            } elseif ($umur >= 26 && $umur <= 35) {
                $anggotaPerUmur[2]['jumlah']++;
            } elseif ($umur >= 36 && $umur <= 50) {
                $anggotaPerUmur[3]['jumlah']++;
            } else {
                $anggotaPerUmur[4]['jumlah']++;
            }
        }
        
        // Anggota baru per bulan (12 bulan terakhir)
        $anggotaBaruPerBulan = [];
        for ($i = 11; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $count = Anggota::whereMonth('created_at', $month->month)
                ->whereYear('created_at', $month->year)
                ->count();
            
            $anggotaBaruPerBulan[] = [
                'bulan' => $month->format('M Y'),
                'jumlah' => $count
            ];
        }
        
        return view('laporan.anggota', compact(
            'anggota', 
            'totalAnggota', 
            'anggotaAktif', 
            'anggotaTidakAktif', 
            'anggotaPerGender',
            'anggotaPerUmur',
            'anggotaBaruPerBulan'
        ));
    }

    /**
     * Personal Report - moved from KehadiranController
     */
    public function personalReport(Request $request)
    {
        $user = Auth::user();
        
        // Check if user can select other users (Admin, Pengurus)
        $canSelectUser = $user->id_role <= 2;
        $selectedUserId = null;
        $anggota = null;
        
        if ($canSelectUser && $request->has('user_id') && $request->user_id) {
            // Admin/Pengurus can view other user's report
            $selectedUser = User::find($request->user_id);
            if ($selectedUser && $selectedUser->id_anggota) {
                $anggota = Anggota::findOrFail($selectedUser->id_anggota);
                $selectedUserId = $selectedUser->id;
            }
        }
        
        // If no specific user selected or user doesn't have permission, use current user
        if (!$anggota) {
            if (!$user->id_anggota) {
                return redirect()->route('laporan.index')
                    ->with('error', 'Profil anggota tidak ditemukan.');
            }
            $anggota = Anggota::findOrFail($user->id_anggota);
        }
        
        // Get filter parameters
        $period = $request->input('period', 6);
        $kegiatan_id = $request->input('kegiatan_id');
        $pelaksanaan_id = $request->input('pelaksanaan_id');
        $status_filter = $request->input('status_filter'); // NEW: Status filter
        
        $startDate = Carbon::now()->subMonths($period);
        $endDate = Carbon::now();
        
        // Get all kegiatan for filter
        $kegiatanList = Kegiatan::orderBy('nama_kegiatan')->get();
        
        // Get pelaksanaan for selected kegiatan
        $pelaksanaanList = collect();
        if ($kegiatan_id) {
            $pelaksanaanList = PelaksanaanKegiatan::where('id_kegiatan', $kegiatan_id)
                ->whereBetween('tanggal_kegiatan', [$startDate, $endDate])
                ->orderBy('tanggal_kegiatan')
                ->get();
        }
        
        // Get pelaksanaan events for this period
        $pelaksanaanEvents = PelaksanaanKegiatan::with('kegiatan')
            ->whereBetween('tanggal_kegiatan', [$startDate, $endDate]);
        
        if ($kegiatan_id) {
            $pelaksanaanEvents->where('id_kegiatan', $kegiatan_id);
        }
        
        if ($pelaksanaan_id) {
            $pelaksanaanEvents->where('id_pelaksanaan', $pelaksanaan_id);
        }
        
        $events = $pelaksanaanEvents->get();
        
        // Generate personal attendance records with status filter
        $kehadiran = $this->generatePersonalAttendanceRecords($anggota, $events, $status_filter);
        
        // Calculate statistics
        $totalKehadiran = $kehadiran->where('status', 'hadir')->count();
        $kehadiranPerBulan = $kehadiran->groupBy(function($item) {
            return Carbon::parse($item['tanggal'])->format('Y-m');
        })->map->count();
        
        $kehadiranPerKegiatan = $kehadiran->groupBy('nama_kegiatan')->map->count()->sortDesc();
        
        // Get all users for selection (if user has permission)
        $allUsers = $canSelectUser ? User::whereNotNull('id_anggota')->with('anggota')->get() : collect();
        
        return view('laporan.personal-report', compact(
            'anggota',
            'kehadiran', 
            'totalKehadiran',
            'kehadiranPerBulan',
            'kehadiranPerKegiatan',
            'startDate',
            'endDate',
            'period',
            'canSelectUser',
            'allUsers',
            'selectedUserId',
            'kegiatanList',
            'pelaksanaanList',
            'kegiatan_id',
            'pelaksanaan_id'
        ));
    }
    /**
     * Generate personal attendance records for a specific member
     */
    private function generatePersonalAttendanceRecords($anggota, $events, $statusFilter = null)
    {
        $attendanceRecords = collect();
        
        foreach ($events as $pelaksanaan) {
            // Check if this member should attend this event
            $shouldAttend = $this->shouldMemberAttend($anggota, $pelaksanaan);
            
            if (!$shouldAttend) {
                continue; // Skip if member shouldn't attend this event
            }
            
            // Check for actual attendance record
            $actualRecord = Kehadiran::where('id_pelaksanaan', $pelaksanaan->id_pelaksanaan)
                ->where('id_anggota', $anggota->id_anggota)
                ->first();
            
            if ($actualRecord) {
                // Use actual attendance record
                $record = [
                    'tanggal' => $pelaksanaan->tanggal_kegiatan,
                    'nama_kegiatan' => $pelaksanaan->kegiatan->nama_kegiatan,
                    'tipe_kegiatan' => $pelaksanaan->kegiatan->tipe_kegiatan,
                    'waktu_absensi' => $actualRecord->waktu_absensi,
                    'status' => $actualRecord->status,
                    'lokasi' => $pelaksanaan->lokasi,
                    'keterangan' => $actualRecord->keterangan
                ];
            } else {
                // PERBAIKAN: Auto-assign sebagai "tidak_hadir" untuk event yang sudah lewat
                $eventDate = Carbon::parse($pelaksanaan->tanggal_kegiatan);
                if ($eventDate->isPast()) {
                    $record = [
                        'tanggal' => $pelaksanaan->tanggal_kegiatan,
                        'nama_kegiatan' => $pelaksanaan->kegiatan->nama_kegiatan,
                        'tipe_kegiatan' => $pelaksanaan->kegiatan->tipe_kegiatan,
                        'waktu_absensi' => null,
                        'status' => 'tidak_hadir',
                        'lokasi' => $pelaksanaan->lokasi,
                        'keterangan' => 'Auto-generated: Tidak hadir'
                    ];
                } else {
                    // Skip future events without attendance
                    continue;
                }
            }
            
            // PERBAIKAN: Apply status filter yang benar
            if ($statusFilter) {
                if ($statusFilter === 'hadir' && $record['status'] !== 'hadir') {
                    continue;
                }
                if ($statusFilter === 'tidak_hadir' && $record['status'] === 'hadir') {
                    continue;
                }
            }
            
            $attendanceRecords->push($record);
        }
        
        return $attendanceRecords->sortByDesc('tanggal');
    }

    /**
     * Check if a member should attend a specific event
     */
    private function shouldMemberAttend($anggota, $pelaksanaan)
    {
        $tipeKegiatan = $pelaksanaan->kegiatan->tipe_kegiatan;
        
        if ($tipeKegiatan === 'komsel') {
            // For komsel, check if member belongs to this komsel
            $namaKomsel = str_replace('Komsel - ', '', $pelaksanaan->kegiatan->nama_kegiatan);
            return $anggota->komsel->contains('nama_komsel', $namaKomsel);
        } else {
            // For other events (ibadah, etc.), all members should attend
            return true;
        }
    }

    /**
     * Komsel Report - moved from KehadiranController
     * Fixed access control: hanya admin dan ketua komsel
     */
    public function komselReport(Request $request)
    {
        $user = Auth::user();
        
        // Check if user can select other users (Admin, Pengurus only - NOT Petugas Pelayanan)
        $canSelectUser = $user->id_role <= 2;
        $selectedUserId = null;
        $anggota = null;
        $selectedKomselId = null;
        
        if ($canSelectUser && $request->has('user_id') && $request->user_id) {
            // Admin/Pengurus dapat melihat laporan komsel user lain
            $selectedUser = User::find($request->user_id);
            if ($selectedUser && $selectedUser->id_anggota) {
                $anggota = Anggota::findOrFail($selectedUser->id_anggota);
                $selectedUserId = $selectedUser->id;
                
                // Jika ada komsel_id yang spesifik dari admin selection
                if ($request->has('komsel_id') && $request->komsel_id) {
                    $selectedKomselId = $request->komsel_id;
                }
            }
        }
        
        // If no specific user selected, use current user
        if (!$anggota) {
            if (!$user->id_anggota) {
                return redirect()->route('laporan.index')
                    ->with('error', 'Profil anggota tidak ditemukan.');
            }
            $anggota = Anggota::findOrFail($user->id_anggota);
        }
        
        // Check if selected anggota is a komsel leader
        $komselLead = Komsel::where('id_pemimpin', $anggota->id_anggota)->get();
        
        if ($komselLead->isEmpty()) {
            $message = $canSelectUser && $selectedUserId ? 
                'Anggota yang dipilih bukan pemimpin komsel.' : 
                'Anda bukan pemimpin komsel.';
            return redirect()->route('laporan.index')
                ->with('error', $message);
        }
        
        // Tentukan komsel yang akan ditampilkan
        if ($selectedKomselId) {
            // Admin memilih komsel spesifik
            $selectedKomsel = Komsel::findOrFail($selectedKomselId);
            
            // Pastikan komsel ini dipimpin oleh anggota yang dipilih
            if ($selectedKomsel->id_pemimpin != $anggota->id_anggota) {
                return redirect()->route('laporan.komsel-report')
                    ->with('error', 'Komsel yang dipilih tidak dipimpin oleh anggota tersebut.');
            }
        } elseif ($request->input('komsel_id')) {
            // Non-admin memilih dari komsel yang mereka pimpin
            $selectedKomsel = Komsel::findOrFail($request->input('komsel_id'));
        } else {
            // Default ke komsel pertama yang dipimpin
            $selectedKomsel = $komselLead->first();
        }
        
        // Handle period parameter
        $period = $request->input('period', 3); // Default 3 bulan
        if (in_array($period, [1, 3, 6, 12])) {
            $startDate = Carbon::now()->subMonths($period);
            $endDate = Carbon::now();
        } else {
            // Custom date range
            $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : Carbon::now()->subMonths(3);
            $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date')) : Carbon::now();
        }
        
        // Get komsel activities
        $komselActivityName = 'Komsel - ' . $selectedKomsel->nama_komsel;
        $pelaksanaanKomsel = PelaksanaanKegiatan::whereHas('kegiatan', function($query) use ($komselActivityName) {
            $query->where('nama_kegiatan', $komselActivityName);
        })
        ->whereBetween('tanggal_kegiatan', [$startDate, $endDate])
        ->orderBy('tanggal_kegiatan', 'desc')
        ->get();
        
        // Get attendance data
        $kehadiran = Kehadiran::whereIn('id_pelaksanaan', $pelaksanaanKomsel->pluck('id_pelaksanaan'))
            ->with(['anggota', 'pelaksanaan'])
            ->get();
        
        // Get komsel members
        $anggotaKomsel = $selectedKomsel->anggota;
        
        // Calculate attendance statistics
        $attendanceStats = [];
        foreach ($anggotaKomsel as $member) {
            $memberAttendance = $kehadiran->where('id_anggota', $member->id_anggota);
            $attendanceStats[$member->id_anggota] = [
                'anggota' => $member,
                'total_kehadiran' => $memberAttendance->count(),
                'total_kegiatan' => $pelaksanaanKomsel->count(),
                'persentase' => $pelaksanaanKomsel->count() > 0 
                    ? round(($memberAttendance->count() / $pelaksanaanKomsel->count()) * 100, 1)
                    : 0
            ];
        }
        
        // Get komsel leaders for selection (if user has permission)
        $komselLeaders = $canSelectUser ? 
            User::whereHas('anggota', function($query) {
                $query->whereIn('id_anggota', Komsel::pluck('id_pemimpin'));
            })->with('anggota')->get() : collect();
        
        return view('laporan.komsel-report', compact(
            'komselLead',
            'selectedKomsel',
            'pelaksanaanKomsel',
            'kehadiran',
            'attendanceStats',
            'startDate',
            'endDate',
            'period', // Tambahkan ini
            'canSelectUser',
            'komselLeaders',
            'selectedUserId',
            'anggota'
        ));
    }

    /**
     * Personal Service Report for individual members
     */
    public function personalServiceReport(Request $request)
    {
        $user = Auth::user();
        
        // Auto-reject expired schedules when viewing personal service reports
        $this->autoRejectExpiredSchedules();
        
        // Check if user can select other users (Admin, Pengurus, Petugas Pelayanan)
        $canSelectUser = $user->id_role <= 3;
        $selectedUserId = null;
        $anggota = null;
        
        if ($canSelectUser && $request->has('user_id') && $request->user_id) {
            // Admin/Pengurus/Petugas can view other user's service report
            $selectedUser = User::find($request->user_id);
            if ($selectedUser && $selectedUser->id_anggota) {
                $anggota = Anggota::findOrFail($selectedUser->id_anggota);
                $selectedUserId = $selectedUser->id;
            }
        }
        
        // If no specific user selected, use current user
        if (!$anggota) {
            if (!$user->id_anggota) {
                return redirect()->route('laporan.index')
                    ->with('error', 'Profil anggota tidak ditemukan.');
            }
            $anggota = Anggota::findOrFail($user->id_anggota);
        }
        
        // Get filter parameters
        $period = $request->input('period', 6);
        $kegiatan_id = $request->input('kegiatan_id');
        $pelaksanaan_id = $request->input('pelaksanaan_id');
        $status_filter = $request->input('status_filter'); // NEW: Status filter
        
        $startDate = Carbon::now()->subMonths($period);
        $endDate = Carbon::now();
        
        // Get all kegiatan for filter
        $kegiatanList = Kegiatan::orderBy('nama_kegiatan')->get();
        
        // Get pelaksanaan for selected kegiatan
        $pelaksanaanList = collect();
        if ($kegiatan_id) {
            $pelaksanaanList = PelaksanaanKegiatan::where('id_kegiatan', $kegiatan_id)
                ->whereBetween('tanggal_kegiatan', [$startDate, $endDate])
                ->orderBy('tanggal_kegiatan')
                ->get();
        }
        
        // Query jadwal pelayanan with filters
        $jadwalPelayananQuery = JadwalPelayanan::where('id_anggota', $anggota->id_anggota)
            ->whereBetween('tanggal_pelayanan', [$startDate, $endDate])
            ->with(['kegiatan', 'pelaksanaan']);
        
        if ($kegiatan_id) {
            $jadwalPelayananQuery->where('id_kegiatan', $kegiatan_id);
        }
        
        if ($pelaksanaan_id) {
            $jadwalPelayananQuery->where('id_pelaksanaan', $pelaksanaan_id);
        }
        
        // Apply status filter
        if ($status_filter) {
            if ($status_filter === 'terima') {
                $jadwalPelayananQuery->where('status_konfirmasi', 'terima');
            } elseif ($status_filter === 'tolak') {
                $jadwalPelayananQuery->where('status_konfirmasi', 'tolak');
            } elseif ($status_filter === 'belum') {
                $jadwalPelayananQuery->where('status_konfirmasi', 'belum');
            }
        }
        
        $jadwalPelayanan = $jadwalPelayananQuery->orderBy('tanggal_pelayanan', 'desc')->get();
        
        // Calculate statistics - hanya yang status_konfirmasi = 'terima'
        $jadwalPelayananTerima = $jadwalPelayanan->where('status_konfirmasi', 'terima');
        $totalPelayanan = $jadwalPelayananTerima->count();
        
        $pelayananPerBulan = $jadwalPelayananTerima->groupBy(function($item) {
            return Carbon::parse($item->tanggal_pelayanan)->format('Y-m');
        })->map->count();
        
        $pelayananPerPosisi = $jadwalPelayananTerima->groupBy('posisi')->map->count()->sortDesc();
        $pelayananPerKegiatan = $jadwalPelayananTerima->groupBy(function($item) {
            return $item->kegiatan->nama_kegiatan ?? 'Tidak Diketahui';
        })->map->count()->sortDesc();
        
        // Get users with service history for selection (if user has permission)
        $usersWithService = $canSelectUser ? 
            User::whereHas('anggota.jadwalPelayanan')->with('anggota')->get() : collect();
        
        return view('laporan.personal-service-report', compact(
            'anggota',
            'jadwalPelayanan',
            'totalPelayanan',
            'pelayananPerBulan',
            'pelayananPerPosisi',
            'pelayananPerKegiatan',
            'startDate',
            'endDate',
            'period',
            'canSelectUser',
            'usersWithService',
            'selectedUserId',
            'kegiatanList',
            'pelaksanaanList',
            'kegiatan_id',
            'pelaksanaan_id'
        ));
    }

    public function export(Request $request, $jenis, $format = 'pdf')
    {
        // Validasi jenis laporan
        if (!in_array($jenis, ['kehadiran', 'pelayanan', 'komsel', 'anggota', 'personal-report', 'komsel-report', 'personal-service-report'])) {
            return redirect()->back()->with('error', 'Jenis laporan tidak valid.');
        }

        // Validasi format export
        if (!in_array($format, ['pdf', 'excel'])) {
            return redirect()->back()->with('error', 'Format export tidak valid.');
        }

        $user = Auth::user();

        // Mengambil data yang akan di-export berdasarkan jenis laporan
        switch ($jenis) {
            case 'kehadiran':
                $bulan = $request->input('bulan', Carbon::now()->month);
                $tahun = $request->input('tahun', Carbon::now()->year);
                $kegiatan_id = $request->input('kegiatan_id');
                $pelaksanaan_id = $request->input('pelaksanaan_id');
                
                $query = Kehadiran::whereMonth('waktu_absensi', $bulan)
                    ->whereYear('waktu_absensi', $tahun)
                    ->with(['anggota', 'pelaksanaan.kegiatan']);
                
                if ($kegiatan_id) {
                    $query->whereHas('pelaksanaan', function($q) use ($kegiatan_id) {
                        $q->where('id_kegiatan', $kegiatan_id);
                    });
                }
                
                if ($pelaksanaan_id) {
                    $query->where('id_pelaksanaan', $pelaksanaan_id);
                }
                
                $data = $query->get();
                $title = 'Laporan Kehadiran ' . Carbon::createFromDate($tahun, $bulan, 1)->format('F Y');
                
                if ($format == 'pdf') {
                    return $this->exportToPdf('laporan.kehadiran-pdf', $data, $title);
                } else {
                    return ExcelExportService::exportKehadiran($data, $bulan, $tahun);
                }
                break;
                
            case 'pelayanan':
                $bulan = $request->input('bulan', Carbon::now()->month);
                $tahun = $request->input('tahun', Carbon::now()->year);
                $kegiatan_id = $request->input('kegiatan_id');
                $pelaksanaan_id = $request->input('pelaksanaan_id');
                
                $query = JadwalPelayanan::whereMonth('tanggal_pelayanan', $bulan)
                    ->whereYear('tanggal_pelayanan', $tahun)
                    ->with(['anggota', 'kegiatan', 'pelaksanaan']);
                
                if ($kegiatan_id) {
                    $query->where('id_kegiatan', $kegiatan_id);
                }
                
                if ($pelaksanaan_id) {
                    $query->where('id_pelaksanaan', $pelaksanaan_id);
                }
                
                $data = $query->get();
                $title = 'Laporan Pelayanan ' . Carbon::createFromDate($tahun, $bulan, 1)->format('F Y');
                
                if ($format == 'pdf') {
                    return $this->exportToPdf('laporan.pelayanan-pdf', $data, $title);
                } else {
                    return ExcelExportService::exportPelayanan($data, $bulan, $tahun);
                }
                break;
                
            case 'komsel':
                $data = Komsel::with('anggota', 'pemimpin')->get();
                $title = 'Laporan Komsel';
                
                if ($format == 'pdf') {
                    return $this->exportToPdf('laporan.komsel-pdf', $data, $title);
                } else {
                    return ExcelExportService::exportKomsel($data);
                }
                break;
                
            case 'anggota':
                $data = Anggota::with('user')->get();
                $title = 'Laporan Anggota';
                
                if ($format == 'pdf') {
                    return $this->exportToPdf('laporan.anggota-pdf', $data, $title);
                } else {
                    return ExcelExportService::exportAnggota($data);
                }
                break;

            case 'personal-report':
                // Personal Report Export
                $canSelectUser = $user->id_role <= 2;
                $selectedUserId = $request->input('user_id');
                $anggota = null;
                
                if ($canSelectUser && $selectedUserId) {
                    $selectedUser = User::find($selectedUserId);
                    if ($selectedUser && $selectedUser->id_anggota) {
                        $anggota = Anggota::findOrFail($selectedUser->id_anggota);
                    }
                }
                
                if (!$anggota) {
                    if (!$user->id_anggota) {
                        return redirect()->back()->with('error', 'Profil anggota tidak ditemukan.');
                    }
                    $anggota = Anggota::findOrFail($user->id_anggota);
                }
                
                $period = $request->input('period', 6);
                $kegiatan_id = $request->input('kegiatan_id');
                $pelaksanaan_id = $request->input('pelaksanaan_id');
                $startDate = Carbon::now()->subMonths($period);
                $endDate = Carbon::now();
                
                $query = Kehadiran::where('id_anggota', $anggota->id_anggota)
                    ->whereBetween('waktu_absensi', [$startDate, $endDate])
                    ->with(['pelaksanaan.kegiatan']);
                
                if ($kegiatan_id) {
                    $query->whereHas('pelaksanaan', function($q) use ($kegiatan_id) {
                        $q->where('id_kegiatan', $kegiatan_id);
                    });
                }
                
                if ($pelaksanaan_id) {
                    $query->where('id_pelaksanaan', $pelaksanaan_id);
                }
                
                $kehadiran = $query->orderBy('waktu_absensi', 'desc')->get();
                $title = 'Laporan Kehadiran Pribadi - ' . $anggota->nama;
                
                if ($format == 'pdf') {
                    return $this->exportPersonalReportToPdf($kehadiran, $anggota, $startDate, $endDate, $title);
                } else {
                    return ExcelExportService::exportPersonalReport($kehadiran, $anggota, $startDate, $endDate);
                }
                break;

            case 'komsel-report':
                // Komsel Report Export
                $canSelectUser = $user->id_role <= 2;
                $selectedUserId = $request->input('user_id');
                $selectedKomselId = $request->input('komsel_id');
                $anggota = null;
                
                if ($canSelectUser && $selectedUserId) {
                    $selectedUser = User::find($selectedUserId);
                    if ($selectedUser && $selectedUser->id_anggota) {
                        $anggota = Anggota::findOrFail($selectedUser->id_anggota);
                    }
                }
                
                if (!$anggota) {
                    if (!$user->id_anggota) {
                        return redirect()->back()->with('error', 'Profil anggota tidak ditemukan.');
                    }
                    $anggota = Anggota::findOrFail($user->id_anggota);
                }
                
                $komselLead = Komsel::where('id_pemimpin', $anggota->id_anggota)->get();
                if ($komselLead->isEmpty()) {
                    return redirect()->back()->with('error', 'Anggota bukan pemimpin komsel.');
                }
                
                if ($selectedKomselId) {
                    $selectedKomsel = Komsel::findOrFail($selectedKomselId);
                } else {
                    $selectedKomsel = $komselLead->first();
                }
                
                $startDate = $request->input('start_date') ? Carbon::parse($request->input('start_date')) : Carbon::now()->subMonths(3);
                $endDate = $request->input('end_date') ? Carbon::parse($request->input('end_date')) : Carbon::now();
                
                $komselActivityName = 'Komsel - ' . $selectedKomsel->nama_komsel;
                $pelaksanaanKomsel = PelaksanaanKegiatan::whereHas('kegiatan', function($query) use ($komselActivityName) {
                    $query->where('nama_kegiatan', $komselActivityName);
                })
                ->whereBetween('tanggal_kegiatan', [$startDate, $endDate])
                ->orderBy('tanggal_kegiatan', 'desc')
                ->get();
                
                $kehadiran = Kehadiran::whereIn('id_pelaksanaan', $pelaksanaanKomsel->pluck('id_pelaksanaan'))
                    ->with(['anggota', 'pelaksanaan'])
                    ->get();
                
                $anggotaKomsel = $selectedKomsel->anggota;
                $attendanceStats = [];
                foreach ($anggotaKomsel as $member) {
                    $memberAttendance = $kehadiran->where('id_anggota', $member->id_anggota);
                    $attendanceStats[$member->id_anggota] = [
                        'anggota' => $member,
                        'total_kehadiran' => $memberAttendance->count(),
                        'total_kegiatan' => $pelaksanaanKomsel->count(),
                        'persentase' => $pelaksanaanKomsel->count() > 0 
                            ? round(($memberAttendance->count() / $pelaksanaanKomsel->count()) * 100, 1)
                            : 0
                    ];
                }
                
                $title = 'Laporan Komsel - ' . $selectedKomsel->nama_komsel;
                
                if ($format == 'pdf') {
                    return $this->exportKomselReportToPdf($pelaksanaanKomsel, $kehadiran, $attendanceStats, $selectedKomsel, $startDate, $endDate, $title);
                } else {
                    return ExcelExportService::exportKomselReport($pelaksanaanKomsel, $kehadiran, $attendanceStats, $selectedKomsel, $startDate, $endDate);
                }
                break;

            case 'personal-service-report':
                // Personal Service Report Export
                $canSelectUser = $user->id_role <= 3;
                $selectedUserId = $request->input('user_id');
                $anggota = null;
                
                if ($canSelectUser && $selectedUserId) {
                    $selectedUser = User::find($selectedUserId);
                    if ($selectedUser && $selectedUser->id_anggota) {
                        $anggota = Anggota::findOrFail($selectedUser->id_anggota);
                    }
                }
                
                if (!$anggota) {
                    if (!$user->id_anggota) {
                        return redirect()->back()->with('error', 'Profil anggota tidak ditemukan.');
                    }
                    $anggota = Anggota::findOrFail($user->id_anggota);
                }
                
                $period = $request->input('period', 6);
                $kegiatan_id = $request->input('kegiatan_id');
                $pelaksanaan_id = $request->input('pelaksanaan_id');
                $startDate = Carbon::now()->subMonths($period);
                $endDate = Carbon::now();
                
                $query = JadwalPelayanan::where('id_anggota', $anggota->id_anggota)
                    ->whereBetween('tanggal_pelayanan', [$startDate, $endDate])
                    ->with(['kegiatan', 'pelaksanaan']);
                
                if ($kegiatan_id) {
                    $query->where('id_kegiatan', $kegiatan_id);
                }
                
                if ($pelaksanaan_id) {
                    $query->where('id_pelaksanaan', $pelaksanaan_id);
                }
                
                $jadwalPelayanan = $query->orderBy('tanggal_pelayanan', 'desc')->get();
                $title = 'Riwayat Pelayanan - ' . $anggota->nama;
                
                if ($format == 'pdf') {
                    return $this->exportPersonalServiceReportToPdf($jadwalPelayanan, $anggota, $startDate, $endDate, $title);
                } else {
                    return ExcelExportService::exportPersonalServiceReport($jadwalPelayanan, $anggota, $startDate, $endDate);
                }
                break;
        }
        
        return redirect()->back()->with('error', 'Export gagal.');
    }

    private function exportToPdf($view, $data, $title)
    {
        $pdf = PDF::loadView($view, [
            'data' => $data,
            'title' => $title,
            'date' => Carbon::now()->format('d F Y')
        ]);
        
        return $pdf->download($title . '.pdf');
    }

    private function exportPersonalReportToPdf($kehadiran, $anggota, $startDate, $endDate, $title)
    {
        $pdf = PDF::loadView('laporan.personal-report-pdf', [
            'kehadiran' => $kehadiran,
            'anggota' => $anggota,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'title' => $title,
            'date' => Carbon::now()->format('d F Y')
        ]);
        
        $filename = 'laporan-kehadiran-' . strtolower(str_replace(' ', '-', $anggota->nama)) . 
                   '-' . $startDate->format('Y-m-d') . '-to-' . $endDate->format('Y-m-d') . '.pdf';
        
        return $pdf->download($filename);
    }

    private function exportKomselReportToPdf($pelaksanaanKomsel, $kehadiran, $attendanceStats, $selectedKomsel, $startDate, $endDate, $title)
    {
        $pdf = PDF::loadView('laporan.komsel-report-pdf', [
            'pelaksanaanKomsel' => $pelaksanaanKomsel,
            'kehadiran' => $kehadiran,
            'attendanceStats' => $attendanceStats,
            'selectedKomsel' => $selectedKomsel,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'title' => $title,
            'date' => Carbon::now()->format('d F Y')
        ]);
        
        $filename = 'laporan-komsel-' . strtolower(str_replace(' ', '-', $selectedKomsel->nama_komsel)) . 
                   '-' . $startDate->format('Y-m-d') . '-to-' . $endDate->format('Y-m-d') . '.pdf';
        
        return $pdf->download($filename);
    }

    private function exportPersonalServiceReportToPdf($jadwalPelayanan, $anggota, $startDate, $endDate, $title)
    {
        $pdf = PDF::loadView('laporan.personal-service-report-pdf', [
            'jadwalPelayanan' => $jadwalPelayanan,
            'anggota' => $anggota,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'title' => $title,
            'date' => Carbon::now()->format('d F Y')
        ]);
        
        $filename = 'riwayat-pelayanan-' . strtolower(str_replace(' ', '-', $anggota->nama)) . 
                   '-' . $startDate->format('Y-m-d') . '-to-' . $endDate->format('Y-m-d') . '.pdf';
        
        return $pdf->download($filename);
    }
}