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
        // Check if column exists - MART DB may be shared between dev/test
        if (!Schema::connection('mart')->hasColumn('mart_pages', 'show_in_menu')) {
            Schema::connection('mart')->table('mart_pages', function (Blueprint $table) {
                $table->boolean('show_in_menu')->default(false)->after('is_success_page');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('mart')->table('mart_pages', function (Blueprint $table) {
            $table->dropColumn('show_in_menu');
        });
    }
};
