<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Anggota;
use App\Models\Kegiatan;
use App\Models\JadwalPelayanan;
use App\Models\PelaksanaanKegiatan;
use App\Models\KetersediaanPelayan;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class PelayananController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        // Get upcoming pelayanan
        $user = Auth::user();
        
        // For admin and pengurus gereja, show all schedules
        if ($user->id_role <= 2) {
            $jadwalPelayanan = JadwalPelayanan::with(['anggota', 'pelaksanaan.kegiatan'])
                ->whereHas('pelaksanaan', function($q) {
                    $q->where('tanggal_kegiatan', '>=', Carbon::now()->format('Y-m-d'));
                })
                ->orderBy('tanggal_pelayanan')
                ->get()
                ->groupBy('tanggal_pelayanan');
        }
        // For pengurus pelayanan, show their team's schedules
        else if ($user->id_role == 3) {
            $jadwalPelayanan = JadwalPelayanan::with(['anggota', 'pelaksanaan.kegiatan'])
                ->whereHas('pelaksanaan', function($q) {
                    $q->where('tanggal_kegiatan', '>=', Carbon::now()->format('Y-m-d'));
                })
                ->orderBy('tanggal_pelayanan')
                ->get()
                ->groupBy('tanggal_pelayanan');
        }
        // For regular members, show their own schedules
        else {
            if ($user->id_anggota) {
                $jadwalPelayanan = JadwalPelayanan::with(['anggota', 'pelaksanaan.kegiatan'])
                    ->where('id_anggota', $user->id_anggota)
                    ->whereHas('pelaksanaan', function($q) {
                        $q->where('tanggal_kegiatan', '>=', Carbon::now()->format('Y-m-d'));
                    })
                    ->orderBy('tanggal_pelayanan')
                    ->get()
                    ->groupBy('tanggal_pelayanan');
            } else {
                $jadwalPelayanan = collect();
            }
        }
        
        // Get previous pelayanan history
        if ($user->id_role <= 2) {
            $riwayatPelayanan = JadwalPelayanan::with(['anggota', 'pelaksanaan.kegiatan'])
                ->whereHas('pelaksanaan', function($q) {
                    $q->where('tanggal_kegiatan', '<', Carbon::now()->format('Y-m-d'));
                })
                ->orderBy('tanggal_pelayanan', 'desc')
                ->limit(30)
                ->get()
                ->groupBy('tanggal_pelayanan');
        } else if ($user->id_role == 3) {
            $riwayatPelayanan = JadwalPelayanan::with(['anggota', 'pelaksanaan.kegiatan'])
                ->whereHas('pelaksanaan', function($q) {
                    $q->where('tanggal_kegiatan', '<', Carbon::now()->format('Y-m-d'));
                })
                ->orderBy('tanggal_pelayanan', 'desc')
                ->limit(30)
                ->get()
                ->groupBy('tanggal_pelayanan');
        } else {
            if ($user->id_anggota) {
                $riwayatPelayanan = JadwalPelayanan::with(['anggota', 'pelaksanaan.kegiatan'])
                    ->where('id_anggota', $user->id_anggota)
                    ->whereHas('pelaksanaan', function($q) {
                        $q->where('tanggal_kegiatan', '<', Carbon::now()->format('Y-m-d'));
                    })
                    ->orderBy('tanggal_pelayanan', 'desc')
                    ->limit(10)
                    ->get()
                    ->groupBy('tanggal_pelayanan');
            } else {
                $riwayatPelayanan = collect();
            }
        }
        
        // Get upcoming pelaksanaan kegiatan for creating new schedules (admin and pengurus only)
        $pelaksanaanKegiatan = [];
        if ($user->id_role <= 3) {
            $pelaksanaanKegiatan = PelaksanaanKegiatan::with('kegiatan')
                ->whereHas('kegiatan', function($q) {
                    $q->where('tipe_kegiatan', 'ibadah');
                })
                ->where('tanggal_kegiatan', '>=', Carbon::now()->format('Y-m-d'))
                ->orderBy('tanggal_kegiatan')
                ->get();
        }
        
        return view('pelayanan.index', compact('jadwalPelayanan', 'riwayatPelayanan', 'pelaksanaanKegiatan'));
    }
    
    public function create(Request $request)
    {
        // Check if user has permission
        if (Auth::user()->id_role > 3) {
            return redirect()->route('pelayanan.index')
                ->with('error', 'Anda tidak memiliki akses untuk membuat jadwal pelayanan.');
        }
        
        // Get pelaksanaan kegiatan
        if ($request->has('id_pelaksanaan')) {
            $pelaksanaan = PelaksanaanKegiatan::with('kegiatan')
                ->findOrFail($request->id_pelaksanaan);
        } else {
            // Jika tidak ada id_pelaksanaan, ambil pelaksanaan terdekat
            $pelaksanaan = PelaksanaanKegiatan::with('kegiatan')
                ->whereHas('kegiatan', function($q) {
                    $q->where('tipe_kegiatan', 'ibadah');
                })
                ->where('tanggal_kegiatan', '>=', Carbon::now()->format('Y-m-d'))
                ->orderBy('tanggal_kegiatan')
                ->first();
        }
        
        if (!$pelaksanaan) {
            return redirect()->route('pelaksanaan.create')
                ->with('error', 'Silahkan buat jadwal kegiatan ibadah terlebih dahulu.');
        }
        
        // Get anggota for pelayanan with their availability
        $anggota = Anggota::with(['jadwalPelayanan' => function($q) {
                $q->orderBy('tanggal_pelayanan', 'desc');
            }])
            ->orderBy('nama')
            ->get();
        
        // Get existing jadwal for this pelaksanaan
        $existingJadwal = JadwalPelayanan::with('anggota')
            ->where('id_pelaksanaan', $pelaksanaan->id_pelaksanaan)
            ->get();
            
        // Prepare posisi options
        $posisiOptions = [
            'Worship Leader', 'Singer', 'Keyboard', 'Guitar', 'Drum', 
            'Sound System', 'Multimedia', 'Usher', 'Pembawa Persembahan', 'Pemimpin Pujian', 'Dokumentasi'
        ];
        
        // Jika sudah ada jadwal, siapkan data edit
        $jadwalByPosisi = [];
        foreach ($existingJadwal as $jadwal) {
            $jadwalByPosisi[$jadwal->posisi] = $jadwal;
        }
        
        // Get all upcoming pelaksanaan
        $allPelaksanaan = PelaksanaanKegiatan::with('kegiatan')
            ->whereHas('kegiatan', function($q) {
                $q->where('tipe_kegiatan', 'ibadah');
            })
            ->where('tanggal_kegiatan', '>=', Carbon::now()->format('Y-m-d'))
            ->orderBy('tanggal_kegiatan')
            ->get();
            
        return view('pelayanan.create', compact(
            'pelaksanaan', 
            'allPelaksanaan', 
            'anggota', 
            'existingJadwal', 
            'posisiOptions', 
            'jadwalByPosisi'
        ));
    }
    
    public function store(Request $request)
    {
        // Check if user has permission
        if (Auth::user()->id_role > 3) {
            return redirect()->route('pelayanan.index')
                ->with('error', 'Anda tidak memiliki akses untuk membuat jadwal pelayanan.');
        }
        
        $validator = Validator::make($request->all(), [
            'id_pelaksanaan' => 'required|exists:pelaksanaan_kegiatan,id_pelaksanaan',
            'petugas' => 'required|array',
            'petugas.*.posisi' => 'required|string',
            'petugas.*.id_anggota' => 'required|exists:anggota,id_anggota',
            'petugas.*.is_reguler' => 'sometimes|boolean',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        // Get pelaksanaan data for the tanggal
        $pelaksanaan = PelaksanaanKegiatan::findOrFail($request->id_pelaksanaan);
        
        DB::beginTransaction();
        
        try {
            // Delete existing jadwal for this pelaksanaan
            JadwalPelayanan::where('id_pelaksanaan', $request->id_pelaksanaan)
                ->delete();
                
            // Create new jadwal
            foreach ($request->petugas as $petugas) {
                JadwalPelayanan::create([
                    'id_kegiatan' => $pelaksanaan->id_kegiatan,
                    'id_pelaksanaan' => $request->id_pelaksanaan,
                    'tanggal_pelayanan' => $pelaksanaan->tanggal_kegiatan,
                    'id_anggota' => $petugas['id_anggota'],
                    'posisi' => $petugas['posisi'],
                    'status_konfirmasi' => 'belum',
                    'is_reguler' => isset($petugas['is_reguler']) ? $petugas['is_reguler'] : false,
                ]);
            }
            
            DB::commit();
            return redirect()->route('pelayanan.index')
                ->with('success', 'Jadwal pelayanan berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat menyimpan jadwal pelayanan: ' . $e->getMessage())
                ->withInput();
        }
    }
    
    public function konfirmasi($id, $status)
    {
        $jadwal = JadwalPelayanan::findOrFail($id);
        
        // Check if user is authorized
        $user = Auth::user();
        if ($user->id_role > 3 && (!$user->id_anggota || $user->id_anggota != $jadwal->id_anggota)) {
            return redirect()->route('pelayanan.index')
                ->with('error', 'Anda tidak memiliki akses untuk mengkonfirmasi jadwal ini.');
        }
        
        // Check if status is valid
        if (!in_array($status, ['terima', 'tolak'])) {
            return redirect()->route('pelayanan.index')
                ->with('error', 'Status konfirmasi tidak valid.');
        }
        
        $jadwal->status_konfirmasi = $status;
        $jadwal->save();
        
        return redirect()->route('pelayanan.index')
            ->with('success', 'Konfirmasi jadwal pelayanan berhasil disimpan.');
    }
    
    public function destroy($id)
    {
        // Check if user has permission
        if (Auth::user()->id_role > 3) {
            return redirect()->route('pelayanan.index')
                ->with('error', 'Anda tidak memiliki akses untuk menghapus jadwal pelayanan.');
        }
        
        $jadwal = JadwalPelayanan::findOrFail($id);
        $jadwal->delete();
        
        return redirect()->route('pelayanan.index')
            ->with('success', 'Jadwal pelayanan berhasil dihapus.');
    }
    
    public function show()
    {
        // Check if user has permission
        if (Auth::user()->id_role > 2) {
            return redirect()->route('pelayanan.index')
                ->with('error', 'Anda tidak memiliki akses untuk generate jadwal pelayanan.');
        }
        
        // Get upcoming pelaksanaan for generator
        $pelaksanaan = PelaksanaanKegiatan::with('kegiatan')
            ->whereHas('kegiatan', function($q) {
                $q->where('tipe_kegiatan', 'ibadah');
            })
            ->where('tanggal_kegiatan', '>=', Carbon::now()->format('Y-m-d'))
            ->orderBy('tanggal_kegiatan')
            ->get();
            
        if ($pelaksanaan->isEmpty()) {
            return redirect()->route('pelaksanaan.create')
                ->with('error', 'Silahkan buat jadwal kegiatan ibadah terlebih dahulu.');
        }
        
        // Get all anggota with service history
        $anggota = Anggota::whereHas('jadwalPelayanan')->get();
        
        return view('pelayanan.generator', compact('pelaksanaan', 'anggota'));
    }
    
    public function generateSchedule(Request $request)
    {
        // Check if user has permission
        if (Auth::user()->id_role > 2) {
            return redirect()->route('pelayanan.index')
                ->with('error', 'Anda tidak memiliki akses untuk generate jadwal pelayanan.');
        }
        
        $validator = Validator::make($request->all(), [
            'id_pelaksanaan' => 'required|exists:pelaksanaan_kegiatan,id_pelaksanaan',
            'positions' => 'required|array',
            'positions.*' => 'required|string',
            'anggota' => 'required|array',
            'anggota.*' => 'exists:anggota,id_anggota',
            'bobot_reguler' => 'required|numeric|min:1|max:10',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $pelaksanaan = PelaksanaanKegiatan::with('kegiatan')->findOrFail($request->id_pelaksanaan);
        $positions = $request->positions;
        $selectedAnggota = $request->anggota;
        $bobotReguler = $request->bobot_reguler;
        
        // Get selected anggota with their service history
        $anggota = Anggota::with(['jadwalPelayanan' => function($q) use ($positions) {
                $q->whereIn('posisi', $positions)
                  ->orderBy('tanggal_pelayanan', 'desc');
            }])
            ->whereIn('id_anggota', $selectedAnggota)
            ->get();
        
        if ($anggota->isEmpty()) {
            return redirect()->back()
                ->with('error', 'Tidak ada anggota yang dipilih untuk dijadwalkan.')
                ->withInput();
        }
        
        DB::beginTransaction();
        
        try {
            // Delete existing jadwal for this pelaksanaan
            JadwalPelayanan::where('id_pelaksanaan', $pelaksanaan->id_pelaksanaan)
                ->delete();
                
            // Prepare array for positions
            $scheduledPositions = [];
            $scheduledAnggota = [];
            
            // For each position, assign the most suitable person
            foreach ($positions as $position) {
                // Find eligible candidates based on position and availability
                $eligibleCandidates = $this->findEligibleCandidates(
                    $anggota, 
                    $position, 
                    $pelaksanaan, 
                    $scheduledAnggota,
                    $bobotReguler
                );
                
                if ($eligibleCandidates->isEmpty()) {
                    continue; // Skip this position if no eligible candidates
                }
                
                // Select the best candidate
                $selectedCandidate = $eligibleCandidates->first();
                
                // Create jadwal
                $jadwal = JadwalPelayanan::create([
                    'id_kegiatan' => $pelaksanaan->id_kegiatan,
                    'id_pelaksanaan' => $pelaksanaan->id_pelaksanaan,
                    'tanggal_pelayanan' => $pelaksanaan->tanggal_kegiatan,
                    'id_anggota' => $selectedCandidate->id_anggota,
                    'posisi' => $position,
                    'status_konfirmasi' => 'belum',
                    'is_reguler' => $selectedCandidate->is_reguler ?? false,
                ]);
                
                $scheduledPositions[] = $position;
                $scheduledAnggota[] = $selectedCandidate->id_anggota;
            }
            
            DB::commit();
            
            if (count($scheduledPositions) < count($positions)) {
                $skippedPositions = array_diff($positions, $scheduledPositions);
                return redirect()->route('pelayanan.index')
                    ->with('warning', 'Beberapa posisi tidak dapat dijadwalkan karena tidak ada petugas yang tersedia: ' . implode(', ', $skippedPositions))
                    ->with('success', 'Jadwal pelayanan berhasil digenerate untuk ' . count($scheduledPositions) . ' posisi.');
            } else {
                return redirect()->route('pelayanan.index')
                    ->with('success', 'Jadwal pelayanan berhasil digenerate untuk semua posisi.');
            }
        } catch (\Exception $e) {
            DB::rollback();
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat generate jadwal: ' . $e->getMessage())
                ->withInput();
        }
    }
    
    /**
     * Find eligible candidates for a position
     */
    private function findEligibleCandidates($anggota, $position, $pelaksanaan, $scheduledAnggota, $bobotReguler)
    {
        // Extract day of week and event times
        $eventDay = Carbon::parse($pelaksanaan->tanggal_kegiatan)->dayOfWeek;
        $eventStart = $pelaksanaan->jam_mulai;
        $eventEnd = $pelaksanaan->jam_selesai;
        
        // Filter candidates
        $eligibleCandidates = $anggota->filter(function ($anggota) use ($position, $scheduledAnggota, $eventDay, $eventStart, $eventEnd) {
            // Skip if already scheduled for this event
            if (in_array($anggota->id_anggota, $scheduledAnggota)) {
                return false;
            }
            
            // Check if they have served in this position before
            $hasServedInPosition = $anggota->jadwalPelayanan->contains('posisi', $position);
            
            // Check if they're available at this time
            $isAvailable = true;
            if ($anggota->ketersediaan_hari && $anggota->ketersediaan_jam) {
                $isAvailable = in_array($eventDay, $anggota->ketersediaan_hari);
                
                if ($isAvailable) {
                    $availableDuringEvent = false;
                    foreach ($anggota->ketersediaan_jam as $timeSlot) {
                        list($availStart, $availEnd) = explode('-', $timeSlot);
                        if ($eventStart >= $availStart && $eventEnd <= $availEnd) {
                            $availableDuringEvent = true;
                            break;
                        }
                    }
                    $isAvailable = $availableDuringEvent;
                }
            }
            
            return $hasServedInPosition && $isAvailable;
        });
        
        if ($eligibleCandidates->isEmpty()) {
            // If no specific candidates, try anyone who is available
            $eligibleCandidates = $anggota->filter(function ($anggota) use ($scheduledAnggota, $eventDay, $eventStart, $eventEnd) {
                // Skip if already scheduled for this event
                if (in_array($anggota->id_anggota, $scheduledAnggota)) {
                    return false;
                }
                
                // Check if they're available at this time
                $isAvailable = true;
                if ($anggota->ketersediaan_hari && $anggota->ketersediaan_jam) {
                    $isAvailable = in_array($eventDay, $anggota->ketersediaan_hari);
                    
                    if ($isAvailable) {
                        $availableDuringEvent = false;
                        foreach ($anggota->ketersediaan_jam as $timeSlot) {
                            list($availStart, $availEnd) = explode('-', $timeSlot);
                            if ($eventStart >= $availStart && $eventEnd <= $availEnd) {
                                $availableDuringEvent = true;
                                break;
                            }
                        }
                        $isAvailable = $availableDuringEvent;
                    }
                }
                
                return $isAvailable;
            });
        }
        
        // Sort candidates by score (combination of factors)
        return $eligibleCandidates->sortByDesc(function ($anggota) use ($position, $bobotReguler) {
            // Base score
            $score = 0;
            
            // Check when they served last time in this position
            $lastServed = $anggota->jadwalPelayanan
                ->where('posisi', $position)
                ->first();
                
            if ($lastServed) {
                $daysSinceLastServed = Carbon::parse($lastServed->tanggal_pelayanan)
                    ->diffInDays(Carbon::now());
                    
                // Higher score for those who haven't served in a while
                $score += min($daysSinceLastServed / 7, 100); // Max score is 100 (after ~2 years)
            } else {
                // If never served in this position, give a moderate score
                $score += 50;
            }
            
            // Count how many times they've served in this position
            $serveCount = $anggota->jadwalPelayanan
                ->where('posisi', $position)
                ->count();
                
            // Favor those who have served less often (but at least once)
            if ($serveCount > 0) {
                $score += max(20 - $serveCount, 0); // Max bonus of 20, decreasing with more services
            }
            
            // Check if they are regular players for this position
            $isReguler = $anggota->jadwalPelayanan
                ->where('posisi', $position)
                ->where('is_reguler', true)
                ->count() > 0;
                
            // Give bonus to regular players
            if ($isReguler) {
                $score += $bobotReguler * 10; // Bonus based on reguler weight setting
            }
            
            return $score;
        });
    }
    
    /**
     * Save member availability
     */
    public function saveAvailability(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_anggota' => 'required|exists:anggota,id_anggota',
            'ketersediaan_hari' => 'required|array',
            'ketersediaan_hari.*' => 'required|integer|min:0|max:6',
            'ketersediaan_jam' => 'required|array',
            'ketersediaan_jam.*' => 'required|string|regex:/^([0-1][0-9]|2[0-3]):[0-5][0-9]-([0-1][0-9]|2[0-3]):[0-5][0-9]$/',
            'posisi_reguler' => 'sometimes|array',
            'posisi_reguler.*' => 'string',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $user = Auth::user();
        $anggota = Anggota::findOrFail($request->id_anggota);
        
        // Ensure the user is authorized to update this anggota
        if ($user->id_role > 3 && $user->id_anggota != $anggota->id_anggota) {
            return redirect()->back()
                ->with('error', 'Anda tidak memiliki akses untuk mengubah ketersediaan anggota lain.');
        }
        
        // Update availability
        $anggota->ketersediaan_hari = $request->ketersediaan_hari;
        $anggota->ketersediaan_jam = $request->ketersediaan_jam;
        $anggota->save();
        
        // Update regular positions if provided
        if ($request->has('posisi_reguler')) {
            // Clear existing regular positions
            DB::table('jadwal_pelayanan')
                ->where('id_anggota', $anggota->id_anggota)
                ->update(['is_reguler' => false]);
                
            // Set new regular positions
            foreach ($request->posisi_reguler as $posisi) {
                DB::table('jadwal_pelayanan')
                    ->where('id_anggota', $anggota->id_anggota)
                    ->where('posisi', $posisi)
                    ->update(['is_reguler' => true]);
            }
        }
        
        return redirect()->back()
            ->with('success', 'Ketersediaan anggota berhasil disimpan.');
    }
    
    /**
     * Show member availability form
     */
    public function editAvailability($id = null)
    {
        $user = Auth::user();
        
        // If no ID provided, use the logged-in user's anggota
        if (!$id && $user->id_anggota) {
            $id = $user->id_anggota;
        }
        
        // Ensure the user is authorized to view this anggota
        if ($user->id_role > 3 && $user->id_anggota != $id) {
            return redirect()->route('dashboard')
                ->with('error', 'Anda tidak memiliki akses untuk melihat ketersediaan anggota lain.');
        }
        
        $anggota = Anggota::findOrFail($id);
        
        // Get positions this member has served in
        $positions = JadwalPelayanan::where('id_anggota', $id)
            ->distinct('posisi')
            ->pluck('posisi')
            ->toArray();
            
        // Get positions where they are marked as regular
        $regularPositions = JadwalPelayanan::where('id_anggota', $id)
            ->where('is_reguler', true)
            ->pluck('posisi')
            ->toArray();
        
        return view('pelayanan.availability', compact('anggota', 'positions', 'regularPositions'));
    }
}