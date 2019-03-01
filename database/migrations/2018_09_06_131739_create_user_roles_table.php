<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUserRolesTable extends Migration {

	public function up()
	{
		Schema::create('user_roles', function(Blueprint $table) {
			$table->increments('id');
			$table->integer('user_id')->unsigned()->references('id')->on('users')->onDelete('cascade');
			$table->integer('role_id')->unsigned()->references('id')->on('roles')->onDelete('cascade');
			$table->timestamps();

			$table->foreign('user_id')->references('id')->on('users');
			$table->foreign('role_id')->references('id')->on('roles');

		});
	}

	public function down()
	{
		Schema::drop('user_roles');
	}
}
