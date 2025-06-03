<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JadwalPelayanan extends Model
{
    use HasFactory;
    
    protected $table = 'jadwal_pelayanan';
    protected $primaryKey = 'id_pelayanan';
    
    protected $fillable = [
        'id_anggota', 
        'id_kegiatan', 
        'id_pelaksanaan', 
        'tanggal_pelayanan', 
        'posisi', 
        'status_konfirmasi',
        'ketersediaan_hari',
        'ketersediaan_jam',
        'is_reguler'
    ];
    
    protected $casts = [
        'ketersediaan_hari' => 'array',
        'ketersediaan_jam' => 'array',
        'is_reguler' => 'boolean',
    ];
    
    public function anggota()
    {
        return $this->belongsTo(Anggota::class, 'id_anggota', 'id_anggota');
    }
    
    public function kegiatan()
    {
        return $this->belongsTo(Kegiatan::class, 'id_kegiatan', 'id_kegiatan');
    }
    
    public function pelaksanaan()
    {
        return $this->belongsTo(PelaksanaanKegiatan::class, 'id_pelaksanaan', 'id_pelaksanaan');
    }
    
    /**
     * Memeriksa apakah anggota tersedia pada waktu tertentu
     */
    public function isAvailable($day, $startTime, $endTime)
    {
        // Jika tidak ada ketersediaan, anggap tersedia
        if (empty($this->ketersediaan_hari) || empty($this->ketersediaan_jam)) {
            return true;
        }
        
        // Cek apakah hari tersedia
        if (!in_array($day, $this->ketersediaan_hari)) {
            return false;
        }
        
        // Cek apakah jam tersedia
        foreach ($this->ketersediaan_jam as $availableTime) {
            list($availStart, $availEnd) = explode('-', $availableTime);
            
            // Jika waktu pelayanan berada dalam rentang ketersediaan
            if ($startTime >= $availStart && $endTime <= $availEnd) {
                return true;
            }
        }
        
        return false;
    }
}