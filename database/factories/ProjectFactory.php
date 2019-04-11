<?php

use Faker\Generator as Faker;

$factory->define(App\Project::class, function (Faker $faker,$params) {

	return [
		'name' => $faker->name,
		'description' => $faker->sentence,
		'duration' => $faker->sentence,
		'is_locked' => 0,
		'created_by' => function() {
			return factory(App\User::class)->create()->id;
		},
		'inputs' => (isset($params['inputs']) ? $params['inputs'] : '')
	];
});

