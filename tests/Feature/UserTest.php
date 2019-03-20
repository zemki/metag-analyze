<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class UserTest extends TestCase
{
   use WithFaker, RefreshDatabase ;

    /**
     * A basic feature test example.
     *
     * @return void
     */
    public function testExample()
    {
        $this->withoutExceptionHandling();
        $this->signIn();

        $user = [
            'username' => 'alebelli',
            'email' => 'belli@uni-bremen.de',
            'password' => bcrypt("test")
        ];
        $this->post('/users',$user);

        $this->assertDatabaseHas('users', $user);

    }
}
