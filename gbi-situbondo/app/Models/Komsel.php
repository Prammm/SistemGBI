<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Komsel extends Model
{
    use HasFactory;
    
    protected $table = 'komsel';
    protected $primaryKey = 'id_komsel';
    
    protected $fillable = [
        'nama_komsel', 'hari', 'jam_mulai', 'jam_selesai', 'lokasi', 'id_pemimpin',
    ];
    
    public function anggota()
    {
        return $this->belongsToMany(Anggota::class, 'anggota_komsel', 'id_komsel', 'id_anggota');
    }
    
    public function pemimpin()
    {
        return $this->belongsTo(Anggota::class, 'id_pemimpin', 'id_anggota');
    }
}
