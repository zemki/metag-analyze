<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;

class UserTest extends TestCase
{
    use WithFaker, RefreshDatabase;

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
            'email' => 'belli@uni-bremen.de',
            'password' => bcrypt('1q2w3e4r5t'),
        ];
        $this->post('/users', $user);
        $user = [
            'email' => 'belli@uni-bremen.de',
        ];
        $this->assertDatabaseHas('users', $user);
    }
}
