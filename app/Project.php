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
        return $this->HasMany(Cases::class);
    }

    public function path()
    {
    	return "/projects/{$this->id}";
    }

    public function created_by()
    {
        return $this->belongsTo(User::class,'created_by')->first();
    }



    public function addCase($name,$inputs)
    {
        return $this->cases()->create(compact('name','inputs'));
    }

}
