<?php

use Illuminate\Database\Seeder;
use Faker\Factory as Faker;
use Illuminate\Support\Facades\DB;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

	    DB::table('roles')->insert([[
	            'name' => "admin",
	            'description' => "user with all privileges"
	        ],
	    	[
	            'name' => "researcher",
	            'description' => "create projects and monitor them"
	        ],
	    	[
	            'name' => "user",
	            'description' => "user of the app and data provider"
	        ]]);

    }
}
