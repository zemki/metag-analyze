<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Place extends Model
{

	protected $guarded = [];

	public function entries()
	{
		return $this->hasMany(Entry::class);
	}

	 public function projects()
    {
        return $this->belongsToMany('App\Project','place_projects')->withTimestamps();
    }
}
