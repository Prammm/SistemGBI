<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('jadwal_template', function (Blueprint $table) {
            $table->id();
            $table->string('nama_template');
            $table->text('deskripsi')->nullable();
            $table->json('posisi_required'); // Array posisi yang dibutuhkan
            $table->json('team_compatibility')->nullable(); // Rules compatibility antar posisi
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('jadwal_template');
    }
};
