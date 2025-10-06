<?php

use Database\Seeders\MartProjectSeeder;
use Database\Seeders\MartDataMigrationSeeder;
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

        // Seed MART authentication user
        $this->call(MartAuthSeeder::class);

        // Seed Playwright test user
        $this->call(PlaywrightUserSeeder::class);

        // Always seed media first
        $this->call(MediaSeeder::class);

        // Ask user which seeder to run
        $choice = $this->command->choice(
            'Which data seeder would you like to run?',
            ['Interactive (original)', 'Realistic (new)', 'MART Projects', 'Combined (Realistic + MART)', 'All'],
            3 // Default to 'Combined'
        );

        switch ($choice) {
            case 'Interactive (original)':
                $this->call(ProjectCaseUserEntrySeeder::class);
                break;
            case 'Realistic (new)':
                $this->call(RealisticDataSeeder::class);
                break;
            case 'MART Projects':
                $this->call(MartProjectSeeder::class);
                break;
            case 'Combined (Realistic + MART)':
                $this->command->info('Running Combined Data Seeder...');
                $this->command->info('Creating realistic standard projects...');
                $this->call(RealisticDataSeeder::class);
                $this->command->info('Creating MART/ESM projects...');
                $this->call(MartProjectSeeder::class);
                $this->command->info('Combined seeding completed!');
                break;
            case 'All':
                $this->call(ProjectCaseUserEntrySeeder::class);
                $this->call(RealisticDataSeeder::class);
                $this->call(MartProjectSeeder::class);
                break;
        }
    }
}
