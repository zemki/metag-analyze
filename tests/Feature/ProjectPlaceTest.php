<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Facades\Tests\Setup\ProjectFactory;
use App\Place;
use App\Project;

class ProjectPlaceTest extends TestCase
{
     /** @test */
    public function a_project_can_have_places()
    {
        $this->withoutExceptionHandling();

        $project = ProjectFactory::withPlaces(1)->create();

        $this->assertDatabaseHas('place_projects',['project_id' => $project->id]);

    }
}
