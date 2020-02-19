<?php

namespace App\Console\Commands;

use App\Project;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;

class DeleteProjectCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'project:delete';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Force Delete a Project and all relative data.';

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
        $info = $this->choice('Name or ID?', ['name', 'id']);

        if ($info === 'name') {
            $name = $this->ask('Enter project name');

            try {
                $project = Project::where('name', '=', $name)->firstOrFail();
            } catch (ModelNotFoundException $e) {
                $this->warn('project not found!');

                return false;
            }
        } else {
            $id = $this->ask('Enter id');

            try {
                $project = Project::where('id', '=', $id)->firstOrFail();
            } catch (ModelNotFoundException $e) {
                $this->warn('project not found!');

                return false;
            }
        }

        $this->confirm('ARE YOU SURE YOU WANT TO DELETE THIS PROJECT AND ALL RELATIVE DATA? ' . $project->name, false);

        Auth::loginUsingId($project->created_by()->id, true);
        $project->delete();
        Auth::logout();

        $this->warn("PROJECT DELETED!");

    }
}
