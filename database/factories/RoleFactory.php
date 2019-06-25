<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Model;
use Faker\Generator as Faker;

$factory->define(\App\Role::class, function (Faker $faker,$params) {
    return [
        'name' => function() {
            return (isset($params['name']) ? $params['name'] : $faker->name);
        },
        'description' => $faker->sentence
    ];
});
