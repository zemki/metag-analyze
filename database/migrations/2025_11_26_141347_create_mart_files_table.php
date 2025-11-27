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
        // Check if table exists - MART DB may be shared between dev/test
        if (Schema::connection('mart')->hasTable('mart_files')) {
            return;
        }

        Schema::connection('mart')->create('mart_files', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->unsignedBigInteger('mart_entry_id')->nullable(); // Nullable until linked at submit
            $table->unsignedBigInteger('case_id'); // Links to cases table in main DB
            $table->unsignedBigInteger('project_id'); // For organizing storage
            $table->string('question_uuid')->nullable(); // Links to which question this file answers
            $table->string('file_type'); // photo, video, audio, document
            $table->string('mime_type');
            $table->string('original_name')->nullable();
            $table->string('storage_path'); // Path to encrypted file
            $table->unsignedBigInteger('size'); // File size in bytes
            $table->json('metadata')->nullable(); // Duration, dimensions, quality etc.
            $table->timestamps();

            // Index for faster lookups
            $table->index('case_id');
            $table->index('mart_entry_id');
            $table->index('question_uuid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('mart')->dropIfExists('mart_files');
    }
};
