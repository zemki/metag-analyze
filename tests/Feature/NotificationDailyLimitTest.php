<?php

namespace Tests\Feature;

use App\Console\Commands\NotificationChecker;
use App\Mart\MartEntry;
use App\Mart\MartProject;
use App\Mart\MartQuestion;
use App\Mart\MartSchedule;
use Tests\TestCase;

/**
 * The daily limit for repeating questionnaires used to count main entries by
 * `inputs->_mart_metadata->questionnaire_id`, which nothing writes, so the
 * count was always 0 and the limit never stopped a notification.
 *
 * Submissions go through the real endpoint here, so this breaks if a submit
 * ever stops being countable.
 */
class NotificationDailyLimitTest extends TestCase
{
    private const TOKEN = 'notification_daily_limit_token';

    private MartSchedule $schedule;

    protected function setUp(): void
    {
        parent::setUp();

        $this->project->update(['inputs' => json_encode([['type' => 'mart']])]);
        $this->user->forceFill(['api_token' => hash('sha256', self::TOKEN)])->save();

        $this->schedule = $this->addSchedule(1, [
            'daily_start_time' => '00:00',
            'daily_end_time' => '23:59',
            'max_daily_submits' => 2,
        ]);
    }

    private function addSchedule(int $number, array $timing = []): MartSchedule
    {
        $schedule = MartSchedule::create([
            'mart_project_id' => MartProject::firstOrCreate(['main_project_id' => $this->project->id])->id,
            'questionnaire_id' => $this->project->id * 1000 + $number,
            'name' => "Questionnaire {$number}",
            'type' => 'repeating',
            'timing_config' => $timing,
            'notification_config' => [],
        ]);

        MartQuestion::create([
            'schedule_id' => $schedule->id,
            'position' => 1,
            'text' => 'How was your day?',
            'type' => 'text',
            'is_mandatory' => false,
            'version' => 1,
        ]);

        return $schedule;
    }

    private function submitToday(MartSchedule $schedule): void
    {
        $this->withToken(self::TOKEN)
            ->postJson("/api/mobile/cases/{$this->case->id}/submit", [
                'projectId' => $this->project->id,
                'questionnaireId' => $schedule->questionnaire_id,
                'userId' => $this->user->email,
                'participantId' => $this->case->name,
                'questionnaireStarted' => now()->timestamp * 1000,
                'questionnaireDuration' => 1000,
                'answers' => ['1' => 'fine'],
                'timestamp' => now()->timestamp * 1000,
                'timezone' => 'Europe/Berlin',
            ])
            ->assertOk();
    }

    private function addSubmissionWithoutMainEntry(): void
    {
        MartEntry::create([
            'case_id' => $this->case->id,
            'schedule_id' => $this->schedule->id,
            'questionnaire_id' => $this->schedule->questionnaire_id,
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
        $other = $this->addSchedule(2);

        $this->submitToday($other);
        $this->submitToday($other);

        $this->assertTrue($this->shouldSend());
    }

    /** @test */
    public function submissions_without_a_main_entry_count_towards_the_limit()
    {
        $this->addSubmissionWithoutMainEntry();
        $this->addSubmissionWithoutMainEntry();

        $this->assertFalse($this->shouldSend());
    }
}
