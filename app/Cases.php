<?php

namespace App;

use App\Helpers\Helper;
use Illuminate\Database\Eloquent\Model;

class Cases extends Model
{
    protected const VALUE = "value";
    protected const PR_INPUTS = "pr_inputs";
    protected const ENTRIES = 'entries';
    protected const TITLE = 'title';
    protected const AVAILABLE = 'available';
    protected const INPUTS = 'inputs';
    protected const MULTIPLE_CHOICE = "multiple choice";
    protected const ONE_CHOICE = "one choice";
    protected const SCALE = "scale";
    protected $table = 'cases';
    protected $guarded = [];

    public static function boot()
    {
        parent::boot();
        static::deleting(function ($case)
        {

            if ($case->project->created_by === auth()->user()->id)
            {
                foreach ($case->entries as $entry)
                {
                    $entry->delete();
                }
            }
        });
    }

    /**
     * @param Cases $case
     * @return array
     */
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
        foreach (array_map('array_values', $mediaEntries) as $media)
        {
            array_push($mediaValues, [self::VALUE => $media[0], "start" => $media[1], "end" => $media[2]]);
        }
        return array($mediaValues, $availableMedia);
    }

    public function entries()
    {
        return $this->hasMany(Entry::class, 'case_id');
    }

    /**
     * @param Cases $case
     * @param       $data
     * @return array
     */
    public static function getInputValues(Cases $case, &$data): array
    {
        $entries = $case->entries()
            ->join('cases', 'entries.case_id', '=', 'cases.id')
            ->join('projects', 'cases.project_id', '=', 'projects.id')
            ->select('entries.inputs', 'entries.begin', 'entries.end', 'projects.inputs as pr_inputs')
            ->get()
            ->toArray();

        $inputType = function ($value)
        {
            return $value->type;
        };
        $availableInputs = array_map($inputType, json_decode($entries[0][self::PR_INPUTS]));
        $inputValues = [];


        foreach ($entries as $entry)
        {
            $inputs = json_decode($entry[self::INPUTS], true);
            $pr_inputs = json_decode($entry[self::PR_INPUTS], true);
            foreach ($inputs as $key => $index)
            {
                foreach ($pr_inputs as $pr_input)
                {
                    if ($pr_input['name'] === $key)
                    {
                        array_push($inputValues, [self::VALUE => $index, "type" => $pr_input['type'], "name" => $key, "start" => $entry["begin"], "end" => $entry["end"]]);
                    }
                }
            }
        }
        $availableOptions = json_decode($entries[0][self::PR_INPUTS]);
        foreach ($availableOptions as $availableOption)
        {
            $availableOptions[$availableOption->type] = $availableOption;
        }
        foreach ($availableInputs as $availableInput)
        {
            self::formatInputValues($data, $availableInput, $availableOptions, $inputValues);
            foreach ($inputValues as $inputValue)
            {
                if ($inputValue['type'] == $availableInput && $inputValue != null) array_push($data['entries']['inputs'][$availableInput], $inputValue);
            }
        }
        return array($availableInputs, $data);
    }

    /**
     * @param       $data
     * @param       $availableInput
     * @param       $availableOptions
     * @param array $inputValues
     */
    private static function formatInputValues(&$data, $availableInput, $availableOptions, array $inputValues): void
    {
        $data[self::ENTRIES][self::INPUTS][$availableInput] = array();
        $data[self::ENTRIES][self::INPUTS][$availableInput][self::TITLE] = $availableInput;
        if ($availableInput === self::MULTIPLE_CHOICE)
        {
            $data[self::ENTRIES][self::INPUTS][$availableInput][self::TITLE] = $availableInput;
            $data[self::ENTRIES][self::INPUTS][$availableInput][self::AVAILABLE] = $availableOptions[self::MULTIPLE_CHOICE]->answers;
            $data[self::ENTRIES][self::INPUTS][$availableInput][self::TITLE] = $availableOptions[self::MULTIPLE_CHOICE]->name;
        } else if ($availableInput === self::ONE_CHOICE)
        {
            $data[self::ENTRIES][self::INPUTS][$availableInput][self::AVAILABLE] = $availableOptions[self::ONE_CHOICE]->answers;
            $data[self::ENTRIES][self::INPUTS][$availableInput][self::TITLE] = $availableOptions[self::ONE_CHOICE]->name;
        } else if ($availableInput === self::SCALE)
        {
            $data[self::ENTRIES][self::INPUTS][$availableInput][self::AVAILABLE] = [0, 1, 2, 3, 4, 5];
            $data[self::ENTRIES][self::INPUTS][$availableInput][self::TITLE] = $availableOptions[self::SCALE]->name;
        } else if ($availableInput === "text")
        {
            $data[self::ENTRIES][self::INPUTS][$availableInput][self::AVAILABLE] = [];
            $data[self::ENTRIES][self::INPUTS][$availableInput][self::TITLE] = $availableOptions["text"]->name;
            // loop through the values you already have and make it part of the 'available'
            foreach ($inputValues as $inputValue)
            {
                if ($inputValue['type'] === "text")
                {
                    array_push($data[self::ENTRIES][self::INPUTS][$availableInput][self::AVAILABLE], $inputValue[self::VALUE]);
                }
            }
        }
    }

    /**
     * @param int $datetime
     * @param     $caseDuration
     * @return false|string
     */
    public static function calculateDuration(int $datetime, $caseDuration)
    {

        $sub = substr($caseDuration, strpos($caseDuration, ":") + strlen(":"), strlen($caseDuration));
        $realDuration = (int)substr($sub, 0, strpos($sub, "|"));
        return date("d.m.Y", $datetime + $realDuration * 3600);
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function path()
    {
        return "/cases/{$this->id}";
    }

    /**
     * assign a user to this case
     * this will be the user that fills the entries
     * @param $user user to assign to the case
     * @return User
     */
    public function addUser($user)
    {

        if (is_array($user))
        {
            $user = User::firstOrCreate($user);
        }
        $this->user()->associate($user);
        $this->save();
        return $user;
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * edit the case only if has no entries
     * @return bool
     */
    public function isEditable(): bool
    {
        return !$this->entries()->count() > 0;
    }

    /**
     * @return bool|string
     */
    public function isConsultable()
    {
        $timestampLastDay = strtotime($this->lastDay());
        if ($timestampLastDay == "") return "not yet logged in";
        $now = strtotime(date("Y-m-d H:i:s"));
        return $timestampLastDay < $now;
    }

    /**
     * write the duration from the database value to a readable format
     * @return string
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
        $now = strtotime(date("Y-m-d H:i:s"));
        $timestampFirstDay = strtotime($this->firstDay());
        return $this->lastDay() == "" || ($now < $timestampFirstDay);
    }

    /**
     * write the duration from the database value to a readable format
     * @return string
     */
    public function firstDay(): string
    {
        return Helper::get_string_between($this->duration, 'firstDay:', '|');
    }
}
