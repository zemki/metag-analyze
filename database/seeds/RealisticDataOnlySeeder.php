<?php

use Illuminate\Database\Seeder;

class RealisticDataOnlySeeder extends Seeder
{
    /**
     * Run only the realistic data seeder
     *
     * @return void
     */
    public function run()
    {
        $this->call(RealisticDataSeeder::class);
    }
}