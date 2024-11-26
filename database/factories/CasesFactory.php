<?php

namespace Database\Factories;

use App\Cases;
use App\Project;
use App\User;
use Helper;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Crypt;

class CasesFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Cases::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->sentence(5),
            'duration' => $this->faker->randomElement(['1 week', '2 weeks', '3 weeks', '4 weeks', '6 weeks']),
            'project_id' => Project::factory(),
            'user_id' => User::factory(),
            'created_at' => $this->faker->dateTimeThisYear,
            'updated_at' => $this->faker->dateTimeThisYear,
            'file_token' => Crypt::encryptString(Helper::random_str(60)),
        ];

    }
}
