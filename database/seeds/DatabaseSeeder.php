<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {

        $this->call(RolesTableSeeder::class);
        $this->call(UserRoleTableSeeder::class);
        
        // Always seed media first
        $this->call(MediaSeeder::class);
        
        // Ask user which seeder to run
        $choice = $this->command->choice(
            'Which data seeder would you like to run?',
            ['Interactive (original)', 'Realistic (new)', 'Both'],
            2 // Default to 'Realistic'
        );
        
        switch ($choice) {
            case 'Interactive (original)':
                $this->call(ProjectCaseUserEntrySeeder::class);
                break;
            case 'Realistic (new)':
                $this->call(RealisticDataSeeder::class);
                break;
            case 'Both':
                $this->call(ProjectCaseUserEntrySeeder::class);
                $this->call(RealisticDataSeeder::class);
                break;
        }
    }
}
