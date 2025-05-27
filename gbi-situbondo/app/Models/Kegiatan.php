<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kegiatan extends Model
{
    use HasFactory;
    
    protected $table = 'kegiatan';
    protected $primaryKey = 'id_kegiatan';
    
    protected $fillable = [
        'nama_kegiatan', 'tipe_kegiatan', 'deskripsi',
    ];
    
    public function pelaksanaan()
    {
        return $this->hasMany(PelaksanaanKegiatan::class, 'id_kegiatan', 'id_kegiatan');
    }
    
    public function jadwalPelayanan()
    {
        return $this->hasMany(JadwalPelayanan::class, 'id_kegiatan', 'id_kegiatan');
    }
}
