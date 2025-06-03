<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scheduling_history', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_anggota');
            $table->string('posisi');
            $table->date('tanggal_pelayanan');
            $table->string('jenis_kegiatan'); // ibadah_umum, ibadah_khusus, etc.
            $table->integer('workload_score')->default(1); // Bobot beban kerja untuk posisi ini
            $table->timestamps();
            
            $table->foreign('id_anggota')->references('id_anggota')->on('anggota');
            $table->index(['id_anggota', 'tanggal_pelayanan']);
            $table->index(['posisi', 'tanggal_pelayanan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scheduling_history');
    }
};
