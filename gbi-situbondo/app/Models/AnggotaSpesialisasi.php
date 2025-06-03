<?php
// App/Models/AnggotaSpesialisasi.php

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
    
    /**
     * Get all available positions
     */
    public static function getAvailablePositions()
    {
        return [
            'Worship Leader',
            'Singer',
            'Keyboard',
            'Guitar',
            'Bass',
            'Drum',
            'Sound System',
            'Multimedia',
            'Usher',
            'Pembaca Alkitab',
            'Pembawa Persembahan',
            'Dokumentasi',
        ];
    }
    
    /**
     * Get positions by category
     */
    public static function getPositionsByCategory()
    {
        return [
            'Musik' => [
                'Worship Leader',
                'Singer',
                'Keyboard',
                'Guitar',
                'Bass',
                'Drum',
            ],
            'Teknis' => [
                'Sound System',
                'Multimedia',
                'Dokumentasi',
            ],
            'Pelayanan' => [
                'Usher',
                'Pembaca Alkitab',
                'Pembawa Persembahan',
            ],
        ];
    }
    
    /**
     * Get workload score for position
     */
    public static function getWorkloadScore($posisi)
    {
        $workloadScores = [
            'Worship Leader' => 5,
            'Singer' => 3,
            'Keyboard' => 4,
            'Guitar' => 4,
            'Bass' => 4,
            'Drum' => 4,
            'Sound System' => 3,
            'Multimedia' => 2,
            'Usher' => 2,
            'Liturgos' => 3,
            'Pembaca Alkitab' => 2,
            'Pembawa Persembahan' => 2,
            'Dokumentasi' => 2,
            'Security' => 1,
            'Cleaning Service' => 1,
            'Dekorasi' => 2,
        ];
        
        return $workloadScores[$posisi] ?? 1;
    }
}