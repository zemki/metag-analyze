<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Entry;
use Faker\Generator as Faker;

$factory->define(Entry::class, function (Faker $faker,$params) {

    $minutes = rand(10,35);
    $end = $faker->dateTimeBetween('-10 days', '-1 day');
    $begin = clone $end;
    $begin->modify("-10 minutes");
   // $end->add(new DateInterval('PT10H30S'));
   // $stamp = $end->format('Y-m-d H:i:s');
    while($begin == $end){
        $begin->modify("-{$minutes} minutes");
    }

    if(isset($params['case_id'])){
       // App\Cases::where('id','=',$params['case_id'])->get();
    }

    return [
        		//'name', 'description', 'properties','media_group_id'

		'begin' => $begin,
		'end' => $end,
		'inputs' => '{}',
		'case_id' => function() {
			return  (isset($params['case_id']) ? $params['case_id'] : factory(App\Cases::class)->create()->id);
		},
		'media_id' => function() {
			return factory(App\Media::class)->create()->id;
		}
    ];
});
