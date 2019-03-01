<?php

namespace App;

use Illuminate\Auth\MustVerifyEmail;
use Laravel\Passport\HasApiTokens;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
use Illuminate\Support\Facades\Auth;
use Validator;


class User extends Authenticatable
{
    use HasApiTokens,Notifiable;


    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'username', 'email', 'password', 'last_login_date'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function roles()
    {
        return $this->belongsToMany('App\Role','user_roles')->withTimestamps();
    }

    public function profile()
    {
     return $this->hasOne('App\Profile');
    }

    public function isAdmin()
    {
        return in_array('admin',$this->roles()->pluck('roles.name')->toArray());
    }

    public function isResearcher()
    {
        return in_array('researcher',$this->roles()->pluck('roles.name')->toArray());
    }

    public function isUser()
    {
        return in_array('user',$this->roles()->pluck('roles.name')->toArray());
    }

}
