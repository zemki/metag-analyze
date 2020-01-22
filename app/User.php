<?php

namespace App;

use App\Mail\VerificationEmail;
use Helper;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Mail;
use Validator;

/**
 * eyJ0eXAiOiJKV1QiLCJhbGciOiJSUzI1NiIsImp0aSI6ImZmNWVhMzYxN2Y2YzJlMTg2ZGFkOGNlMTEyMjdhYzE1NmU4NTc5YjZmMDEyZGZkOTU5MzA5ODEzODEwYTU2NmFkOWZjM2U0OTIwYzkzOGUxIn0.eyJhdWQiOiIxIiwianRpIjoiZmY1ZWEzNjE3ZjZjMmUxODZkYWQ4Y2UxMTIyN2FjMTU2ZTg1NzliNmYwMTJkZmQ5NTkzMDk4MTM4MTBhNTY2YWQ5ZmMzZTQ5MjBjOTM4ZTEiLCJpYXQiOjE1NTM2OTM0NzUsIm5iZiI6MTU1MzY5MzQ3NSwiZXhwIjoxNTg1MzE1ODc1LCJzdWIiOiIyIiwic2NvcGVzIjpbXX0.Vlcrt-0sHsd1-J25WKahbJlhRNA99CWqo35JkmTKwpL_S9DfMYehnrg6tDGNs-JCxwfRQKpEmH5fJKWXlJC_c_26Z3eBKKyWGDTYtX1obfSEAaDdzj654wrFcZiqmY5y1H46ugXSFUEwC_oEvaxZQRNQwViyDyA4vQjO0aC95CcwY3OeIo03q7uLmuC8qg21wnpIegd8_eYUkVCaUZbi7rBicHLYpbNF0jSUPjlC9FRnNYl3v4gEFtOO0DCwtf-DgCNGsn9kIBaPnhuHQ0KhHhMog5Lv91HVhqYC47JKHweXKGWK6SiaazafUs8nhcV2RPgfz3LdR4V5JXvzZMZBzkm4457me3mb8nFjUmHIs6ufta8BP2V49CxYPsD_MispM2swS5u5cjGHuW2WuIiYRDphwk8kw1mH0xwDp_tRXXTEpJzFSKnHcfEXA4aliWtrIei8CTqJM0Gm6cgZcCo1EkDgZE2Gm34-h1TEQnHL3E7CiFHWCkDO8bE_co12AtzPOFU9Me4bm3wR5Cp8VMz7BDL54T_9eZsprvc_lnMdZF9q1ccEqtiIX2z-0n4XIbf1sRpg1pubKRKDPD-E2tBYirFlt5uBPlWqK1os-gLZkepuuTEzvmCpMChubtVQlB_khGH2gMZ-Jmh_cf_rFv2FQs7TwFSTrZ6TNcxqzuP8jBI
 */
class User extends Authenticatable
{
    use Notifiable,SoftDeletes;


    // this is a recommended way to declare event handlers
    public static function boot()
    {
        parent::boot();
        static::deleting(function ($user) {


            foreach ($user->projects as $project) {
                // if the user created the project
                if ($project->created_by == $user->id) {
                    // detach the user
                    // and assign a new created_by/owner of the project
                    if ($project->invited()->count() > 0) {
                        $newOwner = $project->invites()->random();
                        $project->created_by = $newOwner->id;
                    } else {
                        foreach($project->cases as $case){
                            foreach($case->entries as $entry){
                                $entry->delete();
                            }
                            $case->delete();
                        }
                        $project->delete();
                    }
                }


            }

        });



    }
    

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email', 'password', 'last_login_date'

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
        return $this->belongsToMany('App\Role', 'user_roles')->withTimestamps();
    }

    public function groups()
    {
        return $this->belongsToMany('App\Group', 'user_groups')->withTimestamps();
    }

    public function isAdmin()
    {
        return in_array('admin', $this->roles()->pluck('roles.name')->toArray());
    }

    public function isResearcher()
    {
        return in_array('researcher', $this->roles()->pluck('roles.name')->toArray());
    }

    public function notOwnerNorInvited($project)
    {
        return auth()->user()->isNot($project->created_by()) && !in_array($project->id, auth()->user()->invites()->pluck('project_id')->toArray());
    }

    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    public function case()
    {
        return $this->hasMany(Cases::class, 'user_id');
    }

    public function latestCase()
    {
        return $this->hasOne(Cases::class)->latest();
    }

    public function getOrderedCases()
    {
        return $this->case->entries()->groupBy('begin')->get();
    }

    public function projects()
    {
        return $this->hasMany(Project::class, 'created_by');
    }

    public function invites()
    {
        return $this->belongsToMany(Project::class, 'user_projects');

    }

    public static function createIfDoesNotExists($user)
    {
        if (!$user->exists) {
            $user->email = request('email');
            $role = Role::where('name', '=', 'user')->first();
            $user->password = bcrypt(Helper::random_str(60));
            $user->password_token = bcrypt(Helper::random_str(60));
            $user->save();
            $user->roles()->sync($role);
            Mail::to($user->email)->send(new VerificationEmail($user, config('utilities.emailDefaultText')));
        }
        return $user;
    }


}
