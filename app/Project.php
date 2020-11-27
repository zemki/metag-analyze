<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use stdClass;

/**
 * App\Project
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property string|null $inputs
 * @property int $created_by
 * @property int $is_locked
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Cases[] $cases
 * @property-read int|null $cases_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\User[] $invited
 * @property-read int|null $invited_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Media[] $media
 * @property-read int|null $media_count
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Project newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Project newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Project query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Project whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Project whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Project whereDescription($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Project whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Project whereInputs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Project whereIsLocked($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Project whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\Project whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Project extends Model
{
    /**
     * @var array
     */
    protected $fillable = [
        'name', 'description', 'duration', 'created_by', 'is_locked', 'inputs'
    ];

    // this is a recommended way to declare event handlers
    public static function boot()
    {
        parent::boot();
        static::deleting(function ($project) {

            $project->invited()->detach();
            // if the user created the project
            if (!app()->runningInConsole() && $project->created_by === auth()->user()->id && $project->cases->count() > 0) {

                foreach ($project->cases as $case) {
                    foreach ($case->entries as $entry) {
                        $entry->delete();
                    }
                    $case->delete();
                }
            }
        });
    }

    /**
     * @param Project $project
     * @return array
     */
    public static function getProjectInputHeadings(Project $project): array
    {
        $headings = [];
        foreach (json_decode($project->inputs) as $input) {
            $isMultipleOrOneChoice = property_exists($input, "numberofanswer") && $input->numberofanswer > 0;
            if ($isMultipleOrOneChoice) {
                for ($i = 0; $i < $input->numberofanswer; $i++) {
                    array_push($headings, $input->name);
                }
            } else {
                array_push($headings, $input->name);
            }
        }
        return $headings;
    }

    public function entries($class)
    {
        return $this->hasMany($class, 'case_id');
    }

    public function getInputs()
    {
        return json_decode($this->inputs);
    }

    public function getAnswersInputs()
    {
        $inputs = json_decode($this->inputs);

        $availableAnswers = [];
        $idForInputs = 1;
        foreach($inputs as $input)
        {
            $tempObj = [];
            if($input->type === "scale")
            {

               $tempArray = [];
                for ($i = 1; $i < 6;$i++)
                {
                    $tempObj['id'] = $idForInputs;
                    $tempObj['name'] = $i;
                    $tempObj['color'] = config('colors.chartCategories')[$idForInputs];
                    $tempObj['type'] = 'scale';
                    array_push($availableAnswers,(object)$tempObj);
                    $idForInputs++;
                }
             //   array_push($availableAnswers,(object)$tempArray);
            }

            if($input->type === "one choice" || $input->type === "multiple choice" )
            {

                $tempArray = [];
                foreach (array_filter($input->answers) as $key => $answer)
                {
                    $tempObj['id'] = $idForInputs;
                    $tempObj['name'] = $answer;
                    $tempObj['color'] = config('colors.chartCategories')[$idForInputs];
                    array_push($availableAnswers,(object)$tempObj);
                    $idForInputs++;
                }

            }

            if($input->type === "text")
            {
                $tempObj['id'] = $idForInputs;
                $tempObj['name'] = $input->name;
                $tempObj['type'] = 'text';
                $tempObj['color'] = config('colors.chartCategories')[$idForInputs];
                array_push($availableAnswers,(object)$tempObj);
                $idForInputs++;
            }
        }


        return $availableAnswers;
    }

    public function getSpecificInput($name)
    {
        if ($this->inputs === "[]") {
            return false;
        }
        $item = null;
        foreach (json_decode($this->inputs) as $input) {
            if ($name === $input->name) {
                $item = $input;
                break;
            }
        }
        return $item;
    }

    /**
     * @return array|bool
     */
    public function getProjectInputNames()
    {
        if ($this->inputs === "[]") {
            return false;
        }
        $inputNames = [];
        foreach (json_decode($this->inputs) as $input) {
            array_push($inputNames, $input->name);
        }
        return $inputNames;
    }

    public function getNumberOfAnswersByQuestion($question)
    {
        if ($this->inputs === "[]") {
            return false;
        }
        $item = null;
        foreach (json_decode($this->inputs) as $input) {
            if ($question === $input->name) {
                $item = $input->numberofanswer;
                break;
            }
        }
        return $item;
    }

    public function getAnswersByQuestion($question)
    {
        if ($this->inputs === "[]") {
            return false;
        }
        $item = null;
        $inputs = json_decode($this->inputs);
        foreach ($inputs as $input) {
            if ($question === $input->name) {
                $item = $input->answers;
                break;
            }
        }
        return $item;
    }

    public function isEditable()
    {
        return $this->cases()->count() === 0;
    }

    public function cases()
    {
        return $this->HasMany(Cases::class);
    }

    public function media()
    {
        return $this->belongsToMany(Media::class, 'media_projects');
    }

    public function path()
    {
        return "/projects/{$this->id}";
    }

    public function created_by()
    {
        return $this->belongsTo(User::class, 'created_by')->first();
    }

    public function addCase($name, $duration)
    {
        return $this->cases()->firstOrCreate(['name' => $name, 'duration' => $duration]);
    }

    public function invited()
    {
        return $this->belongsToMany(User::class, 'user_projects');
    }
}
