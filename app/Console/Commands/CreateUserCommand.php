<?php

namespace App\Console\Commands;

use App\Mail\VerificationEmail;
use App\Role;
use App\User;
use Helper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Throwable;

class CreateUserCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:create';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a user';

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
        $email = $this->ask('Enter email');
        /*        $this->info('0 -> Admin');
                $this->info('1 -> Researcher');
                $role = $this->choice('User role?', [0, 1]);
        */
        $password = $this->secret('Enter password');
        if ($this->store(2, $email, $password, $user)) {
            $this->info('User ' . $email . ' created');

            // Reported separately from creation: the account exists either way,
            // and saying "created" then failing hard would suggest otherwise.
            try {
                Mail::to($email)->send(new VerificationEmail($user, config('utilities.emailDefaultText')));
                $this->info('Invitation email sent.');
            } catch (Throwable $e) {
                Log::error('Failed to send the invitation email for a CLI-created user', [
                    'email' => $email,
                    'error' => $e->getMessage(),
                ]);
                $this->error('The user was created, but the invitation email could not be sent:');
                $this->line('  ' . $e->getMessage());
                $this->line('Check the configuration with: php artisan mail:sendtest ' . $email);
            }

            return true;
        }
        $this->info('There it was an error during user creation, please try again.');

        return false;
    }

    public function store($roleId, $email, $password, &$user)
    {
        $role = Role::where('id', $roleId)->first();
        $user = new User;
        $user->email = $email;
        $user->password = bcrypt($password);
        $user->password_token = bcrypt(Helper::random_str(60));
        $user->save();

        if ($role) {
            $user->roles()->sync($role);
        }

        // Mail::to($user->email)->send(new VerificationEmail($user, config('utilities.emailDefaultText')));
        return true;
    }
}
