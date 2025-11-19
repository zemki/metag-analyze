<?php

namespace App\Mart;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MartSchedule extends Model
{
    /**
     * The connection name for the model.
     */
    protected $connection = 'mart';

    /**
     * The table associated with the model.
     */
    protected $table = 'mart_schedules';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'mart_project_id',
        'questionnaire_id',
        'name',
        'introductory_text',
        'type',
        'timing_config',
        'notification_config',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'timing_config' => 'array',
        'notification_config' => 'array',
    ];

    /**
     * Get the MART project that owns this schedule.
     */
    public function martProject(): BelongsTo
    {
        return $this->belongsTo(MartProject::class, 'mart_project_id');
    }

    /**
     * Get the questions for this schedule.
     */
    public function questions(): HasMany
    {
        return $this->hasMany(MartQuestion::class, 'schedule_id')->orderBy('position');
    }

    /**
     * Get the entries for this schedule.
     */
    public function entries(): HasMany
    {
        return $this->hasMany(MartEntry::class, 'schedule_id');
    }

    /**
     * Scope to get schedules for a specific MART project.
     */
    public function scopeForProject($query, $martProjectId)
    {
        return $query->where('mart_project_id', $martProjectId);
    }

    /**
     * Scope to get single type schedules.
     */
    public function scopeSingle($query)
    {
        return $query->where('type', 'single');
    }

    /**
     * Scope to get repeating type schedules.
     */
    public function scopeRepeating($query)
    {
        return $query->where('type', 'repeating');
    }

    /**
     * Check if this is a single questionnaire.
     */
    public function isSingle(): bool
    {
        return $this->type === 'single';
    }

    /**
     * Check if this is a repeating questionnaire.
     */
    public function isRepeating(): bool
    {
        return $this->type === 'repeating';
    }

    /**
     * Convert schedule to mobile format according to martTypes.ts
     */
    public function toMobileFormat(): array
    {
        $timingConfig = $this->timing_config ?? [];
        $notificationConfig = $this->notification_config ?? [];

        // Base format for all questionnaires
        $format = [
            'questionnaireId' => $this->questionnaire_id,
            'type' => $this->type,
            'startDateAndTime' => [
                'date' => $this->formatDateForMobile($timingConfig['start_date_time']['date'] ?? null),
                'time' => $timingConfig['start_date_time']['time'] ?? '00:00',
            ],
            'showProgressBar' => $notificationConfig['show_progress_bar'] ?? false,
            'showNotifications' => $notificationConfig['show_notifications'] ?? false,
            'notificationText' => $notificationConfig['notification_text'] ?? null,
        ];

        // Add repeating-specific fields
        if ($this->isRepeating()) {
            $format['endDateAndTime'] = [
                'date' => $this->formatDateForMobile($timingConfig['end_date_time']['date'] ?? null),
                'time' => $timingConfig['end_date_time']['time'] ?? '23:59',
            ];
            $format['minBreakBetweenQuestionnaire'] = $timingConfig['min_break_between'] ?? 180;
            $format['dailyIntervalDuration'] = $timingConfig['daily_interval_duration'] ?? 4;
            $format['maxDailySubmits'] = $timingConfig['max_daily_submits'] ?? 6;
            $format['dailyStartTime'] = $timingConfig['daily_start_time'] ?? '09:00';
            $format['dailyEndTime'] = $timingConfig['daily_end_time'] ?? '21:00';
            $format['questAvailableAt'] = $timingConfig['quest_available_at'] ?? 'startOfInterval';
        }

        // Add showAfterRepeating for single questionnaires if set
        if ($this->isSingle() && isset($timingConfig['show_after_repeating'])) {
            $format['showAfterRepeatingQ'] = $timingConfig['show_after_repeating'];
        }

        return $format;
    }

    /**
     * Format date from YYYY-MM-DD to DD.MM.YYYY for mobile
     */
    private function formatDateForMobile($date)
    {
        if (! $date) {
            return null;
        }

        // If already in DD.MM.YYYY format, return as is
        if (preg_match('/^\d{2}\.\d{2}\.\d{4}$/', $date)) {
            return $date;
        }

        // Convert from YYYY-MM-DD to DD.MM.YYYY
        $timestamp = strtotime($date);
        if ($timestamp) {
            return date('d.m.Y', $timestamp);
        }

        return $date;
    }
}