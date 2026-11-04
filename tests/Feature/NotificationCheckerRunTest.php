<?php

namespace Tests\Feature;

use App\Cases;
use App\Notifications\researcherNotificationToUser;
use App\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

/**
 * One bad planned notification must not stop the run for everyone after it.
 * These failures stayed hidden while the scheduler crashed on every tick.
 */
class NotificationCheckerRunTest extends TestCase
{
    private const FIRST_ROW = '00000000-0000-0000-0000-000000000001';

    private const LAST_ROW = 'ffffffff-ffff-ffff-ffff-ffffffffffff';

    protected function setUp(): void
    {
        parent::setUp();

        Notification::fake();

        // The command compares the planned time with the wall clock by minute,
        // so do not start a test on the edge of one.
        if ((int) date('s') >= 57) {
            sleep(4);
        }
    }

    private function planNotification(string $id, int $caseId, int $userId): void
    {
        DB::table('notifications')->insert([
            'id' => $id,
            'type' => researcherNotificationToUser::class,
            'notifiable_type' => User::class,
            'notifiable_id' => $userId,
            'data' => json_encode([
                'title' => 'Reminder',
                'message' => 'Time for your check-in',
                'case' => $caseId,
                'planning' => 'Every day at ' . date('H:i'),
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function runChecker(): int
    {
        return $this->artisan('notifications:check')->run();
    }

    /** @test */
    public function a_participant_without_a_profile_still_gets_the_notification()
    {
        $this->assertNull($this->user->profile);
        $this->planNotification(self::FIRST_ROW, $this->case->id, $this->user->id);

        $this->assertSame(0, $this->runChecker());

        Notification::assertSentTo($this->user, researcherNotificationToUser::class);
    }

    /** @test */
    public function a_planned_notification_for_a_deleted_case_is_removed()
    {
        $this->planNotification(self::FIRST_ROW, 999999999, $this->user->id);

        $this->assertSame(0, $this->runChecker());

        $this->assertDatabaseMissing('notifications', ['id' => self::FIRST_ROW]);
    }

    /** @test */
    public function a_deleted_case_does_not_stop_the_notifications_after_it()
    {
        $this->planNotification(self::FIRST_ROW, 999999999, $this->user->id);
        $this->planNotification(self::LAST_ROW, $this->case->id, $this->user->id);

        $this->runChecker();

        Notification::assertSentTo($this->user, researcherNotificationToUser::class);
    }

    /** @test */
    public function a_case_without_a_user_does_not_stop_the_notifications_after_it()
    {
        $orphan = Cases::factory()->create([
            'project_id' => $this->project->id,
            'user_id' => null,
            'duration' => 'value:24|days:1',
        ]);
        $this->planNotification(self::FIRST_ROW, $orphan->id, $this->user->id);
        $this->planNotification(self::LAST_ROW, $this->case->id, $this->user->id);

        $this->runChecker();

        Notification::assertSentTo($this->user, researcherNotificationToUser::class);
    }
}
