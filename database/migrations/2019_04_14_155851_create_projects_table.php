<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProjectsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('projects', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 200);
            $table->string('description', 250);

            // inputs are bind to project because every case in a project needs to have same inputs
            $table->text('inputs')->nullable();
            $table->unsignedInteger('created_by')->references('id')->on('users')->onDelete('cascade');
            $table->unsignedInteger('is_locked')->default(0);
            $table->timestamps();

            $table->foreign('created_by')->references('id')->on('users');




        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('projects');
    }
}
