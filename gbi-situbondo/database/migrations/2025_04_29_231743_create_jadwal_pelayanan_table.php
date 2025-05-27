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
        Schema::create('jadwal_pelayanan', function (Blueprint $table) {
            $table->id('id_pelayanan');
            $table->unsignedBigInteger('id_anggota');
            $table->unsignedBigInteger('id_kegiatan');
            $table->date('tanggal_pelayanan');
            $table->string('posisi')->nullable();
            $table->enum('status_konfirmasi', ['belum', 'terima', 'tolak'])->default('belum');
            $table->timestamps();
            
            $table->foreign('id_anggota')->references('id_anggota')->on('anggota');
            $table->foreign('id_kegiatan')->references('id_kegiatan')->on('kegiatan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwal_pelayanan');
    }
};
