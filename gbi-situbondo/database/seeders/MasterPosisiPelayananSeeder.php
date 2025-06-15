<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MasterPosisiPelayanan;

class MasterPosisiPelayananSeeder extends Seeder
{
    public function run(): void
    {
        $positions = [
            // Musik
            ['nama_posisi' => 'Worship Leader', 'kategori' => 'Musik', 'urutan' => 1, 'workload_score' => 5],
            ['nama_posisi' => 'Singer', 'kategori' => 'Musik', 'urutan' => 2, 'workload_score' => 3],
            ['nama_posisi' => 'Keyboard', 'kategori' => 'Musik', 'urutan' => 3, 'workload_score' => 4],
            ['nama_posisi' => 'Guitar', 'kategori' => 'Musik', 'urutan' => 4, 'workload_score' => 4],
            ['nama_posisi' => 'Bass', 'kategori' => 'Musik', 'urutan' => 5, 'workload_score' => 4],
            ['nama_posisi' => 'Drum', 'kategori' => 'Musik', 'urutan' => 6, 'workload_score' => 4],
            
            // Teknis
            ['nama_posisi' => 'Sound System', 'kategori' => 'Teknis', 'urutan' => 1, 'workload_score' => 3],
            ['nama_posisi' => 'Multimedia', 'kategori' => 'Teknis', 'urutan' => 2, 'workload_score' => 2],
            ['nama_posisi' => 'Dokumentasi', 'kategori' => 'Teknis', 'urutan' => 3, 'workload_score' => 2],
            
            // Pelayanan
            ['nama_posisi' => 'Usher', 'kategori' => 'Pelayanan', 'urutan' => 1, 'workload_score' => 2],
            ['nama_posisi' => 'Pembaca Alkitab', 'kategori' => 'Pelayanan', 'urutan' => 2, 'workload_score' => 2],
            ['nama_posisi' => 'Pembawa Persembahan', 'kategori' => 'Pelayanan', 'urutan' => 3, 'workload_score' => 2],
        ];
        
        foreach ($positions as $position) {
            MasterPosisiPelayanan::create($position);
        }
    }
}