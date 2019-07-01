<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Cases extends Model
{

  protected $table = 'cases';

  protected $guarded = [];

  public function entries()
  {
    return $this->hasMany(Entry::class,'case_id');
  }

  public function project()
  {
    return $this->belongsTo(Project::class);
  }

  public function user()
  {
    return $this->belongsTo(User::class,'user_id');
  }

  public function path()
  {
   return "/cases/{$this->id}";
 }



/**
 * assign a user to this case
 * this will be the user that fills the entries
 * @param User $user user to assign to the case
 */
public function addUser($user)
{
  is_array($user)? $user = \App\User::firstOrCreate($user) : $user = $user;

  $this->user()->associate($user);
  $this->save();
  return $user;
}

/**
 * edit the case only if has no entries
 * @return bool
 */
public function isEditable()
{
  return $this->entries()->count() > 0 ? false : true;
}

}
