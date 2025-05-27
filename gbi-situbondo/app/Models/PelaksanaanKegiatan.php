<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PelaksanaanKegiatan extends Model
{
    use HasFactory;
    
    protected $table = 'pelaksanaan_kegiatan';
    protected $primaryKey = 'id_pelaksanaan';
    
    protected $fillable = [
        'id_kegiatan', 'tanggal_kegiatan', 'jam_mulai', 'jam_selesai', 'lokasi',
        'is_recurring', 'recurring_type', 'recurring_day', 'recurring_end_date', 'parent_id'
    ];
    
    protected $casts = [
        'is_recurring' => 'boolean',
        'tanggal_kegiatan' => 'date',
        'recurring_end_date' => 'date',
    ];
    
    public function kegiatan()
    {
        return $this->belongsTo(Kegiatan::class, 'id_kegiatan', 'id_kegiatan');
    }
    
    public function kehadiran()
    {
        return $this->hasMany(Kehadiran::class, 'id_pelaksanaan', 'id_pelaksanaan');
    }
    
    public function parent()
    {
        return $this->belongsTo(PelaksanaanKegiatan::class, 'parent_id', 'id_pelaksanaan');
    }
    
    public function children()
    {
        return $this->hasMany(PelaksanaanKegiatan::class, 'parent_id', 'id_pelaksanaan');
    }
    
    /**
     * Generate recurring schedules
     */
    public function generateRecurringSchedules()
    {
        if (!$this->is_recurring || !$this->recurring_end_date) {
            return [];
        }
        
        $schedules = [];
        $currentDate = $this->tanggal_kegiatan->copy();
        $endDate = $this->recurring_end_date;
        
        while ($currentDate->lte($endDate)) {
            if ($this->recurring_type === 'weekly') {
                $currentDate->addWeek();
            } elseif ($this->recurring_type === 'monthly') {
                $currentDate->addMonth();
            }
            
            if ($currentDate->lte($endDate)) {
                $schedules[] = [
                    'id_kegiatan' => $this->id_kegiatan,
                    'tanggal_kegiatan' => $currentDate->format('Y-m-d'),
                    'jam_mulai' => $this->jam_mulai,
                    'jam_selesai' => $this->jam_selesai,
                    'lokasi' => $this->lokasi,
                    'is_recurring' => false,
                    'parent_id' => $this->id_pelaksanaan,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }
        
        return $schedules;
    }
    
    /**
     * Get day names in Indonesian
     */
    public static function getDayNames()
    {
        return [
            0 => 'Minggu',
            1 => 'Senin',
            2 => 'Selasa',
            3 => 'Rabu',
            4 => 'Kamis',
            5 => 'Jumat',
            6 => 'Sabtu',
        ];
    }
    
    /**
     * Get recurring type names
     */
    public static function getRecurringTypes()
    {
        return [
            'weekly' => 'Mingguan',
            'monthly' => 'Bulanan',
        ];
    }
}