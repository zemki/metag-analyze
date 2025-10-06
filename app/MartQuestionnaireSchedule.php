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
        'daily_start_time',
        'daily_end_time',
        'quest_available_at',
        'show_after_repeating',
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
        'daily_interval_duration' => 'integer',
        'min_break_between' => 'integer',
        'max_daily_submits' => 'integer',
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
}
