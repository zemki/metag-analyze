<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Cases extends Model
{

	protected $fillable = [
		'name'
	];

    public function project()
    {
        return $this->belongsTo('App\Project');
    }

    public function path()
    {
    	return "projects/{$this->project->id}/cases/{$this->id}";
    }
}
