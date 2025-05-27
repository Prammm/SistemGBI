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
        Schema::create('anggota_komsel', function (Blueprint $table) {
            $table->id('id_anggota_komsel');
            $table->unsignedBigInteger('id_komsel');
            $table->unsignedBigInteger('id_anggota');
            $table->timestamps();
            
            $table->foreign('id_komsel')->references('id_komsel')->on('komsel');
            $table->foreign('id_anggota')->references('id_anggota')->on('anggota');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anggota_komsel');
    }
};
