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
        // Cases table indexes
        Schema::table('cases', function (Blueprint $table) {
            $table->index(['project_id', 'created_at'], 'idx_cases_project_created_at');
            $table->index('user_id', 'idx_cases_user_id');
        });

        // Entries table indexes
        Schema::table('entries', function (Blueprint $table) {
            $table->index(['case_id', 'media_id'], 'idx_entries_case_media');
            $table->index(['case_id', 'begin', 'end'], 'idx_entries_case_begin_end');
        });

        // Files table indexes (table is named files_cases)
        Schema::table('files_cases', function (Blueprint $table) {
            $table->index('case_id', 'idx_files_cases_case_id');
        });

        // Media table indexes
        Schema::table('media', function (Blueprint $table) {
            $table->index('name', 'idx_media_name');
        });

        // Projects table indexes
        Schema::table('projects', function (Blueprint $table) {
            $table->index('created_by', 'idx_projects_created_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cases', function (Blueprint $table) {
            $table->dropIndex('idx_cases_project_created_at');
            $table->dropIndex('idx_cases_user_id');
        });

        Schema::table('entries', function (Blueprint $table) {
            $table->dropIndex('idx_entries_case_media');
            $table->dropIndex('idx_entries_case_begin_end');
        });

        Schema::table('files_cases', function (Blueprint $table) {
            $table->dropIndex('idx_files_cases_case_id');
        });

        Schema::table('media', function (Blueprint $table) {
            $table->dropIndex('idx_media_name');
        });

        Schema::table('projects', function (Blueprint $table) {
            $table->dropIndex('idx_projects_created_by');
        });
    }
};
