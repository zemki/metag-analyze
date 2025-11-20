<?php

namespace App;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

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

    /**
     * In-memory cache for MART project to avoid repeated DB queries
     *
     * @var \App\Mart\MartProject|false|null
     */
    protected $martProjectCache = null;

    // this is a recommended way to declare event handlers
    public static function boot()
    {
        parent::boot();
        static::deleting(function ($project) {
            $project->invited()->detach();

            // Delete MART data if exists (cascade handles related tables)
            $martProject = \App\Mart\MartProject::where('main_project_id', $project->id)->first();
            if ($martProject) {
                $martProject->delete();
            }

            // if the user created the project, delete main DB data
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
        $inputs = json_decode($project->inputs);

        // Skip MART configuration object if present
        foreach ($inputs as $input) {
            // Skip MART configuration object
            if (property_exists($input, 'type') && $input->type === 'mart') {
                continue;
            }

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
            // Skip MART configuration object
            if (property_exists($input, 'type') && $input->type === 'mart') {
                continue;
            }
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
                $item = property_exists($input, 'numberofanswer') ? $input->numberofanswer : null;
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
                $item = property_exists($input, 'answers') ? $input->answers : null;
                break;
            }
        }

        return $item;
    }

    /**
     * Check if the project can be edited.
     * MART projects are always editable (questions support versioning).
     * Non-MART projects are locked once cases exist.
     *
     * @return bool
     */
    public function isEditable()
    {
        // MART projects are always editable (questions support versioning)
        if ($this->isMartProject()) {
            return true;
        }

        // Non-MART projects are locked once cases exist
        return $this->cases()->count() === 0;
    }

    /**
     * @return HasMany
     */
    public function cases()
    {
        return $this->HasMany(Cases::class);
    }

    /**
     * @return HasMany
     */
    public function notBackendcases()
    {
        return $this->HasMany(Cases::class)->where('duration', 'not like', 'value:0%');
    }

    /**
     * @return BelongsToMany
     */
    public function media()
    {
        return $this->belongsToMany(Media::class, 'media_projects');
    }

    /**
     * @return HasMany
     */
    public function pages()
    {
        return $this->hasMany(MartPage::class)->ordered();
    }

    /**
     * Check if this is a MART project
     *
     * @return bool
     */
    public function isMartProject()
    {
        $inputs = json_decode($this->inputs, true);

        if (is_array($inputs)) {
            foreach ($inputs as $input) {
                if (isset($input['type']) && $input['type'] === 'mart') {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Get the MART project from the MART database (cross-DB query).
     * Returns null if this is not a MART project or no MART data exists.
     * Results are cached in-memory to avoid repeated queries during the same request.
     *
     * @return \App\Mart\MartProject|null
     */
    public function martProject()
    {
        // Check if we've already queried this during the request
        if ($this->martProjectCache === null) {
            // Query and cache result (false = not found, to distinguish from null = not checked)
            $this->martProjectCache = \App\Mart\MartProject::where('main_project_id', $this->id)->first() ?: false;
        }

        // Return null if not found (false indicates "checked but not found")
        return $this->martProjectCache === false ? null : $this->martProjectCache;
    }

    /**
     * Check if this project has MART data in the MART database.
     *
     * @return bool
     */
    public function hasMartData()
    {
        return $this->martProject() !== null;
    }

    /**
     * @return string
     */
    public function path()
    {
        return "/projects/{$this->id}";
    }

    /**
     * @return Model|BelongsTo|object|null
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
     * @return BelongsToMany
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
