<?php

use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
    	$faker = Faker::create();


    	DB::table('users')->insert([
    		'email' => "belli@uni-bremen.de",
    		'password' => bcrypt('1q2w3e4r5t'),
    		'api_token' => hash('sha256',str_random(60)),
    		'created_at' => $faker->dateTime($max = 'now', $timezone = "Europe/Berlin"),
    		'updated_at' => $faker->dateTime($max = 'now', $timezone = "Europe/Berlin")
    	],
    	[
    		'email' => "fhohmann@uni-bremen.de",
    		'password' => bcrypt('q1w2e3r4t5'),
    		'api_token' =>  hash('sha256',str_random(60)),
    		'created_at' => $faker->dateTime($max = 'now', $timezone = "Europe/Berlin"),
    		'updated_at' => $faker->dateTime($max = 'now', $timezone = "Europe/Berlin")
    	]);

    	$this->command->info('Admin user seeded');
    	$fakeusers = 0;

    	$this->command->info('Seeding fake users');
	    /// create fake data
    	foreach (range(1,$fakeusers) as $index) {

    		$this->command->getOutput()->write("<info>".$index."</info>\r");



    		DB::table('users')->insert([
    			'email' => $faker->email,
    			'password' => bcrypt('secret'),
    			'created_at' => $faker->dateTime($max = 'now', $timezone = "Europe/Berlin"),
    			'updated_at' => $faker->dateTime($max = 'now', $timezone = "Europe/Berlin")
    		]);
    	}



    }
}
