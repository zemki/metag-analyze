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
        Schema::table('settings', function (Blueprint $table) {
            if (!Schema::hasColumn('settings', 'type')) {
                $table->string('type')->default('string')->after('value');
            }
            if (!Schema::hasColumn('settings', 'description')) {
                $table->text('description')->nullable()->after('type');
            }
            if (!Schema::hasColumn('settings', 'is_locked')) {
                $table->boolean('is_locked')->default(false)->after('description');
            }
            if (!Schema::hasColumn('settings', 'updated_by')) {
                $table->unsignedInteger('updated_by')->nullable()->after('is_locked');
                $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            if (Schema::hasColumn('settings', 'updated_by')) {
                $table->dropForeign(['updated_by']);
                $table->dropColumn('updated_by');
            }
            if (Schema::hasColumn('settings', 'type')) {
                $table->dropColumn('type');
            }
            if (Schema::hasColumn('settings', 'description')) {
                $table->dropColumn('description');
            }
            if (Schema::hasColumn('settings', 'is_locked')) {
                $table->dropColumn('is_locked');
            }
        });
    }
};
