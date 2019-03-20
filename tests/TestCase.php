<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\WithFaker;

abstract class TestCase extends BaseTestCase
{
	use CreatesApplication,WithFaker;

	protected function signIn($user = null)
	{
		$user = $user ?: factory('App\User')->create();
		$this->actingAs($user);

		return $user;
	}

	protected function create_user(){

		$user = [
			'username' => $this->faker->name,
			'email' => $this->faker->email,
			'password' => "test"
		];
		 $this->post('/users',$user);

		 $user = \App\User::where('email',$user['email'])->first();

		 return $user;
	}
}
