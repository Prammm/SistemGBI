<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Anggota;
use App\Models\Keluarga;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create admin keluarga
        $keluarga = Keluarga::create([
            'nama_keluarga' => 'Admin',
        ]);
        
        // Create admin anggota
        $anggota = Anggota::create([
            'nama' => 'Administrator',
            'tanggal_lahir' => '1990-01-01',
            'jenis_kelamin' => 'L',
            'id_keluarga' => $keluarga->id_keluarga,
            'alamat' => 'Alamat Admin',
            'no_telepon' => '08123456789',
            'email' => 'admin@gbisitubondo.com',
        ]);
        
        // Create admin user
        User::create([
            'name' => 'Administrator',
            'email' => 'admin@gbisitubondo.com',
            'password' => Hash::make('password'),
            'id_role' => 1, // Admin role
            'id_anggota' => $anggota->id_anggota,
        ]);
    }
}
