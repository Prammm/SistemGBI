<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Anggota extends Model
{
    use HasFactory;
    
    protected $table = 'anggota';
    protected $primaryKey = 'id_anggota';
    
    protected $fillable = [
        'nama', 'tanggal_lahir', 'jenis_kelamin', 'id_keluarga', 
        'id_ortu', 'alamat', 'no_telepon', 'email'
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
    
    public function user()
    {
        return $this->hasOne(User::class, 'id_anggota', 'id_anggota');
    }
}
