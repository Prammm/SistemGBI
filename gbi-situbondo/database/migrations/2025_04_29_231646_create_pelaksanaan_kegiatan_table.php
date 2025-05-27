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
        Schema::create('pelaksanaan_kegiatan', function (Blueprint $table) {
            $table->id('id_pelaksanaan');
            $table->unsignedBigInteger('id_kegiatan');
            $table->date('tanggal_kegiatan');
            $table->time('jam_mulai');
            $table->time('jam_selesai');
            $table->string('lokasi')->nullable();
            $table->timestamps();
            
            $table->foreign('id_kegiatan')->references('id_kegiatan')->on('kegiatan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pelaksanaan_kegiatan');
    }
};
