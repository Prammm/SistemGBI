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

    /**
     * Mendapatkan semua anggota keluarga yang berelasi
     */
    public function getAnggotaKeluarga()
    {
        if (!$this->id_keluarga) {
            return collect([]);
        }

        // Ambil semua anggota dalam keluarga yang sama
        $anggotaKeluarga = Anggota::where('id_keluarga', $this->id_keluarga)
            ->where('id_anggota', '!=', $this->id_anggota)
            ->with(['hubunganKeluarga.anggotaTujuan', 'orangtua'])
            ->get();

        return $anggotaKeluarga;
    }

    /**
     * Mendapatkan hubungan dengan anggota lain dalam keluarga
     */
    public function getHubunganDengan($targetAnggotaId)
    {
        // Cek hubungan langsung
        $hubungan = HubunganKeluarga::where('id_anggota', $this->id_anggota)
            ->where('id_anggota_tujuan', $targetAnggotaId)
            ->first();
        
        if ($hubungan) {
            return $hubungan->hubungan;
        }

        // Cek hubungan terbalik
        $hubunganTerbalik = HubunganKeluarga::where('id_anggota', $targetAnggotaId)
            ->where('id_anggota_tujuan', $this->id_anggota)
            ->first();

        if ($hubunganTerbalik) {
            return $this->getBalikanHubungan($hubunganTerbalik->hubungan);
        }

        // Cek hubungan orang tua-anak
        if ($this->id_ortu == $targetAnggotaId) {
            return 'Anak';
        }

        $targetAnggota = Anggota::find($targetAnggotaId);
        if ($targetAnggota && $targetAnggota->id_ortu == $this->id_anggota) {
            return 'Orang Tua';
        }

        return 'Keluarga';
    }

    /**
     * Mendapatkan balikan hubungan
     */
    private function getBalikanHubungan($hubungan)
    {
        $balikan = [
            'Kepala Keluarga' => 'Anggota Keluarga',
            'Istri' => 'Suami',
            'Suami' => 'Istri',
            'Anak' => 'Orang Tua',
            'Orang Tua' => 'Anak',
            'Saudara' => 'Saudara',
            'Kakek' => 'Cucu',
            'Nenek' => 'Cucu',
            'Cucu' => 'Kakek/Nenek',
            'Paman' => 'Keponakan',
            'Bibi' => 'Keponakan',
            'Keponakan' => 'Paman/Bibi',
        ];

        return $balikan[$hubungan] ?? 'Keluarga';
    }

    /**
     * Otomatis set keluarga berdasarkan orang tua
     */
    public static function boot()
    {
        parent::boot();

        static::saving(function ($anggota) {
            // Jika id_ortu diisi dan id_keluarga kosong, ambil keluarga dari orang tua
            if ($anggota->id_ortu && !$anggota->id_keluarga) {
                $orangtua = Anggota::find($anggota->id_ortu);
                if ($orangtua && $orangtua->id_keluarga) {
                    $anggota->id_keluarga = $orangtua->id_keluarga;
                }
            }
        });

        static::saved(function ($anggota) {
            // Setelah disimpan, buat hubungan keluarga jika id_ortu ada
            if ($anggota->id_ortu) {
                // Cek apakah hubungan sudah ada
                $existingRelation = HubunganKeluarga::where('id_anggota', $anggota->id_anggota)
                    ->where('id_anggota_tujuan', $anggota->id_ortu)
                    ->where('hubungan', 'Anak')
                    ->first();

                if (!$existingRelation) {
                    HubunganKeluarga::create([
                        'id_anggota' => $anggota->id_anggota,
                        'id_anggota_tujuan' => $anggota->id_ortu,
                        'hubungan' => 'Anak'
                    ]);
                }
            }
        });
    }
}