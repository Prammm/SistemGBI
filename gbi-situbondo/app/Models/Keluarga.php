<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Keluarga extends Model
{
    use HasFactory;
    
    protected $table = 'keluarga';
    protected $primaryKey = 'id_keluarga';
    
    protected $fillable = [
        'nama_keluarga',
    ];
    
    public function anggota()
    {
        return $this->hasMany(Anggota::class, 'id_keluarga', 'id_keluarga');
    }
}