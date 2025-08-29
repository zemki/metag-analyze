<?php

namespace App\Providers;

use Exception;
use App\Helpers\Helper;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        'App/Project' => 'App\Policies\ProjectPolicy',
        'App/Entry' => 'App\Policies\EntryPolicy',
    ];

    /**
     * Validation rules for token authentication
     */
    protected array $tokenValidationRules = [
        'user_id' => 'bail|required|exists:users,id',
        'token' => 'bail|required|string|min:30|max:30',
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        // Register the token validation method
        Auth::viaRequest('api-token', function ($request) {
            return $this->findUserViaToken($request->api_user, $request->api_token);
        });
    }

    /**
     * Find a user via their API token
     *
     * @param  int|string|null  $userId
     * @param  string|null  $token
     */
    protected function findUserViaToken($userId, $token): ?User
    {
        // Early return if validation fails
        $data = compact('userId', 'token');
        if (validator($data, $this->tokenValidationRules)->fails()) {
            return null;
        }

        // Check cache first
        $cacheKey = "user_token:{$userId}";
        $cachedUser = Cache::get($cacheKey);

        if ($cachedUser && $this->validateToken($cachedUser, $token)) {
            return $cachedUser;
        }

        // If not in cache, check database
        $user = User::find($userId);
        if (! $user || ! $this->validateToken($user, $token)) {
            return null;
        }

        // Cache the user for future requests (1 hour)
        Cache::put($cacheKey, $user, Carbon::now()->addHour());

        return $user;
    }

    /**
     * Validate a user's API token
     */
    protected function validateToken(User $user, string $token): bool
    {
        try {
            // For backward compatibility, if no expiration set, token is valid
            if ($user->token_expires_at && Carbon::parse($user->token_expires_at)->isPast()) {
                return false;
            }

            return hash('sha256', $token) === $user->api_token;

        } catch (Exception $e) {
            report($e);

            return false;
        }
    }

    /**
     * Issue a new API token for a user
     *
     * @return array
     */
    public function issueToken(User $user): string
    {
        $token = Helper::random_str(60);

        $user->forceFill([
            'api_token' => hash('sha256', $token),
            'token_expires_at' => Carbon::now()->addDays(
                config('auth.token_expiration_days', 30)
            ),
        ])->save();

        return $token;
    }

    /**
     * Invalidate a user's token
     */
    public function invalidateToken(User $user): void
    {
        $user->forceFill([
            'api_token' => null,
            'token_expires_at' => null,
        ])->save();

        Cache::forget("user_token:{$user->id}");
    }
}
