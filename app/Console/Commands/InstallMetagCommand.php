<?php

namespace App\Console\Commands;

use App\Role;
use App\User;
use Helper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class InstallMetagCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'metag:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install Metag application';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $this->warn('---Migrating the database---');
        Artisan::call('migrate');
        $this->info(Artisan::output());
        $this->warn('--Cleaning the cache---');
        Artisan::call('optimize:clear');
        $this->info(Artisan::output());
        $this->warn('You will now create your personal user with all access rights.');
        $stored = false;
        while (! $stored) {

            $email = $this->ask('Enter your email');
            $password = $this->ask('Enter your password - minimum 6 chars.');
            $stored = $this->storeUser(0, $email, $password, $user);
        }
        $this->info('You can now enter with the following data:');
        $this->info('Email: ' . $email);
        $this->info('Password: ' . $password);
    }

    public function storeUser($roleId, $email, $password, &$user)
    {
        $role = Role::where('id', $roleId)->first();
        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->warn('Please enter a valid email.');

            return false;
        }
        if (strlen($password) < 6) {
            $this->warn('Please enter a valid password.');

            return false;
        }
        $user = new User();
        $user->email = $email;
        $user->password = bcrypt($password);
        $user->password_token = bcrypt(Helper::random_str(60));
        $user->save();
        $user->roles()->sync($role);

        return true;
    }
}
