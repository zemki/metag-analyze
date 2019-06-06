<?php

use Faker\Generator as Faker;

$factory->define(App\Project::class, function (Faker $faker,$params) {

	return [
		'name' => $faker->name,
		'description' => $faker->sentence,
		'is_locked' => 0,
		'created_by' => function() {
			return (isset($params['user_id']) ? $params['user_id'] : factory(App\User::class)->create()->id);
		},
		'inputs' => (isset($params['inputs']) ? $params['inputs'] : '')
	];
});

