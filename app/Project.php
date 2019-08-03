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

}
