<?php

namespace Database\Factories;

use App\Role;
use App\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/*
|--------------------------------------------------------------------------
| Model Factories
|--------------------------------------------------------------------------
|
| This directory should contain each of the model factory definitions for
| your application. Factories provide a convenient way to generate new
| model instances for testing / seeding your application's database.
|
*/
class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition()
    {
        return [
            'email' => $this->faker->unique()->safeEmail,
            'password' => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token' => Str::random(10),
            'email_verified_at' => now(),

        ];
    }

    protected static function newFactory()
    {
        return UserFactory::new()->afterCreating(function (User $user, $faker) {
            $role = $user->roles->first();

            if ($role) {
                $user->roles()->sync([$role->id]);
            }
        });
    }

    public function researcher()
    {
        return $this->afterCreating(function (User $user) {
            $role_id = Role::where('name', 'researcher')->firstOrCreate(['name' => 'researcher', 'description' => 'researcher'])->id;
            $user->roles()->sync([$role_id]);
        });
    }
}
