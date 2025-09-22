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
        Schema::create('mart_pages', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // title of the page
            $table->longText('content'); // content of the page, can be html
            $table->boolean('show_on_first_app_start')->default(false); // page shown on first app start
            $table->string('button_text')->default('Continue'); // text of the button
            $table->unsignedInteger('project_id'); // reference to project (matching projects.id type)
            $table->integer('sort_order')->default(0); // for ordering pages
            $table->timestamps();

            $table->foreign('project_id')->references('id')->on('projects')->onDelete('cascade');
            $table->index(['project_id', 'sort_order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mart_pages');
    }
};
