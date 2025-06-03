<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('anggota_spesialisasi', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_anggota');
            $table->string('posisi'); // Worship Leader, Singer, Guitar, etc.
            $table->boolean('is_reguler')->default(false); // Apakah reguler di posisi ini
            $table->integer('prioritas')->default(5); // 1-10, semakin tinggi semakin prioritas
            $table->text('catatan')->nullable();
            $table->timestamps();
            
            $table->foreign('id_anggota')->references('id_anggota')->on('anggota');
            $table->unique(['id_anggota', 'posisi']); // Satu anggota tidak bisa duplicate posisi
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('anggota_spesialisasi');
    }
};
