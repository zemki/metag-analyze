<?php

namespace App\Console\Commands;

use App\User;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class DeleteUserCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:delete';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete a user from the database. He will not be able to log in anymore!';

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
        $info = $this->choice('Email or ID?', ['email', 'id']);

        if ($info === 'email') {
            $email = $this->ask('Enter email');

            try {
                $user = User::where('email', '=', $email)->firstOrFail();
            } catch (ModelNotFoundException $e) {
                $this->warn('user not found!');

                return false;
            }
        } else {
            $id = $this->ask('Enter id');

            try {
                $user = User::where('id', '=', $id)->firstOrFail();
            } catch (ModelNotFoundException $e) {
                $this->warn('user not found!');

                return false;
            }
        }

        if ($this->confirm('ARE YOU SURE YOU WANT TO DELETE THIS USER? '.$user->email, false)) {
            $whichDelete = $this->choice('Soft or Force deletion?', ['Soft', 'Force']);
            if ($whichDelete == 'Force') {
                $user->forceDelete();
            } else {
                $user->delete();
            }


        }

    }
}
