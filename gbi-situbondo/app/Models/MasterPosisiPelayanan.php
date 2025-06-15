<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MasterPosisiPelayanan extends Model
{
    use HasFactory;
    
    protected $table = 'master_posisi_pelayanan';
    protected $primaryKey = 'id_posisi';
    
    protected $fillable = [
        'nama_posisi',
        'kategori',
        'urutan',
        'is_active',
        'deskripsi',
        'workload_score'
    ];
    
    protected $casts = [
        'is_active' => 'boolean',
        'urutan' => 'integer',
        'workload_score' => 'integer'
    ];
    
    /**
     * Get all active positions grouped by category
     */
    public static function getActivePositionsByCategory()
    {
        return self::where('is_active', true)
            ->orderBy('kategori')
            ->orderBy('urutan')
            ->get()
            ->groupBy('kategori')
            ->map(function($positions) {
                return $positions->pluck('nama_posisi')->toArray();
            })
            ->toArray();
    }
    
    /**
     * Get all active positions as flat array
     */
    public static function getActivePositions()
    {
        return self::where('is_active', true)
            ->orderBy('kategori')
            ->orderBy('urutan')
            ->pluck('nama_posisi')
            ->toArray();
    }
    
    /**
     * Get workload score for position
     */
    public static function getWorkloadScore($posisi)
    {
        $position = self::where('nama_posisi', $posisi)->first();
        return $position ? $position->workload_score : 1;
    }
    
    /**
     * Relation to specializations
     */
    public function spesialisasi()
    {
        return $this->hasMany(AnggotaSpesialisasi::class, 'posisi', 'nama_posisi');
    }
    
    /**
     * Relation to schedules
     */
    public function jadwalPelayanan()
    {
        return $this->hasMany(JadwalPelayanan::class, 'posisi', 'nama_posisi');
    }
}