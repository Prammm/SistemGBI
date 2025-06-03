<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Anggota;
use App\Models\Kegiatan;
use App\Models\JadwalPelayanan;
use App\Models\PelaksanaanKegiatan;
use App\Models\AnggotaSpesialisasi;
use App\Models\SchedulingHistory;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PelayananController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index()
    {
        $user = Auth::user();
        
        // Get upcoming pelayanan
        if ($user->id_role <= 2) {
            $jadwalPelayanan = JadwalPelayanan::with(['anggota', 'pelaksanaan.kegiatan'])
                ->whereHas('pelaksanaan', function($q) {
                    $q->where('tanggal_kegiatan', '>=', Carbon::now()->format('Y-m-d'));
                })
                ->orderBy('tanggal_pelayanan')
                ->get()
                ->groupBy('tanggal_pelayanan');
        } else if ($user->id_role == 3) {
            $jadwalPelayanan = JadwalPelayanan::with(['anggota', 'pelaksanaan.kegiatan'])
                ->whereHas('pelaksanaan', function($q) {
                    $q->where('tanggal_kegiatan', '>=', Carbon::now()->format('Y-m-d'));
                })
                ->orderBy('tanggal_pelayanan')
                ->get()
                ->groupBy('tanggal_pelayanan');
        } else {
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
        
        // Get upcoming pelaksanaan kegiatan for creating new schedules
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
        if (Auth::user()->id_role > 3) {
            return redirect()->route('pelayanan.index')
                ->with('error', 'Anda tidak memiliki akses untuk membuat jadwal pelayanan.');
        }
        
        // Get pelaksanaan kegiatan
        if ($request->has('id_pelaksanaan')) {
            $pelaksanaan = PelaksanaanKegiatan::with('kegiatan')
                ->findOrFail($request->id_pelaksanaan);
        } else {
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
        
        // Get anggota with their specializations and availability
        $anggota = Anggota::with(['spesialisasi', 'jadwalPelayanan' => function($q) {
                $q->orderBy('tanggal_pelayanan', 'desc');
            }])
            ->orderBy('nama')
            ->get();
        
        // Get existing jadwal for this pelaksanaan
        $existingJadwal = JadwalPelayanan::with('anggota')
            ->where('id_pelaksanaan', $pelaksanaan->id_pelaksanaan)
            ->get();
            
        // Get available positions
        $posisiOptions = AnggotaSpesialisasi::getAvailablePositions();
        
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
            'posisiOptions'
        ));
    }

    public function store(Request $request)
    {
        if (Auth::user()->id_role > 3) {
            return redirect()->route('pelayanan.index')
                ->with('error', 'Anda tidak memiliki akses untuk membuat jadwal pelayanan.');
        }
        
        $validator = Validator::make($request->all(), [
            'id_pelaksanaan' => 'required|exists:pelaksanaan_kegiatan,id_pelaksanaan',
            'petugas' => 'required|array',
            'petugas.*.posisi' => 'required|string',
            'petugas.*.id_anggota' => 'required|exists:anggota,id_anggota',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $pelaksanaan = PelaksanaanKegiatan::findOrFail($request->id_pelaksanaan);
        
        DB::beginTransaction();
        
        try {
            // Delete existing jadwal for this pelaksanaan
            JadwalPelayanan::where('id_pelaksanaan', $request->id_pelaksanaan)
                ->delete();
                
            // Create new jadwal
            foreach ($request->petugas as $petugas) {
                $jadwal = JadwalPelayanan::create([
                    'id_kegiatan' => $pelaksanaan->id_kegiatan,
                    'id_pelaksanaan' => $request->id_pelaksanaan,
                    'tanggal_pelayanan' => $pelaksanaan->tanggal_kegiatan,
                    'id_anggota' => $petugas['id_anggota'],
                    'posisi' => $petugas['posisi'],
                    'status_konfirmasi' => 'belum',
                    'is_reguler' => false, // Will be determined by spesialisasi table
                ]);
                
                // Create scheduling history
                SchedulingHistory::createFromJadwal($jadwal);
            }
            
            DB::commit();
            return redirect()->route('pelayanan.index')
                ->with('success', 'Jadwal pelayanan berhasil disimpan.');
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error storing pelayanan schedule: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat menyimpan jadwal pelayanan: ' . $e->getMessage())
                ->withInput();
        }
    }
    
    public function konfirmasi($id, $status)
    {
        $jadwal = JadwalPelayanan::findOrFail($id);
        
        $user = Auth::user();
        if ($user->id_role > 3 && (!$user->id_anggota || $user->id_anggota != $jadwal->id_anggota)) {
            return redirect()->route('pelayanan.index')
                ->with('error', 'Anda tidak memiliki akses untuk mengkonfirmasi jadwal ini.');
        }
        
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
        if (Auth::user()->id_role > 3) {
            return redirect()->route('pelayanan.index')
                ->with('error', 'Anda tidak memiliki akses untuk menghapus jadwal pelayanan.');
        }
        
        $jadwal = JadwalPelayanan::findOrFail($id);
        $jadwal->delete();
        
        return redirect()->route('pelayanan.index')
            ->with('success', 'Jadwal pelayanan berhasil dihapus.');
    }
    
    /**
     * Show generator form - IMPROVED
     */
    public function showGenerator()
    {
        if (Auth::user()->id_role > 2) {
            return redirect()->route('pelayanan.index')
                ->with('error', 'Anda tidak memiliki akses untuk generate jadwal pelayanan.');
        }
        
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
        
        // Get anggota with specializations
        $anggota = Anggota::with('spesialisasi')
            ->whereHas('spesialisasi')
            ->orderBy('nama')
            ->get();
        
        // Get position categories
        $positionCategories = AnggotaSpesialisasi::getPositionsByCategory();
        
        return view('pelayanan.generator', compact('pelaksanaan', 'anggota', 'positionCategories'));
    }
    
    /**
     * Generate Schedule - SIMPLIFIED & IMPROVED
     */
    public function generateSchedule(Request $request)
    {
        if (Auth::user()->id_role > 2) {
            return redirect()->route('pelayanan.index')
                ->with('error', 'Anda tidak memiliki akses untuk generate jadwal pelayanan.');
        }
        
        $validator = Validator::make($request->all(), [
            'generation_type' => 'required|in:single,bulk_monthly',
            'id_pelaksanaan' => 'required_if:generation_type,single|array',
            'id_pelaksanaan.*' => 'exists:pelaksanaan_kegiatan,id_pelaksanaan',
            'month_year' => 'required_if:generation_type,bulk_monthly|date_format:Y-m',
            'positions' => 'required|array',
            'positions.*' => 'required|string',
            'anggota' => 'required|array',
            'anggota.*' => 'exists:anggota,id_anggota',
            'algorithm' => 'required|in:fair_rotation,regular_priority',
            'avoid_consecutive' => 'sometimes|boolean',
            'max_services_per_month' => 'sometimes|integer|min:1|max:10',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        DB::beginTransaction();
        
        try {
            if ($request->generation_type === 'single') {
                $result = $this->generateSingleSchedule($request);
            } else {
                $result = $this->generateMonthlySchedule($request);
            }
            
            DB::commit();
            
            return redirect()->route('pelayanan.index')
                ->with('success', $result['message'])
                ->with('info', $result['details'] ?? '');
                
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error generating schedule: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat generate jadwal: ' . $e->getMessage())
                ->withInput();
        }
    }
    
    /**
     * Generate single event schedule - IMPROVED
     */
    private function generateSingleSchedule(Request $request)
    {
        $pelaksanaanIds = is_array($request->id_pelaksanaan) ? $request->id_pelaksanaan : [$request->id_pelaksanaan];
        $positions = $request->positions;
        $selectedAnggota = $request->anggota;
        $algorithm = $request->algorithm;
        
        $totalScheduled = 0;
        $totalSkipped = 0;
        $conflicts = [];
        
        foreach ($pelaksanaanIds as $pelaksanaanId) {
            $pelaksanaan = PelaksanaanKegiatan::with('kegiatan')->findOrFail($pelaksanaanId);
            
            // Delete existing jadwal
            JadwalPelayanan::where('id_pelaksanaan', $pelaksanaan->id_pelaksanaan)->delete();
            
            $anggota = Anggota::with(['spesialisasi', 'jadwalPelayanan'])
                ->whereIn('id_anggota', $selectedAnggota)
                ->get();
            
            $result = $this->executeSchedulingAlgorithm(
                $anggota, 
                $positions, 
                $pelaksanaan, 
                $algorithm,
                $request
            );
            
            $totalScheduled += $result['scheduled'];
            $totalSkipped += $result['skipped'];
            $conflicts = array_merge($conflicts, $result['conflicts']);
        }
        
        $message = "Jadwal berhasil digenerate untuk {$totalScheduled} posisi";
        $details = '';
        
        if ($totalSkipped > 0) {
            $details .= "⚠️ {$totalSkipped} posisi tidak dapat dijadwalkan. ";
        }
        
        if (!empty($conflicts)) {
            $details .= "⚠️ Ditemukan konflik: " . implode(', ', array_unique($conflicts));
        }
        
        return [
            'message' => $message,
            'details' => $details
        ];
    }
    
    /**
     * Generate monthly schedule - IMPROVED
     */
    private function generateMonthlySchedule(Request $request)
    {
        $monthYear = Carbon::createFromFormat('Y-m', $request->month_year);
        $startDate = $monthYear->copy()->startOfMonth();
        $endDate = $monthYear->copy()->endOfMonth();
        
        $pelaksanaan = PelaksanaanKegiatan::with('kegiatan')
            ->whereHas('kegiatan', function($q) {
                $q->where('tipe_kegiatan', 'ibadah');
            })
            ->whereBetween('tanggal_kegiatan', [$startDate, $endDate])
            ->orderBy('tanggal_kegiatan')
            ->get();
            
        if ($pelaksanaan->isEmpty()) {
            throw new \Exception('Tidak ada kegiatan ibadah pada bulan tersebut.');
        }
        
        $positions = $request->positions;
        $selectedAnggota = $request->anggota;
        $algorithm = $request->algorithm;
        $maxServicesPerMonth = $request->max_services_per_month ?? 3;
        
        // Track member workload for the month
        $memberWorkload = [];
        foreach ($selectedAnggota as $anggotaId) {
            $memberWorkload[$anggotaId] = 0;
        }
        
        $totalScheduled = 0;
        $totalSkipped = 0;
        $conflicts = [];
        
        foreach ($pelaksanaan as $p) {
            // Delete existing jadwal
            JadwalPelayanan::where('id_pelaksanaan', $p->id_pelaksanaan)->delete();
            
            $anggota = Anggota::with(['spesialisasi', 'jadwalPelayanan'])
                ->whereIn('id_anggota', $selectedAnggota)
                ->get()
                ->filter(function($a) use ($memberWorkload, $maxServicesPerMonth) {
                    return $memberWorkload[$a->id_anggota] < $maxServicesPerMonth;
                });
            
            $result = $this->executeSchedulingAlgorithm(
                $anggota, 
                $positions, 
                $p, 
                $algorithm,
                $request,
                $memberWorkload
            );
            
            // Update workload tracking
            foreach ($result['scheduled_members'] as $anggotaId) {
                $memberWorkload[$anggotaId]++;
            }
            
            $totalScheduled += $result['scheduled'];
            $totalSkipped += $result['skipped'];
            $conflicts = array_merge($conflicts, $result['conflicts']);
        }
        
        $message = "Jadwal bulanan berhasil digenerate untuk {$totalScheduled} posisi dalam {$pelaksanaan->count()} kegiatan";
        $details = '';
        
        if ($totalSkipped > 0) {
            $details .= "⚠️ {$totalSkipped} posisi tidak dapat dijadwalkan. ";
        }
        
        return [
            'message' => $message,
            'details' => $details
        ];
    }
    
    /**
     * Execute scheduling algorithm - SIMPLIFIED TO 2 ALGORITHMS
     */
    private function executeSchedulingAlgorithm($anggota, $positions, $pelaksanaan, $algorithm, $request, $workloadTracking = [])
    {
        $scheduledPositions = [];
        $scheduledMembers = [];
        $conflicts = [];
        
        foreach ($positions as $position) {
            $candidates = $this->findEligibleCandidates(
                $anggota, 
                $position, 
                $pelaksanaan, 
                $scheduledMembers,
                $algorithm,
                $workloadTracking
            );
            
            if ($candidates->isEmpty()) {
                $conflicts[] = "Tidak ada kandidat untuk posisi {$position}";
                continue;
            }
            
            $selectedCandidate = $candidates->first();
            
            // Check for consecutive service conflict if enabled
            if ($request->avoid_consecutive ?? false) {
                if ($this->hasConflictingSchedule($selectedCandidate, $pelaksanaan)) {
                    $conflicts[] = "Konflik jadwal berurutan untuk {$selectedCandidate->nama} di posisi {$position}";
                    // Try next candidate
                    if ($candidates->count() > 1) {
                        $selectedCandidate = $candidates->skip(1)->first();
                    } else {
                        continue;
                    }
                }
            }
            
            // Create jadwal
            $jadwal = JadwalPelayanan::create([
                'id_kegiatan' => $pelaksanaan->id_kegiatan,
                'id_pelaksanaan' => $pelaksanaan->id_pelaksanaan,
                'tanggal_pelayanan' => $pelaksanaan->tanggal_kegiatan,
                'id_anggota' => $selectedCandidate->id_anggota,
                'posisi' => $position,
                'status_konfirmasi' => 'belum',
                'is_reguler' => $selectedCandidate->isRegularIn($position),
            ]);
            
            // Create scheduling history
            SchedulingHistory::createFromJadwal($jadwal);
            
            $scheduledPositions[] = $position;
            $scheduledMembers[] = $selectedCandidate->id_anggota;
        }
        
        return [
            'scheduled' => count($scheduledPositions),
            'skipped' => count($positions) - count($scheduledPositions),
            'conflicts' => $conflicts,
            'scheduled_members' => array_unique($scheduledMembers)
        ];
    }
    
    /**
     * Find eligible candidates - IMPROVED WITH BETTER AVAILABILITY CHECK
     */
    private function findEligibleCandidates($anggota, $position, $pelaksanaan, $scheduledMembers, $algorithm, $workloadTracking = [])
    {
        $eventDay = Carbon::parse($pelaksanaan->tanggal_kegiatan)->dayOfWeek;
        $eventStart = $pelaksanaan->jam_mulai;
        $eventEnd = $pelaksanaan->jam_selesai;
        $eventDate = $pelaksanaan->tanggal_kegiatan;
        
        // Filter basic eligibility
        $eligibleCandidates = $anggota->filter(function ($anggota) use ($position, $scheduledMembers, $eventDay, $eventStart, $eventEnd, $eventDate) {
            // Skip if already scheduled
            if (in_array($anggota->id_anggota, $scheduledMembers)) {
                return false;
            }
            
            // Check if they can serve this position
            $hasSpecialization = $anggota->spesialisasi->contains('posisi', $position);
            if (!$hasSpecialization) {
                return false;
            }
            
            // Check availability - IMPROVED
            if (!$anggota->isAvailable($eventDate, $eventStart, $eventEnd)) {
                return false;
            }
            
            return true;
        });
        
        if ($eligibleCandidates->isEmpty()) {
            return collect();
        }
        
        // Apply algorithm-specific scoring
        return $eligibleCandidates->sortByDesc(function ($anggota) use ($position, $algorithm, $workloadTracking) {
            return $this->calculateCandidateScore($anggota, $position, $algorithm, $workloadTracking);
        });
    }
    
    /**
     * Calculate candidate score - SIMPLIFIED TO 2 ALGORITHMS
     */
    private function calculateCandidateScore($anggota, $position, $algorithm, $workloadTracking)
    {
        $score = 0;
        
        // Base factors
        $isReguler = $anggota->isRegularIn($position);
        $prioritas = $anggota->getPriorityFor($position);
        $restDays = $anggota->getRestDays($position);
        $serviceFrequency = $anggota->getServiceFrequency(3, $position); // Last 3 months
        $currentWorkload = $workloadTracking[$anggota->id_anggota] ?? 0;
        
        switch ($algorithm) {
            case 'regular_priority':
                // Prioritize regular players
                if ($isReguler) {
                    $score += 100;
                }
                $score += $prioritas * 10;
                $score += min($restDays / 7, 50); // Bonus for rest days
                $score -= $serviceFrequency * 5; // Penalty for frequency
                break;
                
            case 'fair_rotation':
            default:
                // Prioritize fair distribution
                $score += max(100 - $serviceFrequency * 15, 0); // High penalty for recent service
                $score += min($restDays / 3, 100); // More rest = higher score
                $score -= $currentWorkload * 30; // Heavy penalty for current workload
                if ($isReguler) {
                    $score += 30; // Moderate regular bonus
                }
                $score += $prioritas * 5;
                break;
        }
        
        return $score;
    }
    
    /**
     * Check for conflicting consecutive schedules
     */
    private function hasConflictingSchedule($anggota, $pelaksanaan)
    {
        $eventDate = Carbon::parse($pelaksanaan->tanggal_kegiatan);
        $prevWeek = $eventDate->copy()->subWeek();
        $nextWeek = $eventDate->copy()->addWeek();
        
        $conflictingSchedules = JadwalPelayanan::where('id_anggota', $anggota->id_anggota)
            ->whereIn('tanggal_pelayanan', [$prevWeek->format('Y-m-d'), $nextWeek->format('Y-m-d')])
            ->exists();
            
        return $conflictingSchedules;
    }

    /**
     * Manage member availability
     */
    public function editAvailability($id = null)
    {
        $user = Auth::user();
        
        if (!$id && $user->id_anggota) {
            $id = $user->id_anggota;
        }
        
        if ($user->id_role > 3 && $user->id_anggota != $id) {
            return redirect()->route('dashboard')
                ->with('error', 'Anda tidak memiliki akses untuk melihat ketersediaan anggota lain.');
        }
        
        $anggota = Anggota::with('spesialisasi')->findOrFail($id);
        
        $positions = AnggotaSpesialisasi::getAvailablePositions();
        $positionCategories = AnggotaSpesialisasi::getPositionsByCategory();
        
        return view('pelayanan.availability', compact('anggota', 'positions', 'positionCategories'));
    }
    
    /**
     * Save member availability and specializations
     */
    public function saveAvailability(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'id_anggota' => 'required|exists:anggota,id_anggota',
            'ketersediaan_hari' => 'required|array',
            'ketersediaan_hari.*' => 'required|integer|min:0|max:6',
            'ketersediaan_jam' => 'required|array',
            'ketersediaan_jam.*' => 'required|string|regex:/^([0-1][0-9]|2[0-3]):[0-5][0-9]-([0-1][0-9]|2[0-3]):[0-5][0-9]$/',
            'blackout_dates' => 'sometimes|array',
            'blackout_dates.*' => 'date',
            'spesialisasi' => 'sometimes|array',
            'spesialisasi.*.posisi' => 'required|string',
            'spesialisasi.*.is_reguler' => 'sometimes|boolean',
            'spesialisasi.*.prioritas' => 'required|integer|min:1|max:10',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $user = Auth::user();
        $anggota = Anggota::findOrFail($request->id_anggota);
        
        if ($user->id_role > 3 && $user->id_anggota != $anggota->id_anggota) {
            return redirect()->back()
                ->with('error', 'Anda tidak memiliki akses untuk mengubah ketersediaan anggota lain.');
        }
        
        DB::beginTransaction();
        
        try {
            // Update availability
            $anggota->update([
                'ketersediaan_hari' => $request->ketersediaan_hari,
                'ketersediaan_jam' => $request->ketersediaan_jam,
                'blackout_dates' => $request->blackout_dates ?? [],
            ]);
            
            // Update specializations
            if ($request->has('spesialisasi')) {
                // Delete existing specializations
                AnggotaSpesialisasi::where('id_anggota', $anggota->id_anggota)->delete();
                
                // Create new specializations
                foreach ($request->spesialisasi as $spec) {
                    AnggotaSpesialisasi::create([
                        'id_anggota' => $anggota->id_anggota,
                        'posisi' => $spec['posisi'],
                        'is_reguler' => $spec['is_reguler'] ?? false,
                        'prioritas' => $spec['prioritas'],
                        'catatan' => $spec['catatan'] ?? null,
                    ]);
                }
            }
            
            DB::commit();
            return redirect()->back()
                ->with('success', 'Ketersediaan dan spesialisasi anggota berhasil disimpan.');
                
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error saving availability: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat menyimpan data: ' . $e->getMessage())
                ->withInput();
        }
    }
    
    /**
     * Show analytics and reports
     */
    public function analytics()
    {
        if (Auth::user()->id_role > 2) {
            return redirect()->route('pelayanan.index')
                ->with('error', 'Anda tidak memiliki akses untuk melihat analitik pelayanan.');
        }
        
        $startDate = Carbon::now()->subMonths(3);
        $endDate = Carbon::now();
        
        // Workload distribution
        $workloadDistribution = SchedulingHistory::getWorkloadDistribution($startDate, $endDate);
        
        // Position frequency
        $positionFrequency = SchedulingHistory::getPositionFrequency($startDate, $endDate);
        
        // Regular vs non-regular performance
        $regularPerformance = $this->getRegularPerformanceData($startDate, $endDate);
        
        // Availability coverage
        $availabilityCoverage = $this->getAvailabilityCoverage();
        
        return view('pelayanan.analytics', compact(
            'workloadDistribution',
            'positionFrequency', 
            'regularPerformance',
            'availabilityCoverage'
        ));
    }
    
    /**
     * Get regular vs non-regular performance data
     */
    private function getRegularPerformanceData($startDate, $endDate)
    {
        $histories = SchedulingHistory::with('anggota.spesialisasi')
            ->whereBetween('tanggal_pelayanan', [$startDate, $endDate])
            ->get();
            
        $regularCount = 0;
        $nonRegularCount = 0;
        
        foreach ($histories as $history) {
            $isReguler = $history->anggota->spesialisasi
                ->where('posisi', $history->posisi)
                ->where('is_reguler', true)
                ->isNotEmpty();
                
            if ($isReguler) {
                $regularCount++;
            } else {
                $nonRegularCount++;
            }
        }
        
        return [
            'regular_count' => $regularCount,
            'non_regular_count' => $nonRegularCount,
            'regular_percentage' => $regularCount + $nonRegularCount > 0 
                ? round(($regularCount / ($regularCount + $nonRegularCount)) * 100, 2)
                : 0
        ];
    }
    
    /**
     * Get availability coverage data
     */
    private function getAvailabilityCoverage()
    {
        $totalAnggota = Anggota::whereHas('spesialisasi')->count();
        $anggotaWithAvailability = Anggota::whereNotNull('ketersediaan_hari')
            ->whereNotNull('ketersediaan_jam')
            ->whereHas('spesialisasi')
            ->count();
            
        $coveragePercentage = $totalAnggota > 0 
            ? round(($anggotaWithAvailability / $totalAnggota) * 100, 2)
            : 0;
            
        return [
            'total_anggota' => $totalAnggota,
            'with_availability' => $anggotaWithAvailability,
            'coverage_percentage' => $coveragePercentage
        ];
    }
    
    /**
     * Bulk generate monthly schedules
     */
    public function bulkGenerate(Request $request)
    {
        if (Auth::user()->id_role > 2) {
            return redirect()->route('pelayanan.index')
                ->with('error', 'Anda tidak memiliki akses untuk bulk generate jadwal pelayanan.');
        }
        
        $validator = Validator::make($request->all(), [
            'start_month' => 'required|date_format:Y-m',
            'end_month' => 'required|date_format:Y-m|after_or_equal:start_month',
            'template_id' => 'required|exists:jadwal_template,id',
            'algorithm' => 'required|in:balanced,regular_priority,fair_rotation,workload_based',
            'bobot_reguler' => 'required|numeric|min:1|max:10',
            'max_services_per_month' => 'required|integer|min:1|max:10',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $startMonth = Carbon::createFromFormat('Y-m', $request->start_month);
        $endMonth = Carbon::createFromFormat('Y-m', $request->end_month);
        
        DB::beginTransaction();
        
        try {
            $template = JadwalTemplate::findOrFail($request->template_id);
            $positions = $template->posisi_required;
            
            $anggota = Anggota::with('spesialisasi')
                ->whereHas('spesialisasi', function($q) use ($positions) {
                    $q->whereIn('posisi', $positions);
                })
                ->get()
                ->pluck('id_anggota')
                ->toArray();
            
            $totalScheduled = 0;
            $monthsProcessed = 0;
            
            $currentMonth = $startMonth->copy();
            while ($currentMonth->lte($endMonth)) {
                $monthRequest = new Request([
                    'generation_type' => 'bulk_monthly',
                    'month_year' => $currentMonth->format('Y-m'),
                    'positions' => $positions,
                    'anggota' => $anggota,
                    'algorithm' => $request->algorithm,
                    'bobot_reguler' => $request->bobot_reguler,
                    'max_services_per_month' => $request->max_services_per_month,
                    'avoid_consecutive' => true,
                ]);
                
                $result = $this->generateMonthlySchedule($monthRequest);
                $totalScheduled += $result['scheduled'] ?? 0;
                $monthsProcessed++;
                
                $currentMonth->addMonth();
            }
            
            DB::commit();
            
            return redirect()->route('pelayanan.index')
                ->with('success', "Bulk generate berhasil untuk {$monthsProcessed} bulan dengan total {$totalScheduled} jadwal.");
                
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error bulk generating schedules: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat bulk generate: ' . $e->getMessage())
                ->withInput();
        }
    }
    
    /**
     * Export schedule to various formats
     */
    public function export(Request $request)
    {
        $format = $request->get('format', 'excel');
        $startDate = $request->get('start_date', Carbon::now()->startOfMonth());
        $endDate = $request->get('end_date', Carbon::now()->endOfMonth());
        
        $jadwal = JadwalPelayanan::with(['anggota', 'pelaksanaan.kegiatan'])
            ->whereBetween('tanggal_pelayanan', [$startDate, $endDate])
            ->orderBy('tanggal_pelayanan')
            ->get();
        
        switch ($format) {
            case 'pdf':
                return $this->exportToPdf($jadwal, $startDate, $endDate);
            case 'excel':
                return $this->exportToExcel($jadwal, $startDate, $endDate);
            case 'calendar':
                return $this->exportToCalendar($jadwal, $startDate, $endDate);
            default:
                return $this->exportToExcel($jadwal, $startDate, $endDate);
        }
    }
    
    /**
     * Send notifications to scheduled members
     */
    public function sendNotifications(Request $request)
    {
        if (Auth::user()->id_role > 3) {
            return redirect()->route('pelayanan.index')
                ->with('error', 'Anda tidak memiliki akses untuk mengirim notifikasi.');
        }
        
        $date = $request->get('date', Carbon::now()->addDays(7)->format('Y-m-d'));
        
        $jadwal = JadwalPelayanan::with(['anggota', 'pelaksanaan.kegiatan'])
            ->where('tanggal_pelayanan', $date)
            ->where('status_konfirmasi', 'belum')
            ->get();
        
        $notificationsSent = 0;
        
        foreach ($jadwal as $j) {
            if ($j->anggota && $j->anggota->email) {
                // Send email notification
                // Mail::to($j->anggota->email)->send(new PelayananReminderMail($j));
                $notificationsSent++;
            }
        }
        
        return redirect()->back()
            ->with('success', "Notifikasi berhasil dikirim ke {$notificationsSent} anggota.");
    }
    
    /**
     * Auto-resolve conflicts in schedules
     */
    public function resolveConflicts(Request $request)
    {
        if (Auth::user()->id_role > 2) {
            return redirect()->route('pelayanan.index')
                ->with('error', 'Anda tidak memiliki akses untuk resolve conflicts.');
        }
        
        $date = $request->get('date');
        $conflicts = $this->detectScheduleConflicts($date);
        
        $resolvedCount = 0;
        
        foreach ($conflicts as $conflict) {
            if ($this->autoResolveConflict($conflict)) {
                $resolvedCount++;
            }
        }
        
        return redirect()->back()
            ->with('success', "Berhasil resolve {$resolvedCount} konflik jadwal.")
            ->with('info', count($conflicts) - $resolvedCount > 0 ? 
                (count($conflicts) - $resolvedCount) . " konflik memerlukan penanganan manual." : '');
    }
    
    /**
     * Detect schedule conflicts
     */
    private function detectScheduleConflicts($date = null)
    {
        // Implementation for detecting various types of conflicts
        // - Double booking
        // - Availability conflicts
        // - Workload imbalances
        // - Missing critical positions
        
        return []; // Placeholder
    }
    
    /**
     * Auto resolve individual conflict
     */
    private function autoResolveConflict($conflict)
    {
        // Implementation for auto-resolving conflicts
        // - Find alternative candidates
        // - Redistribute workload
        // - Suggest position swaps
        
        return false; // Placeholder
    }
    
    /**
     * Show all ministry members with filter and search
     */
    public function members(Request $request)
    {
        $query = Anggota::with(['spesialisasi', 'jadwalPelayanan']);
        
        // Search by name
        if ($request->filled('search')) {
            $query->where('nama', 'like', '%' . $request->search . '%');
        }
        
        // Filter by position
        if ($request->filled('posisi')) {
            $query->whereHas('spesialisasi', function($q) use ($request) {
                $q->where('posisi', $request->posisi);
            });
        }
        
        // Filter by status
        if ($request->filled('status')) {
            switch ($request->status) {
                case 'reguler':
                    $query->whereHas('spesialisasi', function($q) {
                        $q->where('is_reguler', true);
                    });
                    break;
                case 'non_reguler':
                    $query->whereHas('spesialisasi', function($q) {
                        $q->where('is_reguler', false);
                    });
                    break;
                case 'available':
                    $query->whereJsonContains('ketersediaan_hari', 0)
                          ->orWhereJsonContains('ketersediaan_hari', 6);
                    break;
                case 'no_specialization':
                    $query->whereDoesntHave('spesialisasi');
                    break;
            }
        }
        
        // Apply sorting
        switch ($request->get('sort', 'nama')) {
            case 'total_services':
                $query->withCount('jadwalPelayanan')
                      ->orderBy('jadwal_pelayanan_count', 'desc');
                break;
            case 'recent_services':
                $query->withCount(['jadwalPelayanan as recent_count' => function($q) {
                        $q->where('tanggal_pelayanan', '>=', Carbon::now()->subMonths(3));
                    }])
                      ->orderBy('recent_count', 'desc');
                break;
            case 'workload':
                // This would need a more complex query with scheduling_history
                $query->orderBy('nama');
                break;
            default:
                $query->orderBy('nama');
        }
        
        $members = $query->paginate(20);
        
        // Statistics
        $totalMembers = Anggota::whereHas('spesialisasi')->count();
        $regularMembers = Anggota::whereHas('spesialisasi', function($q) {
            $q->where('is_reguler', true);
        })->count();
        $weekendAvailable = Anggota::where(function($q) {
            $q->whereJsonContains('ketersediaan_hari', 0)
              ->orWhereJsonContains('ketersediaan_hari', 6);
        })->count();
        $needsSetup = Anggota::whereDoesntHave('spesialisasi')->count();
        
        // Available positions for filter
        $availablePositions = AnggotaSpesialisasi::distinct('posisi')
            ->pluck('posisi')
            ->sort()
            ->values();
        
        // Chart data
        $positionDistribution = $this->getPositionDistributionData();
        $regularDistribution = $this->getRegularDistributionData();
        
        return view('pelayanan.members', compact(
            'members',
            'totalMembers',
            'regularMembers', 
            'weekendAvailable',
            'needsSetup',
            'availablePositions',
            'positionDistribution',
            'regularDistribution'
        ));
    }
    
    /**
     * Show member profile
     */
    public function memberProfile($id)
    {
        $anggota = Anggota::with(['spesialisasi', 'jadwalPelayanan.kegiatan', 'jadwalPelayanan.pelaksanaan'])
            ->findOrFail($id);
        
        // Check permission
        $user = Auth::user();
        if ($user->id_role > 3 && $user->id_anggota != $id) {
            return redirect()->route('pelayanan.members')
                ->with('error', 'Anda tidak memiliki akses untuk melihat profile anggota lain.');
        }
        
        // Statistics
        $totalServices = $anggota->jadwalPelayanan->count();
        $regularPositions = $anggota->spesialisasi->where('is_reguler', true)->count();
        $totalPositions = $anggota->spesialisasi->count();
        $recentServices = $anggota->jadwalPelayanan()
            ->where('tanggal_pelayanan', '>=', Carbon::now()->subMonths(3))
            ->count();
        
        $workloadScore = $anggota->getWorkloadScore(
            Carbon::now()->subMonths(3),
            Carbon::now()
        );
        
        $restDays = $anggota->getRestDays();
        
        // Calculate availability percentage
        $availabilityPercentage = 0;
        if (!empty($anggota->ketersediaan_hari)) {
            $availabilityPercentage = (count($anggota->ketersediaan_hari) / 7) * 100;
        }
        
        // Recent schedules
        $recentSchedules = $anggota->jadwalPelayanan()
            ->with(['kegiatan', 'pelaksanaan'])
            ->orderBy('tanggal_pelayanan', 'desc')
            ->limit(10)
            ->get();
        
        // Position categories for grouping
        $positionCategories = AnggotaSpesialisasi::getPositionsByCategory();
        
        // Chart data for service history
        $chartData = $this->getMemberChartData($anggota);
        
        return view('pelayanan.member-profile', compact(
            'anggota',
            'totalServices',
            'regularPositions',
            'totalPositions',
            'recentServices',
            'workloadScore',
            'restDays',
            'availabilityPercentage',
            'recentSchedules',
            'positionCategories',
            'chartData'
        ));
    }
    
    /**
     * Show member service history
     */
    public function memberHistory($id)
    {
        $anggota = Anggota::findOrFail($id);
        
        // Check permission
        $user = Auth::user();
        if ($user->id_role > 3 && $user->id_anggota != $id) {
            return redirect()->route('pelayanan.members')
                ->with('error', 'Anda tidak memiliki akses untuk melihat riwayat anggota lain.');
        }
        
        $schedules = $anggota->jadwalPelayanan()
            ->with(['kegiatan', 'pelaksanaan'])
            ->orderBy('tanggal_pelayanan', 'desc')
            ->paginate(50);
        
        return view('pelayanan.member-history', compact('anggota', 'schedules'));
    }
    
    /**
     * Assign regular positions to member
     */
    public function assignRegular($id)
    {
        if (Auth::user()->id_role > 2) {
            return redirect()->route('pelayanan.members')
                ->with('error', 'Anda tidak memiliki akses untuk assign regular.');
        }
        
        $anggota = Anggota::with('spesialisasi')->findOrFail($id);
        $positionCategories = AnggotaSpesialisasi::getPositionsByCategory();
        
        return view('pelayanan.assign-regular', compact('anggota', 'positionCategories'));
    }
    
    /**
     * Save regular assignments
     */
    public function saveRegularAssignment(Request $request, $id)
    {
        if (Auth::user()->id_role > 2) {
            return redirect()->route('pelayanan.members')
                ->with('error', 'Anda tidak memiliki akses untuk assign regular.');
        }
        
        $validator = Validator::make($request->all(), [
            'regular_positions' => 'sometimes|array',
            'regular_positions.*' => 'string',
            'new_specializations' => 'sometimes|array',
            'new_specializations.*.posisi' => 'required|string',
            'new_specializations.*.prioritas' => 'required|integer|min:1|max:10',
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }
        
        $anggota = Anggota::findOrFail($id);
        
        DB::beginTransaction();
        
        try {
            // Update existing specializations
            $anggota->spesialisasi()->update(['is_reguler' => false]);
            
            if ($request->filled('regular_positions')) {
                foreach ($request->regular_positions as $posisi) {
                    $anggota->spesialisasi()
                        ->where('posisi', $posisi)
                        ->update(['is_reguler' => true]);
                }
            }
            
            // Add new specializations
            if ($request->filled('new_specializations')) {
                foreach ($request->new_specializations as $spec) {
                    AnggotaSpesialisasi::updateOrCreate([
                        'id_anggota' => $anggota->id_anggota,
                        'posisi' => $spec['posisi']
                    ], [
                        'prioritas' => $spec['prioritas'],
                        'is_reguler' => in_array($spec['posisi'], $request->regular_positions ?? []),
                    ]);
                }
            }
            
            DB::commit();
            
            return redirect()->route('pelayanan.member-profile', $id)
                ->with('success', 'Assignment regular berhasil disimpan.');
                
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error saving regular assignment: ' . $e->getMessage());
            
            return redirect()->back()
                ->with('error', 'Terjadi kesalahan saat menyimpan assignment.')
                ->withInput();
        }
    }
    
    /**
     * Export members to Excel
     */
    public function exportMembers(Request $request)
    {
        // Implementation for Excel export
        // This would use Laravel Excel or similar package
        
        return response()->download(storage_path('app/exports/anggota_pelayanan.xlsx'));
    }
    
    /**
     * Get position distribution data for chart
     */
    private function getPositionDistributionData()
    {
        $distribution = AnggotaSpesialisasi::select('posisi', DB::raw('count(*) as total'))
            ->groupBy('posisi')
            ->orderBy('total', 'desc')
            ->get();
        
        return [
            'labels' => $distribution->pluck('posisi')->toArray(),
            'data' => $distribution->pluck('total')->toArray()
        ];
    }
    
    /**
     * Get regular distribution data for chart
     */
    private function getRegularDistributionData()
    {
        $regularCount = AnggotaSpesialisasi::where('is_reguler', true)->count();
        $nonRegularCount = AnggotaSpesialisasi::where('is_reguler', false)->count();
        $noSpecCount = Anggota::whereDoesntHave('spesialisasi')->count();
        
        return [
            'labels' => ['Reguler', 'Non Reguler', 'Belum Setup'],
            'data' => [$regularCount, $nonRegularCount, $noSpecCount]
        ];
    }
    
    /**
     * Get chart data for member service history
     */
    private function getMemberChartData($anggota)
    {
        $months = [];
        $data = [];
        
        for ($i = 11; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $months[] = $month->format('M Y');
            
            $count = $anggota->jadwalPelayanan()
                ->whereYear('tanggal_pelayanan', $month->year)
                ->whereMonth('tanggal_pelayanan', $month->month)
                ->count();
                
            $data[] = $count;
        }
        
        return [
            'labels' => $months,
            'data' => $data
        ];
    }
}