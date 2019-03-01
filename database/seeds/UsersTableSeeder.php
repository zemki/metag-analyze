<?php

use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\DB;

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
	            'username' => "admin",
	            'email' => "belli@uni-bremen.de",
	            'password' => bcrypt('1234'),
	            'created_at' => $faker->dateTime($max = 'now', $timezone = "Europe/Berlin"),
	            'updated_at' => $faker->dateTime($max = 'now', $timezone = "Europe/Berlin")
	        ],
	        [
	            'username' => "test_user",
	            'email' => "belli@uni.de",
	            'password' => bcrypt('1234'),
	            'created_at' => $faker->dateTime($max = 'now', $timezone = "Europe/Berlin"),
	            'updated_at' => $faker->dateTime($max = 'now', $timezone = "Europe/Berlin")
	        ]);
	    
	    $this->command->info('Admin user seeded');
	    $fakeusers = 100;
 		
 		$this->command->info('Seeding fake users');
	    /// create fake data
    	foreach (range(1,$fakeusers) as $index) {
    		
    		$this->command->getOutput()->write("<info>".$index."</info>\r");



	        DB::table('users')->insert([
	            'username' => $faker->username,
	            'email' => $faker->email,
	            'password' => bcrypt('secret'),
	            'created_at' => $faker->dateTime($max = 'now', $timezone = "Europe/Berlin"),
	            'updated_at' => $faker->dateTime($max = 'now', $timezone = "Europe/Berlin")
	        ]);
	    }



    }
}
