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
        if (!Schema::hasColumn('mart_questionnaire_schedules', 'max_total_submits')) {
            Schema::table('mart_questionnaire_schedules', function (Blueprint $table) {
                $table->integer('max_total_submits')->nullable()->after('max_daily_submits');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('mart_questionnaire_schedules', 'max_total_submits')) {
            Schema::table('mart_questionnaire_schedules', function (Blueprint $table) {
                $table->dropColumn('max_total_submits');
            });
        }
    }
};
