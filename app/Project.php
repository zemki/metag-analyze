<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = [
        'name', 'description', 'duration', 'created_by', 'is_locked', 'inputs'
    ];


    public function entries($class)
    {
        return $this->hasMany($class, 'case_id');
    }

    /*public function getInputsAttribute($value)
    {
      return is_array($value) ? $value : (array) json_decode($value);
    }*/

    public function getInputs()
    {
        return json_decode($this->inputs);
    }

    public function getSpecificInput($name)
    {
        if ($this->inputs == "[]") return false;
        $item = null;
        foreach (json_decode($this->inputs) as $input) {
            if ($name == $input->name) {
                $item = $input;
                break;
            }
        }
        return $item;
    }

    public function getProjectInputNames()
    {
        if ($this->inputs == "[]") return false;

        $inputNames = [];
        foreach (json_decode($this->inputs) as $input) {
            array_push($inputNames, $input->name);
        }
        return $inputNames;
    }

    public function getNumberOfAnswersByQuestion($question)
    {
        if ($this->inputs == "[]") return false;
        $item = null;

        foreach (json_decode($this->inputs) as $input) {
            if ($question == $input->name) {
                $item = $input->numberofanswer;
                break;
            }
        }
        return $item;
    }

    public function getAnswersByQuestion($question)
    {
        if ($this->inputs == "[]") return false;
        $item = null;
        $inputs = json_decode($this->inputs);

        foreach ($inputs as $input) {
            if ($question == $input->name) {
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
