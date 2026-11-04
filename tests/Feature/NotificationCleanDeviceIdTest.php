<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * notification:cleanID runs nightly and must only clear the push tokens of
 * users whose latest case has ended. MART cases start as 'startDay:...|' with
 * no lastDay, and Cases::isConsultable() is true for those, so the tokens of
 * active participants used to be wiped every night.
 */
class NotificationCleanDeviceIdTest extends TestCase
{
    private function cleanWithDuration(string $duration): array
    {
        $this->user->forceFill(['deviceID' => ['fcm-token']])->save();
        $this->case->update(['duration' => $duration]);

        $this->artisan('notification:cleanID')->run();

        return $this->user->fresh()->deviceID;
    }

    /** @test */
    public function it_keeps_the_token_of_a_case_without_a_last_day()
    {
        $tokens = $this->cleanWithDuration('startDay:' . now()->format('d.m.Y') . '|');

        $this->assertSame(['fcm-token'], $tokens);
    }

    /** @test */
    public function it_keeps_the_token_while_the_case_is_running()
    {
        $tokens = $this->cleanWithDuration(
            'startDay:' . now()->subDay()->format('d.m.Y') . '|lastDay:' . now()->addDays(5)->format('d.m.Y') . '|'
        );

        $this->assertSame(['fcm-token'], $tokens);
    }

    /** @test */
    public function it_clears_the_token_once_the_case_has_ended()
    {
        $tokens = $this->cleanWithDuration(
            'startDay:' . now()->subDays(9)->format('d.m.Y') . '|lastDay:' . now()->subDays(2)->format('d.m.Y') . '|'
        );

        $this->assertSame([], $tokens);
    }
}
