<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Media_Group extends Model
{
	protected $table = 'media_groups';

    protected $fillable = [
		'name', 'description'
	];

	public function path()
	{
		return "/media_groups/{$this->id}";
	}

	public function media()
	{
		return $this->hasMany(Media::class);
	}

}
