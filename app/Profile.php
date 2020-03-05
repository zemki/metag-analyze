<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    protected $table = 'users_profiles';
    /**
     * The attributes that are mass assignable.
     * @var array
     */
    protected $fillable = [
        'name', 'address', 'workaddress', 'phonenumber1', 'phonenumber2'
    ];

    public function user()
    {
        return $this->belongsTo('App\User');
    }
}
