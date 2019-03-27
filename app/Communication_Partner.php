<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Communication_Partner extends Model
{


	protected $guarded = [];

	public function entries()
	{
		return $this->hasMany(Entry::class);
	}
}
