<?php

use App\User;
use Illuminate\Database\Seeder;

class ProjectCaseUserEntrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {

        $projectCount = (int) $this->command->ask('How many projects do you need ?', 1);
        if ($projectCount > 0) {
            $casesCount = (int) $this->command->ask('How many cases do you need ? You\'ll be asked how many entries for each case.', 1);

            $admin = User::find(1); // Assuming the admin user is the first user in the users table

            // Create the Users
            $projects = \App\Project::factory()->count($projectCount)->create(['created_by' => $admin->id]);
            foreach ($projects as $p) {
                $cases = \App\Cases::factory()->count($casesCount)->create(['project_id' => $p->id, 'user_id' => $admin->id]);

                $c = $admin->latestCase;
                $entriesCount = (int) $this->command->ask('How many entries for this case do you need ?', 1);
                $p->media()->sync(\App\Media::inRandomOrder()->limit(10)->get());
                \App\Entry::factory()->count($entriesCount)->create(['case_id' => $c->id]);

            }
        }
        $this->command->info('Everything Created!');
    }
}
