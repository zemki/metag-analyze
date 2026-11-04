<?php

namespace Tests\Feature;

use App\Cases;
use App\Enums\CaseStatus;
use Tests\TestCase;

/**
 * Case status is derived from the `duration` string rather than a column, so
 * these pin the three MART states against the two key spellings that string
 * uses in practice.
 *
 * The pending cases are the regression guard: Cases::firstDay() coalesced its
 * `firstDay:` lookup to a `startDay:` fallback with `??`, but
 * Helper::get_string_between() returns an empty string when the key is absent
 * and never null, so the fallback was unreachable. Every case written with
 * `startDay:` reported no first day, and getStatus() could not call it pending.
 *
 * Ported from Helge's mart-web tests.
 */
class CaseStatusTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->project->update(['inputs' => json_encode([['type' => 'mart']])]);
    }

    private function caseWithDuration(string $duration): Cases
    {
        return Cases::factory()->create([
            'project_id' => $this->project->id,
            'user_id' => $this->user->id,
            'duration' => $duration,
        ]);
    }

    /** @test */
    public function a_case_whose_last_day_has_passed_is_completed()
    {
        $case = $this->caseWithDuration(
            'startDay:' . now()->subDays(60)->format('d.m.Y') . '|lastDay:' . now()->subDays(30)->format('d.m.Y')
        );

        $this->assertSame(CaseStatus::COMPLETED, $case->getStatus());
    }

    /** @test */
    public function a_case_starting_in_the_future_is_pending_with_start_day()
    {
        $case = $this->caseWithDuration(
            'startDay:' . now()->addDays(30)->format('d.m.Y') . '|lastDay:' . now()->addDays(90)->format('d.m.Y')
        );

        $this->assertSame(CaseStatus::PENDING, $case->getStatus());
    }

    /** @test */
    public function a_case_starting_in_the_future_is_pending_with_first_day()
    {
        $case = $this->caseWithDuration(
            'firstDay:' . now()->addDays(30)->format('d.m.Y') . '|lastDay:' . now()->addDays(90)->format('d.m.Y')
        );

        $this->assertSame(CaseStatus::PENDING, $case->getStatus());
    }

    /** @test */
    public function a_case_inside_its_window_is_active()
    {
        $case = $this->caseWithDuration(
            'startDay:' . now()->subDays(10)->format('d.m.Y') . '|lastDay:' . now()->addDays(10)->format('d.m.Y')
        );

        $this->assertSame(CaseStatus::ACTIVE, $case->getStatus());
    }

    /** @test */
    public function first_day_falls_back_to_the_start_day_spelling()
    {
        $case = $this->caseWithDuration('startDay:01.03.2026|lastDay:01.04.2026');

        $this->assertSame('01.03.2026', $case->firstDay());
    }

    /** @test */
    public function mart_cases_are_never_backend_cases()
    {
        $case = $this->caseWithDuration('value:0|startDay:01.03.2026|lastDay:01.04.2026');

        $this->assertFalse($case->isBackend());
    }
}
