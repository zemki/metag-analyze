<?php

use App\User;
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
        // Ask for user email and password interactively
        $email = trim($this->command->ask('Enter the email for the admin user'));
        $password = trim($this->command->secret('Enter the password for the admin user'));

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->command->error('Invalid email format!');
            return;
        }

        // Check if the user already exists
        $user = User::where('email', '=', $email)->first();

        if ($user) {
            // Update existing user's password
            $user->password = bcrypt($password);
            $user->email_verified_at = now(); // Ensure verified
            $user->save();
            $this->command->info("Updated the existing user's password.");
            $this->command->info("Email: {$email}");
            $this->command->info("Password: {$password}");
        } else {
            // Create a new user
            $user = User::create([
                'email' => $email,
                'password' => bcrypt($password),
                'email_verified_at' => now(),
            ]);
            $this->command->info('Created a new user.');
            $this->command->info("Email: {$email}");
            $this->command->info("Password: {$password}");
        }

        // Assign the admin role to the user (check for duplicates)
        $roleExists = DB::table('user_roles')
            ->where('user_id', $user->id)
            ->where('role_id', 1)
            ->exists();

        if (!$roleExists) {
            DB::table('user_roles')->insert([
                'user_id' => $user->id,
                'role_id' => 1, // Assuming 1 is the role ID for admin
            ]);
            $this->command->info('Admin role assigned to the user.');
        } else {
            $this->command->info('User already has admin role.');
        }
    }
}
