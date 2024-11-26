<?php

use App\User;
use Faker\Factory as Faker;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UsersProfilesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $faker = Faker::create();

        foreach (User::all() as $user) {

            DB::table('users_profiles')->insert([
                'name' => $faker->name,
                'user_id' => $user->id,
                'address' => $faker->address,
                'workaddress' => $faker->address,
                'birthday' => $faker->date,
                'phonenumber1' => $faker->phoneNumber,
                'phonenumber2' => $faker->phoneNumber,
            ]);
        }

    }
}
