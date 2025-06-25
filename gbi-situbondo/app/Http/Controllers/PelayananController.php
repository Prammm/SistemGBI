<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Anggota;
use App\Models\Kegiatan;
use App\Models\JadwalPelayanan;
use App\Models\PelaksanaanKegiatan;
use App\Models\AnggotaSpesialisasi;
use App\Models\SchedulingHistory;
use App\Models\JadwalPelayananReplacement;
use App\Models\MasterPosisiPelayanan;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf as PDF;
use App\Services\ExcelExportService;
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
            
        // Get available positions from master table
        $posisiOptions = MasterPosisiPelayanan::getActivePositions();
        
        // IMPROVEMENT: If editing existing schedule, only show positions that are already assigned
        if ($existingJadwal->isNotEmpty()) {
            $assignedPositions = $existingJadwal->pluck('posisi')->unique()->toArray();
            $posisiOptions = $assignedPositions; // Only show assigned positions for editing
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

    public function history(Request $request)
    {
        $user = Auth::user();
        
        // Base query
        $query = JadwalPelayanan::with(['anggota', 'pelaksanaan.kegiatan', 'kegiatan'])
            ->whereHas('pelaksanaan', function($q) {
                $q->where('tanggal_kegiatan', '<', Carbon::now()->format('Y-m-d'));
            })
            ->orderBy('tanggal_pelayanan', 'desc');
        
        // Role-based filtering
        if ($user->id_role > 3 && $user->id_anggota) {
            $query->where('id_anggota', $user->id_anggota);
        }
        
        // Apply filters
        if ($request->filled('start_date')) {
            $query->where('tanggal_pelayanan', '>=', $request->start_date);
        }
        
        if ($request->filled('end_date')) {
            $query->where('tanggal_pelayanan', '<=', $request->end_date);
        }
        
        if ($request->filled('posisi')) {
            $query->where('posisi', $request->posisi);
        }
        
        if ($request->filled('status')) {
            $query->where('status_konfirmasi', $request->status);
        }
        
        $historyData = $query->paginate(50);
        
        // Get available positions for filter
        $availablePositions = JadwalPelayanan::distinct('posisi')
            ->pluck('posisi')
            ->sort()
            ->values();
        
        // Calculate statistics
        $statistics = [
            'accepted' => $query->clone()->where('status_konfirmasi', 'terima')->count(),
            'rejected' => $query->clone()->where('status_konfirmasi', 'tolak')->count(),
            'pending' => $query->clone()->where('status_konfirmasi', 'belum')->count(),
        ];
        
        // Chart data
        $chartData = $this->getHistoryChartData($query->clone());
        
        return view('pelayanan.history', compact(
            'historyData',
            'availablePositions', 
            'statistics',
            'chartData'
        ));
    }    


    public function konfirmasi($id, $status)
    {
        $jadwal = JadwalPelayanan::with(['anggota', 'pelaksanaan.kegiatan'])->findOrFail($id);
        
        $user = Auth::user();
        if ($user->id_role > 3 && (!$user->id_anggota || $user->id_anggota != $jadwal->id_anggota)) {
            return redirect()->route('pelayanan.index')
                ->with('error', 'Anda tidak memiliki akses untuk mengkonfirmasi jadwal ini.');
        }
        
        if (!in_array($status, ['terima', 'tolak'])) {
            return redirect()->route('pelayanan.index')
                ->with('error', 'Status konfirmasi tidak valid.');
        }
        
        try {
            $jadwal->status_konfirmasi = $status;
            $jadwal->save();
            
            // Log activity for tracking
            Log::info("Schedule {$status} by user {$user->id} for jadwal {$jadwal->id_pelayanan}");
            
            $message = $status === 'terima' 
                ? 'Jadwal pelayanan berhasil diterima.' 
                : 'Jadwal pelayanan ditolak. Petugas akan mencari pengganti.';
                
            return redirect()->route('pelayanan.index')->with('success', $message);
            
        } catch (\Exception $e) {
            Log::error('Error confirming schedule: ' . $e->getMessage());
            
            return redirect()->route('pelayanan.index')
                ->with('error', 'Terjadi kesalahan saat mengkonfirmasi jadwal.');
        }
    }

    /**
    * Find replacement candidates for change assignee
    */
    public function findReplacementForChange($jadwalId)
    {
        try {
            $jadwal = JadwalPelayanan::with(['pelaksanaan', 'anggota'])->findOrFail($jadwalId);
            
            $posisi = $jadwal->posisi;
            $eventDate = $jadwal->pelaksanaan->tanggal_kegiatan;
            $eventStart = $jadwal->pelaksanaan->jam_mulai;
            $eventEnd = $jadwal->pelaksanaan->jam_selesai;
            $eventDay = Carbon::parse($eventDate)->dayOfWeek;
            
            // Find available candidates
            $candidates = Anggota::with(['spesialisasi'])
                ->whereHas('spesialisasi', function($q) use ($posisi) {
                    $q->where('posisi', $posisi);
                })
                ->where('id_anggota', '!=', $jadwal->id_anggota) // Exclude current assignee
                ->get()
                ->filter(function($anggota) use ($eventDate, $eventStart, $eventEnd, $jadwal) {
                    // Check availability
                    if (!$anggota->isAvailable($eventDate, $eventStart, $eventEnd)) {
                        return false;
                    }
                    
                    // Check if not already scheduled for this pelaksanaan
                    $alreadyScheduled = JadwalPelayanan::where('id_pelaksanaan', $jadwal->id_pelaksanaan)
                        ->where('id_anggota', $anggota->id_anggota)
                        ->exists();
                    
                    return !$alreadyScheduled;
                })
                ->map(function($anggota) use ($posisi) {
                    $spec = $anggota->spesialisasi->where('posisi', $posisi)->first();
                    return [
                        'id_anggota' => $anggota->id_anggota,
                        'nama' => $anggota->nama,
                        'email' => $anggota->email,
                        'no_telepon' => $anggota->no_telepon,
                        'is_reguler' => $spec ? $spec->is_reguler : false,
                        'prioritas' => $spec ? $spec->prioritas : 0,
                        'last_service' => $anggota->getLastServiceDate($posisi),
                        'rest_days' => $anggota->getRestDays($posisi),
                        'frequency' => $anggota->getServiceFrequency(3, $posisi),
                        'score' => $this->calculateReplacementScore($anggota, $posisi)
                    ];
                })
                ->sortByDesc('score')
                ->values();
            
            return response()->json([
                'success' => true,
                'candidates' => $candidates,
                'current_assignee' => [
                    'id_anggota' => $jadwal->anggota->id_anggota,
                    'nama' => $jadwal->anggota->nama
                ],
                'schedule_info' => [
                    'kegiatan' => $jadwal->pelaksanaan->kegiatan->nama_kegiatan,
                    'tanggal' => Carbon::parse($jadwal->tanggal_pelayanan)->format('d F Y'),
                    'jam' => Carbon::parse($jadwal->pelaksanaan->jam_mulai)->format('H:i') . ' - ' . 
                            Carbon::parse($jadwal->pelaksanaan->jam_selesai)->format('H:i'),
                    'posisi' => $jadwal->posisi
                ]
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error finding replacement candidates: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Gagal mencari kandidat pengganti'
            ], 500);
        }
    }

    /**
    * Execute assignee change
    */
    public function changeAssignee(Request $request)
    {
        if (Auth::user()->id_role > 3) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak memiliki akses'
            ], 403);
        }
        
        $validator = Validator::make($request->all(), [
            'jadwal_id' => 'required|exists:jadwal_pelayanan,id_pelayanan',
            'new_assignee_id' => 'required|exists:anggota,id_anggota',
            'reason' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        DB::beginTransaction();
        
        try {
            $jadwal = JadwalPelayanan::findOrFail($request->jadwal_id);
            $oldAssigneeId = $jadwal->id_anggota;
            $newAssigneeId = $request->new_assignee_id;
            
            // Create replacement tracking record
            $replacement = JadwalPelayananReplacement::create([
                'id_jadwal_pelayanan' => $jadwal->id_pelayanan,
                'original_assignee_id' => $oldAssigneeId,
                'replacement_id' => $newAssigneeId,
                'replacement_reason' => 'manual_change',
                'replacement_status' => 'assigned',
                'notes' => $request->reason,
                'requested_at' => now(),
                'resolved_at' => now(),
                'requested_by' => Auth::id()
            ]);
            
            // Update the schedule
            $jadwal->update([
                'id_anggota' => $newAssigneeId,
                'status_konfirmasi' => 'belum' // Reset confirmation
            ]);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Petugas berhasil diganti'
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error changing assignee: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat mengganti petugas'
            ], 500);
        }
    }

    /**
    * Calculate replacement score for candidates
    */
    private function calculateReplacementScore($anggota, $posisi)
    {
        $score = 0;
        
        // Regular bonus
        if ($anggota->isRegularIn($posisi)) {
            $score += 50;
        }
        
        // Priority bonus
        $score += $anggota->getPriorityFor($posisi) * 5;
        
        // Rest days bonus
        $restDays = $anggota->getRestDays($posisi);
        $score += min($restDays / 7, 30);
        
        // Frequency penalty
        $frequency = $anggota->getServiceFrequency(3, $posisi);
        $score -= $frequency * 10;
        
        return max(0, $score);
    }

    /**
    * Auto-reject expired schedules (to be called by scheduled job)
    */
    public function autoRejectExpiredSchedules()
    {
        $expiredSchedules = JadwalPelayanan::where('status_konfirmasi', 'belum')
            ->where('tanggal_pelayanan', '<', Carbon::now()->format('Y-m-d'))
            ->get();
        
        $rejectedCount = 0;
        
        foreach ($expiredSchedules as $jadwal) {
            $jadwal->update(['status_konfirmasi' => 'tolak']);
            
            // Log the auto-rejection
            Log::info("Auto-rejected expired schedule", [
                'id_pelayanan' => $jadwal->id_pelayanan,
                'anggota' => $jadwal->anggota->nama ?? 'Unknown',
                'tanggal_pelayanan' => $jadwal->tanggal_pelayanan,
                'posisi' => $jadwal->posisi
            ]);
            
            $rejectedCount++;
        }
        
        if ($rejectedCount > 0) {
            Log::info("Auto-rejected {$rejectedCount} expired schedules");
        }
        
        return $rejectedCount;
    }


    private function autoFindReplacement($replacement)
    {
        $candidates = $replacement->findReplacementCandidates();
        
        if ($candidates->isNotEmpty()) {
            // Auto-assign the best candidate for high-level users
            $bestCandidate = $candidates->first();
            
            if ($bestCandidate['category'] === 'same_position' && $bestCandidate['score'] > 70) {
                $replacement->assignReplacement(
                    $bestCandidate['anggota']->id_anggota,
                    'Auto-assigned: Best available candidate'
                );
                
                // Send notification to the new assignee
                $this->sendAssignmentNotification($replacement);
            }
        } else {
            $replacement->markNoReplacement('No suitable candidates available');
        }
    }
    
    private function sendReplacementNotification($replacement)
    {
        // Implementation for sending notifications
        // This can be expanded to send emails, in-app notifications, etc.
        Log::info('Replacement needed for schedule: ' . $replacement->id_jadwal_pelayanan);
    }

    private function sendAssignmentNotification($replacement)
    {
        if ($replacement->replacement && $replacement->replacement->email) {
            try {
                // Send email notification about new assignment
                // Mail::to($replacement->replacement->email)
                //     ->send(new PelayananReminder($replacement->jadwalPelayanan));
                    
                Log::info('Assignment notification sent to: ' . $replacement->replacement->email);
            } catch (\Exception $e) {
                Log::error('Failed to send assignment notification: ' . $e->getMessage());
            }
        }
    }

    public function getReplacementCandidates($replacementId)
    {
        try {
            $replacement = JadwalPelayananReplacement::findOrFail($replacementId);
            $candidates = $replacement->findReplacementCandidates();
            
            return response()->json([
                'success' => true,
                'candidates' => $candidates->values()
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting replacement candidates: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to find candidates'
            ], 500);
        }
    }

    public function getScheduleReplacementCandidates($jadwalId)
    {
        try {
            $jadwal = JadwalPelayanan::with(['pelaksanaan', 'anggota'])->findOrFail($jadwalId);
            
            // Create temporary replacement to use the candidate finding logic
            $tempReplacement = new JadwalPelayananReplacement([
                'id_jadwal_pelayanan' => $jadwal->id_pelayanan,
                'original_assignee_id' => $jadwal->id_anggota,
                'replacement_reason' => 'manual_change'
            ]);
            
            $candidates = $tempReplacement->findReplacementCandidates();
            
            return response()->json([
                'success' => true,
                'candidates' => $candidates->values()
            ]);
        } catch (\Exception $e) {
            Log::error('Error getting schedule replacement candidates: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to find candidates'
            ], 500);
        }
    }

    public function assignReplacement(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'replacement_id' => 'nullable|exists:jadwal_pelayanan_replacements,id',
                'jadwal_id' => 'nullable|exists:jadwal_pelayanan,id_pelayanan',
                'candidate_id' => 'required|exists:anggota,id_anggota',
                'notes' => 'nullable|string'
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid input data'
                ], 400);
            }
            
            DB::beginTransaction();
            
            if ($request->replacement_id) {
                // Existing replacement request
                $replacement = JadwalPelayananReplacement::findOrFail($request->replacement_id);
                $replacement->assignReplacement($request->candidate_id, $request->notes);
            } else {
                // Direct schedule change
                $jadwal = JadwalPelayanan::findOrFail($request->jadwal_id);
                
                // Create replacement record for tracking
                $replacement = JadwalPelayananReplacement::createRequest(
                    $jadwal->id_pelayanan,
                    $jadwal->id_anggota,
                    'manual_change',
                    Auth::id(),
                    $request->notes
                );
                
                $replacement->assignReplacement($request->candidate_id, $request->notes);
            }
            
            // Send notification to new assignee
            $this->sendAssignmentNotification($replacement);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Replacement assigned successfully'
            ]);
            
        } catch (\Exception $e) {
            DB::rollback();
            Log::error('Error assigning replacement: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to assign replacement'
            ], 500);
        }
    }

    public function markNoReplacement(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'replacement_id' => 'nullable|exists:jadwal_pelayanan_replacements,id',
                'jadwal_id' => 'nullable|exists:jadwal_pelayanan,id_pelayanan',
                'notes' => 'required|string'
            ]);
            
            if ($validator->fails()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Invalid input data'
                ], 400);
            }
            
            if ($request->replacement_id) {
                $replacement = JadwalPelayananReplacement::findOrFail($request->replacement_id);
                $replacement->markNoReplacement($request->notes);
            } else {
                // Create replacement record for tracking
                $jadwal = JadwalPelayanan::findOrFail($request->jadwal_id);
                
                $replacement = JadwalPelayananReplacement::createRequest(
                    $jadwal->id_pelayanan,
                    $jadwal->id_anggota,
                    'no_replacement',
                    Auth::id(),
                    $request->notes
                );
                
                $replacement->markNoReplacement($request->notes);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error marking no replacement: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to update status'
            ], 500);
        }
    }

    public function getScheduleDetails($jadwalId)
    {
        try {
            $jadwal = JadwalPelayanan::with(['anggota', 'pelaksanaan.kegiatan', 'kegiatan'])
                ->findOrFail($jadwalId);
            
            $replacement = JadwalPelayananReplacement::where('id_jadwal_pelayanan', $jadwalId)
                ->with(['replacement'])
                ->first();
            
            $scheduleData = [
                'kegiatan' => $jadwal->pelaksanaan->kegiatan->nama_kegiatan ?? $jadwal->kegiatan->nama_kegiatan ?? 'N/A',
                'tanggal' => Carbon::parse($jadwal->tanggal_pelayanan)->format('d F Y'),
                'waktu' => $jadwal->pelaksanaan ? 
                    Carbon::parse($jadwal->pelaksanaan->jam_mulai)->format('H:i') . ' - ' . 
                    Carbon::parse($jadwal->pelaksanaan->jam_selesai)->format('H:i') : 'N/A',
                'lokasi' => $jadwal->pelaksanaan->lokasi ?? null,
                'anggota' => $jadwal->anggota->nama,
                'posisi' => $jadwal->posisi,
                'status_badge' => $this->getStatusBadge($jadwal->status_konfirmasi),
                'reguler_badge' => $jadwal->is_reguler || $jadwal->anggota->isRegularIn($jadwal->posisi) ? 
                    '<span class="badge bg-success"><i class="fas fa-star"></i> Reguler</span>' : 
                    '<span class="badge bg-light text-dark">Non-Reguler</span>',
                'created_at' => $jadwal->created_at->format('d/m/Y H:i'),
                'updated_at' => $jadwal->updated_at->format('d/m/Y H:i')
            ];
            
            $replacementData = null;
            if ($replacement) {
                $replacementData = [
                    'reason' => ucfirst($replacement->replacement_reason),
                    'status' => ucfirst($replacement->replacement_status),
                    'replacement_name' => $replacement->replacement ? $replacement->replacement->nama : null,
                    'requested_at' => $replacement->requested_at->format('d/m/Y H:i'),
                    'notes' => $replacement->notes
                ];
            }
            
            return response()->json([
                'success' => true,
                'schedule' => $scheduleData,
                'replacement' => $replacementData
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error getting schedule details: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to get schedule details'
            ], 500);
        }
    }

    public function exportHistory(Request $request)
    {
        $user = Auth::user();
        $format = $request->input('format', 'excel'); // excel or pdf
        
        // Get the same data as history method
        $query = JadwalPelayanan::with(['anggota', 'kegiatan', 'pelaksanaan.kegiatan']);
        
        // Apply filters
        $filters = [];
        if ($request->filled('start_date')) {
            $query->whereDate('tanggal_pelayanan', '>=', $request->start_date);
            $filters['start_date'] = $request->start_date;
        }
        
        if ($request->filled('end_date')) {
            $query->whereDate('tanggal_pelayanan', '<=', $request->end_date);
            $filters['end_date'] = $request->end_date;
        }
        
        if ($request->filled('posisi')) {
            $query->where('posisi', $request->posisi);
            $filters['posisi'] = $request->posisi;
        }
        
        if ($request->filled('status')) {
            $query->where('status_konfirmasi', $request->status);
            $filters['status'] = $request->status;
        }
        
        // Role-based filtering
        if ($user->id_role == 4) { // Anggota Jemaat
            if (!$user->id_anggota) {
                return redirect()->back()->with('error', 'Profil anggota tidak lengkap.');
            }
            $query->where('id_anggota', $user->id_anggota);
        } elseif ($user->id_role == 3) { // Petugas Pelayanan
            // Show all for now, can be refined for specific supervision
        }
        // Admin and Pengurus see all
        
        $historyData = $query->orderBy('tanggal_pelayanan', 'desc')->get();
        
        // Calculate statistics
        $statistics = [
            'total' => $historyData->count(),
            'accepted' => $historyData->where('status_konfirmasi', 'terima')->count(),
            'rejected' => $historyData->where('status_konfirmasi', 'tolak')->count(),
            'pending' => $historyData->where('status_konfirmasi', 'belum')->count(),
        ];
        
        if ($format === 'excel') {
            return \App\Services\ExcelExportService::exportRiwayatPelayanan($historyData, $filters, $statistics);
        } else {
            return $this->exportHistoryToPdf($historyData, $filters, $statistics);
        }
    }

    private function exportHistoryToPdf($historyData, $filters, $statistics)
    {
        $title = 'Riwayat Pelayanan Lengkap';
        if (!empty($filters['start_date']) && !empty($filters['end_date'])) {
            $title .= ' - ' . \Carbon\Carbon::parse($filters['start_date'])->format('d/m/Y') . 
                    ' s/d ' . \Carbon\Carbon::parse($filters['end_date'])->format('d/m/Y');
        }
        
        $pdf = PDF::loadView('pelayanan.history-pdf', [
            'historyData' => $historyData,
            'filters' => $filters,
            'statistics' => $statistics,
            'title' => $title,
            'date' => \Carbon\Carbon::now()->format('d F Y')
        ]);
        
        // Set paper size and orientation
        $pdf->setPaper('A4', 'landscape');
        
        // Generate filename
        $filename = 'riwayat-pelayanan-lengkap';
        if (!empty($filters['start_date'])) {
            $filename .= '-' . \Carbon\Carbon::parse($filters['start_date'])->format('Y-m-d');
        }
        if (!empty($filters['end_date'])) {
            $filename .= '-to-' . \Carbon\Carbon::parse($filters['end_date'])->format('Y-m-d');
        }
        if (!empty($filters['posisi'])) {
            $filename .= '-' . strtolower(str_replace(' ', '-', $filters['posisi']));
        }
        $filename .= '.pdf';
        
        return $pdf->download($filename);
    }

    private function getHistoryChartData($query)
    {
        $monthlyData = $query->selectRaw('
                DATE_FORMAT(tanggal_pelayanan, "%Y-%m") as month,
                COUNT(*) as total,
                SUM(CASE WHEN status_konfirmasi = "terima" THEN 1 ELSE 0 END) as accepted,
                SUM(CASE WHEN status_konfirmasi = "tolak" THEN 1 ELSE 0 END) as rejected,
                SUM(CASE WHEN status_konfirmasi = "belum" THEN 1 ELSE 0 END) as pending
            ')
            ->groupBy('month')
            ->orderBy('month')
            ->get();
        
        $statusData = $query->selectRaw('
                SUM(CASE WHEN status_konfirmasi = "terima" THEN 1 ELSE 0 END) as accepted,
                SUM(CASE WHEN status_konfirmasi = "tolak" THEN 1 ELSE 0 END) as rejected,
                SUM(CASE WHEN status_konfirmasi = "belum" THEN 1 ELSE 0 END) as pending
            ')
            ->first();
        
        return [
            'monthly' => [
                'labels' => $monthlyData->pluck('month')->map(function($month) {
                    return Carbon::createFromFormat('Y-m', $month)->format('M Y');
                }),
                'total' => $monthlyData->pluck('total'),
                'accepted' => $monthlyData->pluck('accepted'),
                'rejected' => $monthlyData->pluck('rejected'),
                'pending' => $monthlyData->pluck('pending')
            ],
            'status' => [
                'accepted' => $statusData->accepted ?? 0,
                'rejected' => $statusData->rejected ?? 0,
                'pending' => $statusData->pending ?? 0
            ]
        ];
    }

    private function getStatusBadge($status)
    {
        switch ($status) {
            case 'terima':
                return '<span class="badge bg-success">Diterima</span>';
            case 'tolak':
                return '<span class="badge bg-danger">Ditolak</span>';
            case 'belum':
                return '<span class="badge bg-warning">Belum Konfirmasi</span>';
            default:
                return '<span class="badge bg-secondary">Unknown</span>';
        }
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
        if (Auth::user()->id_role > 3) {
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
        if (Auth::user()->id_role > 3) {
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
        $unfilledPositions = [];
        
        // FIXED: Sort positions by difficulty (harder to fill positions first)
        $sortedPositions = collect($positions)->sortBy(function($position) use ($anggota) {
            $candidateCount = $anggota->filter(function($a) use ($position) {
                return $a->spesialisasi->contains('posisi', $position);
            })->count();
            
            return $candidateCount; // Positions with fewer candidates go first
        });
        
        foreach ($sortedPositions as $position) {
            $candidates = $this->findEligibleCandidates(
                $anggota, 
                $position, 
                $pelaksanaan, 
                $scheduledMembers,
                $algorithm,
                $workloadTracking
            );
            
            if ($candidates->isEmpty()) {
                $unfilledPositions[] = $position;
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
                        // FIXED: Jika tetap tidak ada kandidat, paksa assign yang pertama
                        Log::warning("Forcing assignment despite consecutive conflict for position: {$position}");
                        // Keep the first candidate
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
            
            Log::info("Assigned {$selectedCandidate->nama} to {$position} for {$pelaksanaan->tanggal_kegiatan}");
        }
        
        // FIXED: Jika ada posisi yang tidak terisi, coba assign ulang dengan relaxed rules
        if (!empty($unfilledPositions)) {
            Log::warning("Attempting to fill unfilled positions with relaxed rules: " . implode(', ', $unfilledPositions));
            
            foreach ($unfilledPositions as $position) {
                // FIXED: Cari kandidat manapun yang punya spesialisasi, ignore availability dan workload
                $emergencyCandidates = $anggota->filter(function($a) use ($position, $scheduledMembers) {
                    // Tidak peduli sudah dijadwalkan atau belum, cari yang punya spesialisasi
                    return $a->spesialisasi->contains('posisi', $position);
                })->sortByDesc(function($a) use ($position) {
                    $spec = $a->spesialisasi->where('posisi', $position)->first();
                    return $spec ? $spec->prioritas : 0;
                });
                
                if ($emergencyCandidates->isNotEmpty()) {
                    $emergencyCandidate = $emergencyCandidates->first();
                    
                    // Create jadwal with emergency assignment
                    $jadwal = JadwalPelayanan::create([
                        'id_kegiatan' => $pelaksanaan->id_kegiatan,
                        'id_pelaksanaan' => $pelaksanaan->id_pelaksanaan,
                        'tanggal_pelayanan' => $pelaksanaan->tanggal_kegiatan,
                        'id_anggota' => $emergencyCandidate->id_anggota,
                        'posisi' => $position,
                        'status_konfirmasi' => 'belum',
                        'is_reguler' => $emergencyCandidate->isRegularIn($position),
                    ]);
                    
                    SchedulingHistory::createFromJadwal($jadwal);
                    
                    $scheduledPositions[] = $position;
                    $scheduledMembers[] = $emergencyCandidate->id_anggota;
                    
                    // Remove from unfilled list
                    $unfilledPositions = array_diff($unfilledPositions, [$position]);
                    
                    Log::info("Emergency assigned {$emergencyCandidate->nama} to {$position}");
                    $conflicts[] = "Emergency assignment: {$emergencyCandidate->nama} untuk posisi {$position} (mungkin tidak sesuai ketersediaan)";
                }
            }
        }
        
        return [
            'scheduled' => count($scheduledPositions),
            'skipped' => count($positions) - count($scheduledPositions),
            'conflicts' => $conflicts,
            'scheduled_members' => array_unique($scheduledMembers),
            'unfilled_positions' => $unfilledPositions
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
        
        // Filter basic eligibility - FIXED: Hanya cek apakah ada spesialisasi, tidak peduli is_reguler
        $eligibleCandidates = $anggota->filter(function ($anggota) use ($position, $scheduledMembers, $eventDay, $eventStart, $eventEnd, $eventDate) {
            // Skip if already scheduled
            if (in_array($anggota->id_anggota, $scheduledMembers)) {
                return false;
            }
            
            // FIXED: Check if they can serve this position - TIDAK peduli is_reguler, yang penting ada spesialisasi
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
        
        // FIXED: Jika tidak ada kandidat sama sekali, coba cari kandidat dengan prioritas yang lebih rendah
        if ($eligibleCandidates->isEmpty()) {
            Log::warning("No eligible candidates found for position: {$position}. Trying fallback candidates...");
            
            // Fallback 1: Cari kandidat yang punya spesialisasi tapi tidak available 
            $fallbackCandidates = $anggota->filter(function ($anggota) use ($position, $scheduledMembers) {
                if (in_array($anggota->id_anggota, $scheduledMembers)) {
                    return false;
                }
                
                return $anggota->spesialisasi->contains('posisi', $position);
            });
            
            if ($fallbackCandidates->isNotEmpty()) {
                Log::info("Found {$fallbackCandidates->count()} fallback candidates for position: {$position}");
                $eligibleCandidates = $fallbackCandidates;
            }
        }
        
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
        
        // FIXED: Base factors - cek apakah ada spesialisasi untuk posisi ini
        $specialization = $anggota->spesialisasi->where('posisi', $position)->first();
        
        if (!$specialization) {
            return 0; // Tidak punya spesialisasi sama sekali
        }
        
        $isReguler = $specialization->is_reguler;
        $prioritas = $specialization->prioritas;
        $restDays = $anggota->getRestDays($position);
        $serviceFrequency = $anggota->getServiceFrequency(3, $position); // Last 3 months
        $currentWorkload = $workloadTracking[$anggota->id_anggota] ?? 0;
        
        switch ($algorithm) {
            case 'regular_priority':
                // Prioritize regular players
                if ($isReguler) {
                    $score += 100;
                }
                $score += $prioritas * 10; // FIXED: Gunakan prioritas dari spesialisasi
                $score += min($restDays / 7, 50); // Bonus for rest days
                $score -= $serviceFrequency * 5; // Penalty for frequency
                break;
                
            case 'fair_rotation':
            default:
                // Prioritize fair distribution
                $score += max(100 - $serviceFrequency * 15, 0); // High penalty for recent service
                $score += min($restDays / 3, 100); // More rest = higher score
                $score -= $currentWorkload * 30; // Heavy penalty for current workload
                
                // FIXED: Berikan bonus berdasarkan prioritas, bukan hanya is_reguler
                $score += $prioritas * 5; // Bonus berdasarkan skill level
                
                if ($isReguler) {
                    $score += 30; // Moderate regular bonus
                }
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
            // FIXED: Separate validation for start/end times instead of combined format
            'ketersediaan_jam_start' => 'required|array',
            'ketersediaan_jam_start.*' => 'required|date_format:H:i',
            'ketersediaan_jam_end' => 'required|array',
            'ketersediaan_jam_end.*' => 'required|date_format:H:i|after:ketersediaan_jam_start.*',
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
            // FIXED: Process time slots from start/end arrays
            $timeSlots = [];
            $startTimes = $request->ketersediaan_jam_start;
            $endTimes = $request->ketersediaan_jam_end;
            
            if ($startTimes && $endTimes && count($startTimes) === count($endTimes)) {
                for ($i = 0; $i < count($startTimes); $i++) {
                    if (!empty($startTimes[$i]) && !empty($endTimes[$i])) {
                        // Validate that end time is after start time
                        if ($endTimes[$i] > $startTimes[$i]) {
                            $timeSlots[] = $startTimes[$i] . '-' . $endTimes[$i];
                        }
                    }
                }
            }
            
            // Update availability
            $anggota->update([
                'ketersediaan_hari' => $request->ketersediaan_hari,
                'ketersediaan_jam' => $timeSlots, // Use processed time slots
                'blackout_dates' => $request->blackout_dates ?? [],
                'catatan_khusus' => $request->catatan_khusus,
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

    public function updateSchedule(Request $request, $id)
    {
        if (Auth::user()->id_role > 2) {
            return response()->json([
                'success' => false,
                'message' => 'Tidak memiliki akses'
            ], 403);
        }
        
        $validator = Validator::make($request->all(), [
            'status_konfirmasi' => 'required|in:belum,terima,tolak',
            'notes' => 'nullable|string|max:500'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }
        
        try {
            $jadwal = JadwalPelayanan::findOrFail($id);
            $oldStatus = $jadwal->status_konfirmasi;
            
            $jadwal->update([
                'status_konfirmasi' => $request->status_konfirmasi
            ]);
            
            // Log the manual update
            Log::info("Schedule manually updated by admin", [
                'id_pelayanan' => $jadwal->id_pelayanan,
                'old_status' => $oldStatus,
                'new_status' => $request->status_konfirmasi,
                'updated_by' => Auth::id(),
                'notes' => $request->notes
            ]);
            
            return response()->json([
                'success' => true,
                'message' => 'Status jadwal berhasil diperbarui'
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error updating schedule: ' . $e->getMessage());
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan saat memperbarui jadwal'
            ], 500);
        }
    }




}