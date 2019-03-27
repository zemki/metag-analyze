<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Project;
use App\CaseInput;


class CaseEntriesTest extends TestCase
{

    /**
     * @test
     * @return
     *
     */
    public function a_case_can_have_entries()
    {
    	$this->withoutExceptionHandling();

    }
}
