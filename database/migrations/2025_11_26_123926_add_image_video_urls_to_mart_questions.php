<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds image_url and video_url columns to mart_questions and mart_question_history tables.
     * Per martTypes.ts, these URLs display media above the question/scale.
     */
    public function up(): void
    {
        // Add to questions table (check if columns exist - MART DB may be shared)
        if (!Schema::connection('mart')->hasColumn('mart_questions', 'image_url')) {
            Schema::connection('mart')->table('mart_questions', function (Blueprint $table) {
                $table->string('image_url')->nullable()->after('text');
            });
        }
        if (!Schema::connection('mart')->hasColumn('mart_questions', 'video_url')) {
            Schema::connection('mart')->table('mart_questions', function (Blueprint $table) {
                $table->string('video_url')->nullable()->after('image_url');
            });
        }

        // Add to history table for version tracking
        if (!Schema::connection('mart')->hasColumn('mart_question_history', 'image_url')) {
            Schema::connection('mart')->table('mart_question_history', function (Blueprint $table) {
                $table->string('image_url')->nullable()->after('text');
            });
        }
        if (!Schema::connection('mart')->hasColumn('mart_question_history', 'video_url')) {
            Schema::connection('mart')->table('mart_question_history', function (Blueprint $table) {
                $table->string('video_url')->nullable()->after('image_url');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('mart')->table('mart_questions', function (Blueprint $table) {
            $table->dropColumn(['image_url', 'video_url']);
        });

        Schema::connection('mart')->table('mart_question_history', function (Blueprint $table) {
            $table->dropColumn(['image_url', 'video_url']);
        });
    }
};
