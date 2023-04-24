<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserRoleTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        // admin with all db2_column_privileges(connection)
        DB::table('user_roles')->insert([
            'user_id' => 1,
            'role_id' => 1,
        ]);

        DB::table('user_roles')->insert([
            'user_id' => 2,
            'role_id' => 1,
        ]);
    }
}
