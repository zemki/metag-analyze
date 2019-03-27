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

            // right now I save date as text to avoid problems with multiple date's format expected
            $table->string('begin', 200);
            $table->string('end', 200);

            // custom inputs
            $table->text('inputs')->nullable();

            // content of the communication
            $table->text('content');

            $table->string('comment', 255);
            $table->integer('case_id')->unsigned()->references('id')->on('cases')->onDelete('cascade');
            $table->integer('media_id')->unsigned()->references('id')->on('media')->onDelete('cascade');
            $table->integer('place_id')->nullable()->unsigned()->references('id')->on('places')->onDelete('cascade');
            $table->integer('communication_partner_id')->nullable()->unsigned()->references('id')->on('communication_partner')->onDelete('cascade');

            $table->foreign('case_id')->references('id')->on('cases');
            $table->foreign('media_id')->references('id')->on('media');
            $table->foreign('place_id')->references('id')->on('places');
            $table->foreign('communication_partner_id')->references('id')->on('communication_partners');

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
