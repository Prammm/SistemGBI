<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Anggota;
use App\Models\AnggotaSpesialisasi;

class AnggotaSpesialisasiSeeder extends Seeder
{
    public function run()
    {
        // Get existing anggota
        $anggotaList = Anggota::all();
        
        if ($anggotaList->isEmpty()) {
            $this->command->warn('No anggota found. Please seed anggota first.');
            return;
        }

        $positions = [
            'Worship Leader' => ['count' => 3, 'reguler_ratio' => 0.6],
            'Singer' => ['count' => 8, 'reguler_ratio' => 0.4],
            'Keyboard' => ['count' => 4, 'reguler_ratio' => 0.8],
            'Guitar' => ['count' => 5, 'reguler_ratio' => 0.6],
            'Bass' => ['count' => 3, 'reguler_ratio' => 0.7],
            'Drum' => ['count' => 3, 'reguler_ratio' => 0.8],
            'Sound System' => ['count' => 4, 'reguler_ratio' => 0.5],
            'Multimedia' => ['count' => 4, 'reguler_ratio' => 0.5],
            'Usher' => ['count' => 10, 'reguler_ratio' => 0.3],
            'Liturgos' => ['count' => 6, 'reguler_ratio' => 0.5],
            'Pembaca Alkitab' => ['count' => 8, 'reguler_ratio' => 0.4],
            'Pembawa Persembahan' => ['count' => 6, 'reguler_ratio' => 0.3],
            'Dokumentasi' => ['count' => 3, 'reguler_ratio' => 0.6],
            'Security' => ['count' => 4, 'reguler_ratio' => 0.5],
            'Cleaning Service' => ['count' => 5, 'reguler_ratio' => 0.4],
            'Dekorasi' => ['count' => 4, 'reguler_ratio' => 0.3],
        ];

        foreach ($positions as $posisi => $config) {
            $selectedAnggota = $anggotaList->random(min($config['count'], $anggotaList->count()));
            $regulerCount = (int) ($config['count'] * $config['reguler_ratio']);
            $regulerAssigned = 0;

            foreach ($selectedAnggota as $anggota) {
                $isReguler = $regulerAssigned < $regulerCount;
                if ($isReguler) $regulerAssigned++;

                AnggotaSpesialisasi::create([
                    'id_anggota' => $anggota->id_anggota,
                    'posisi' => $posisi,
                    'is_reguler' => $isReguler,
                    'prioritas' => $isReguler ? rand(7, 10) : rand(4, 7),
                    'catatan' => $isReguler ? 'Pemain reguler dengan pengalaman' : null,
                ]);
            }
        }

        $this->command->info('Anggota spesialisasi seeded successfully!');
    }
}