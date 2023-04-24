<?php

namespace Tests\Unit;

use Tests\TestCase;

class ProjectTest extends TestCase
{
    /**
     * @test
     */
    public function it_has_a_path()
    {
        $project = factory('App\Project')->create();

        $this->assertEquals('/projects/' . $project->id, $project->path());
    }

    /** @test */
    public function it_can_add_cases()
    {
        $project = factory('App\Project')->create();
        $case = $project->addCase('Test Case');

        $this->assertCount(1, $project->cases);
        $this->assertTrue($project->cases->contains($case));
    }
}
