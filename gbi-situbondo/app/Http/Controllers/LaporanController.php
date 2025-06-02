<?php
// app/Http/Controllers/LaporanController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Anggota;
use App\Models\Kehadiran;
use App\Models\JadwalPelayanan;
use App\Models\PelaksanaanKegiatan;
use App\Models\Komsel;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\KehadiranExport;
use App\Exports\PelayananExport;
use App\Exports\KomselExport;
use App\Exports\AnggotaExport;

class LaporanController extends Controller
{
    public function index()
    {
        // Halaman utama daftar laporan yang tersedia
        return view('laporan.index');
    }

    public function kehadiran(Request $request)
    {
        $bulan = $request->input('bulan', Carbon::now()->month);
        $tahun = $request->input('tahun', Carbon::now()->year);
        
        // Data untuk filter
        $bulanList = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        
        $tahunList = range(Carbon::now()->year - 5, Carbon::now()->year);
        
        // Query data kehadiran berdasarkan bulan dan tahun
        // FIXED: Menggunakan pelaksanaan.kegiatan instead of kegiatan langsung
        $kehadiran = Kehadiran::whereMonth('waktu_absensi', $bulan)
            ->whereYear('waktu_absensi', $tahun)
            ->with(['anggota', 'pelaksanaan.kegiatan'])
            ->get();
        
        // Menghitung statistik kehadiran
        $totalAnggota = Anggota::count();
        $totalKehadiran = $kehadiran->count();
        
        // Kehadiran per kegiatan - FIXED: Access kegiatan through pelaksanaan
        $kehadiranPerKegiatan = $kehadiran->groupBy(function($item) {
            return $item->pelaksanaan->id_kegiatan ?? 'unknown';
        })
        ->map(function ($items, $key) {
            $firstItem = $items->first();
            return [
                'kegiatan' => $firstItem->pelaksanaan->kegiatan->nama_kegiatan ?? 'Tidak Diketahui',
                'jumlah' => $items->count()
            ];
        })
        ->sortByDesc('jumlah')
        ->values();
        
        // Kehadiran per minggu
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
            
            $weeklyCount = Kehadiran::whereBetween('waktu_absensi', [$weekStartDate, $weekEndDate])->count();
            $kehadiranPerMinggu[] = [
                'minggu' => "Minggu $week",
                'jumlah' => $weeklyCount
            ];
        }
        
        return view('laporan.kehadiran', compact(
            'kehadiran', 
            'bulanList', 
            'tahunList', 
            'bulan', 
            'tahun', 
            'totalAnggota', 
            'totalKehadiran', 
            'kehadiranPerKegiatan',
            'kehadiranPerMinggu'
        ));
    }

    public function pelayanan(Request $request)
    {
        $bulan = $request->input('bulan', Carbon::now()->month);
        $tahun = $request->input('tahun', Carbon::now()->year);
        
        // Data untuk filter
        $bulanList = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
        ];
        
        $tahunList = range(Carbon::now()->year - 5, Carbon::now()->year);
        
        // Query data pelayanan berdasarkan bulan dan tahun
        // FIXED: Remove 'posisi' from with() since it's not a relationship
        $jadwalPelayanan = JadwalPelayanan::whereMonth('tanggal_pelayanan', $bulan)
            ->whereYear('tanggal_pelayanan', $tahun)
            ->with(['anggota', 'kegiatan'])
            ->get();
        
        // Statistik pelayanan
        $totalPelayanan = $jadwalPelayanan->count();
        $totalPelayan = $jadwalPelayanan->groupBy('id_anggota')->count();
        
        // Pelayanan per posisi - FIXED: Use posisi field directly
        $pelayananPerPosisi = $jadwalPelayanan->groupBy('posisi')
            ->map(function ($items, $key) {
                return [
                    'posisi' => $key ?: 'Tidak Diketahui',
                    'jumlah' => $items->count()
                ];
            })
            ->sortByDesc('jumlah')
            ->values();
        
        // Pelayan paling aktif
        $pelayanAktif = $jadwalPelayanan->groupBy('id_anggota')
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
            'totalPelayanan', 
            'totalPelayan', 
            'pelayananPerPosisi',
            'pelayanAktif'
        ));
    }

    public function komsel(Request $request)
    {
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
        // FIXED: Use pelaksanaan.kegiatan instead of direct kegiatan relationship
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

    public function dashboard()
    {
        // Statistik untuk dashboard
        $totalAnggota = Anggota::count();
        $totalKegiatan = PelaksanaanKegiatan::whereMonth('tanggal_kegiatan', Carbon::now()->month)
            ->whereYear('tanggal_kegiatan', Carbon::now()->year)
            ->count();
        $totalKomsel = Komsel::count();
        
        // Kehadiran 4 minggu terakhir
        $kehadiranMingguIni = Kehadiran::whereBetween('waktu_absensi', [
            Carbon::now()->startOfWeek(), 
            Carbon::now()->endOfWeek()
        ])->count();
        
        $kehadiranMingguLalu = Kehadiran::whereBetween('waktu_absensi', [
            Carbon::now()->subWeek()->startOfWeek(), 
            Carbon::now()->subWeek()->endOfWeek()
        ])->count();
        
        $kehadiranPerMinggu = [];
        for ($i = 3; $i >= 0; $i--) {
            $weekStart = Carbon::now()->subWeeks($i)->startOfWeek();
            $weekEnd = Carbon::now()->subWeeks($i)->endOfWeek();
            
            $count = Kehadiran::whereBetween('waktu_absensi', [$weekStart, $weekEnd])->count();
            
            $kehadiranPerMinggu[] = [
                'minggu' => $weekStart->format('d M') . ' - ' . $weekEnd->format('d M'),
                'jumlah' => $count
            ];
        }
        
        // Kegiatan yang akan datang
        $kegiatanMendatang = PelaksanaanKegiatan::where('tanggal_kegiatan', '>=', Carbon::now())
            ->with('kegiatan')
            ->orderBy('tanggal_kegiatan', 'asc')
            ->take(5)
            ->get();
        
        // Anggota yang berulang tahun bulan ini
        $ultahBulanIni = Anggota::whereMonth('tanggal_lahir', Carbon::now()->month)
            ->orderByRaw('DAY(tanggal_lahir)')
            ->get();
        
        return view('laporan.dashboard', compact(
            'totalAnggota', 
            'totalKegiatan', 
            'totalKomsel', 
            'kehadiranMingguIni', 
            'kehadiranMingguLalu',
            'kehadiranPerMinggu',
            'kegiatanMendatang',
            'ultahBulanIni'
        ));
    }

    public function export(Request $request, $jenis, $format = 'pdf')
    {
        // Validasi jenis laporan
        if (!in_array($jenis, ['kehadiran', 'pelayanan', 'komsel', 'anggota'])) {
            return redirect()->back()->with('error', 'Jenis laporan tidak valid.');
        }

        // Validasi format export
        if (!in_array($format, ['pdf', 'excel'])) {
            return redirect()->back()->with('error', 'Format export tidak valid.');
        }

        // Mengambil data yang akan di-export berdasarkan jenis laporan
        switch ($jenis) {
            case 'kehadiran':
                $bulan = $request->input('bulan', Carbon::now()->month);
                $tahun = $request->input('tahun', Carbon::now()->year);
                
                // FIXED: Use pelaksanaan.kegiatan instead of direct kegiatan
                $data = Kehadiran::whereMonth('waktu_absensi', $bulan)
                    ->whereYear('waktu_absensi', $tahun)
                    ->with(['anggota', 'pelaksanaan.kegiatan'])
                    ->get();
                
                $title = 'Laporan Kehadiran ' . Carbon::createFromDate($tahun, $bulan, 1)->format('F Y');
                
                if ($format == 'pdf') {
                    return $this->exportToPdf('laporan.kehadiran-pdf', $data, $title);
                } else {
                    return Excel::download(new KehadiranExport($data), 'laporan-kehadiran-' . $bulan . '-' . $tahun . '.xlsx');
                }
                break;
                
            case 'pelayanan':
                $bulan = $request->input('bulan', Carbon::now()->month);
                $tahun = $request->input('tahun', Carbon::now()->year);
                
                // FIXED: Remove 'posisi' from with()
                $data = JadwalPelayanan::whereMonth('tanggal_pelayanan', $bulan)
                    ->whereYear('tanggal_pelayanan', $tahun)
                    ->with(['anggota', 'kegiatan'])
                    ->get();
                
                $title = 'Laporan Pelayanan ' . Carbon::createFromDate($tahun, $bulan, 1)->format('F Y');
                
                if ($format == 'pdf') {
                    return $this->exportToPdf('laporan.pelayanan-pdf', $data, $title);
                } else {
                    return Excel::download(new PelayananExport($data), 'laporan-pelayanan-' . $bulan . '-' . $tahun . '.xlsx');
                }
                break;
                
            case 'komsel':
                $data = Komsel::with('anggota', 'pemimpin')->get();
                $title = 'Laporan Komsel';
                
                if ($format == 'pdf') {
                    return $this->exportToPdf('laporan.komsel-pdf', $data, $title);
                } else {
                    return Excel::download(new KomselExport($data), 'laporan-komsel.xlsx');
                }
                break;
                
            case 'anggota':
                $data = Anggota::with('user')->get();
                $title = 'Laporan Anggota';
                
                if ($format == 'pdf') {
                    return $this->exportToPdf('laporan.anggota-pdf', $data, $title);
                } else {
                    return Excel::download(new AnggotaExport($data), 'laporan-anggota.xlsx');
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
}