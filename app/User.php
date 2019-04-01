<?php

namespace App;

use Illuminate\Auth\MustVerifyEmail;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Contracts\Auth\MustVerifyEmail as MustVerifyEmailContract;
use Illuminate\Support\Facades\Auth;
use Validator;
use Laravel\Passport\HasApiTokens;

/**
 * eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6ImZmNWVhMzYxN2Y2YzJlMTg2ZGFkOGNlMTEyMjdhYzE1NmU4NTc5YjZmMDEyZGZkOTU5MzA5ODEzODEwYTU2NmFkOWZjM2U0OTIwYzkzOGUxIn0.eyJhdWQiOiIxIiwianRpIjoiZmY1ZWEzNjE3ZjZjMmUxODZkYWQ4Y2UxMTIyN2FjMTU2ZTg1NzliNmYwMTJkZmQ5NTkzMDk4MTM4MTBhNTY2YWQ5ZmMzZTQ5MjBjOTM4ZTEiLCJpYXQiOjE1NTM2OTM0NzUsIm5iZiI6MTU1MzY5MzQ3NSwiZXhwIjoxNTg1MzE1ODc1LCJzdWIiOiIyIiwic2NvcGVzIjpbXX0.Vlcrt-0sHsd1-J25WKahbJlhRNA99CWqo35JkmTKwpL_S9DfMYehnrg6tDGNs-JCxwfRQKpEmH5fJKWXlJC_c_26Z3eBKKyWGDTYtX1obfSEAaDdzj654wrFcZiqmY5y1H46ugXSFUEwC_oEvaxZQRNQwViyDyA4vQjO0aC95CcwY3OeIo03q7uLmuC8qg21wnpIegd8_eYUkVCaUZbi7rBicHLYpbNF0jSUPjlC9FRnNYl3v4gEFtOO0DCwtf-DgCNGsn9kIBaPnhuHQ0KhHhMog5Lv91HVhqYC47JKHweXKGWK6SiaazafUs8nhcV2RPgfz3LdR4V5JXvzZMZBzkm4457me3mb8nFjUmHIs6ufta8BP2V49CxYPsD_MispM2swS5u5cjGHuW2WuIiYRDphwk8kw1mH0xwDp_tRXXTEpJzFSKnHcfEXA4aliWtrIei8CTqJM0Gm6cgZcCo1EkDgZE2Gm34-h1TEQnHL3E7CiFHWCkDO8bE_co12AtzPOFU9Me4bm3wR5Cp8VMz7BDL54T_9eZsprvc_lnMdZF9q1ccEqtiIX2z-0n4XIbf1sRpg1pubKRKDPD-E2tBYirFlt5uBPlWqK1os-gLZkepuuTEzvmCpMChubtVQlB_khGH2gMZ-Jmh_cf_rFv2FQs7TwFSTrZ6TNcxqzuP8jBI
 */

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
       return $this->hasOne(Profile::class);
   }

   public function case()
   {
       return $this->hasMany(Cases::class,'user_id');
   }

   public function latestCase() {
    return $this->hasOne(Cases::class)->latest();
}

   public function getOrderedCases()
   {
    return $this->case->entries()->groupBy('begin')->get();
  }

  public function projects()
  {
    return $this->hasMany(Project::class,'created_by');
}




}
