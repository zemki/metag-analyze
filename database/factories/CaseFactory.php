<?php

use Faker\Generator as Faker;



$factory->define(App\Cases::class, function (Faker $faker) {
	return [
		'name' => $faker->name,
	];
});
