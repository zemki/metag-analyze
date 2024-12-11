<?php

namespace App;

use App\Helpers\Helper;
use DB;
use File;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
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
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
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

        return $user;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
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
        return Helper::get_string_between($this->duration, 'value:', '|') == 0;
    }

    public function notifications(): array|DatabaseNotificationCollection
    {
        return $this->user->notifications->sortByDesc('created_at')->where('data.case', $this->id)->where('data.planning', false);
    }

    public function plannedNotifications()
    {
        //return DB::select('SELECT *  FROM notifications WHERE data NOT LIKE ? and data LIKE ? and data LIKE ?', ['%"planning":false%', '%planning%', '%"case":' . $this->id . '%']);

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
     * Accessor for entries_count, which is the count of entries
     *
     * @return int
     */
    public function getEntriesCountAttribute()
    {
        return $this->entries()->count();
    }
}
