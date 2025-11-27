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
            $table->uuid('qr_token_uuid')->nullable()->unique();
            $table->text('qr_encrypted_data')->nullable();
            $table->timestamp('qr_token_generated_at')->nullable();
            $table->timestamp('qr_token_revoked_at')->nullable();
            $table->string('qr_token_revoked_reason', 500)->nullable();

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
