<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCasesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cases', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 200);

            // insert many to many with
            // media
            // places
            // communication partners

            // how long the users can submit data
            $table->string('duration', 100);

            $table->integer('project_id')->unsigned()->references('id')->on('projects')->onDelete('cascade');
            $table->integer('user_id')->nullable()->unsigned()->references('id')->on('users')->onDelete('cascade');
            $table->timestamps();

            $table->foreign('project_id')->references('id')->on('projects');
            $table->foreign('user_id')->references('id')->on('users');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cases');
    }
}
