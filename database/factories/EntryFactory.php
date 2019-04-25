<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Entry;
use Faker\Generator as Faker;

$factory->define(Entry::class, function (Faker $faker,$params) {
    return [
        		//'name', 'description', 'properties','media_group_id'

		'begin' => $faker->DateTime,
		'end' => $faker->DateTime,
		'inputs' => '{}',
		'content' => $faker->sentence,
		'comment' => $faker->sentence,
		'case_id' => function() {
			return  (isset($params['case_id']) ? $params['case_id'] : factory(App\Cases::class)->create()->id);
		},
		'media_id' => function() {
			return factory(App\Media::class)->create()->id;
		},
		'place_id' => function() {
			return factory(App\Place::class)->create()->id;
		},
		'communication_partner_id' => function() {
			return factory(App\Communication_Partner::class)->create()->id;
		}
    ];
});
