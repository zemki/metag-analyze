<?php

namespace App\Providers;

use Auth;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    protected $rules = [
        "user_id" => "bail|required|exists:users,id",
        "token" => "bail|required|string|min:30|max:30"
    ];
    /**
     * The policy mappings for the application.
     * @var array
     */
    protected $policies = [
        // 'App\Model' => 'App\Policies\ModelPolicy',
        'App/Project' => 'App\Policies\ProjectPolicy',
        'App/Entry' => 'App\Policies\EntryPolicy'
    ];

    /**
     * Register any authentication / authorization services.
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();
        Auth::viaRequest("api-token", function ($request) {
            return TokenGuard::findUser($request->api_user, $request->api_token);
        });
    }

    /**
     * Retrieve a user via a given identifier and API token
     * @param int    $user_id
     * @param string $token api_token
     * @return token          return the token
     */
    public function findUser($user_id, $token)
    {
        $data = compact("user_id", "token");
        if (validator($data, $this->rules)->fails()) {
            return null;
        }
        $user = User::find($user_id);
        return decrypt($user->api_token) === $token ? $user : null;
    }
}
