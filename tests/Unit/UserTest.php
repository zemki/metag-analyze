<?php

namespace Tests\Unit;

use Illuminate\Database\Eloquent\Collection;
use Tests\TestCase;

class UserTest extends TestCase
{

    /** @test */
    public function a_user_has_projects()
    {

        $this->assertInstanceOf(Collection::class, $this->user->projects);
    }
}
