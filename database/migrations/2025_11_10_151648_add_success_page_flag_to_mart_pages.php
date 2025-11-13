<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddSuccessPageFlagToMartPages extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds field to mart_pages table:
     * - is_success_page: marks page as the success/completion page
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mart')->table('mart_pages', function (Blueprint $table) {
            $table->boolean('is_success_page')->default(false)->after('sort_order');

            // Add index for performance when finding success page
            $table->index('is_success_page', 'idx_success_page');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mart')->table('mart_pages', function (Blueprint $table) {
            $table->dropIndex('idx_success_page');
            $table->dropColumn('is_success_page');
        });
    }
}
