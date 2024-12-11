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

        $this->assertEquals('/projects/' . $this->project->id, $this->project->path());
    }

    /** @test */
    public function it_can_add_cases()
    {

        $case = $this->project->addCase('Test Case', time());

        // 1 is added on project creation
        $this->assertCount(2, $this->project->cases);
        $this->assertTrue($this->project->cases->contains($case));
    }
}
