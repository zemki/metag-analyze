<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUsersTable extends Migration {

	public function up()
	{
		Schema::create('users', function(Blueprint $table) {
			$table->increments('id');
			$table->string('username', 190)->unique();
			$table->string('email', 190)->unique()->nullable();
			$table->string('password', 255);
			$table->string('remember_token', 255)->nullable();
			$table->string('api_token', 255)
			->nullable()
			->default(null);
			$table->string('last_login_date', 255)->nullable();



			$table->timestamps();
			$table->softDeletes();

		});
	}

	public function down()
	{
		Schema::drop('users');
	}
}
