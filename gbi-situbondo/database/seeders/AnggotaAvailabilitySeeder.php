<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Anggota;

class AnggotaAvailabilitySeeder extends Seeder
{
    public function run()
    {
        $anggotaList = Anggota::all();
        
        if ($anggotaList->isEmpty()) {
            $this->command->warn('No anggota found. Please seed anggota first.');
            return;
        }

        $availabilityPatterns = [
            // Full time - available all days
            [
                'ketersediaan_hari' => [0, 1, 2, 3, 4, 5, 6],
                'ketersediaan_jam' => ['06:00-22:00'],
                'weight' => 20
            ],
            // Weekend warrior
            [
                'ketersediaan_hari' => [0, 6],
                'ketersediaan_jam' => ['06:00-12:00', '17:00-21:00'],
                'weight' => 30
            ],
            // Weekday available
            [
                'ketersediaan_hari' => [1, 2, 3, 4, 5],
                'ketersediaan_jam' => ['17:00-21:00'],
                'weight' => 25
            ],
            // Limited weekend
            [
                'ketersediaan_hari' => [0],
                'ketersediaan_jam' => ['06:00-12:00'],
                'weight' => 15
            ],
            // Flexible schedule
            [
                'ketersediaan_hari' => [0, 3, 6],
                'ketersediaan_jam' => ['06:00-09:00', '17:00-21:00'],
                'weight' => 10
            ]
        ];

        foreach ($anggotaList as $anggota) {
            $pattern = $this->selectWeightedPattern($availabilityPatterns);
            
            $blackoutDates = [];
            // 20% chance of having blackout dates
            if (rand(1, 100) <= 20) {
                $blackoutDates = [
                    now()->addDays(rand(7, 30))->format('Y-m-d'),
                ];
                // 50% chance of additional blackout date
                if (rand(1, 100) <= 50) {
                    $blackoutDates[] = now()->addDays(rand(31, 60))->format('Y-m-d');
                }
            }

            $anggota->update([
                'ketersediaan_hari' => $pattern['ketersediaan_hari'],
                'ketersediaan_jam' => $pattern['ketersediaan_jam'],
                'blackout_dates' => $blackoutDates,
                'catatan_khusus' => $this->generateRandomNote(),
            ]);
        }

        $this->command->info('Anggota availability seeded successfully!');
    }

    private function selectWeightedPattern($patterns)
    {
        $totalWeight = array_sum(array_column($patterns, 'weight'));
        $random = rand(1, $totalWeight);
        $currentWeight = 0;

        foreach ($patterns as $pattern) {
            $currentWeight += $pattern['weight'];
            if ($random <= $currentWeight) {
                return $pattern;
            }
        }

        return $patterns[0]; // fallback
    }

    private function generateRandomNote()
    {
        $notes = [
            null, // 60% chance of no note
            null,
            null,
            null,
            null,
            null,
            'Lebih prefer pelayanan pagi',
            'Bisa backup jika diperlukan',
            'Sedang dalam masa training',
            'Pengalaman > 5 tahun',
            'Bisa lead tim jika diperlukan',
        ];

        return $notes[array_rand($notes)];
    }
}