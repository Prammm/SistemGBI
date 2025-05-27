
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
        Schema::table('pelaksanaan_kegiatan', function (Blueprint $table) {
            $table->boolean('is_recurring')->default(false)->after('lokasi');
            $table->string('recurring_type')->nullable()->after('is_recurring'); // weekly, monthly
            $table->tinyInteger('recurring_day')->nullable()->after('recurring_type'); // 0=Sunday, 1=Monday, etc
            $table->date('recurring_end_date')->nullable()->after('recurring_day');
            $table->unsignedBigInteger('parent_id')->nullable()->after('recurring_end_date');
            
            $table->foreign('parent_id')->references('id_pelaksanaan')->on('pelaksanaan_kegiatan')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pelaksanaan_kegiatan', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn(['is_recurring', 'recurring_type', 'recurring_day', 'recurring_end_date', 'parent_id']);
        });
    }
};