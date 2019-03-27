<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMediaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('media', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 200);
            $table->string('description', 250);

            // json column about color in graph
            // probably not necessary?
            $table->text('properties');
            $table->integer('media_group_id')->unsigned()->references('id')->on('media_group')->onDelete('cascade');

            $table->foreign('media_group_id')->references('id')->on('media_groups');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('media');
    }
}
