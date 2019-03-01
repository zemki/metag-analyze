<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEntriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('entries', function (Blueprint $table) {
            $table->increments('id');
            $table->string('begin', 200);
            $table->string('end', 200);
            $table->text('content');
            $table->string('comment', 255);
            $table->integer('case_id')->unsigned()->references('id')->on('cases')->onDelete('cascade');
            $table->integer('media_id')->unsigned()->references('id')->on('media')->onDelete('cascade');
            $table->integer('place_id')->unsigned()->references('id')->on('places')->onDelete('cascade');
            $table->integer('communcation_partner_id')->unsigned()->references('id')->on('communication_partner')->onDelete('cascade');

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
        Schema::dropIfExists('entries');
    }
}
