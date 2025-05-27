<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AnggotaKomsel extends Model
{
    use HasFactory;
    
    protected $table = 'anggota_komsel';
    protected $primaryKey = 'id_anggota_komsel';
    
    protected $fillable = [
        'id_komsel', 'id_anggota',
    ];
    
    public function komsel()
    {
        return $this->belongsTo(Komsel::class, 'id_komsel', 'id_komsel');
    }
    
    public function anggota()
    {
        return $this->belongsTo(Anggota::class, 'id_anggota', 'id_anggota');
    }
}
