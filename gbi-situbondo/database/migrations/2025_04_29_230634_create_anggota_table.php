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
        Schema::create('anggota', function (Blueprint $table) {
            $table->id('id_anggota');
            $table->string('nama');
            $table->date('tanggal_lahir');
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->unsignedBigInteger('id_keluarga')->nullable();
            $table->unsignedBigInteger('id_ortu')->nullable();
            $table->string('alamat')->nullable();
            $table->string('no_telepon')->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
            
            $table->foreign('id_keluarga')->references('id_keluarga')->on('keluarga');
            $table->foreign('id_ortu')->references('id_anggota')->on('anggota');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('anggota');
    }
};
