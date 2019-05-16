<?php

use App\Communication_Partner;
use App\Media;
use App\Place;
use Illuminate\Database\Seeder;
use \App\User;
use \App\Project;
use \App\Cases;
use \App\Entry;

class ProjectCaseUserEntrySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
 // How many genres you need, defaulting to 10
        $projectCount = (int)$this->command->ask('How many projects do you need ?', 1);
        $casesCount = (int)$this->command->ask('How many cases do you need ? You"ll be asked how many entries for each case.', 1);

        $alessandrobelli = User::where('email', '=', 'belli@uni-bremen.de')->first();

        // Create the Users
        $projects = factory(App\Project::class, $projectCount)->create(['created_by' => $alessandrobelli->id]);
        foreach ($projects as $p) {
            $cases = factory(App\Cases::class, $casesCount)->create(['project_id'=>$p->id,'user_id' => $alessandrobelli->id]);

            $c = $alessandrobelli->latestCase;
            $entriesCount = (int)$this->command->ask('How many entries for this case do you need ?', 1);
            $p->media()->sync(\App\Media::inRandomOrder()->limit(10)->get());
            $p->places()->sync(factory(App\Place::class, 10)->create());
            $p->communication_partners()->sync(factory(App\Communication_Partner::class, 10)->create());
            factory(App\Entry::class, $entriesCount)->create(['case_id'=>$c->id]);


        }

        $this->command->info('Everything Created!');
    }
}
