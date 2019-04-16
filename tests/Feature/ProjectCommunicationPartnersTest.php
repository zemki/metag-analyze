<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Project;
use App\Communication_Partner;
use Facades\Tests\Setup\ProjectFactory;

class ProjectCommunicationPartnersTest extends TestCase
{

    use RefreshDatabase;

     /** @test */
    public function a_project_can_have_communication_partners()
    {
        $this->withoutExceptionHandling();

        $project = ProjectFactory::withCommunicationPartners(1)->create();

        $this->assertDatabaseHas('communication_partner_projects',['project_id' => $project->id]);

    }
}
