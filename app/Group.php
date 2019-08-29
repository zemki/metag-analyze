<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Group extends Model
{
    protected $fillable = [
        'name', 'is_active'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */


    /**
     * The users that belong to the group.
     */
    public function users()
    {
        return $this->belongsToMany('App\User','user_groups');
    }

    public function projects()
    {
        return $this->users();
    }

}
