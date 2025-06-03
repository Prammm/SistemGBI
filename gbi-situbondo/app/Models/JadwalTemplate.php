<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class JadwalTemplate extends Model
{
    use HasFactory;
    
    protected $table = 'jadwal_template';
    
    protected $fillable = [
        'nama_template',
        'deskripsi',
        'posisi_required',
        'team_compatibility',
        'is_active'
    ];
    
    protected $casts = [
        'posisi_required' => 'array',
        'team_compatibility' => 'array',
        'is_active' => 'boolean',
    ];
    
    /**
     * Get default templates
     */
    public static function getDefaultTemplates()
    {
        return [
            [
                'nama_template' => 'Ibadah Umum',
                'deskripsi' => 'Template untuk ibadah minggu reguler',
                'posisi_required' => [
                    'Worship Leader',
                    'Singer',
                    'Keyboard',
                    'Guitar',
                    'Drum',
                    'Sound System',
                    'Multimedia',
                    'Usher',
                ],
                'team_compatibility' => [
                    'music_team' => ['Worship Leader', 'Singer', 'Keyboard', 'Guitar', 'Bass', 'Drum'],
                    'tech_team' => ['Sound System', 'Multimedia'],
                    'service_team' => ['Usher', 'Liturgos', 'Pembaca Alkitab'],
                ],
                'is_active' => true,
            ],
            [
                'nama_template' => 'Ibadah Khusus',
                'deskripsi' => 'Template untuk ibadah khusus (Natal, Paskah, dll)',
                'posisi_required' => [
                    'Worship Leader',
                    'Singer',
                    'Singer', // Multiple singers
                    'Keyboard',
                    'Guitar',
                    'Bass',
                    'Drum',
                    'Sound System',
                    'Multimedia',
                    'Usher',
                    'Usher', // Multiple ushers
                    'Liturgos',
                    'Pembaca Alkitab',
                    'Dokumentasi',
                    'Dekorasi',
                ],
                'team_compatibility' => [
                    'music_team' => ['Worship Leader', 'Singer', 'Keyboard', 'Guitar', 'Bass', 'Drum'],
                    'tech_team' => ['Sound System', 'Multimedia', 'Dokumentasi'],
                    'service_team' => ['Usher', 'Liturgos', 'Pembaca Alkitab'],
                ],
                'is_active' => true,
            ],
            [
                'nama_template' => 'Doa Pagi/Malam',
                'deskripsi' => 'Template untuk ibadah doa sederhana',
                'posisi_required' => [
                    'Worship Leader',
                    'Keyboard',
                    'Sound System',
                ],
                'team_compatibility' => [
                    'simple_team' => ['Worship Leader', 'Keyboard', 'Sound System'],
                ],
                'is_active' => true,
            ],
        ];
    }
}