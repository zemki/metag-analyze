<?php

namespace App\Mart;

use App\Cases;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MartCaseSchedule extends Model
{
    /**
     * The connection name for the model.
     */
    protected $connection = 'mart';

    /**
     * The table associated with the model.
     */
    protected $table = 'mart_case_schedules';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'case_id',
        'schedule_id',
        'timing_overrides',
        'calculated_at',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'timing_overrides' => 'array',
        'calculated_at' => 'datetime',
    ];

    /**
     * Get the schedule that this case override belongs to.
     */
    public function schedule(): BelongsTo
    {
        return $this->belongsTo(MartSchedule::class, 'schedule_id');
    }

    /**
     * Get the case-specific overrides for a given case and schedule.
     */
    public static function getForCase(int $caseId, int $scheduleId): ?self
    {
        return static::where('case_id', $caseId)
            ->where('schedule_id', $scheduleId)
            ->first();
    }

    /**
     * Set or update the case-specific overrides for a given case and schedule.
     */
    public static function setForCase(int $caseId, int $scheduleId, array $overrides): self
    {
        return static::updateOrCreate(
            ['case_id' => $caseId, 'schedule_id' => $scheduleId],
            ['timing_overrides' => $overrides, 'calculated_at' => now()]
        );
    }

    /**
     * Get all overrides for a specific case.
     */
    public static function getAllForCase(int $caseId)
    {
        return static::where('case_id', $caseId)->get();
    }

    /**
     * Check if overrides exist for a case and schedule.
     */
    public static function hasOverrides(int $caseId, int $scheduleId): bool
    {
        return static::where('case_id', $caseId)
            ->where('schedule_id', $scheduleId)
            ->exists();
    }

    /**
     * Work out this case's per-case schedule overrides and store them.
     *
     * `start_on_first_login` starts a single questionnaire
     * `start_hours_after_login` hours after the participant's first login
     * (0 = immediately). `use_dynamic_end_date` derives an end date from how
     * many submissions the schedule asks for. Both depend on the case, so they
     * live here as overrides rather than on the schedule.
     *
     * Called once, when first_login_at is set (MeTag login, MART check-access,
     * or a structure fetch that auto-creates the case).
     */
    public static function calculateForCase(Cases $case): void
    {
        $martProject = $case->project?->martProject();

        if (! $martProject || ! $case->first_login_at) {
            return;
        }

        $schedules = MartSchedule::where('mart_project_id', $martProject->id)->get();

        foreach ($schedules as $schedule) {
            $timing = $schedule->timing_config ?? [];
            $overrides = [];

            // Single questionnaires can start relative to the participant's first login.
            if (($timing['start_on_first_login'] ?? false) && $schedule->type === 'single') {
                $hoursDelay = (int) ($timing['start_hours_after_login'] ?? 0);
                $startDateTime = $case->first_login_at->copy()->addHours($hoursDelay);

                $overrides['start_date_time'] = [
                    'date' => $startDateTime->format('Y-m-d'),
                    'time' => $startDateTime->format('H:i'),
                ];
            }

            // A dynamic end date is however long the requested submissions take.
            if ($timing['use_dynamic_end_date'] ?? false) {
                $startDate = $overrides['start_date_time']['date']
                    ?? ($timing['start_date_time']['date'] ?? null);

                if ($startDate) {
                    $maxTotalSubmits = $timing['max_total_submits'] ?? 30;
                    $maxDailySubmits = $timing['max_daily_submits'] ?? 6;

                    $durationDays = (int) ceil($maxTotalSubmits / $maxDailySubmits);
                    $endDate = Carbon::parse($startDate)->addDays($durationDays);

                    $overrides['end_date_time'] = [
                        'date' => $endDate->format('Y-m-d'),
                        'time' => $timing['daily_end_time'] ?? '21:00',
                    ];
                }
            }

            if (! empty($overrides)) {
                self::setForCase($case->id, $schedule->id, $overrides);
            }
        }
    }
}
