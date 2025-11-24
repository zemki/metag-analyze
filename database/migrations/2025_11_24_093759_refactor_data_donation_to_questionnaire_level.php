<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Refactor data donation flags from question level to questionnaire level.
     * This consolidates the approach: data donation questionnaires are marked at the
     * questionnaire level rather than marking individual questions.
     */
    public function up(): void
    {
        // Drop triggers first (if they exist)
        $triggers = [
            'before_insert_ios_data_collection_question',
            'before_update_ios_data_collection_question',
            'before_insert_android_data_collection_question',
            'before_update_android_data_collection_question',
        ];

        foreach ($triggers as $trigger) {
            try {
                DB::connection('mart')->unprepared("DROP TRIGGER IF EXISTS {$trigger}");
            } catch (\Exception $e) {
                // Trigger doesn't exist or no permission, continue
            }
        }

        // Remove question-level data donation columns from mart_questions
        Schema::connection('mart')->table('mart_questions', function (Blueprint $table) {
            if (Schema::connection('mart')->hasColumn('mart_questions', 'is_ios_data_collection')) {
                $table->dropIndex('idx_ios_data_collection');
                $table->dropColumn('is_ios_data_collection');
            }

            if (Schema::connection('mart')->hasColumn('mart_questions', 'is_android_data_collection')) {
                $table->dropIndex('idx_android_data_collection');
                $table->dropColumn('is_android_data_collection');
            }

            if (Schema::connection('mart')->hasColumn('mart_questions', 'item_group')) {
                $table->dropColumn('item_group');
            }
        });

        // Add questionnaire-level data donation flags to mart_schedules
        Schema::connection('mart')->table('mart_schedules', function (Blueprint $table) {
            if (!Schema::connection('mart')->hasColumn('mart_schedules', 'is_ios_data_donation')) {
                $table->boolean('is_ios_data_donation')->default(false);
            }

            if (!Schema::connection('mart')->hasColumn('mart_schedules', 'is_android_data_donation')) {
                $table->boolean('is_android_data_donation')->default(false);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove questionnaire-level flags from mart_schedules
        Schema::connection('mart')->table('mart_schedules', function (Blueprint $table) {
            if (Schema::connection('mart')->hasColumn('mart_schedules', 'is_ios_data_donation')) {
                $table->dropColumn('is_ios_data_donation');
            }

            if (Schema::connection('mart')->hasColumn('mart_schedules', 'is_android_data_donation')) {
                $table->dropColumn('is_android_data_donation');
            }
        });

        // Restore question-level data donation columns to mart_questions
        Schema::connection('mart')->table('mart_questions', function (Blueprint $table) {
            if (!Schema::connection('mart')->hasColumn('mart_questions', 'is_ios_data_collection')) {
                $table->boolean('is_ios_data_collection')->default(false);
                $table->index('is_ios_data_collection', 'idx_ios_data_collection');
            }

            if (!Schema::connection('mart')->hasColumn('mart_questions', 'is_android_data_collection')) {
                $table->boolean('is_android_data_collection')->default(false);
                $table->index('is_android_data_collection', 'idx_android_data_collection');
            }

            if (!Schema::connection('mart')->hasColumn('mart_questions', 'item_group')) {
                $table->string('item_group')->nullable();
            }
        });
    }
};
