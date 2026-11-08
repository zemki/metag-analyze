<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const BATCH_SIZE = 500;

    /**
     * Link MART submissions to their case directly, so counting them no longer
     * needs a row in main `entries`. Existing rows get their case through
     * `main_entry_id`; rows whose main entry is gone stay empty.
     */
    public function up(): void
    {
        if (! Schema::connection('mart')->hasColumn('mart_entries', 'case_id')) {
            Schema::connection('mart')->table('mart_entries', function (Blueprint $table) {
                $table->unsignedBigInteger('case_id')->nullable()->after('main_entry_id'); // References main.cases.id (no FK - cross-DB)
                $table->index(['case_id', 'schedule_id']);
            });
        }

        // The unique index stays: MySQL allows any number of NULLs in it.
        Schema::connection('mart')->table('mart_entries', function (Blueprint $table) {
            $table->unsignedBigInteger('main_entry_id')->nullable()->change();
        });

        $this->fillCaseIds();
    }

    /**
     * Query builder, so `updated_at` keeps its value and no model hooks fire.
     */
    private function fillCaseIds(): void
    {
        DB::connection('mart')->table('mart_entries')
            ->whereNull('case_id')
            ->whereNotNull('main_entry_id')
            ->select('id', 'main_entry_id')
            ->chunkById(self::BATCH_SIZE, function ($submissions) {
                $caseIds = DB::connection('mysql')->table('entries')
                    ->whereIn('id', $submissions->pluck('main_entry_id'))
                    ->pluck('case_id', 'id');

                foreach ($submissions as $submission) {
                    if (isset($caseIds[$submission->main_entry_id])) {
                        DB::connection('mart')->table('mart_entries')
                            ->where('id', $submission->id)
                            ->update(['case_id' => $caseIds[$submission->main_entry_id]]);
                    }
                }
            });
    }

    /**
     * `main_entry_id` stays nullable: rows written after this migration may
     * have no main entry, so it can't go back to NOT NULL safely.
     */
    public function down(): void
    {
        Schema::connection('mart')->table('mart_entries', function (Blueprint $table) {
            $table->dropIndex(['case_id', 'schedule_id']);
            $table->dropColumn('case_id');
        });
    }
};
