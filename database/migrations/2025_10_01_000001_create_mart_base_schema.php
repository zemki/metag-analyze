<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Creates all MART tables in the separate MART database.
     */
    public function up(): void
    {
        // Create mart_projects table - links to main.projects
        if (!Schema::connection('mart')->hasTable('mart_projects')) {
            Schema::connection('mart')->create('mart_projects', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('main_project_id'); // References main.projects.id (no FK - cross-DB)
            $table->timestamps();

            $table->index('main_project_id');
            $table->unique('main_project_id'); // One-to-one with main project
            });
        }

        // Create mart_schedules table - questionnaire schedules with improved structure
        if (!Schema::connection('mart')->hasTable('mart_schedules')) {
            Schema::connection('mart')->create('mart_schedules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mart_project_id')->constrained()->onDelete('cascade');
            $table->integer('questionnaire_id'); // Mobile app reference ID
            $table->string('name');
            $table->enum('type', ['single', 'repeating']);

            // Timing configuration stored as JSON
            $table->json('timing_config'); // Contains: start_date_time, end_date_time, intervals, etc.

            // Notification configuration stored as JSON
            $table->json('notification_config'); // Contains: show_progress_bar, show_notifications, text

            $table->timestamps();

            $table->unique(['mart_project_id', 'questionnaire_id']);
            $table->index('type');
            });
        }

        // Create mart_questions table - individual questions with UUIDs
        if (!Schema::connection('mart')->hasTable('mart_questions')) {
            Schema::connection('mart')->create('mart_questions', function (Blueprint $table) {
            $table->uuid('uuid')->primary();
            $table->foreignId('schedule_id')->constrained('mart_schedules')->onDelete('cascade');
            $table->integer('position'); // Order within schedule
            $table->text('text'); // Question text
            $table->string('type'); // number, range, text, one choice, multiple choice
            $table->json('config')->nullable(); // Type-specific config (minValue, maxValue, answers, etc.)
            $table->boolean('is_mandatory')->default(true);
            $table->integer('version')->default(1); // Version number
            $table->timestamps();

            $table->index(['schedule_id', 'position']);
            $table->index('version');
            });
        }

        // Create mart_question_history table - tracks question changes
        if (!Schema::connection('mart')->hasTable('mart_question_history')) {
            Schema::connection('mart')->create('mart_question_history', function (Blueprint $table) {
            $table->id();
            $table->uuid('question_uuid'); // References mart_questions.uuid (no FK - for flexibility)
            $table->integer('version');
            $table->text('text');
            $table->string('type');
            $table->json('config')->nullable();
            $table->boolean('is_mandatory');
            $table->timestamp('changed_at');

            $table->index(['question_uuid', 'version']);
            });
        }

        // Create mart_pages table - instruction pages
        if (!Schema::connection('mart')->hasTable('mart_pages')) {
            Schema::connection('mart')->create('mart_pages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mart_project_id')->constrained()->onDelete('cascade');
            $table->string('name'); // Page title
            $table->longText('content'); // HTML content
            $table->boolean('show_on_first_app_start')->default(false);
            $table->string('button_text')->default('Continue');
            $table->integer('sort_order')->default(0);
            $table->timestamps();

            $table->index(['mart_project_id', 'sort_order']);
            });
        }

        // Create mart_entries table - submission metadata
        if (!Schema::connection('mart')->hasTable('mart_entries')) {
            Schema::connection('mart')->create('mart_entries', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('main_entry_id'); // References main.entries.id (no FK - cross-DB)
            $table->foreignId('schedule_id')->constrained('mart_schedules')->onDelete('cascade');
            $table->integer('questionnaire_id');
            $table->string('participant_id'); // From main.cases.name
            $table->string('user_id'); // Email
            $table->timestamp('started_at');
            $table->timestamp('completed_at');
            $table->integer('duration_ms'); // Duration in milliseconds
            $table->string('timezone');
            $table->bigInteger('timestamp'); // Original timestamp from mobile
            $table->timestamps();

            $table->unique('main_entry_id'); // One-to-one with main entry
            $table->index(['schedule_id', 'participant_id']);
            $table->index('questionnaire_id');
            });
        }

        // Create mart_answers table - individual answers linked to question UUIDs
        if (!Schema::connection('mart')->hasTable('mart_answers')) {
            Schema::connection('mart')->create('mart_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('entry_id')->constrained('mart_entries')->onDelete('cascade');
            $table->uuid('question_uuid'); // References mart_questions.uuid (no FK - historical flexibility)
            $table->integer('question_version'); // Which version was answered
            $table->text('answer_value'); // Stores answer as string/JSON
            $table->timestamps();

            $table->index(['entry_id', 'question_uuid']);
            $table->index(['question_uuid', 'question_version']);
            });
        }

        // Create mart_stats table - usage statistics
        if (!Schema::connection('mart')->hasTable('mart_stats')) {
            Schema::connection('mart')->create('mart_stats', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mart_project_id')->constrained()->onDelete('cascade');
            $table->string('participant_id'); // From main.cases.name
            $table->string('user_id'); // Email
            $table->json('android_usage_stats')->nullable();
            $table->json('android_event_stats')->nullable();
            $table->integer('ios_activations')->nullable();
            $table->integer('ios_screen_time')->nullable();
            $table->json('ios_stats')->nullable();
            $table->text('device_id')->nullable();
            $table->bigInteger('timestamp');
            $table->string('timezone');
            $table->timestamps();

            $table->index(['mart_project_id', 'participant_id']);
            $table->index('timestamp');
            });
        }

        // Create mart_device_info table - device information per participant
        if (!Schema::connection('mart')->hasTable('mart_device_info')) {
            Schema::connection('mart')->create('mart_device_info', function (Blueprint $table) {
            $table->id();
            $table->string('participant_id'); // From main.cases.name
            $table->string('user_id'); // Email
            $table->string('os'); // android/ios
            $table->string('os_version');
            $table->string('model');
            $table->string('manufacturer');
            $table->timestamp('last_updated');
            $table->timestamps();

            $table->index('participant_id');
            $table->index('user_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop tables in reverse order (respect foreign keys)
        Schema::connection('mart')->dropIfExists('mart_device_info');
        Schema::connection('mart')->dropIfExists('mart_stats');
        Schema::connection('mart')->dropIfExists('mart_answers');
        Schema::connection('mart')->dropIfExists('mart_entries');
        Schema::connection('mart')->dropIfExists('mart_pages');
        Schema::connection('mart')->dropIfExists('mart_question_history');
        Schema::connection('mart')->dropIfExists('mart_questions');
        Schema::connection('mart')->dropIfExists('mart_schedules');
        Schema::connection('mart')->dropIfExists('mart_projects');
    }
};