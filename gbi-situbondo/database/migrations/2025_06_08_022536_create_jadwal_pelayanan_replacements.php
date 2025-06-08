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
        Schema::create('jadwal_pelayanan_replacements', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('id_jadwal_pelayanan');
            $table->unsignedBigInteger('original_assignee_id');
            $table->unsignedBigInteger('replacement_id')->nullable();
            $table->string('replacement_reason'); // 'tolak', 'sakit', 'berhalangan', etc.
            $table->enum('replacement_status', ['pending', 'assigned', 'no_replacement'])->default('pending');
            $table->text('notes')->nullable();
            $table->timestamp('requested_at');
            $table->timestamp('resolved_at')->nullable();
            $table->unsignedBigInteger('requested_by'); // user yang melakukan perubahan
            $table->timestamps();
            
            $table->foreign('id_jadwal_pelayanan')->references('id_pelayanan')->on('jadwal_pelayanan')->onDelete('cascade');
            $table->foreign('original_assignee_id')->references('id_anggota')->on('anggota')->onDelete('cascade');
            $table->foreign('replacement_id')->references('id_anggota')->on('anggota')->onDelete('set null');
            $table->foreign('requested_by')->references('id')->on('users')->onDelete('cascade');
            
            $table->index(['id_jadwal_pelayanan', 'replacement_status'], 'idx_jadwal_replacement_status');
            $table->index(['requested_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jadwal_pelayanan_replacements');
    }
};