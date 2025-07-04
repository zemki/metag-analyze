<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    use HasFactory;

    /**
     * @var array
     */
    protected $fillable = [
        'name', 'description', 'duration', 'created_by', 'is_locked', 'inputs',
        'entity_name', 'use_entity', // New entity-related fields
    ];

    // this is a recommended way to declare event handlers
    public static function boot()
    {
        parent::boot();
        static::deleting(function ($project) {
            $project->invited()->detach();
            // if the user created the project
            if ($project->created_by === auth()->user()->id && $project->cases->count() > 0) {
                foreach ($project->cases as $case) {
                    foreach ($case->entries as $entry) {
                        $entry->delete();
                    }
                    $case->delete();
                }
            }
        });
    }

    public static function getProjectInputHeadings(Project $project): array
    {
        $headings = [];
        foreach (json_decode($project->inputs) as $input) {
            $isMultipleOrOneChoice = property_exists($input, 'numberofanswer') && $input->numberofanswer > 0;
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

    /**
     * @return mixed
     */
    public function getInputs()
    {
        return json_decode($this->inputs);
    }

    /**
     * @return array
     */
    public function getAnswersInputs()
    {
        $inputs = json_decode($this->inputs);

        $availableAnswers = [];
        $idForInputs = 1;
        foreach ($inputs as $input) {
            $tempObj = [];
            if ($input->type === 'scale') {
                $tempArray = [];
                for ($i = 1; $i < 6; $i++) {
                    $tempObj['id'] = $idForInputs;
                    $tempObj['name'] = $i;
                    $tempObj['color'] = config('colors.chartCategories')[$idForInputs];
                    $tempObj['type'] = 'scale';
                    array_push($availableAnswers, (object) $tempObj);
                    $idForInputs++;
                }
                //   array_push($availableAnswers,(object)$tempArray);
            }

            if ($input->type === 'one choice' || $input->type === 'multiple choice') {
                $tempArray = [];
                foreach (array_filter($input->answers) as $key => $answer) {
                    $tempObj['id'] = $idForInputs;
                    $tempObj['name'] = $answer;
                    $tempObj['color'] = config('colors.chartCategories')[$idForInputs];
                    array_push($availableAnswers, (object) $tempObj);
                    $idForInputs++;
                }
            }

            if ($input->type === 'text') {
                $tempObj['id'] = $idForInputs;
                $tempObj['name'] = $input->name;
                $tempObj['type'] = 'text';
                $tempObj['color'] = config('colors.chartCategories')[$idForInputs];
                array_push($availableAnswers, (object) $tempObj);
                $idForInputs++;
            }
        }

        return $availableAnswers;
    }

    /**
     * @return false|mixed|null
     */
    public function getSpecificInput($name)
    {
        if ($this->inputs === '[]') {
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
        if ($this->inputs === '[]') {
            return false;
        }
        $inputNames = [];
        foreach (json_decode($this->inputs) as $input) {
            array_push($inputNames, $input->name);
        }

        return $inputNames;
    }

    /**
     * @return false|null
     */
    public function getNumberOfAnswersByQuestion($question)
    {
        if ($this->inputs === '[]') {
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

    /**
     * @return false|null
     */
    public function getAnswersByQuestion($question)
    {
        if ($this->inputs === '[]') {
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

    /**
     * @return bool
     */
    public function isEditable()
    {
        return $this->cases()->count() === 0;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function cases()
    {
        return $this->HasMany(Cases::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function notBackendcases()
    {
        return $this->HasMany(Cases::class)->where('duration', 'not like', 'value:0%');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function media()
    {
        return $this->belongsToMany(Media::class, 'media_projects');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function pages()
    {
        return $this->hasMany(MartPage::class)->ordered();
    }

    /**
     * @return string
     */
    public function path()
    {
        return "/projects/{$this->id}";
    }

    /**
     * @return Model|\Illuminate\Database\Eloquent\Relations\BelongsTo|object|null
     */
    public function created_by()
    {
        return $this->belongsTo(User::class, 'created_by')->first();
    }

    /**
     * @return Cases|Model
     */
    public function addCase($name, $duration)
    {
        return $this->cases()->create(['name' => $name, 'duration' => $duration]);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function invited()
    {
        return $this->belongsToMany(User::class, 'user_projects');
    }

    /**
     * @return User instance of the creator of the study.
     */
    public function creator()
    {
        return User::find($this->created_by);
    }
}
