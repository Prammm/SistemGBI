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
        Schema::create('kehadiran', function (Blueprint $table) {
            $table->id('id_kehadiran');
            $table->unsignedBigInteger('id_anggota');
            $table->unsignedBigInteger('id_pelaksanaan');
            $table->timestamp('waktu_absensi');
            $table->enum('status', ['hadir', 'izin', 'sakit', 'alfa'])->default('hadir');
            $table->text('keterangan')->nullable();
            $table->timestamps();
            
            $table->foreign('id_anggota')->references('id_anggota')->on('anggota');
            $table->foreign('id_pelaksanaan')->references('id_pelaksanaan')->on('pelaksanaan_kegiatan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kehadiran');
    }
};
