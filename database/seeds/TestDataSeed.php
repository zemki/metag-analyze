<?php

use Illuminate\Database\Seeder;

class TestDataSeed extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $path = base_path() . '/database/testdata/media_groups.sql';
        $sql = file_get_contents($path);
        DB::unprepared($sql);

        $path = base_path() . '/database/testdata/media.sql';
        $sql = file_get_contents($path);
        DB::unprepared($sql);

        $path = base_path() . '/database/testdata/places.sql';
        $sql = file_get_contents($path);
        DB::unprepared($sql);

        $path = base_path() . '/database/testdata/communication_partner.sql';
        $sql = file_get_contents($path);
        DB::unprepared($sql);
    }
}
