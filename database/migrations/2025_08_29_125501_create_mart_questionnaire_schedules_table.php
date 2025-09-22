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
        Schema::create('mart_questionnaire_schedules', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('project_id');
            $table->integer('questionnaire_id'); // Mobile app reference ID
            $table->string('name');
            $table->enum('type', ['single', 'repeating']);

            // Timing
            $table->json('start_date_time'); // {date: "2025-04-01", time: "09:00"}
            $table->json('end_date_time')->nullable(); // Required for repeating, optional for single

            // Notifications
            $table->boolean('show_progress_bar')->default(true);
            $table->boolean('show_notifications')->default(true);
            $table->string('notification_text')->nullable();

            // Repeating questionnaire settings (only used when type = 'repeating')
            $table->integer('daily_interval_duration')->nullable(); // hours between questionnaires
            $table->integer('min_break_between')->nullable(); // minimum minutes between questionnaires
            $table->integer('max_daily_submits')->nullable(); // max submissions per day
            $table->string('daily_start_time')->nullable(); // e.g., "06:00"
            $table->string('daily_end_time')->nullable(); // e.g., "22:00"
            $table->enum('quest_available_at', ['startOfInterval', 'randomTimeWithinInterval'])->nullable();

            // Conditional single questionnaire (only used when type = 'single')
            $table->json('show_after_repeating')->nullable(); // {repeatingQuestId: 1, showAfterAmount: 5}

            $table->timestamps();

            // Indexes and foreign keys
            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->unique(['project_id', 'questionnaire_id']); // Ensure unique questionnaire_id per project
            $table->index('type');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mart_questionnaire_schedules');
    }
};
