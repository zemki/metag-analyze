<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds introductory_text field to mart_schedules table.
     * This text will be displayed at the top of the questionnaire.
     */
    public function up(): void
    {
        if (!Schema::connection('mart')->hasColumn('mart_schedules', 'introductory_text')) {
            Schema::connection('mart')->table('mart_schedules', function (Blueprint $table) {
                $table->text('introductory_text')->nullable()->after('name');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::connection('mart')->table('mart_schedules', function (Blueprint $table) {
            $table->dropColumn('introductory_text');
        });
    }
};
