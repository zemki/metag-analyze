<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MartQuestionnaireSchedule extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     */
    protected $table = 'mart_questionnaire_schedules';

    /**
     * The attributes that are mass assignable.
     */
    protected $fillable = [
        'project_id',
        'questionnaire_id',
        'name',
        'type',
        'start_date_time',
        'end_date_time',
        'show_progress_bar',
        'show_notifications',
        'notification_text',
        'daily_interval_duration',
        'min_break_between',
        'max_daily_submits',
        'max_total_submits',
        'daily_start_time',
        'daily_end_time',
        'quest_available_at',
        'show_after_repeating',
        'is_ios_data_donation',
        'is_android_data_donation',
        'questions',
        'questions_version',
        'questions_history',
    ];

    /**
     * The attributes that should be cast.
     */
    protected $casts = [
        'start_date_time' => 'array',
        'end_date_time' => 'array',
        'show_after_repeating' => 'array',
        'show_progress_bar' => 'boolean',
        'show_notifications' => 'boolean',
        'is_ios_data_donation' => 'boolean',
        'is_android_data_donation' => 'boolean',
        'daily_interval_duration' => 'integer',
        'min_break_between' => 'integer',
        'max_daily_submits' => 'integer',
        'max_total_submits' => 'integer',
        'questionnaire_id' => 'integer',
        'questions' => 'array',
        'questions_history' => 'array',
        'questions_version' => 'integer',
    ];

    /**
     * Get the project that owns the questionnaire schedule.
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * Scope to get single questionnaires.
     */
    public function scopeSingle($query)
    {
        return $query->where('type', 'single');
    }

    /**
     * Scope to get repeating questionnaires.
     */
    public function scopeRepeating($query)
    {
        return $query->where('type', 'repeating');
    }

    /**
     * Scope to get schedules for a specific project.
     */
    public function scopeForProject($query, $projectId)
    {
        return $query->where('project_id', $projectId);
    }

    /**
     * Check if this is a repeating questionnaire.
     */
    public function isRepeating()
    {
        return $this->type === 'repeating';
    }

    /**
     * Check if this is a single questionnaire.
     */
    public function isSingle()
    {
        return $this->type === 'single';
    }

    /**
     * Format for mobile API response.
     */
    public function toMobileFormat()
    {
        $data = [
            'questionnaireId' => $this->questionnaire_id,
            'type' => $this->type,
            'startDateAndTime' => $this->formatDateTimeForMobile($this->start_date_time),
            'showProgressBar' => $this->show_progress_bar,
            'showNotifications' => $this->show_notifications,
        ];

        if ($this->notification_text) {
            $data['notificationText'] = $this->notification_text;
        }

        if ($this->isRepeating()) {
            $data['endDateAndTime'] = $this->formatDateTimeForMobile($this->end_date_time);
            $data['minBreakBetweenQuestionnaire'] = $this->min_break_between;
            $data['dailyIntervalDuration'] = $this->daily_interval_duration;
            $data['maxDailySubmits'] = $this->max_daily_submits;
            $data['dailyStartTime'] = $this->daily_start_time;
            $data['dailyEndTime'] = $this->daily_end_time;
            $data['questAvailableAt'] = $this->quest_available_at;
        }

        if ($this->isSingle() && $this->show_after_repeating) {
            $data['showAfterRepeatingQ'] = $this->show_after_repeating;
        }

        return $data;
    }

    /**
     * Update questions and increment version.
     */
    public function updateQuestions(array $newQuestions): bool
    {
        // Save current version to history if questions exist
        if ($this->questions) {
            $history = $this->questions_history ?? [];
            $history[] = [
                'version' => $this->questions_version,
                'questions' => $this->questions,
                'changed_at' => now()->toIso8601String(),
            ];
            $this->questions_history = $history;
            $this->questions_version++;
        }

        $this->questions = $newQuestions;
        return $this->save();
    }

    /**
     * Format date and time for mobile API.
     */
    private function formatDateTimeForMobile($dateTime)
    {
        if (! $dateTime) {
            return null;
        }

        // If it's already an array with date and time, format the date
        if (is_array($dateTime)) {
            if (isset($dateTime['date'])) {
                $date = $dateTime['date'];
                // Convert from YYYY-MM-DD to DD.MM.YYYY
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
                    $timestamp = strtotime($date);
                    $dateTime['date'] = date('d.m.Y', $timestamp);
                }
            }

            return $dateTime;
        }

        return $dateTime;
    }

    /**
     * Calculate end date/time based on max total submits and schedule parameters.
     *
     * @param array $startDateTime ['date' => 'YYYY-MM-DD', 'time' => 'HH:MM']
     * @param string $dailyStartTime 'HH:MM'
     * @param string $dailyEndTime 'HH:MM'
     * @param int $dailyIntervalDuration Hours between intervals
     * @param int $maxDailySubmits Maximum submissions per day
     * @param int $maxTotalSubmits Total submissions needed
     * @return array ['date' => 'YYYY-MM-DD', 'time' => 'HH:MM']
     */
    public static function calculateEndDateTime(
        array $startDateTime,
        string $dailyStartTime,
        string $dailyEndTime,
        int $dailyIntervalDuration,
        int $maxDailySubmits,
        int $maxTotalSubmits
    ): array {
        // Parse start date and time
        $startDate = \Carbon\Carbon::createFromFormat('Y-m-d H:i', $startDateTime['date'] . ' ' . $startDateTime['time']);

        // Parse daily window times
        list($dailyStartHour, $dailyStartMin) = explode(':', $dailyStartTime);
        list($dailyEndHour, $dailyEndMin) = explode(':', $dailyEndTime);

        // Calculate how many intervals fit in a full day based on the daily window
        $dailyWindowHours = ((int)$dailyEndHour * 60 + (int)$dailyEndMin) - ((int)$dailyStartHour * 60 + (int)$dailyStartMin);
        $dailyWindowMinutes = $dailyWindowHours;
        $intervalsPerFullDay = floor($dailyWindowMinutes / ($dailyIntervalDuration * 60));

        // Use the smaller of: calculated intervals per day OR maxDailySubmits
        $intervalsPerFullDay = min($intervalsPerFullDay, $maxDailySubmits);

        // Calculate remaining intervals on the first day
        $firstDayStart = $startDate->copy();
        $firstDayWindowStart = $firstDayStart->copy()->setTime((int)$dailyStartHour, (int)$dailyStartMin);
        $firstDayWindowEnd = $firstDayStart->copy()->setTime((int)$dailyEndHour, (int)$dailyEndMin);

        // Find the first interval on or after the start time
        $currentInterval = $firstDayWindowStart->copy();
        while ($currentInterval->lessThan($startDate) && $currentInterval->lessThan($firstDayWindowEnd)) {
            $currentInterval->addHours($dailyIntervalDuration);
        }

        // Count intervals remaining on first day
        $firstDayIntervals = 0;
        while ($currentInterval->lessThanOrEqualTo($firstDayWindowEnd->copy()->subHours($dailyIntervalDuration))) {
            $firstDayIntervals++;
            $currentInterval->addHours($dailyIntervalDuration);
            if ($firstDayIntervals >= $maxDailySubmits) {
                break;
            }
        }

        // Calculate how many more intervals we need
        $remainingIntervals = $maxTotalSubmits - $firstDayIntervals;

        if ($remainingIntervals <= 0) {
            // All intervals fit in the first day - find the last interval time
            $lastInterval = $firstDayWindowStart->copy();
            while ($lastInterval->lessThan($startDate)) {
                $lastInterval->addHours($dailyIntervalDuration);
            }
            // Move to the Nth interval
            for ($i = 1; $i < $maxTotalSubmits; $i++) {
                $lastInterval->addHours($dailyIntervalDuration);
            }

            return [
                'date' => $lastInterval->format('Y-m-d'),
                'time' => $lastInterval->format('H:i')
            ];
        }

        // Calculate how many full days we need after the first day
        $fullDaysNeeded = floor($remainingIntervals / $intervalsPerFullDay);
        $intervalsOnFinalDay = $remainingIntervals % $intervalsPerFullDay;

        // If there's no remainder, the last full day IS the final day
        if ($intervalsOnFinalDay == 0) {
            $fullDaysNeeded--;
            $intervalsOnFinalDay = $intervalsPerFullDay;
        }

        // Calculate the final date
        $finalDate = $startDate->copy()->addDays($fullDaysNeeded + 1); // +1 because we start counting from day after first day
        $finalDate->setTime((int)$dailyStartHour, (int)$dailyStartMin);

        // Move to the Nth interval of the final day
        for ($i = 1; $i < $intervalsOnFinalDay; $i++) {
            $finalDate->addHours($dailyIntervalDuration);
        }

        return [
            'date' => $finalDate->format('Y-m-d'),
            'time' => $finalDate->format('H:i')
        ];
    }
}
