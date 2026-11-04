<?php

namespace Tests\Feature;

use App\MartQuestionnaireSchedule;
use Tests\TestCase;

/**
 * Characterisation tests for MartQuestionnaireSchedule::calculateEndDateTime().
 *
 * This decides when a repeating study stops accepting submissions
 * (MartQuestionnaireController uses it when no end date is given). The
 * expectations were captured from the current implementation, so they pin its
 * behaviour rather than assert the arithmetic is desirable.
 *
 * Ported from Helge's mart-web tests, where the method lives on App\Schedule.
 */
class ScheduleEndDateTest extends TestCase
{
    /**
     * @dataProvider scheduleProvider
     *
     * @test
     */
    public function it_calculates_the_end_of_a_repeating_schedule(
        array $startDateTime,
        string $dailyStart,
        string $dailyEnd,
        int $intervalHours,
        int $maxDaily,
        int $maxTotal,
        string $expectedDate,
        string $expectedTime
    ) {
        $result = MartQuestionnaireSchedule::calculateEndDateTime(
            $startDateTime,
            $dailyStart,
            $dailyEnd,
            $intervalHours,
            $maxDaily,
            $maxTotal
        );

        $this->assertSame(
            ['date' => $expectedDate, 'time' => $expectedTime],
            $result
        );
    }

    public static function scheduleProvider(): array
    {
        return [
            // All submissions fit inside the first day's window.
            'all fit on the first day' => [
                ['date' => '2025-01-01', 'time' => '09:00'], '09:00', '21:00', 4, 3, 3,
                '2025-01-01', '17:00',
            ],

            // A single submission ends where it starts.
            'a single submission' => [
                ['date' => '2025-01-01', 'time' => '09:00'], '09:00', '21:00', 4, 3, 1,
                '2025-01-01', '09:00',
            ],

            // More submissions than one day holds, spilling partway into day two.
            'spills onto the second day' => [
                ['date' => '2025-01-01', 'time' => '09:00'], '09:00', '21:00', 4, 3, 5,
                '2025-01-02', '13:00',
            ],

            // An exact multiple of the daily capacity ends on the last full day
            // rather than rolling over to an empty extra day.
            'an exact multiple of daily capacity' => [
                ['date' => '2025-01-01', 'time' => '09:00'], '09:00', '21:00', 4, 3, 6,
                '2025-01-02', '17:00',
            ],

            // Starting late in the day leaves fewer intervals on day one, so the
            // remainder pushes into the following day.
            'a late start on the first day' => [
                ['date' => '2025-01-01', 'time' => '17:00'], '09:00', '21:00', 4, 3, 4,
                '2025-01-02', '17:00',
            ],

            // A long study on a two-hour interval inside an 08:00-22:00 window.
            'a long study on a two hour interval' => [
                ['date' => '2025-03-20', 'time' => '06:00'], '08:00', '22:00', 2, 7, 20,
                '2025-03-22', '18:00',
            ],
        ];
    }

    /** @test */
    public function it_returns_the_date_and_time_keys_the_caller_expects()
    {
        $result = MartQuestionnaireSchedule::calculateEndDateTime(
            ['date' => '2025-01-01', 'time' => '09:00'], '09:00', '21:00', 4, 3, 3
        );

        // MartQuestionnaireController stores this as the schedule's
        // end_date_time, so the key names are part of the contract.
        $this->assertSame(['date', 'time'], array_keys($result));
    }
}
