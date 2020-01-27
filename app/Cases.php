<?php

namespace App;

use Helper;
use Illuminate\Database\Eloquent\Model;

class Cases extends Model
{

    protected $table = 'cases';

    protected $guarded = [];

    public function entries()
    {
        return $this->hasMany(Entry::class, 'case_id');
    }

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function path()
    {
        return "/cases/{$this->id}";
    }


    /**
     * assign a user to this case
     * this will be the user that fills the entries
     * @param User $user user to assign to the case
     * @return User
     */
    public function addUser($user)
    {
        is_array($user) ? $user = \App\User::firstOrCreate($user) : $user = $user;

        $this->user()->associate($user);
        $this->save();
        return $user;
    }

    /**
     * edit the case only if has no entries
     * @return bool
     */
    public function isEditable()
    {
        return $this->entries()->count() > 0 ? false : true;
    }


    public function isConsultable()
    {
        $timestampLastDay = strtotime($this->lastDay());
        $now = strtotime(date("Y-m-d H:i:s"));

        return ($timestampLastDay < $now);
    }


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
    public function lastDay()
    {

        $duration = $this->duration;
        //$formattedString = "<p><strong class=\"title\">Duration</strong><br><strong>Hours</strong>: ";
        // $formattedString = Helper::get_string_between($this->duration, 'value:', '|');
        //$formattedString = "Days: " . Helper::get_string_between($duration, 'days:', '|');

        $lastDay = Helper::get_string_between($duration, 'lastDay:', '|');

        return $lastDay;
    }

    /**
     * write the duration from the database value to a readable format
     * @return string
     */
    public function firstDay()
    {

        $duration = $this->duration;
        //$formattedString = "<p><strong class=\"title\">Duration</strong><br><strong>Hours</strong>: ";
        // $formattedString = Helper::get_string_between($this->duration, 'value:', '|');
        //$formattedString = "Days: " . Helper::get_string_between($duration, 'days:', '|');

        $lastDay = Helper::get_string_between($duration, 'firstDay:', '|');

        return $lastDay;
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
        foreach (array_map('array_values', $mediaEntries) as $media) {
            array_push($mediaValues, ["value" => $media[0], "start" => $media[1], "end" => $media[2]]);
        }
        return array($mediaValues, $availableMedia);
    }

    /**
     * @param Cases $case
     * @param $data
     * @return array
     */
    public static function getInputValues(Cases $case, &$data): array
    {
        $entries = $case->entries()
            ->join('cases', 'entries.case_id', '=', 'cases.id')
            ->join('projects', 'cases.project_id', '=', 'projects.id')
            ->select('entries.inputs', 'entries.begin', 'entries.end', 'projects.inputs as pr_inputs')
            ->where('entries.inputs', '<>', '[]')
            ->get()
            ->toArray();


        $getInputTypeFunction = function ($o) {
            return $o->type;
        };

        $availableInputs = array_map($getInputTypeFunction, json_decode($entries[0]['pr_inputs']));
        $inputValues = [];

        foreach ($entries as $entry) {
            $inputs = json_decode($entry["inputs"], true);
            $pr_inputs = json_decode($entry["pr_inputs"], true);
            foreach ($inputs as $key => $index) {
                foreach ($pr_inputs as $pr) {
                    if ($pr['name'] == $key) array_push($inputValues, ["value" => $index, "type" => $pr['type'], "name" => $key, "start" => $entry["begin"], "end" => $entry["end"]]);
                }
            }
        }

        $availableOptions = json_decode($entries[0]['pr_inputs']);
        foreach ($availableOptions as $availableOption) {
            $availableOptions[$availableOption->type] = $availableOption;
        }
        foreach ($availableInputs as $availableInput) {
            $data['entries']['inputs'][$availableInput] = array();
            $data['entries']['inputs'][$availableInput]['title'] = $availableInput;

            if ($availableInput == "multiple choice") {
                $data['entries']['inputs'][$availableInput]['title'] = $availableInput;
                $data['entries']['inputs'][$availableInput]['available'] = $availableOptions["multiple choice"]->answers;
                $data['entries']['inputs'][$availableInput]['title'] = $availableOptions["multiple choice"]->name;
            } else if ($availableInput == "one choice") {
                $data['entries']['inputs'][$availableInput]['available'] = $availableOptions["one choice"]->answers;
                $data['entries']['inputs'][$availableInput]['title'] = $availableOptions["one choice"]->name;

            } else if ($availableInput == "scale") {
                $data['entries']['inputs'][$availableInput]['available'] = [1, 2, 3, 4, 5];
                $data['entries']['inputs'][$availableInput]['title'] = $availableOptions["scale"]->name;

            } else if ($availableInput == "text") {
                $data['entries']['inputs'][$availableInput]['available'] = [];
                $data['entries']['inputs'][$availableInput]['title'] = $availableOptions["text"]->name;

                // loop through the values you already have and make it part of the 'available'
                foreach ($inputValues as $inputValue) {
                    if ($inputValue['type'] == "text") array_push($data['entries']['inputs'][$availableInput]['available'], $inputValue['value']);
                }
            }
            foreach ($inputValues as $inputValue) {
                if ($inputValue['type'] == $availableInput) array_push($data['entries']['inputs'][$availableInput], $inputValue);
            }
        }
        return array($availableInputs, $inputValues, $data);
    }

}
