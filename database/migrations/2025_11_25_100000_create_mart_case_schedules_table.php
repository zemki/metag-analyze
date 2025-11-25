<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Creates mart_case_schedules table in MART database for per-case date overrides.
     */
    public function up(): void
    {
        if (!Schema::connection('mart')->hasTable('mart_case_schedules')) {
            Schema::connection('mart')->create('mart_case_schedules', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('case_id'); // Reference to cases.id in main DB (no FK - cross-DB)
                $table->foreignId('schedule_id')->constrained('mart_schedules')->cascadeOnDelete();
                $table->json('timing_overrides'); // Per-case date overrides (start_date_time, end_date_time)
                $table->timestamp('calculated_at')->nullable(); // When dates were calculated
                $table->timestamps();

                $table->unique(['case_id', 'schedule_id']); // One record per case-schedule pair
                $table->index('case_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('mart')->dropIfExists('mart_case_schedules');
    }
};
