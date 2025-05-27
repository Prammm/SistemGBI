<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kehadiran extends Model
{
    use HasFactory;
    
    protected $table = 'kehadiran';
    protected $primaryKey = 'id_kehadiran';
    
    protected $fillable = [
        'id_anggota', 'id_pelaksanaan', 'waktu_absensi', 'status', 'keterangan',
    ];
    
    public function anggota()
    {
        return $this->belongsTo(Anggota::class, 'id_anggota', 'id_anggota');
    }
    
    public function pelaksanaan()
    {
        return $this->belongsTo(PelaksanaanKegiatan::class, 'id_pelaksanaan', 'id_pelaksanaan');
    }
}
