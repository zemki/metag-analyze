<?php

namespace Database\Factories;

use App\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition()
    {
        return [
            'name' => $this->faker->name,
            'description' => $this->faker->sentence,
            'created_by' => $this->faker->numberBetween(1, 10),
            'is_locked' => $this->faker->boolean,
            'inputs' => $this->faker->sentence,
        ];
    }

    public function forUser($user)
    {
        return $this->state(function (array $attributes) use ($user) {
            return [
                'created_by' => $user->id,
            ];
        });
    }
}
