<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds page_type column to mart_pages for special purpose pages:
     * - success: shown after questionnaire completion
     * - android_stats_permission: instructions for Android log data access
     * - android_notification_permission: Android notification permission instructions
     * - ios_notification_permission: iOS notification permission instructions
     */
    public function up(): void
    {
        // Check if column exists - MART DB may be shared between dev/test
        if (!Schema::connection('mart')->hasColumn('mart_pages', 'page_type')) {
            Schema::connection('mart')->table('mart_pages', function (Blueprint $table) {
                $table->string('page_type')->nullable()->after('show_in_menu');
            });

            // Migrate existing is_success_page data to page_type
            DB::connection('mart')->table('mart_pages')
                ->where('is_success_page', true)
                ->update(['page_type' => 'success']);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::connection('mart')->hasColumn('mart_pages', 'page_type')) {
            // Migrate page_type back to is_success_page before dropping
            DB::connection('mart')->table('mart_pages')
                ->where('page_type', 'success')
                ->update(['is_success_page' => true]);

            Schema::connection('mart')->table('mart_pages', function (Blueprint $table) {
                $table->dropColumn('page_type');
            });
        }
    }
};
