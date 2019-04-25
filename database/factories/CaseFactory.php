<?php

use Faker\Generator as Faker;



$factory->define(App\Cases::class, function (Faker $faker,$params) {
	return [
		'name' => $faker->name,
		'project_id' => function() {
			return factory(App\Project::class)->create()->id;
		},
		'user_id' => function(){

			return (isset($params['user_id']) ? $params['user_id'] : factory(App\User::class)->create()->id);

		}
	];
});
