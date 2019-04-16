<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Facades\Tests\Setup\ProjectFactory;
use App\Media;
use App\Project;

class ProjectMediaTest extends TestCase
{

    /** @test */
    public function a_project_can_have_media()
    {
        $this->withoutExceptionHandling();

        $project = ProjectFactory::withMedia(1)->create();
        $this->assertDatabaseHas('media_projects',['project_id' => $project->id]);

    }
}
