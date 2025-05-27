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
        Schema::create('komsel', function (Blueprint $table) {
            $table->id('id_komsel');
            $table->string('nama_komsel');
            $table->string('hari');
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->string('lokasi')->nullable();
            $table->unsignedBigInteger('id_pemimpin')->nullable();
            $table->timestamps();
            
            $table->foreign('id_pemimpin')->references('id_anggota')->on('anggota');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('komsel');
    }
};
