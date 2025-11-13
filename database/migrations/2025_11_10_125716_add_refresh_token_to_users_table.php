<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddRefreshTokenToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * Add refresh_token and refresh_token_expires_at columns to users table
     * for implementing refresh token functionality in MART authentication flow.
     *
     * These columns are nullable and will only be used by MART mobile app.
     * Non-MART projects will not use these fields.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('refresh_token', 255)->nullable()->after('api_token');
            $table->timestamp('refresh_token_expires_at')->nullable()->after('refresh_token');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['refresh_token', 'refresh_token_expires_at']);
        });
    }
}
