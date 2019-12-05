<?php

namespace App\Console\Commands;

use Helper;
use App\Role;
use App\User;
use App\Permission;
use App\Mail\VerificationEmail;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

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
        $this->info('0 -> Admin');
        $this->info('1 -> Researcher');
        $role = $this->choice('User role?', [0, 1]);

        if ($this->store($role, $email, $user)) {
            Mail::to($email)->send(new VerificationEmail($user, config('utilities.emailDefaultText')));
            $this->info('An email was sent to '.$user->email.' he/she needs to set the password.');

            return true;
        } else {
            $this->info('There it was an error during user creation, please try again.');

            return false;
        }
    }

    public function store($roleId, $email, &$user)
    {
        $role = Role::where('id', $roleId)->first();

            $user = new User();

            $user->email = $email;
            $user->password = bcrypt(Helper::random_str(60));
            $user->password_token = bcrypt(Helper::random_str(60));
            $user->save();
            $user->roles()->sync($role);
            Mail::to($user->email)->send(new VerificationEmail($user, config('utilities.emailDefaultText')));
            return true;

    }
}