<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
	protected $fillable = [
		'name', 'description', 'duration','created_by','is_locked'
	];

    public function cases()
    {
        return $this->HasMany('App\Cases')->withTimestamps();
    }

}
