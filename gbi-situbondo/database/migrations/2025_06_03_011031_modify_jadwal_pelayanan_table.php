<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Buat kolom baru
        Schema::table('jadwal_pelayanan', function (Blueprint $table) {
            $table->unsignedBigInteger('id_pelaksanaan')->nullable()->after('id_kegiatan');
            $table->foreign('id_pelaksanaan')->references('id_pelaksanaan')->on('pelaksanaan_kegiatan');
            
            // Tambah kolom untuk ketersediaan pelayan
            $table->json('ketersediaan_hari')->nullable()->after('status_konfirmasi');
            $table->json('ketersediaan_jam')->nullable()->after('ketersediaan_hari');
            $table->boolean('is_reguler')->default(false)->after('ketersediaan_jam');
        });
        
        // 2. Migrate data: Untuk setiap jadwal pelayanan, cari pelaksanaan_kegiatan yang cocok
        // Ini perlu dilakukan di seeder atau melalui script terpisah
        
        // 3. Setelah migrasi data, jadikan kolom id_pelaksanaan required (di migration terpisah)
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jadwal_pelayanan', function (Blueprint $table) {
            $table->dropForeign(['id_pelaksanaan']);
            $table->dropColumn('id_pelaksanaan');
            $table->dropColumn('ketersediaan_hari');
            $table->dropColumn('ketersediaan_jam');
            $table->dropColumn('is_reguler');
        });
    }
};