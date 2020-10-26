<?php

namespace App;

use App\Mail\VerificationEmail;
use Helper;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Mail;

class User extends Authenticatable implements MustVerifyEmail
{
    use Notifiable, SoftDeletes;
    /**
     * The attributes that should be cast to native types.
     * @var array
     */
    protected $casts = [
        'deviceID' => 'array',
    ];
    /**
     * The attributes that are mass assignable.
     * @var array
     */
    protected $fillable = [
        'email', 'password', 'last_login_date', 'deviceID'
    ];
    /**
     * The attributes that should be hidden for arrays.
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

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
                        $newOwner = $project->invited()->random();
                        $project->created_by = $newOwner->id;
                    } else {
                        foreach ($project->cases as $case) {
                            foreach ($case->entries as $entry) {
                                $entry->delete();
                            }
                            $case->delete();
                        }
                        $project->delete();
                    }
                }
            }
            foreach ($user->case as $case) {
                foreach ($case->entries as $entry) {
                    $entry->delete();
                }
                $case->delete();
            }
            $user->roles()->sync([]);

            foreach ($user->actions as $action)
            {
                $action->user_id = null;
                $action->save();
                $action->delete();

            }

            if($user->profile()->exists()) $user->profile->delete();


        });
    }

    /**
     * @param $user
     * @return mixed
     */
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

    /**
     * @param Request $request
     * save the device ID of the user by adding it into the device array.
     */
    public static function saveDeviceId(Request $request)
    {
        $currentDeviceId = auth()->user()->deviceID == null ? [] : auth()->user()->deviceID;
        if ($request->has('deviceID') && $request->deviceID != '' && !in_array($request->deviceID, $currentDeviceId)) {
            array_push($currentDeviceId, $request->deviceID);
        }
        auth()->user()->forceFill(['deviceID' => $currentDeviceId ?? ''])->save();
    }

    public function groups()
    {
        return $this->belongsToMany('App\Group', 'user_groups')->withTimestamps();
    }

    /**
     * @return bool
     */
    public function isAdmin()
    {
        return in_array('admin', $this->roles()->pluck('roles.name')->toArray());
    }

    /**
     * @return BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany('App\Role', 'user_roles')->withTimestamps();
    }

    public function notOwnerNorInvited($project)
    {
        return auth()->user()->isNot($project->created_by()) && !in_array($project->id, auth()->user()->invites()->pluck('project_id')->toArray()) ;
    }

    public function isInvited($project)
    {
        return in_array($project->id, auth()->user()->invites()->pluck('project_id')->toArray());
    }

    public function invites()
    {
        return $this->belongsToMany(Project::class, 'user_projects');
    }



    public function case()
    {
        return $this->hasMany(Cases::class, 'user_id');
    }

    public function actions()
    {
        return $this->hasMany(Action::class, 'user_id');
    }

    /**
     * @return bool
     */
    public function isResearcher(): bool
    {
        return in_array('researcher', $this->roles()->pluck('roles.name')->toArray());
    }

    public function latestCase()
    {
        return $this->hasOne(Cases::class)->latest();
    }

    /**
     * @return mixed
     */
    public function getOrderedCases()
    {
        return $this->case->entries()->groupBy('begin')->get();
    }

    /**
     * @return bool
     */
    public function hasReachMaxNumberOfProjecs(): bool
    {

        return $this->projects()->count() >= config('utilities.maxNumberOfProjects');
    }

    public function projects()
    {
        return $this->hasMany(Project::class, 'created_by');
    }

    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    /**
     *
     */
    public function addProfile($user)
    {
        $profile = new Profile();
        $profile->user_id = $user->id;
        $profile->save();
        return $profile;
    }

    /**
     * @param        $name
     * @param        $url
     * @param string $description
     * @return Action
     * add action to the action table.
     */
    public function addAction($name, $url, $description = "")
    {
        $action = new Action();
        $action->name = $name;
        $action->description = $description;
        $action->url = $url;
        $action->user_id = auth()->user()->id;
        $action->time = date("Y-m-d H:i:s");
        $action->save();
        return $action;
    }
}
