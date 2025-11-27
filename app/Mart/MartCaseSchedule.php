<?php

namespace App\Mart;

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
}
