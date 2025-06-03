<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class SchedulingHistory extends Model
{
    use HasFactory;
    
    protected $table = 'scheduling_history';
    
    protected $fillable = [
        'id_anggota',
        'posisi',
        'tanggal_pelayanan',
        'jenis_kegiatan',
        'workload_score'
    ];
    
    protected $casts = [
        'tanggal_pelayanan' => 'date',
        'workload_score' => 'integer',
    ];
    
    public function anggota()
    {
        return $this->belongsTo(Anggota::class, 'id_anggota', 'id_anggota');
    }
    
    /**
     * Create history record from jadwal pelayanan
     */
    public static function createFromJadwal(JadwalPelayanan $jadwal)
    {
        $jenisKegiatan = 'ibadah_umum'; // default
        
        if ($jadwal->pelaksanaan && $jadwal->pelaksanaan->kegiatan) {
            $namaKegiatan = strtolower($jadwal->pelaksanaan->kegiatan->nama_kegiatan);
            
            if (str_contains($namaKegiatan, 'khusus') || 
                str_contains($namaKegiatan, 'natal') || 
                str_contains($namaKegiatan, 'paskah') ||
                str_contains($namaKegiatan, 'tahun baru')) {
                $jenisKegiatan = 'ibadah_khusus';
            } elseif (str_contains($namaKegiatan, 'komsel') || 
                     str_contains($namaKegiatan, 'kelompok')) {
                $jenisKegiatan = 'komsel';
            } elseif (str_contains($namaKegiatan, 'doa') || 
                     str_contains($namaKegiatan, 'prayer')) {
                $jenisKegiatan = 'doa';
            }
        }
        
        return self::create([
            'id_anggota' => $jadwal->id_anggota,
            'posisi' => $jadwal->posisi,
            'tanggal_pelayanan' => $jadwal->tanggal_pelayanan,
            'jenis_kegiatan' => $jenisKegiatan,
            'workload_score' => AnggotaSpesialisasi::getWorkloadScore($jadwal->posisi),
        ]);
    }
    
    /**
     * Get workload distribution for period
     */
    public static function getWorkloadDistribution($startDate, $endDate)
    {
        return self::whereBetween('tanggal_pelayanan', [$startDate, $endDate])
            ->selectRaw('id_anggota, SUM(workload_score) as total_workload')
            ->groupBy('id_anggota')
            ->with('anggota')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->id_anggota => [
                    'anggota' => $item->anggota,
                    'total_workload' => $item->total_workload
                ]];
            });
    }
    
    /**
     * Get service frequency by position for period
     */
    public static function getPositionFrequency($startDate, $endDate, $posisi = null)
    {
        $query = self::whereBetween('tanggal_pelayanan', [$startDate, $endDate])
            ->selectRaw('id_anggota, posisi, COUNT(*) as frequency')
            ->groupBy(['id_anggota', 'posisi'])
            ->with('anggota');
            
        if ($posisi) {
            $query->where('posisi', $posisi);
        }
        
        return $query->get();
    }
}