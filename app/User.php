<?php

namespace App;

use App\Mail\VerificationEmail;
use App\Notifications\notifyUserforNewCaseWhenAlreadyRegistered;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Helper;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Http\Request;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class User extends Authenticatable implements MustVerifyEmail, FilamentUser, HasName
{
    use HasFactory, Notifiable, SoftDeletes;

    /**
     * Set when this user's invitation email could not be sent
     *
     * Request-scoped only - declared explicitly so Eloquent treats it as a
     * plain property rather than routing it through __set into the model's
     * attributes, where it would be persisted.
     */
    public bool $invitationMailFailed = false;

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
     *
     * @var array
     */
    protected $casts = [
        'deviceID' => 'array',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email', 'password', 'last_login_date', 'deviceID',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
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
     * @return mixed
     */
    public static function createIfDoesNotExists($user, $sendmessage = false, $subject = '', $message = '')
    {
        if (! $user->exists) {
            $role = Role::where('name', '=', 'user')->first();
            $user->password = Crypt::encryptString(Helper::random_str(30));
            $user->password_token = Crypt::encryptString(Helper::random_str(30));

            DB::transaction(function () use ($user, $role) {
                $user->save();

                // Guard against a missing role: sync(null) would detach every
                // role rather than assigning one.
                if ($role) {
                    $user->roles()->sync($role);
                }
            });

            // A failed invitation must not abort the caller. Participants are
            // created in a loop (ProjectCasesController), so an exception here
            // used to stop every remaining participant from getting a case.
            try {
                Mail::to($user->email)->send(new VerificationEmail($user, config('utilities.emailDefaultText')));
            } catch (Throwable $e) {
                $user->invitationMailFailed = true;
                Log::error('Failed to send participant invitation email', [
                    'email' => $user->email,
                    'error' => $e->getMessage(),
                ]);
            }
        } elseif ($sendmessage) {
            try {
                $user->notify(new notifyUserforNewCaseWhenAlreadyRegistered(['subject' => $subject, 'message' => $message]));
            } catch (Throwable $e) {
                $user->invitationMailFailed = true;
                Log::error('Failed to notify an existing user about a new case', [
                    'email' => $user->email,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $user;
    }

    /**
     * Save the logged-in user's device ID from the request.
     *
     * Kept for the MeTag login and QR login (ApiController), which still call it.
     */
    public static function saveDeviceId(Request $request)
    {
        $user = auth()->user();

        if ($user && $request->filled('deviceID')) {
            $user->addDeviceToken((string) $request->deviceID);
        }
    }

    /**
     * Most FCM registration tokens to keep per user.
     *
     * Tokens are rotated by FCM (reinstall, restore, cleared app data), so the
     * list would otherwise grow without limit. The newest are kept; sending to
     * a stale token is a no-op at FCM's end.
     */
    public const MAX_DEVICE_TOKENS = 10;

    /**
     * Register an FCM token for this user.
     *
     * Before this, only the MeTag login stored tokens (saveDeviceId); MART
     * participants never registered one, so push could not reach them.
     */
    public function addDeviceToken(string $token): bool
    {
        $token = trim($token);

        if ($token === '') {
            return false;
        }

        // The column is cast to array; a legacy row holding a bare string
        // decodes to null, so normalise before touching it.
        $tokens = array_values(array_filter((array) ($this->deviceID ?? []), 'is_string'));

        if (in_array($token, $tokens, true)) {
            return false;
        }

        $tokens[] = $token;

        $this->forceFill([
            'deviceID' => array_slice($tokens, -self::MAX_DEVICE_TOKENS),
        ])->save();

        return true;
    }

    /**
     * Unregister an FCM token, e.g. on logout or when FCM reports it as stale.
     */
    public function removeDeviceToken(string $token): bool
    {
        $tokens = array_values(array_filter((array) ($this->deviceID ?? []), 'is_string'));
        $remaining = array_values(array_filter($tokens, fn ($t) => $t !== $token));

        if (count($remaining) === count($tokens)) {
            return false;
        }

        $this->forceFill(['deviceID' => $remaining])->save();

        return true;
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
     * Determine if the user can access the Filament admin panel.
     *
     * @param  Panel  $panel
     * @return bool
     */
    public function canAccessPanel(Panel $panel): bool
    {
        return $this->isAdmin();
    }

    /**
     * Get the user's display name for Filament.
     *
     * @return string
     */
    public function getFilamentName(): string
    {
        return $this->email ?? 'Unknown User';
    }

    /**
     * Get the user's avatar URL for Filament.
     *
     * @return string|null
     */
    public function getFilamentAvatarUrl(): ?string
    {
        return null;
    }

    /**
     * @return BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany('App\Role', 'user_roles')->withTimestamps();
    }

    /**
     * @return bool
     */
    public function notOwnerNorInvited($project)
    {
        return ! auth()->user()->isAdmin() && (auth()->user()->isNot($project->created_by()) && ! in_array($project->id, auth()->user()->invites()->pluck('project_id')->toArray()));
    }

    /**
     * @return bool
     */
    public function isInvited($project)
    {
        return in_array($project->id, auth()->user()->invites()->pluck('project_id')->toArray());
    }

    /**
     * @return BelongsToMany
     */
    public function invites()
    {
        return $this->belongsToMany(Project::class, 'user_projects');
    }

    /**
     * @return HasMany
     */
    public function case()
    {
        return $this->hasMany(Cases::class, 'user_id');
    }

    /**
     * @return HasMany
     */
    public function actions()
    {
        return $this->hasMany(Action::class, 'user_id');
    }

    public function isResearcher(): bool
    {
        return in_array('researcher', $this->roles()->pluck('roles.name')->toArray());
    }

    /**
     * @return HasOne
     */
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

    public function hasReachMaxNumberOfProjecs(): bool
    {
        return $this->projects()->count() >= config('utilities.maxNumberOfProjects');
    }

    /**
     * @return HasMany
     */
    public function projects()
    {
        return $this->hasMany(Project::class, 'created_by');
    }

    /**
     * @return HasOne
     */
    public function profile()
    {
        return $this->hasOne(Profile::class);
    }

    /**
     * @return Profile
     */
    public function addProfile($user)
    {
        $profile = new Profile;
        $profile->user_id = $user->id;
        $profile->save();

        return $profile;
    }

    /**
     * @param  string  $description
     * @return Action
     *                add action to the action table.
     */
    public function addAction($name, $url, $description = '')
    {
        $action = new Action;
        $action->name = $name;
        $action->description = $description;
        $action->url = $url;
        // Falls back to the model the action is recorded against when there is
        // no session. Some actions legitimately happen unauthenticated - the
        // email-change confirmation link is opened from the new mailbox, often
        // on a device that is not logged in - and auth()->user()->id fataled
        // there, after the change had already been committed.
        // For authenticated callers this is unchanged: auth()->id() wins.
        $action->user_id = auth()->id() ?? $this->id;
        $action->time = date('Y-m-d H:i:s');
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
     *
     * @return bool
     *
     * @var array
     */
    public function hasRole($roles)
    {
        if (is_array($roles)) {
            return count(array_intersect($roles, $this->roles()->distinct()->pluck('name')->toArray())) > 0;
        } else {
            return in_array($roles, $this->roles()->distinct()->pluck('name')->toArray());
        }
    }
}
