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

        $alessandrobelli = User::where('email', '=', 'belli@uni-bremen.de')->first();

        // Create the Users
        $projects = factory(App\Project::class, $projectCount)->create(['created_by' => $alessandrobelli->id]);
        foreach ($projects as $p) {
            $cases = factory(App\Cases::class, $casesCount)->create(['project_id' => $p->id, 'user_id' => $alessandrobelli->id]);

            $c = $alessandrobelli->latestCase;
            $entriesCount = (int) $this->command->ask('How many entries for this case do you need ?', 1);
            $p->media()->sync(\App\Media::inRandomOrder()->limit(10)->get());
            factory(App\Entry::class, $entriesCount)->create(['case_id' => $c->id]);

        }
        }
        $this->command->info('Everything Created!');
    }
}
