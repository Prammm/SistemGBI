<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnggotaSpesialisasi extends Model
{
    use HasFactory;
    
    protected $table = 'anggota_spesialisasi';
    
    protected $fillable = [
        'id_anggota',
        'posisi',
        'is_reguler',
        'prioritas',
        'catatan'
    ];
    
    protected $casts = [
        'is_reguler' => 'boolean',
        'prioritas' => 'integer',
    ];
    
    public function anggota()
    {
        return $this->belongsTo(Anggota::class, 'id_anggota', 'id_anggota');
    }
    
    public function masterPosisi()
    {
        return $this->belongsTo(MasterPosisiPelayanan::class, 'posisi', 'nama_posisi');
    }
    
    /**
     * Get all available positions from master table
     */
    public static function getAvailablePositions()
    {
        return MasterPosisiPelayanan::getActivePositions();
    }
    
    /**
     * Get positions by category from master table
     */
    public static function getPositionsByCategory()
    {
        return MasterPosisiPelayanan::getActivePositionsByCategory();
    }
    
    /**
     * Get workload score for position from master table
     */
    public static function getWorkloadScore($posisi)
    {
        return MasterPosisiPelayanan::getWorkloadScore($posisi);
    }
}