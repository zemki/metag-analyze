<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddDataCollectionFlagsToMartQuestions extends Migration
{
    /**
     * Run the migrations.
     *
     * Adds fields to mart_questions table:
     * - is_ios_data_collection: marks question as iOS data collection question
     * - is_android_data_collection: marks question as Android data collection question
     * - item_group: text field for grouping questions
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('mart')->table('mart_questions', function (Blueprint $table) {
            $table->boolean('is_ios_data_collection')->default(false)->after('is_mandatory');
            $table->boolean('is_android_data_collection')->default(false)->after('is_ios_data_collection');
            $table->string('item_group', 255)->nullable()->after('is_android_data_collection');

            // Add indexes for performance when finding marked questions
            $table->index('is_ios_data_collection', 'idx_ios_data_collection');
            $table->index('is_android_data_collection', 'idx_android_data_collection');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('mart')->table('mart_questions', function (Blueprint $table) {
            $table->dropIndex('idx_ios_data_collection');
            $table->dropIndex('idx_android_data_collection');
            $table->dropColumn(['is_ios_data_collection', 'is_android_data_collection', 'item_group']);
        });
    }
}
