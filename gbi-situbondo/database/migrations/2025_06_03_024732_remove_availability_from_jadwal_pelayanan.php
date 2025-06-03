<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('jadwal_pelayanan', function (Blueprint $table) {
            // Remove availability fields from jadwal_pelayanan (moved to anggota table)
            $table->dropColumn(['ketersediaan_hari', 'ketersediaan_jam']);
        });
    }

    public function down(): void
    {
        Schema::table('jadwal_pelayanan', function (Blueprint $table) {
            $table->json('ketersediaan_hari')->nullable();
            $table->json('ketersediaan_jam')->nullable();
        });
    }
};
