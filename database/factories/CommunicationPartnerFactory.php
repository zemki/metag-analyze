<?php

use Faker\Generator as Faker;

$factory->define(App\Communication_Partner::class, function (Faker $faker) {
    return [
		'name' => $faker->name,
		'comment' => $faker->sentence,

    ];
});
