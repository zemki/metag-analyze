<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    /**
     * @var array
     */
    protected $fillable = [
        'name', 'is_active'
    ];

    public function projects()
    {
        return $this->users();
    }

    /**
     * The users that belong to the group.
     */
    public function users()
    {
        return $this->belongsToMany('App\User', 'user_groups');
    }
}
