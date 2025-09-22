<?php

namespace App;

use App\Enums\CaseStatus;
use App\Helpers\Helper;
use App\Notifications\researcherNotificationToUser;
use File;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Notifications\DatabaseNotificationCollection;
use JetBrains\PhpStorm\Pure;

/**
 * Case, is a reserved keyword in most programming languages, that's why we use Cases
 */
class Cases extends Model
{
    use HasFactory;

    protected const string VALUE = 'value';

    protected const string PR_INPUTS = 'pr_inputs';

    protected const string ENTRIES = 'entries';

    protected const string TITLE = 'title';

    protected const string AVAILABLE = 'available';

    protected const string INPUTS = 'inputs';

    protected const string MULTIPLE_CHOICE = 'multiple choice';

    protected const string ONE_CHOICE = 'one choice';

    protected const string SCALE = 'scale';

    protected $table = 'cases';

    protected $appends = ['entries_count'];

    protected $guarded = [];

    public static function boot()
    {
        parent::boot();
        static::deleting(function ($case) {
            $owner = $case->project->created_by === auth()->user()->id;
            if ($owner) {
                foreach ($case->entries as $entry) {
                    $entry->delete();
                }

                foreach ($case->plannedNotifications() as $notification) {
                    $notification->delete();
                }

                foreach ($case->files as $file) {
                    File::delete($file->path);
                    $file->delete();
                }
            }
        });
    }

    public static function getMediaValues(Cases $case): array
    {
        $mediaValues = [];
        $mediaEntries = $case->entries()
            ->join('media', 'entries.media_id', '=', 'media.id')
            ->get()
            ->map
            ->only(['name', 'begin', 'end'])
            ->flatten()
            ->chunk(3)
            ->toArray();
        $availableMedia = $case->entries()
            ->leftJoin('media', 'entries.media_id', '=', 'media.id')
            ->pluck('media.name')->unique()->toArray();
        foreach (array_map('array_values', $mediaEntries) as $media) {
            array_push($mediaValues, [self::VALUE => $media[0], 'start' => $media[1], 'end' => $media[2]]);
        }

        return [$mediaValues, $availableMedia];
    }

    public function entries()
    {
        return $this->hasMany(Entry::class, 'case_id', 'id');
    }

    public static function getInputValues(Cases $case, &$data): array
    {
        $entries = $case->entries()
            ->join('cases', 'entries.case_id', '=', 'cases.id')
            ->join('projects', 'cases.project_id', '=', 'projects.id')
            ->select('entries.inputs', 'entries.begin', 'entries.end', 'projects.inputs as pr_inputs')
            ->get()
            ->toArray();
        $inputType = function ($value) {
            return $value->type;
        };
        $availableInputs = array_map($inputType, json_decode($entries[0][self::PR_INPUTS]));
        $inputValues = [];
        foreach ($entries as $entry) {
            $inputs = json_decode($entry[self::INPUTS], true);
            $project_inputs = json_decode($entry[self::PR_INPUTS], true);
            foreach ($inputs as $key => $index) {
                if ($key == 'firstValue') {
                    continue;
                }
                foreach ($project_inputs as $project_input) {
                    if ($key === 'file') {
                        $project_input['name'] = 'file';
                    }

                    if ($project_input['name'] === $key) {
                        array_push($inputValues, [self::VALUE => $index, 'type' => $project_input['type'], 'name' => $key, 'start' => $entry['begin'], 'end' => $entry['end']]);
                    }
                }
            }
        }
        $availableOptions = json_decode($entries[0][self::PR_INPUTS]);
        foreach ($availableOptions as $availableOption) {
            $availableOptions[$availableOption->type] = $availableOption;
        }

        foreach ($availableInputs as $availableInput) {
            self::formatInputValues($data, $availableInput, $availableOptions, $inputValues);
            foreach ($inputValues as $inputValue) {
                $inputIsUsedInEntries = $inputValue['type'] == $availableInput && $inputValue != null;
                if ($inputIsUsedInEntries) {
                    if ($inputValue['type'] === 'audio recording') {
                        $inputValue['value'] = 'File';
                    }
                    array_push($data['entries']['inputs'][$availableInput], $inputValue);
                }
            }
        }

        return [$availableInputs, $data];
    }

    /**
     * Provide the available values for the default additional inputs
     */
    private static function formatInputValues(&$data, $availableInput, $availableOptions, array $inputValues): void
    {
        $data[self::ENTRIES][self::INPUTS][$availableInput] = [];
        $data[self::ENTRIES][self::INPUTS][$availableInput][self::TITLE] = $availableInput;
        if ($availableInput === self::MULTIPLE_CHOICE) {
            $data[self::ENTRIES][self::INPUTS][$availableInput][self::TITLE] = $availableInput;
            $data[self::ENTRIES][self::INPUTS][$availableInput][self::AVAILABLE] = $availableOptions[self::MULTIPLE_CHOICE]->answers;
            $data[self::ENTRIES][self::INPUTS][$availableInput][self::TITLE] = $availableOptions[self::MULTIPLE_CHOICE]->name;
        } elseif ($availableInput === self::ONE_CHOICE) {
            $data[self::ENTRIES][self::INPUTS][$availableInput][self::AVAILABLE] = $availableOptions[self::ONE_CHOICE]->answers;
            $data[self::ENTRIES][self::INPUTS][$availableInput][self::TITLE] = $availableOptions[self::ONE_CHOICE]->name;
        } elseif ($availableInput === self::SCALE) {
            $data[self::ENTRIES][self::INPUTS][$availableInput][self::AVAILABLE] = ['0', '1', '2', '3', '4', '5'];
            $data[self::ENTRIES][self::INPUTS][$availableInput][self::TITLE] = $availableOptions[self::SCALE]->name;
        } elseif ($availableInput === 'text') {
            $data[self::ENTRIES][self::INPUTS][$availableInput][self::AVAILABLE] = [];
            $data[self::ENTRIES][self::INPUTS][$availableInput][self::TITLE] = $availableOptions['text']->name;
            // loop through the values you already have and make it part of the 'available'
            foreach ($inputValues as $inputValue) {
                if ($inputValue['type'] === 'text') {
                    array_push($data[self::ENTRIES][self::INPUTS][$availableInput][self::AVAILABLE], $inputValue[self::VALUE]);
                }
            }
        } elseif ($availableInput === 'audio recording') {
            $data[self::ENTRIES][self::INPUTS][$availableInput][self::AVAILABLE] = ['File', 'No File'];
            $data[self::ENTRIES][self::INPUTS][$availableInput][self::TITLE] = $availableOptions['audio recording']->name;
        }
    }

    /**
     * calcilate the duration of the case
     *
     * @return false|string
     */
    public static function calculateDuration(int $datetime, $caseDuration)
    {
        $sub = substr($caseDuration, strpos($caseDuration, ':') + strlen(':'), strlen($caseDuration));
        $realDuration = (int) substr($sub, 0, strpos($sub, '|'));

        return date('d.m.Y', $datetime + $realDuration * 3600);
    }

    /**
     * @return BelongsTo
     */
    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    /**
     * @return string
     */
    public function path()
    {
        return "/cases/{$this->id}";
    }

    /**
     * show grouped entries graph path
     *
     * @return string
     */
    public function groupedEntriesPath()
    {
        return "/groupedcases/{$this->id}";
    }

    /**
     * show distinct entries graph path
     *
     * @return string
     */
    public function distinctpath()
    {
        return "/distinctcases/{$this->id}";
    }

    /**
     * assign a user to this case
     * this will be the user that fills the entries
     *
     * @param  $user  user to assign to the case
     * @return User
     */
    public function addUser($user)
    {
        if (is_array($user)) {
            $user = User::firstOrCreate($user);
        }
        $this->user()->associate($user);
        $this->save();

        // Auto-create planned notifications for MART projects with repeating questionnaires
        $this->createAutoNotificationsForMartProject($user);

        return $user;
    }

    /**
     * Create automatic planned notifications for MART projects with repeating questionnaires
     */
    private function createAutoNotificationsForMartProject($user)
    {
        $project = $this->project;

        // First, try to use new questionnaire schedules system
        $schedules = MartQuestionnaireSchedule::forProject($project->id)->get();
        if ($schedules->isNotEmpty()) {
            $this->createNotificationsFromSchedules($user, $schedules);

            return;
        }

        // Fallback to legacy notification config for backward compatibility
        $inputs = json_decode($project->inputs, true);

        if (! $inputs || ! is_array($inputs)) {
            return;
        }

        // Find MART configuration
        $martConfig = null;
        foreach ($inputs as $input) {
            if (isset($input['type']) && $input['type'] === 'mart') {
                $martConfig = $input;
                break;
            }
        }

        if (! $martConfig || ! isset($martConfig['projectOptions'])) {
            return;
        }

        $options = $martConfig['projectOptions'];

        // Check if this is a repeating questionnaire with notification config (legacy)
        if (isset($options['questionnaireType']) &&
            $options['questionnaireType'] === 'repeating' &&
            isset($options['notificationConfig']) &&
            $options['notificationConfig']['enabled']) {

            $notificationConfig = $options['notificationConfig'];

            // Create the planning string in the format expected by the system
            $frequency = $this->mapFrequencyToText($notificationConfig['frequency']);
            $planningText = $frequency . ' at 09:00'; // Default time, can be customized

            // Create planned notification (legacy format)
            $user->notify(new researcherNotificationToUser([
                'title' => 'Study Reminder',
                'message' => $notificationConfig['text'] ?? 'You have a new questionnaire available',
                'case' => ['id' => $this->id],
                'planning' => $planningText,
            ]));
        }
    }

    /**
     * Create notifications from new questionnaire schedules system
     */
    private function createNotificationsFromSchedules($user, $schedules)
    {
        foreach ($schedules as $schedule) {
            if ($schedule->type === 'repeating' && $schedule->show_notifications) {
                // Create the planning string in the format expected by the system
                $planningText = 'Every day at ' . ($schedule->daily_start_time ?? '09:00');

                // Create planned notification with questionnaire ID
                $user->notify(new researcherNotificationToUser([
                    'title' => $schedule->name ?? 'Study Reminder',
                    'message' => $schedule->notification_text ?? 'You have a new questionnaire available',
                    'case' => ['id' => $this->id],
                    'questionnaire_id' => $schedule->questionnaire_id, // Add questionnaire tracking
                    'planning' => $planningText,
                ]));
            }
        }
    }

    /**
     * Map frequency values to text format expected by notification system
     */
    private function mapFrequencyToText($frequency)
    {
        $mapping = [
            'daily' => 'Every day',
            'every-2-days' => 'Every 2 days',
            'every-3-days' => 'Every 3 days',
            'weekly' => 'Every week',
        ];

        return $mapping[$frequency] ?? 'Every day';
    }

    /**
     * @return BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Get the stats associated with the case.
     */
    public function stats()
    {
        return $this->hasMany(Stat::class);
    }

    /**
     * edit the case only if has no entries
     */
    public function isEditable(): bool
    {
        return ! $this->entries()->count() > 0;
    }

    /**
     * Check whether right now is past the time of the last day
     *
     * @return bool
     */
    public function isConsultable()
    {
        $timestampLastDay = strtotime($this->lastDay());
        $now = strtotime(date('Y-m-d H:i:s'));

        return $timestampLastDay < $now;
    }

    /**
     * write the duration from the database value to a readable format
     */
    public function lastDay(): string
    {
        return Helper::get_string_between($this->duration, 'lastDay:', '|');
    }

    /**
     * @return bool
     */
    public function notYetStarted()
    {
        $now = strtotime(date('Y-m-d H:i:s'));
        $timestampFirstDay = strtotime($this->firstDay());

        return $this->lastDay() == '' || ($now < $timestampFirstDay);
    }

    /**
     * Get the first day of the case, when the user started by login the app
     */
    #[Pure]
    public function firstDay(): string
    {
        return Helper::get_string_between($this->duration, 'firstDay:', '|') ?? Helper::get_string_between($this->duration, 'startDay:', '|');
    }

    /**
     * Get the start day of the case, when the day is manually set
     */
    #[Pure]
    public function startDay(): string
    {
        return Helper::get_string_between($this->duration, 'startDay:', '|');
    }

    /**
     * A backend case is a case where you can fill entries only in the backend
     */
    #[Pure]
    public function isBackend(): bool
    {
        // For MART projects, cases are never considered "backend"
        // since they're designed for mobile app usage
        if ($this->project && $this->project->isMartProject()) {
            return false;
        }

        return Helper::get_string_between($this->duration, 'value:', '|') == 0;
    }

    public function notifications(): array|DatabaseNotificationCollection
    {
        return $this->user->notifications->sortByDesc('created_at')->where('data.case', $this->id)->where('data.planning', false);
    }

    public function plannedNotifications()
    {

        return Notification::whereJsonDoesntContain('data->planning', false)
            ->where('data->case', $this->id)
            ->get();

    }

    /**
     * Get the comments for the blog post.
     */
    public function files()
    {
        return $this->hasMany(Files::class, 'case_id');
    }

    /**
     * Get the current status of this case
     */
    public function getStatus(): CaseStatus
    {
        // Backend cases (standard projects only)
        if ($this->isBackend()) {
            return CaseStatus::BACKEND;
        }

        $now = strtotime(date('Y-m-d H:i:s'));
        $lastDay = $this->lastDay();
        $firstDay = $this->firstDay();

        // If no lastDay set or before firstDay, it's pending
        if (empty($lastDay) || ($firstDay && $now < strtotime($firstDay))) {
            return CaseStatus::PENDING;
        }

        // If past lastDay, it's completed
        if ($now > strtotime($lastDay)) {
            return CaseStatus::COMPLETED;
        }

        // Otherwise it's active
        return CaseStatus::ACTIVE;
    }

    /**
     * Check if case is accessible for data entry/viewing
     * For MART projects: only completed cases are accessible
     * For standard projects: active cases are accessible
     */
    public function isAccessible(): bool
    {
        $status = $this->getStatus();

        if ($this->project && $this->project->isMartProject()) {
            // MART projects: only completed cases are accessible
            return $status === CaseStatus::COMPLETED;
        }

        // Standard projects: active cases are accessible (existing logic)
        return $status === CaseStatus::ACTIVE;
    }

    /**
     * Accessor for entries_count, which is the count of entries
     *
     * @return int
     */
    public function getEntriesCountAttribute()
    {
        return $this->entries()->count();
    }
}
