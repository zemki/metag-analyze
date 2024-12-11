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
        Schema::table('users', function (Blueprint $table) {
            // Don't modify existing api_token column to maintain compatibility
            $table->timestamp('token_expires_at')->nullable()->after('api_token');
            $table->timestamp('last_login_at')->nullable()->after('token_expires_at');
            $table->integer('failed_login_attempts')->default(0)->after('last_login_at');
            $table->timestamp('lockout_until')->nullable()->after('failed_login_attempts');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'token_expires_at',
                'last_login_at',
                'failed_login_attempts',
                'lockout_until',
            ]);
        });
    }
};
