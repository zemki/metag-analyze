<?php

use Faker\Generator as Faker;

$factory->define(App\Media::class, function (Faker $faker) {
    return [
        		//'name', 'description', 'properties','media_group_id'

		'name' => $faker->name,
		'description' => $faker->sentence,
		'properties' => $faker->sentence,
		'media_group_id' => function() {
			return factory(App\Media_Group::class)->create()->id;
		}

    ];
});
