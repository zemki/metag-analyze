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
        if (!Schema::hasColumn('mart_questionnaire_schedules', 'questions')) {
            Schema::table('mart_questionnaire_schedules', function (Blueprint $table) {
                $table->json('questions')->nullable()->after('questionnaire_id');
                $table->integer('questions_version')->default(1)->after('questions');
                $table->json('questions_history')->nullable()->after('questions_version');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mart_questionnaire_schedules', function (Blueprint $table) {
            $table->dropColumn(['questions', 'questions_version', 'questions_history']);
        });
    }
};
