<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class JadwalPelayananReplacement extends Model
{
    use HasFactory;
    
    protected $fillable = [
        'id_jadwal_pelayanan',
        'original_assignee_id',
        'replacement_id',
        'replacement_reason',
        'replacement_status',
        'notes',
        'requested_at',
        'resolved_at',
        'requested_by'
    ];
    
    protected $casts = [
        'requested_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];
    
    public function jadwalPelayanan()
    {
        return $this->belongsTo(JadwalPelayanan::class, 'id_jadwal_pelayanan', 'id_pelayanan');
    }
    
    public function originalAssignee()
    {
        return $this->belongsTo(Anggota::class, 'original_assignee_id', 'id_anggota');
    }
    
    public function replacement()
    {
        return $this->belongsTo(Anggota::class, 'replacement_id', 'id_anggota');
    }
    
    public function requestedBy()
    {
        return $this->belongsTo(User::class, 'requested_by');
    }
    
    /**
     * Create replacement request
     */
    public static function createRequest($jadwalId, $originalAssigneeId, $reason, $requestedBy, $notes = null)
    {
        return self::create([
            'id_jadwal_pelayanan' => $jadwalId,
            'original_assignee_id' => $originalAssigneeId,
            'replacement_reason' => $reason,
            'replacement_status' => 'pending',
            'notes' => $notes,
            'requested_at' => now(),
            'requested_by' => $requestedBy
        ]);
    }
    
    /**
     * Find suitable replacement candidates
     */
    public function findReplacementCandidates()
    {
        $jadwal = $this->jadwalPelayanan;
        $pelaksanaan = $jadwal->pelaksanaan;
        
        if (!$pelaksanaan) {
            return collect();
        }
        
        $posisi = $jadwal->posisi;
        $eventDate = $pelaksanaan->tanggal_kegiatan;
        $eventStart = $pelaksanaan->jam_mulai;
        $eventEnd = $pelaksanaan->jam_selesai;
        
        // Priority 1: Same position specialists who are available
        $candidates = Anggota::with(['spesialisasi', 'jadwalPelayanan'])
            ->whereHas('spesialisasi', function($q) use ($posisi) {
                $q->where('posisi', $posisi);
            })
            ->where('id_anggota', '!=', $this->original_assignee_id)
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
                    'anggota' => $anggota,
                    'score' => $this->calculateReplacementScore($anggota, $posisi, 'same_position'),
                    'is_reguler' => $spec ? $spec->is_reguler : false,
                    'prioritas' => $spec ? $spec->prioritas : 0,
                    'category' => 'same_position'
                ];
            })
            ->sortByDesc('score');
        
        // Priority 2: If no same position candidates, look for other position specialists
        if ($candidates->isEmpty()) {
            $otherCandidates = Anggota::with(['spesialisasi', 'jadwalPelayanan'])
                ->whereHas('spesialisasi')
                ->where('id_anggota', '!=', $this->original_assignee_id)
                ->get()
                ->filter(function($anggota) use ($eventDate, $eventStart, $eventEnd, $jadwal) {
                    if (!$anggota->isAvailable($eventDate, $eventStart, $eventEnd)) {
                        return false;
                    }
                    
                    $alreadyScheduled = JadwalPelayanan::where('id_pelaksanaan', $jadwal->id_pelaksanaan)
                        ->where('id_anggota', $anggota->id_anggota)
                        ->exists();
                    
                    return !$alreadyScheduled;
                })
                ->map(function($anggota) use ($posisi) {
                    return [
                        'anggota' => $anggota,
                        'score' => $this->calculateReplacementScore($anggota, $posisi, 'different_position'),
                        'is_reguler' => false,
                        'prioritas' => 0,
                        'category' => 'different_position'
                    ];
                })
                ->sortByDesc('score');
            
            $candidates = $candidates->merge($otherCandidates);
        }
        
        return $candidates->take(10);
    }
    
    /**
     * Calculate replacement score for candidate
     */
    private function calculateReplacementScore($anggota, $posisi, $category)
    {
        $score = 0;
        
        if ($category === 'same_position') {
            $spec = $anggota->spesialisasi->where('posisi', $posisi)->first();
            if ($spec) {
                $score += $spec->is_reguler ? 100 : 50;
                $score += $spec->prioritas * 10;
            }
        } else {
            // For different position, lower base score
            $score = 30;
        }
        
        // Add availability bonus
        $restDays = $anggota->getRestDays();
        $score += min($restDays / 7, 20);
        
        // Subtract recent service penalty
        $recentServices = $anggota->getServiceFrequency(1); // Last month
        $score -= $recentServices * 5;
        
        return max(0, $score);
    }
    
    /**
     * Assign replacement
     */
    public function assignReplacement($replacementId, $notes = null)
    {
        $this->update([
            'replacement_id' => $replacementId,
            'replacement_status' => 'assigned',
            'resolved_at' => now(),
            'notes' => $notes
        ]);
        
        // Update the original jadwal_pelayanan
        $this->jadwalPelayanan->update([
            'id_anggota' => $replacementId,
            'status_konfirmasi' => 'belum' // Reset confirmation status
        ]);
        
        return $this;
    }
    
    /**
     * Mark as no replacement available
     */
    public function markNoReplacement($notes = null)
    {
        $this->update([
            'replacement_status' => 'no_replacement',
            'resolved_at' => now(),
            'notes' => $notes
        ]);
        
        return $this;
    }
    
    /**
     * Get pending replacements that need attention
     */
    public static function getPendingReplacements()
    {
        return self::with(['jadwalPelayanan.pelaksanaan.kegiatan', 'originalAssignee', 'requestedBy'])
            ->where('replacement_status', 'pending')
            ->whereHas('jadwalPelayanan.pelaksanaan', function($q) {
                $q->where('tanggal_kegiatan', '>=', now()->format('Y-m-d'));
            })
            ->orderBy('requested_at', 'asc')
            ->get();
    }
}