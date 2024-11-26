<?php

namespace Database\Factories;

use App\Cases;
use App\Entry;
use App\Media;
use Illuminate\Database\Eloquent\Factories\Factory;

class EntryFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Entry::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'begin' => $this->faker->sentence(5),
            'end' => $this->faker->sentence(5),
            'inputs' => json_encode([]),
            'case_id' => Cases::factory(),
            'media_id' => Media::factory(),
            'created_at' => $this->faker->dateTimeThisYear,
            'updated_at' => $this->faker->dateTimeThisYear,
        ];
    }
}
