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

/**
 * App\User
 *
 * @property int $id
 * @property string|null $email
 * @property string $password
 * @property string|null $remember_token
 * @property string|null $password_token
 * @property string|null $api_token
 * @property string|null $last_login_date
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property array|null $deviceID
 * @property string|null $email_verified_at
 * @property string|null $latest_activity
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Action[] $actions
 * @property-read int|null $actions_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Cases[] $case
 * @property-read int|null $case_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Group[] $groups
 * @property-read int|null $groups_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Project[] $invites
 * @property-read int|null $invites_count
 * @property-read \App\Cases|null $latestCase
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection|\Illuminate\Notifications\DatabaseNotification[] $notifications
 * @property-read int|null $notifications_count
 * @property-read \App\Profile|null $profile
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Project[] $projects
 * @property-read int|null $projects_count
 * @property-read \Illuminate\Database\Eloquent\Collection|\App\Role[] $roles
 * @property-read int|null $roles_count
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User newQuery()
 * @method static \Illuminate\Database\Query\Builder|\App\User onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User query()
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereApiToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereDeviceID($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereLastLoginDate($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereLatestActivity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User wherePasswordToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|\App\User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Query\Builder|\App\User withTrashed()
 * @method static \Illuminate\Database\Query\Builder|\App\User withoutTrashed()
 * @mixin \Eloquent
 */
class User extends Authenticatable implements MustVerifyEmail
{
    use Notifiable, SoftDeletes;

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'deviceID' => '[]',
    ];

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

            foreach ($user->actions as $action) {
                $action->user_id = null;
                $action->save();
                $action->delete();
            }

            if ($user->profile()->exists()) {
                $user->profile->delete();
            }
        });
    }

    /**
     * @param $user
     * @return mixed
     */
    public static function createIfDoesNotExists($user)
    {
        if (!$user->exists) {
            $user->email = $user->email;
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
        return !auth()->user()->isAdmin() && (auth()->user()->isNot($project->created_by()) && !in_array($project->id, auth()->user()->invites()->pluck('project_id')->toArray()));
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


    /**
     * Specifies the user's FCM tokens
     *
     * @return string|array
     */
    public function routeNotificationForFcm()
    {
        return $this->getDeviceTokens();
    }

    private function getDeviceTokens()
    {
        return $this->deviceID;
    }
    
    /**
     * Check if the user has a certain or array of roles
     * @return bool
     * @var array
     */
    public function hasRole($roles)
    {
        if (is_array($roles)) {
            return (count(array_intersect($roles, $this->roles()->distinct()->pluck('name')->toArray())) > 0);
        } else {
            return in_array($roles, $this->roles()->distinct()->pluck('name')->toArray());
        }
    }
}
