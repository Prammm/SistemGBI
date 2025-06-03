<?php
// 2025_06_04_000001_add_availability_to_anggota_table.php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add availability fields to anggota table
        Schema::table('anggota', function (Blueprint $table) {
            $table->json('ketersediaan_hari')->nullable()->after('email');
            $table->json('ketersediaan_jam')->nullable()->after('ketersediaan_hari');
            $table->json('blackout_dates')->nullable()->after('ketersediaan_jam'); // Tanggal tidak tersedia
            $table->text('catatan_khusus')->nullable()->after('blackout_dates');
        });
    }

    public function down(): void
    {
        Schema::table('anggota', function (Blueprint $table) {
            $table->dropColumn(['ketersediaan_hari', 'ketersediaan_jam', 'blackout_dates', 'catatan_khusus']);
        });
    }
};