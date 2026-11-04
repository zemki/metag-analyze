<?php

namespace Tests\Feature;

use App\Console\Commands\NotificationChecker;
use App\Entry;
use App\Mart\MartEntry;
use App\Mart\MartProject;
use App\Mart\MartSchedule;
use Tests\TestCase;

/**
 * The daily limit for repeating questionnaires used to count main entries by
 * `inputs->_mart_metadata->questionnaire_id`, which nothing writes, so the
 * count was always 0 and the limit never stopped a notification.
 */
class NotificationDailyLimitTest extends TestCase
{
    private MartSchedule $schedule;

    protected function setUp(): void
    {
        parent::setUp();

        $this->project->update(['inputs' => json_encode([['type' => 'mart']])]);
        $martProject = MartProject::firstOrCreate(['main_project_id' => $this->project->id]);

        $this->schedule = MartSchedule::create([
            'mart_project_id' => $martProject->id,
            'questionnaire_id' => $this->project->id * 1000 + 1,
            'name' => 'Daily check-in',
            'type' => 'repeating',
            'timing_config' => [
                'daily_start_time' => '00:00',
                'daily_end_time' => '23:59',
                'max_daily_submits' => 2,
            ],
            'notification_config' => [],
        ]);
    }

    private function submitToday(MartSchedule $schedule): void
    {
        $entry = Entry::factory()->create(['case_id' => $this->case->id, 'created_at' => now()]);

        MartEntry::create([
            'main_entry_id' => $entry->id,
            'schedule_id' => $schedule->id,
            'questionnaire_id' => $schedule->questionnaire_id,
            'participant_id' => $this->case->name,
            'user_id' => $this->user->email,
            'started_at' => now(),
            'completed_at' => now(),
            'duration_ms' => 1000,
            'timezone' => 'Europe/Berlin',
            'timestamp' => now()->timestamp * 1000,
        ]);
    }

    private function shouldSend(): bool
    {
        $notification = (object) ['data' => (object) ['questionnaire_id' => $this->schedule->questionnaire_id]];
        $method = new \ReflectionMethod(NotificationChecker::class, 'shouldSendQuestionnaireNotification');

        return $method->invoke(new NotificationChecker, $this->case->fresh(), $notification);
    }

    /** @test */
    public function it_notifies_while_the_daily_limit_is_not_reached()
    {
        $this->submitToday($this->schedule);

        $this->assertTrue($this->shouldSend());
    }

    /** @test */
    public function it_stops_notifying_once_the_daily_limit_is_reached()
    {
        $this->submitToday($this->schedule);
        $this->submitToday($this->schedule);

        $this->assertFalse($this->shouldSend());
    }

    /** @test */
    public function submissions_to_another_questionnaire_do_not_count()
    {
        $other = MartSchedule::create([
            'mart_project_id' => $this->schedule->mart_project_id,
            'questionnaire_id' => $this->schedule->questionnaire_id + 1,
            'name' => 'Other',
            'type' => 'repeating',
            'timing_config' => [],
            'notification_config' => [],
        ]);

        $this->submitToday($other);
        $this->submitToday($other);

        $this->assertTrue($this->shouldSend());
    }
}
