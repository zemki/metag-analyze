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
        Schema::create('stats', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('case_id');
            $table->json('android_usage_stats')->nullable();
            $table->json('android_event_stats')->nullable();
            $table->integer('ios_activations')->nullable();
            $table->integer('ios_screen_time')->nullable();
            $table->bigInteger('timestamp')->nullable(); // For millisecond precision timestamps
            $table->string('timezone')->nullable();
            $table->timestamps();

            $table->foreign('case_id')->references('id')->on('cases')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('stats');
    }
};
