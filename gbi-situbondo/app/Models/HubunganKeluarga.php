<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HubunganKeluarga extends Model
{
    use HasFactory;
    
    protected $table = 'hubungan_keluarga';
    protected $primaryKey = 'id_hubungan_keluarga';
    
    protected $fillable = [
        'hubungan', 'id_anggota', 'id_anggota_tujuan',
    ];
    
    public function anggota()
    {
        return $this->belongsTo(Anggota::class, 'id_anggota', 'id_anggota');
    }
    
    public function anggotaTujuan()
    {
        return $this->belongsTo(Anggota::class, 'id_anggota_tujuan', 'id_anggota');
    }
}
