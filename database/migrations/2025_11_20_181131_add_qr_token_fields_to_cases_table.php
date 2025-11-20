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
        Schema::table('cases', function (Blueprint $table) {
            // QR Code Login fields (API v2 non-MART projects only)
            $table->uuid('qr_token_uuid')->nullable()->unique()->after('first_login_at');
            $table->text('qr_encrypted_data')->nullable()->after('qr_token_uuid');
            $table->timestamp('qr_token_generated_at')->nullable()->after('qr_encrypted_data');
            $table->timestamp('qr_token_revoked_at')->nullable()->after('qr_token_generated_at');
            $table->string('qr_token_revoked_reason', 500)->nullable()->after('qr_token_revoked_at');

            // Add index for faster lookups
            $table->index('qr_token_uuid');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('cases', function (Blueprint $table) {
            $table->dropIndex(['qr_token_uuid']);
            $table->dropColumn([
                'qr_token_uuid',
                'qr_encrypted_data',
                'qr_token_generated_at',
                'qr_token_revoked_at',
                'qr_token_revoked_reason'
            ]);
        });
    }
};
