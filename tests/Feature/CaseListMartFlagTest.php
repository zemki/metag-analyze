<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * The case panel hides MeTag-only actions (the graph links) when the case's
 * project says is_mart_project. The case list sends that flag, although the
 * status checks load the case's real project relation.
 */
class CaseListMartFlagTest extends TestCase
{
    /** @test */
    public function the_case_list_marks_cases_of_a_mart_project()
    {
        $this->project->update([
            'inputs' => json_encode([['type' => 'mart', 'name' => 'MART Configuration', 'answers' => []]]),
        ]);

        $this->actingAs($this->user)
            ->getJson("/projects/{$this->project->id}/cases")
            ->assertOk()
            ->assertJsonPath('data.0.project.is_mart_project', true);
    }

    /** @test */
    public function the_case_list_marks_cases_of_a_metag_project()
    {
        $this->actingAs($this->user)
            ->getJson("/projects/{$this->project->id}/cases")
            ->assertOk()
            ->assertJsonPath('data.0.project.is_mart_project', false);
    }
}
