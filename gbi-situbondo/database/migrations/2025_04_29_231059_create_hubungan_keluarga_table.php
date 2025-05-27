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
        Schema::create('hubungan_keluarga', function (Blueprint $table) {
            $table->id('id_hubungan_keluarga');
            $table->string('hubungan');
            $table->unsignedBigInteger('id_anggota');
            $table->unsignedBigInteger('id_anggota_tujuan');
            $table->timestamps();
            
            $table->foreign('id_anggota')->references('id_anggota')->on('anggota');
            $table->foreign('id_anggota_tujuan')->references('id_anggota')->on('anggota');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hubungan_keluarga');
    }
};
