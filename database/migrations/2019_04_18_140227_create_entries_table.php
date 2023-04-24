<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
/*            $table->text('content');

            $table->string('comment', 255)->nullable();*/
            $table->integer('case_id')->unsigned()->references('id')->on('cases')->onDelete('cascade');
            $table->integer('media_id')->unsigned()->references('id')->on('media')->onDelete('cascade');

            $table->foreign('case_id')->references('id')->on('cases');
            $table->foreign('media_id')->references('id')->on('media');

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
