<?php

use App\User;
use Illuminate\Database\Seeder;

class PlaywrightUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * Creates a dedicated user for Playwright testing with known credentials.
     *
     * @return void
     */
    public function run()
    {
        // Create or update Playwright test user
        $user = User::updateOrCreate(
            ['email' => 'playwright@test.com'],
            [
                'password' => bcrypt('playwright123'),
                'email_verified_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        // Assign admin role (ID 1)
        $user->roles()->syncWithoutDetaching([1]);

        if ($this->command) {
            $this->command->info('Playwright test user created: playwright@test.com / playwright123');
            $this->command->info('Admin role assigned to Playwright user');
        }
    }
}
