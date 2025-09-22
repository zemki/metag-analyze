<?php

namespace App\Console\Commands;

use App\User;
use Illuminate\Console\Command;

class MakeUserAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:admin {email}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Add a user email to the ADMINS environment variable';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $email = $this->argument('email');

        // Check if user exists
        $user = User::where('email', $email)->first();
        if (! $user) {
            $this->error("User with email '{$email}' does not exist!");

            return;
        }

        // Read current .env file
        $envPath = base_path('.env');
        if (! file_exists($envPath)) {
            $this->error('.env file not found!');

            return;
        }

        $envContent = file_get_contents($envPath);

        // Check if ADMINS line exists
        if (preg_match('/^ADMINS=(.*)$/m', $envContent, $matches)) {
            $currentAdmins = trim($matches[1]);
            $adminEmails = $currentAdmins ? explode(',', $currentAdmins) : [];

            // Check if email is already an admin
            if (in_array($email, $adminEmails)) {
                $this->info("'{$email}' is already an admin!");

                return;
            }

            // Add email to admins
            $adminEmails[] = $email;
            $newAdminsList = implode(',', $adminEmails);

            // Replace in .env file
            $envContent = preg_replace('/^ADMINS=(.*)$/m', "ADMINS={$newAdminsList}", $envContent);
        } else {
            // Add ADMINS line
            $envContent .= "\nADMINS={$email}\n";
        }

        // Write back to .env file
        file_put_contents($envPath, $envContent);

        $this->info("Successfully added '{$email}' as admin!");
        $this->info('You may need to restart your application for changes to take effect.');
        $this->info('You can now access the admin panel at /admin');
    }
}
