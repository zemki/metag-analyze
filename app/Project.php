<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
	protected $fillable = [
		'name', 'description', 'duration','created_by','is_locked','inputs'
	];

  public function entries()
  {
    return $this->hasMany(Entry::class,'case_id');
}
public function getInputsAttribute($value)
{
  return is_array($value) ? $value : (array) json_decode($value);
}

public function cases()
{
    return $this->HasMany(Cases::class);
}

public function media()
{
    return $this->belongsToMany(Media::class,'media_projects');
}

public function places()
{
    return $this->belongsToMany(Place::class,'place_projects');
}
public function communication_partners()
{
    return $this->belongsToMany(Communication_Partner::class,'communication_partner_projects','project_id','communication_partner_id');
}

public function path()
{
   return "/projects/{$this->id}";
}

public function created_by()
{
    return $this->belongsTo(User::class,'created_by')->first();
}



public function addCase($name)
{
    return $this->cases()->create(compact('name'));
}

}
