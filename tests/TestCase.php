<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\WithFaker;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication,WithFaker;

    /**
     * @param  null  $user  Either give the user model or create a new one
     * @param  string  $roleName Default:Admin
     * @return mixed the user model
     */
    protected function signIn($user = null, $roleName = 'admin')
    {
        $user = $user ?: factory('App\User')->create();

        $role = factory('App\Role')->create(['name' => $roleName]);
        $user->roles()->sync($role);

        $this->actingAs($user);

        return $user;
    }

    protected function create_user()
    {

        $user = [
            'username' => $this->faker->name,
            'email' => $this->faker->email,
            'password' => 'test',
        ];
         $this->post('/users', $user);

         $user = \App\User::where('email', $user['email'])->first();

         return $user;
    }
}
