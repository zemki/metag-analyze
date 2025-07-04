<?php

use App\User;
use App\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class MartAuthSeeder extends Seeder
{
    /**
     * Run the database seeds for MART API authentication.
     *
     * @return void
     */
    public function run()
    {
        // Fixed bearer token for MART testing
        $token = "mart_test_token_2025";
        $hashedToken = hash('sha256', $token);
        
        // Check if MART user already exists
        $martUser = User::where('email', 'mart@metag-analyze.test')->first();
        
        if (!$martUser) {
            // Create MART API user
            $martUser = new User();
            $martUser->email = 'mart@metag-analyze.test';
            $martUser->password = Hash::make('mart_secure_password_2025');
            $martUser->api_token = $hashedToken;
            $martUser->token_expires_at = now()->addYears(5); // Long-lived token for testing
            $martUser->save();
            
            // Assign user role
            $userRole = Role::where('name', 'user')->first();
            if ($userRole) {
                $martUser->roles()->sync($userRole);
            }
            
            $this->command->info('MART API user created successfully!');
            $this->command->info('Email: mart@metag-analyze.test');
            $this->command->info('Bearer Token: ' . $token);
            $this->command->info('Use this token in Authorization header: Bearer ' . $token);
        } else {
            // Update existing user's token
            $martUser->api_token = $hashedToken;
            $martUser->token_expires_at = now()->addYears(5);
            $martUser->save();
            
            $this->command->info('MART API user token updated!');
            $this->command->info('Bearer Token: ' . $token);
        }
    }
}