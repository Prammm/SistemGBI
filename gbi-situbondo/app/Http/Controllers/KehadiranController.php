<?php

namespace App\Http\Controllers;

use App\Models\Kehadiran;
use App\Models\Anggota;
use App\Models\PelaksanaanKegiatan;
use App\Models\Kegiatan;
use App\Models\Komsel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class KehadiranController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:view_kehadiran')->only(['index', 'show', 'laporan']);
        $this->middleware('permission:create_kehadiran')->only(['create', 'store', 'scan', 'processQR']);
        $this->middleware('permission:edit_kehadiran')->only(['edit', 'update']);
    }

    public function index()
    {
        $user = Auth::user();
        $kegiatan = Kegiatan::all();
        
        // Get upcoming events based on user role
        if ($user->id_role == 1) { // Admin only (role 2 pengurus dihapus)
            $pelaksanaan = PelaksanaanKegiatan::with('kegiatan')
                ->where('tanggal_kegiatan', '>=', Carbon::now()->subDays(7)->format('Y-m-d'))
                ->orderBy('tanggal_kegiatan')
                ->limit(10)
                ->get();
        } elseif ($user->id_role == 3) {
            // PETUGAS PELAYANAN (id_role = 3) - see only relevant events (no other komsel)
            $anggota = $user->anggota;
            if ($anggota) {
                // Get user's komsel names
                $komselNames = $anggota->komsel->pluck('nama_komsel')->toArray();
                $komselActivityPatterns = array_map(function($name) {
                    return 'Komsel - ' . $name;
                }, $komselNames);
                
                $pelaksanaan = PelaksanaanKegiatan::with('kegiatan')
                    ->where('tanggal_kegiatan', '>=', Carbon::now()->subDays(7)->format('Y-m-d'))
                    ->where(function($query) use ($komselActivityPatterns) {
                        // Include user's komsel activities
                        if (!empty($komselActivityPatterns)) {
                            $query->whereHas('kegiatan', function($subquery) use ($komselActivityPatterns) {
                                $subquery->where('tipe_kegiatan', 'komsel')
                                    ->whereIn('nama_kegiatan', $komselActivityPatterns);
                            });
                        }
                        
                        // Include non-komsel activities (church-wide events)
                        $query->orWhereHas('kegiatan', function($subquery) {
                            $subquery->where('tipe_kegiatan', '!=', 'komsel');
                        });
                    })
                    ->orderBy('tanggal_kegiatan')
                    ->limit(10)
                    ->get();
            } else {
                // Petugas without anggota profile - only general events
                $pelaksanaan = PelaksanaanKegiatan::with('kegiatan')
                    ->whereHas('kegiatan', function($query) {
                        $query->where('tipe_kegiatan', '!=', 'komsel');
                    })
                    ->where('tanggal_kegiatan', '>=', Carbon::now()->subDays(7)->format('Y-m-d'))
                    ->orderBy('tanggal_kegiatan')
                    ->limit(10)
                    ->get();
            }
        } else {
            // ANGGOTA JEMAAT (id_role = 4) - see only relevant events (no other komsel)
            $anggota = $user->anggota;
            if ($anggota) {
                // Get user's komsel names
                $komselNames = $anggota->komsel->pluck('nama_komsel')->toArray();
                $komselActivityPatterns = array_map(function($name) {
                    return 'Komsel - ' . $name;
                }, $komselNames);
                
                $pelaksanaan = PelaksanaanKegiatan::with('kegiatan')
                    ->where('tanggal_kegiatan', '>=', Carbon::now()->subDays(1)->format('Y-m-d'))
                    ->where('tanggal_kegiatan', '<=', Carbon::now()->addDays(7)->format('Y-m-d'))
                    ->where(function($query) use ($komselActivityPatterns) {
                        // Include user's komsel activities
                        if (!empty($komselActivityPatterns)) {
                            $query->whereHas('kegiatan', function($subquery) use ($komselActivityPatterns) {
                                $subquery->where('tipe_kegiatan', 'komsel')
                                    ->whereIn('nama_kegiatan', $komselActivityPatterns);
                            });
                        }
                        
                        // Include non-komsel activities (church-wide events)
                        $query->orWhereHas('kegiatan', function($subquery) {
                            $subquery->where('tipe_kegiatan', '!=', 'komsel');
                        });
                    })
                    ->orderBy('tanggal_kegiatan')
                    ->limit(10)
                    ->get();
            } else {
                // User without anggota profile - only general events
                $pelaksanaan = PelaksanaanKegiatan::with('kegiatan')
                    ->whereHas('kegiatan', function($query) {
                        $query->where('tipe_kegiatan', '!=', 'komsel');
                    })
                    ->where('tanggal_kegiatan', '>=', Carbon::now()->subDays(1)->format('Y-m-d'))
                    ->where('tanggal_kegiatan', '<=', Carbon::now()->addDays(7)->format('Y-m-d'))
                    ->orderBy('tanggal_kegiatan')
                    ->limit(10)
                    ->get();
            }
        }
            
        return view('kehadiran.index', compact('kegiatan', 'pelaksanaan'));
    }

    public function create(Request $request)
    {
        $user = Auth::user();
        
        // Check if user has permission for manual input
        if ($user->id_role > 3 && !$request->has('after_scan')) {
            return redirect()->route('kehadiran.index')
                ->with('error', 'Anda tidak memiliki akses untuk input manual. Silakan gunakan QR Scanner.');
        }
        
        $pelaksanaan = null;
        
        if ($request->has('id_pelaksanaan')) {
            $pelaksanaan = PelaksanaanKegiatan::with('kegiatan')
                ->findOrFail($request->id_pelaksanaan);
        } else {
            $pelaksanaan = PelaksanaanKegiatan::with('kegiatan')
                ->where('tanggal_kegiatan', '>=', Carbon::now()->subDays(1)->format('Y-m-d'))
                ->where('tanggal_kegiatan', '<=', Carbon::now()->addDays(1)->format('Y-m-d'))
                ->orderBy('tanggal_kegiatan')
                ->first();
        }
        
        if (!$pelaksanaan) {
            return redirect()->route('kehadiran.index')
                ->with('error', 'Tidak ada kegiatan yang dapat dicatat kehadirannya saat ini.');
        }
        
        // PERBAIKAN: Filter anggota berdasarkan tipe kegiatan
        $anggota = collect();
        
        if ($user->id_role <= 3) { // Admin (1) atau Petugas Pelayanan (3)
            // Admin/Petugas can see all members or filtered by event type
            if ($pelaksanaan->kegiatan->tipe_kegiatan === 'komsel') {
                // For komsel, only show komsel members
                $komselName = str_replace('Komsel - ', '', $pelaksanaan->kegiatan->nama_kegiatan);
                $komsel = Komsel::where('nama_komsel', $komselName)->first();
                
                if ($komsel) {
                    $anggota = $komsel->anggota()->orderBy('nama')->get();
                }
            } else {
                // For non-komsel events, show all members
                $anggota = Anggota::orderBy('nama')->get();
            }
        } else {
            // Regular users after scan
            if ($request->has('after_scan') && $user->id_anggota) {
                $currentAnggota = Anggota::find($user->id_anggota);
                
                if ($pelaksanaan->kegiatan->tipe_kegiatan === 'komsel') {
                    // For komsel, check if user is member of this komsel
                    $komselName = str_replace('Komsel - ', '', $pelaksanaan->kegiatan->nama_kegiatan);
                    $isKomselMember = $currentAnggota->komsel->contains('nama_komsel', $komselName);
                    
                    if (!$isKomselMember) {
                        return redirect()->route('kehadiran.index')
                            ->with('error', 'Anda bukan anggota komsel ini.');
                    }
                    
                    // Only show family members for komsel events
                    if ($currentAnggota && $currentAnggota->id_keluarga) {
                        $anggota = Anggota::where('id_keluarga', $currentAnggota->id_keluarga)
                            ->where('id_anggota', '!=', $user->id_anggota)
                            ->orderBy('nama')
                            ->get();
                    }
                } else {
                    // For general events, show family members
                    if ($currentAnggota && $currentAnggota->id_keluarga) {
                        $anggota = Anggota::where('id_keluarga', $currentAnggota->id_keluarga)
                            ->where('id_anggota', '!=', $user->id_anggota)
                            ->orderBy('nama')
                            ->get();
                    }
                }
            } else {
                return redirect()->route('kehadiran.scan')
                    ->with('info', 'Silakan scan QR code terlebih dahulu untuk melakukan presensi.');
            }
        }
        
        // Get attendance that already recorded
        $kehadiran = Kehadiran::where('id_pelaksanaan', $pelaksanaan->id_pelaksanaan)
            ->pluck('id_anggota')
            ->toArray();
        
        $afterScan = $request->has('after_scan');
        
        return view('kehadiran.create', compact('pelaksanaan', 'anggota', 'kehadiran', 'afterScan'));
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        
        // Enhanced validation based on user role
        $rules = [
            'id_pelaksanaan' => 'required|exists:pelaksanaan_kegiatan,id_pelaksanaan',
            'anggota' => 'nullable|array',
            'anggota.*' => 'exists:anggota,id_anggota',
        ];
        
        // Additional validation for regular users
        if ($user->id_role > 3) {
            $rules['after_scan'] = 'required|boolean';
            
            // Validate that regular users can only add family members
            if ($request->filled('anggota') && $user->id_anggota) {
                $currentAnggota = Anggota::find($user->id_anggota);
                if ($currentAnggota && $currentAnggota->id_keluarga) {
                    $allowedAnggota = Anggota::where('id_keluarga', $currentAnggota->id_keluarga)
                        ->where('id_anggota', '!=', $user->id_anggota)
                        ->pluck('id_anggota')
                        ->toArray();
                    
                    foreach ($request->anggota as $anggotaId) {
                        if (!in_array($anggotaId, $allowedAnggota)) {
                            return redirect()->back()
                                ->with('error', 'Anda hanya dapat menambahkan anggota keluarga sendiri.')
                                ->withInput();
                        }
                    }
                }
            }
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        DB::beginTransaction();

        try {
            $anggotaIds = $request->input('anggota', []);
            $pelaksanaan = PelaksanaanKegiatan::findOrFail($request->id_pelaksanaan);
            
            // For regular users after scan, don't delete existing attendance
            if ($user->id_role <= 3) { // Admin (1) atau Petugas Pelayanan (3)
                // Admin/Petugas can reset all attendance
                Kehadiran::where('id_pelaksanaan', $request->id_pelaksanaan)->delete();
            }
            
            $successCount = 0;
            
            // Insert new attendance
            if (!empty($anggotaIds)) {
                foreach ($anggotaIds as $id_anggota) {
                    // Check if attendance already exists
                    $existingKehadiran = Kehadiran::where('id_pelaksanaan', $request->id_pelaksanaan)
                        ->where('id_anggota', $id_anggota)
                        ->first();
                    
                    if (!$existingKehadiran) {
                        Kehadiran::create([
                            'id_anggota' => $id_anggota,
                            'id_pelaksanaan' => $request->id_pelaksanaan,
                            'waktu_absensi' => Carbon::now(),
                            'status' => 'hadir',
                        ]);
                        $successCount++;
                    }
                }
            }
            
            DB::commit();
            
            if ($request->has('after_scan')) {
                $message = $successCount > 0 
                    ? "Berhasil menambahkan kehadiran {$successCount} anggota keluarga."
                    : "Presensi keluarga berhasil disimpan.";
                
                return redirect()->route('dashboard')
                    ->with('success', $message);
            } else {
                $message = $successCount > 0 
                    ? "Data kehadiran berhasil disimpan untuk {$successCount} anggota yang hadir."
                    : "Data kehadiran berhasil disimpan.";
                
                return redirect()->route('kehadiran.index')->with('success', $message);
            }
            
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat menyimpan data kehadiran: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function show($id)
    {
        $pelaksanaan = PelaksanaanKegiatan::with(['kegiatan', 'kehadiran.anggota'])
            ->findOrFail($id);
            
        return view('kehadiran.show', compact('pelaksanaan'));
    }

    public function scan($id = null)
    {
        $pelaksanaan = null;
        
        if ($id) {
            $pelaksanaan = PelaksanaanKegiatan::with('kegiatan')->findOrFail($id);
        } else {
            $pelaksanaan = PelaksanaanKegiatan::with('kegiatan')
                ->where('tanggal_kegiatan', Carbon::now()->format('Y-m-d'))
                ->orderBy('jam_mulai')
                ->first();
        }
        
        if (!$pelaksanaan) {
            return redirect()->route('kehadiran.index')
                ->with('error', 'Tidak ada kegiatan yang dapat di-scan saat ini.');
        }
        
        // URL untuk QR code
        $qrUrl = route('kehadiran.scan-process', $pelaksanaan->id_pelaksanaan);
            
        return view('kehadiran.scan', compact('pelaksanaan', 'qrUrl'));
    }
    
    public function processQR(Request $request, $id)
    {
        $user = Auth::user();
        $pelaksanaan = PelaksanaanKegiatan::findOrFail($id);
        
        // Only logged in users with anggota record can scan
        if (!$user->id_anggota) {
            if ($user->id_role <= 3) { // Admin (1) atau Petugas Pelayanan (3)
                // Admin/Petugas redirect to manual input
                return redirect()->route('kehadiran.create', ['id_pelaksanaan' => $pelaksanaan->id_pelaksanaan]);
            } else {
                return redirect()->route('dashboard')
                    ->with('error', 'Profil anggota Anda belum lengkap. Silakan hubungi admin.');
            }
        }
        
        $anggota = Anggota::findOrFail($user->id_anggota);
        
        // Check if already attended
        $exists = Kehadiran::where('id_anggota', $anggota->id_anggota)
            ->where('id_pelaksanaan', $pelaksanaan->id_pelaksanaan)
            ->exists();
            
        if ($exists) {
            return redirect()->route('dashboard')
                ->with('info', 'Anda sudah melakukan presensi pada kegiatan ini.');
        }
        
        DB::beginTransaction();
        
        try {
            // Record attendance for the scanning user
            Kehadiran::create([
                'id_anggota' => $anggota->id_anggota,
                'id_pelaksanaan' => $pelaksanaan->id_pelaksanaan,
                'waktu_absensi' => Carbon::now(),
                'status' => 'hadir',
            ]);
            
            DB::commit();
            
            // Check if user has family members
            $familyMembers = [];
            if ($anggota->id_keluarga) {
                $familyMembers = Anggota::where('id_keluarga', $anggota->id_keluarga)
                    ->where('id_anggota', '!=', $anggota->id_anggota)
                    ->get();
            }
            
            if ($familyMembers->count() > 0) {
                // Redirect to family attendance page
                return redirect()->route('kehadiran.family-attendance', [
                    'id_pelaksanaan' => $pelaksanaan->id_pelaksanaan
                ])->with('success', 'Presensi Anda berhasil tercatat! Apakah ada anggota keluarga yang ingin diabsen?');
            } else {
                return redirect()->route('dashboard')
                    ->with('success', 'Presensi berhasil tercatat. Terima kasih!');
            }
            
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->route('dashboard')
                ->with('error', 'Terjadi kesalahan saat mencatat presensi: ' . $e->getMessage());
        }
    }
    
    public function familyAttendance($id_pelaksanaan)
    {
        $user = Auth::user();
        
        if (!$user->id_anggota) {
            return redirect()->route('dashboard')
                ->with('error', 'Profil anggota tidak ditemukan.');
        }
        
        $anggota = Anggota::findOrFail($user->id_anggota);
        $pelaksanaan = PelaksanaanKegiatan::with('kegiatan')->findOrFail($id_pelaksanaan);
        
        // Check if user already attended
        $userAttended = Kehadiran::where('id_anggota', $anggota->id_anggota)
            ->where('id_pelaksanaan', $id_pelaksanaan)
            ->exists();
            
        if (!$userAttended) {
            return redirect()->route('kehadiran.scan', $id_pelaksanaan)
                ->with('error', 'Anda harus melakukan presensi pribadi terlebih dahulu.');
        }
        
        // Get family members
        $familyMembers = collect();
        if ($anggota->id_keluarga) {
            $familyMembers = Anggota::where('id_keluarga', $anggota->id_keluarga)
                ->where('id_anggota', '!=', $anggota->id_anggota)
                ->orderBy('nama')
                ->get();
        }
        
        // Get already attended family members
        $attendedFamily = Kehadiran::where('id_pelaksanaan', $id_pelaksanaan)
            ->whereIn('id_anggota', $familyMembers->pluck('id_anggota'))
            ->pluck('id_anggota')
            ->toArray();
        
        return view('kehadiran.family-attendance', compact(
            'pelaksanaan', 
            'familyMembers', 
            'attendedFamily',
            'anggota'
        ));
    }
    
    public function storeFamilyAttendance(Request $request)
    {
        $user = Auth::user();
        
        if (!$user->id_anggota) {
            return redirect()->route('dashboard')
                ->with('error', 'Profil anggota tidak ditemukan.');
        }
        
        $validator = Validator::make($request->all(), [
            'id_pelaksanaan' => 'required|exists:pelaksanaan_kegiatan,id_pelaksanaan',
            'family_members' => 'nullable|array',
            'family_members.*' => 'exists:anggota,id_anggota',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $anggota = Anggota::findOrFail($user->id_anggota);
        $familyMemberIds = $request->input('family_members', []);
        
        // Validate that all selected members are actually family
        if (!empty($familyMemberIds) && $anggota->id_keluarga) {
            $validFamilyIds = Anggota::where('id_keluarga', $anggota->id_keluarga)
                ->where('id_anggota', '!=', $anggota->id_anggota)
                ->pluck('id_anggota')
                ->toArray();
                
            foreach ($familyMemberIds as $memberId) {
                if (!in_array($memberId, $validFamilyIds)) {
                    return redirect()->back()
                        ->with('error', 'Anggota yang dipilih bukan bagian dari keluarga Anda.')
                        ->withInput();
                }
            }
        }
        
        DB::beginTransaction();
        
        try {
            $successCount = 0;
            
            foreach ($familyMemberIds as $memberId) {
                // Check if already attended
                $exists = Kehadiran::where('id_anggota', $memberId)
                    ->where('id_pelaksanaan', $request->id_pelaksanaan)
                    ->exists();
                
                if (!$exists) {
                    Kehadiran::create([
                        'id_anggota' => $memberId,
                        'id_pelaksanaan' => $request->id_pelaksanaan,
                        'waktu_absensi' => Carbon::now(),
                        'status' => 'hadir',
                    ]);
                    $successCount++;
                }
            }
            
            DB::commit();
            
            $message = $successCount > 0 
                ? "Berhasil mencatat kehadiran {$successCount} anggota keluarga."
                : "Presensi keluarga berhasil disimpan.";
            
            return redirect()->route('dashboard')
                ->with('success', $message);
            
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat menyimpan presensi keluarga: ' . $e->getMessage())
                ->withInput();
        }
    }
    
    public function personalReport()
    {
        $user = Auth::user();
        
        if (!$user->id_anggota) {
            return redirect()->route('dashboard')
                ->with('error', 'Profil anggota tidak ditemukan.');
        }
        
        $anggota = Anggota::findOrFail($user->id_anggota);
        
        // Get attendance data for the last 6 months
        $startDate = Carbon::now()->subMonths(6);
        $endDate = Carbon::now();
        
        $kehadiran = Kehadiran::where('id_anggota', $anggota->id_anggota)
            ->whereBetween('waktu_absensi', [$startDate, $endDate])
            ->with(['pelaksanaan.kegiatan'])
            ->orderBy('waktu_absensi', 'desc')
            ->get();
        
        // Calculate statistics
        $totalKehadiran = $kehadiran->count();
        $kehadiranPerBulan = $kehadiran->groupBy(function($item) {
            return Carbon::parse($item->waktu_absensi)->format('Y-m');
        })->map->count();
        
        $kehadiranPerKegiatan = $kehadiran->groupBy(function($item) {
            return $item->pelaksanaan->kegiatan->nama_kegiatan ?? 'Tidak Diketahui';
        })->map->count()->sortDesc();
        
        return view('kehadiran.personal-report', compact(
            'anggota',
            'kehadiran', 
            'totalKehadiran',
            'kehadiranPerBulan',
            'kehadiranPerKegiatan',
            'startDate',
            'endDate'
        ));
    }
    
    public function komselReport()
    {
        $user = Auth::user();
        
        if (!$user->id_anggota) {
            return redirect()->route('dashboard')
                ->with('error', 'Profil anggota tidak ditemukan.');
        }
        
        $anggota = Anggota::findOrFail($user->id_anggota);
        
        // Check if user is a komsel leader
        $komselLead = Komsel::where('id_pemimpin', $anggota->id_anggota)->get();
        
        if ($komselLead->isEmpty()) {
            return redirect()->route('dashboard')
                ->with('error', 'Anda bukan pemimpin komsel.');
        }
        
        $selectedKomsel = request('komsel_id') ? Komsel::findOrFail(request('komsel_id')) : $komselLead->first();
        $startDate = request('start_date') ? Carbon::parse(request('start_date')) : Carbon::now()->subMonths(3);
        $endDate = request('end_date') ? Carbon::parse(request('end_date')) : Carbon::now();
        
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
        
        return view('kehadiran.komsel-report', compact(
            'komselLead',
            'selectedKomsel',
            'pelaksanaanKomsel',
            'kehadiran',
            'attendanceStats',
            'startDate',
            'endDate'
        ));
    }

    // ... (rest of the existing methods remain the same)
    
    public function laporan()
    {
        $kegiatan = Kegiatan::all();
        return view('kehadiran.laporan', compact('kegiatan'));
    }
    
    public function generateLaporan(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_kegiatan' => 'required|exists:kegiatan,id_kegiatan',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $kegiatan = Kegiatan::findOrFail($request->id_kegiatan);
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        
        // Get all pelaksanaan within date range
        $pelaksanaan = PelaksanaanKegiatan::where('id_kegiatan', $request->id_kegiatan)
            ->whereBetween('tanggal_kegiatan', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')])
            ->orderBy('tanggal_kegiatan')
            ->get();
            
        if ($pelaksanaan->isEmpty()) {
            return redirect()->back()
                ->with('error', 'Tidak ada data pelaksanaan kegiatan dalam rentang tanggal yang dipilih.')
                ->withInput();
        }
        
        // Get all anggota
        $anggota = Anggota::orderBy('nama')->get();
        
        // Get all kehadiran for these pelaksanaan
        $kehadiran = Kehadiran::whereIn('id_pelaksanaan', $pelaksanaan->pluck('id_pelaksanaan'))
            ->get()
            ->groupBy('id_pelaksanaan')
            ->map(function($item) {
                return $item->pluck('id_anggota')->toArray();
            });
            
        return view('kehadiran.laporan-hasil', compact('kegiatan', 'pelaksanaan', 'anggota', 'kehadiran', 'startDate', 'endDate'));
    }
}