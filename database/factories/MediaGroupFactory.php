<?php

use Faker\Generator as Faker;

$factory->define(\App\Media_Group::class, function (Faker $faker) {
    return [
		'name' => $faker->name,
		'description' => $faker->sentence

    ];
});
