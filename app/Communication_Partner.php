<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Communication_Partner extends Model
{

	protected $table = 'communication_partners';


	protected $guarded = [];

	public function entries()
	{
		return $this->hasMany(Entry::class);
	}

	public function projects()
	{
		return $this->belongsToMany(Project::class,'communication_partner_projects','communication_partner_id','project_id')->withTimestamps();
	}
}
