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
        Schema::table('entries', function (Blueprint $table) {
            // Drop the existing foreign key constraint
            $table->dropForeign(['media_id']);

            // Make media_id nullable
            $table->integer('media_id')->unsigned()->nullable()->change();

            // Re-add the foreign key constraint (allowing null)
            $table->foreign('media_id')->references('id')->on('media')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('entries', function (Blueprint $table) {
            // Drop the foreign key constraint
            $table->dropForeign(['media_id']);

            // Make media_id not nullable again
            $table->integer('media_id')->unsigned()->nullable(false)->change();

            // Re-add the foreign key constraint
            $table->foreign('media_id')->references('id')->on('media')->onDelete('cascade');
        });
    }
};
