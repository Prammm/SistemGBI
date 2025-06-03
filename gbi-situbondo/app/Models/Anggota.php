<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Anggota extends Model
{
    use HasFactory;
    
    protected $table = 'anggota';
    protected $primaryKey = 'id_anggota';
    
    protected $fillable = [
        'nama', 'tanggal_lahir', 'jenis_kelamin', 'id_keluarga', 
        'id_ortu', 'alamat', 'no_telepon', 'email',
        'ketersediaan_hari', 'ketersediaan_jam', 'blackout_dates', 'catatan_khusus'
    ];
    
    protected $casts = [
        'ketersediaan_hari' => 'array',
        'ketersediaan_jam' => 'array',
        'blackout_dates' => 'array',
        'tanggal_lahir' => 'date',
    ];
    
    public function keluarga()
    {
        return $this->belongsTo(Keluarga::class, 'id_keluarga', 'id_keluarga');
    }
    
    public function orangtua()
    {
        return $this->belongsTo(Anggota::class, 'id_ortu', 'id_anggota');
    }
    
    public function anak()
    {
        return $this->hasMany(Anggota::class, 'id_ortu', 'id_anggota');
    }
    
    public function hubunganKeluarga()
    {
        return $this->hasMany(HubunganKeluarga::class, 'id_anggota', 'id_anggota');
    }
    
    public function komsel()
    {
        return $this->belongsToMany(Komsel::class, 'anggota_komsel', 'id_anggota', 'id_komsel');
    }
    
    public function kehadiran()
    {
        return $this->hasMany(Kehadiran::class, 'id_anggota', 'id_anggota');
    }
    
    public function jadwalPelayanan()
    {
        return $this->hasMany(JadwalPelayanan::class, 'id_anggota', 'id_anggota');
    }
    
    public function spesialisasi()
    {
        return $this->hasMany(AnggotaSpesialisasi::class, 'id_anggota', 'id_anggota');
    }
    
    public function schedulingHistory()
    {
        return $this->hasMany(SchedulingHistory::class, 'id_anggota', 'id_anggota');
    }
    
    public function user()
    {
        return $this->hasOne(User::class, 'id_anggota', 'id_anggota');
    }

    /**
     * Check if member is available on specific date and time
     */
    public function isAvailable($date, $startTime, $endTime)
    {
        $carbonDate = Carbon::parse($date);
        $dayOfWeek = $carbonDate->dayOfWeek;
        
        // Check blackout dates
        if (!empty($this->blackout_dates)) {
            foreach ($this->blackout_dates as $blackoutDate) {
                if (Carbon::parse($blackoutDate)->isSameDay($carbonDate)) {
                    return false;
                }
            }
        }
        
        // Check day availability
        if (empty($this->ketersediaan_hari) || !in_array($dayOfWeek, $this->ketersediaan_hari)) {
            return false;
        }
        
        // Check time availability
        if (empty($this->ketersediaan_jam)) {
            return true; // If no time restriction, assume available
        }
        
        foreach ($this->ketersediaan_jam as $timeSlot) {
            list($availStart, $availEnd) = explode('-', $timeSlot);
            if ($startTime >= $availStart && $endTime <= $availEnd) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Get regular positions for this member
     */
    public function getRegularPositions()
    {
        return $this->spesialisasi()
            ->where('is_reguler', true)
            ->pluck('posisi')
            ->toArray();
    }
    
    /**
     * Get all positions this member can serve
     */
    public function getAllPositions()
    {
        return $this->spesialisasi()
            ->pluck('posisi')
            ->toArray();
    }
    
    /**
     * Check if member is regular in specific position
     */
    public function isRegularIn($posisi)
    {
        return $this->spesialisasi()
            ->where('posisi', $posisi)
            ->where('is_reguler', true)
            ->exists();
    }
    
    /**
     * Get priority for specific position
     */
    public function getPriorityFor($posisi)
    {
        $spesialisasi = $this->spesialisasi()
            ->where('posisi', $posisi)
            ->first();
            
        return $spesialisasi ? $spesialisasi->prioritas : 0;
    }
    
    /**
     * Calculate workload score in date range
     */
    public function getWorkloadScore($startDate, $endDate)
    {
        return $this->schedulingHistory()
            ->whereBetween('tanggal_pelayanan', [$startDate, $endDate])
            ->sum('workload_score');
    }
    
    /**
     * Get last service date for specific position
     */
    public function getLastServiceDate($posisi = null)
    {
        $query = $this->jadwalPelayanan()
            ->orderBy('tanggal_pelayanan', 'desc');
            
        if ($posisi) {
            $query->where('posisi', $posisi);
        }
        
        $lastService = $query->first();
        return $lastService ? $lastService->tanggal_pelayanan : null;
    }
    
    /**
     * Calculate rest days since last service
     */
    public function getRestDays($posisi = null)
    {
        $lastServiceDate = $this->getLastServiceDate($posisi);
        
        if (!$lastServiceDate) {
            return 999; // Never served, high priority
        }
        
        return Carbon::now()->diffInDays(Carbon::parse($lastServiceDate));
    }
    
    /**
     * Get service frequency in last N months
     */
    public function getServiceFrequency($months = 3, $posisi = null)
    {
        $startDate = Carbon::now()->subMonths($months);
        
        $query = $this->jadwalPelayanan()
            ->where('tanggal_pelayanan', '>=', $startDate);
            
        if ($posisi) {
            $query->where('posisi', $posisi);
        }
        
        return $query->count();
    }

    /**
     * Mendapatkan semua anggota keluarga yang berelasi
     */
    public function getAnggotaKeluarga()
    {
        if (!$this->id_keluarga) {
            return collect([]);
        }

        $anggotaKeluarga = Anggota::where('id_keluarga', $this->id_keluarga)
            ->where('id_anggota', '!=', $this->id_anggota)
            ->with(['hubunganKeluarga.anggotaTujuan', 'orangtua'])
            ->get();

        return $anggotaKeluarga;
    }

    /**
     * Mendapatkan hubungan dengan anggota lain dalam keluarga
     */
    public function getHubunganDengan($targetAnggotaId)
    {
        $hubungan = HubunganKeluarga::where('id_anggota', $this->id_anggota)
            ->where('id_anggota_tujuan', $targetAnggotaId)
            ->first();
        
        if ($hubungan) {
            return $hubungan->hubungan;
        }

        $hubunganTerbalik = HubunganKeluarga::where('id_anggota', $targetAnggotaId)
            ->where('id_anggota_tujuan', $this->id_anggota)
            ->first();

        if ($hubunganTerbalik) {
            return $this->getBalikanHubungan($hubunganTerbalik->hubungan);
        }

        if ($this->id_ortu == $targetAnggotaId) {
            return 'Anak';
        }

        $targetAnggota = Anggota::find($targetAnggotaId);
        if ($targetAnggota && $targetAnggota->id_ortu == $this->id_anggota) {
            return 'Orang Tua';
        }

        return 'Keluarga';
    }

    /**
     * Mendapatkan balikan hubungan
     */
    private function getBalikanHubungan($hubungan)
    {
        $balikan = [
            'Kepala Keluarga' => 'Anggota Keluarga',
            'Istri' => 'Suami',
            'Suami' => 'Istri',
            'Anak' => 'Orang Tua',
            'Orang Tua' => 'Anak',
            'Saudara' => 'Saudara',
            'Kakek' => 'Cucu',
            'Nenek' => 'Cucu',
            'Cucu' => 'Kakek/Nenek',
            'Paman' => 'Keponakan',
            'Bibi' => 'Keponakan',
            'Keponakan' => 'Paman/Bibi',
        ];

        return $balikan[$hubungan] ?? 'Keluarga';
    }

    public static function boot()
    {
        parent::boot();

        static::saving(function ($anggota) {
            if ($anggota->id_ortu && !$anggota->id_keluarga) {
                $orangtua = Anggota::find($anggota->id_ortu);
                if ($orangtua && $orangtua->id_keluarga) {
                    $anggota->id_keluarga = $orangtua->id_keluarga;
                }
            }
        });

        static::saved(function ($anggota) {
            if ($anggota->id_ortu) {
                $existingRelation = HubunganKeluarga::where('id_anggota', $anggota->id_anggota)
                    ->where('id_anggota_tujuan', $anggota->id_ortu)
                    ->where('hubungan', 'Anak')
                    ->first();

                if (!$existingRelation) {
                    HubunganKeluarga::create([
                        'id_anggota' => $anggota->id_anggota,
                        'id_anggota_tujuan' => $anggota->id_ortu,
                        'hubungan' => 'Anak'
                    ]);
                }
            }
        });
    }
}