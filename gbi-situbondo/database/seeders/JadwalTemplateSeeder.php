<?php
// database/seeders/JadwalTemplateSeeder.php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\JadwalTemplate;

class JadwalTemplateSeeder extends Seeder
{
    public function run()
    {
        $templates = [
            [
                'nama_template' => 'Ibadah Umum Minggu',
                'deskripsi' => 'Template untuk ibadah minggu reguler',
                'posisi_required' => [
                    'Worship Leader',
                    'Singer',
                    'Singer',
                    'Keyboard',
                    'Guitar',
                    'Drum',
                    'Sound System',
                    'Multimedia',
                    'Usher',
                    'Usher',
                    'Liturgos',
                    'Pembaca Alkitab'
                ],
                'team_compatibility' => [
                    'music_team' => ['Worship Leader', 'Singer', 'Keyboard', 'Guitar', 'Drum'],
                    'tech_team' => ['Sound System', 'Multimedia'],
                    'service_team' => ['Usher', 'Liturgos', 'Pembaca Alkitab']
                ],
                'is_active' => true,
            ],
            [
                'nama_template' => 'Ibadah Khusus (Natal/Paskah)',
                'deskripsi' => 'Template untuk ibadah besar seperti Natal, Paskah',
                'posisi_required' => [
                    'Worship Leader',
                    'Singer',
                    'Singer',
                    'Singer',
                    'Keyboard',
                    'Guitar',
                    'Bass',
                    'Drum',
                    'Sound System',
                    'Sound System',
                    'Multimedia',
                    'Multimedia',
                    'Usher',
                    'Usher',
                    'Usher',
                    'Usher',
                    'Liturgos',
                    'Pembaca Alkitab',
                    'Pembawa Persembahan',
                    'Dokumentasi',
                    'Security',
                    'Dekorasi'
                ],
                'team_compatibility' => [
                    'music_team' => ['Worship Leader', 'Singer', 'Keyboard', 'Guitar', 'Bass', 'Drum'],
                    'tech_team' => ['Sound System', 'Multimedia', 'Dokumentasi'],
                    'service_team' => ['Usher', 'Liturgos', 'Pembaca Alkitab', 'Pembawa Persembahan'],
                    'operational_team' => ['Security', 'Dekorasi']
                ],
                'is_active' => true,
            ],
            [
                'nama_template' => 'Doa Pagi/Malam',
                'deskripsi' => 'Template untuk ibadah doa sederhana',
                'posisi_required' => [
                    'Worship Leader',
                    'Keyboard',
                    'Sound System'
                ],
                'team_compatibility' => [
                    'minimal_team' => ['Worship Leader', 'Keyboard', 'Sound System']
                ],
                'is_active' => true,
            ],
            [
                'nama_template' => 'Ibadah Pemuda',
                'deskripsi' => 'Template untuk ibadah pemuda dengan musik modern',
                'posisi_required' => [
                    'Worship Leader',
                    'Singer',
                    'Singer',
                    'Keyboard',
                    'Guitar',
                    'Bass',
                    'Drum',
                    'Sound System',
                    'Multimedia',
                    'Usher'
                ],
                'team_compatibility' => [
                    'music_team' => ['Worship Leader', 'Singer', 'Keyboard', 'Guitar', 'Bass', 'Drum'],
                    'tech_team' => ['Sound System', 'Multimedia'],
                    'service_team' => ['Usher']
                ],
                'is_active' => true,
            ],
            [
                'nama_template' => 'Komsel/Kelompok Kecil',
                'deskripsi' => 'Template untuk pertemuan komsel',
                'posisi_required' => [
                    'Pemimpin Komsel',
                    'Worship Leader',
                    'Guitar'
                ],
                'team_compatibility' => [
                    'small_group' => ['Pemimpin Komsel', 'Worship Leader', 'Guitar']
                ],
                'is_active' => true,
            ]
        ];

        foreach ($templates as $template) {
            JadwalTemplate::create($template);
        }
    }
}