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
        'id_anggota', 'id_kegiatan', 'tanggal_pelayanan', 'posisi', 'status_konfirmasi',
    ];
    
    public function anggota()
    {
        return $this->belongsTo(Anggota::class, 'id_anggota', 'id_anggota');
    }
    
    public function kegiatan()
    {
        return $this->belongsTo(Kegiatan::class, 'id_kegiatan', 'id_kegiatan');
    }
}