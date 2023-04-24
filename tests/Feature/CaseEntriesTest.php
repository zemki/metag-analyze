<?php

namespace Tests\Feature;

use App\Project;
use Tests\TestCase;

class CaseEntriesTest extends TestCase
{
    /**
     * testing entry delete
     *
     * @test
     */
    public function user_can_delete_entries()
    {
        $this->withoutExceptionHandling();

        $this->signIn();

        $project = auth()->user()->projects()->create(factory(Project::class)->raw());

        $case = $project->addCase('test case', 'test duration');

        $case->addUser(auth()->user());

        $this->assertDatabaseHas('cases', [
            'user_id' => auth()->user()->id,
        ]);
    }
}
