<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('master_posisi_pelayanan', function (Blueprint $table) {
            $table->id('id_posisi');
            $table->string('nama_posisi');
            $table->string('kategori'); // Musik, Teknis, Pelayanan, etc.
            $table->integer('urutan')->default(0); // untuk sorting
            $table->boolean('is_active')->default(true);
            $table->text('deskripsi')->nullable();
            $table->integer('workload_score')->default(1); // untuk scoring
            $table->timestamps();
            
            $table->unique('nama_posisi');
            $table->index(['kategori', 'urutan']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('master_posisi_pelayanan');
    }
};