<?php

use App\Cases;
use App\Mart\MartSchedule;
use App\Project;
use App\Role;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Accounts for testers on a staging server, each with Directus project 1
 * (built by MartReferenceProjectSeeder) and a case in it.
 *
 * It asks for the accounts in a terminal. Tests pass them in through
 * $accounts. Without either it stops, so it never creates an account with a
 * known password. A new account gets the typed password or a random one,
 * shown once. An existing account keeps its password and its roles.
 */
class StagingMartSeeder extends Seeder
{
    /**
     * Accounts to create without asking:
     * [['email' => ..., 'password' => optional, 'projects' => [names]], ...].
     */
    public array $accounts = [];

    /**
     * Also give each project the 4 sample participants and their answers, as
     * completed cases a researcher can consult. Asked in the terminal; tests
     * set it here.
     */
    public bool $withSampleParticipants = false;

    public function run()
    {
        $this->command->info('======================================');
        $this->command->info('   STAGING MART SETUP');
        $this->command->info('======================================');
        $this->command->newLine();

        $accounts = $this->accounts ?: $this->askForAccounts();
        $withSampleParticipants = $this->withSampleParticipants
            || (! $this->accounts && $this->command->confirm('Add the 4 sample participants with their answers, as completed cases you can consult?', false));
        $reference = (new MartReferenceProjectSeeder)->setContainer($this->container)->setCommand($this->command);

        $users = [];
        $allProjects = [];
        $allCases = [];

        foreach ($accounts as $account) {
            [$user, $password] = $this->account($account['email'], $account['password'] ?? null);

            $userProjects = [];
            foreach ($account['projects'] as $index => $projectName) {
                $project = $reference->buildProject($user);
                $project->update(['name' => $projectName]);
                $this->startToday($project);
                $this->command->info("  ✓ {$projectName} created for {$user->email} (ID: {$project->id})");

                $case = Cases::create([
                    'project_id' => $project->id,
                    'user_id' => $user->id,
                    'name' => 'participant-' . str_pad($index + 1, 3, '0', STR_PAD_LEFT),
                    'duration' => 'startDay:' . now()->format('d.m.Y') . '|lastDay:' . now()->addMonths(3)->format('d.m.Y'),
                    'created_at' => now(),
                ]);

                if ($withSampleParticipants) {
                    $reference->seedSampleData($project);
                    $this->command->info('    + 4 sample participants with their answers (completed cases)');
                }

                $userProjects[] = $project;
                $allProjects[] = $project;
                $allCases[] = ['case' => $case, 'user_email' => $user->email, 'project_name' => $projectName];
            }

            $users[] = ['user' => $user, 'email' => $user->email, 'password' => $password, 'projects' => $userProjects];
        }

        $this->command->info('✓ Created ' . count($allCases) . ' cases across ' . count($allProjects) . ' projects');
        $this->command->newLine();

        $this->displaySummary($users, $allProjects, $allCases);
    }

    /**
     * Directus's dates for project 1 are past: its repeating questionnaires
     * ended in March 2026. So on staging, the project and each questionnaire
     * start today at midnight and keep their length, and testers can try the
     * repeating questionnaires. The reference project keeps Directus's dates.
     */
    private function startToday(Project $project): void
    {
        $today = Carbon::today();

        $inputs = json_decode($project->inputs, true);
        $options = $inputs[0]['projectOptions'];
        [$options['startDateAndTime'], $options['endDateAndTime']] = $this->windowFrom($today, $options['startDateAndTime'], $options['endDateAndTime']);
        $inputs[0]['projectOptions'] = $options;
        $project->update(['inputs' => json_encode($inputs)]);

        foreach (MartSchedule::where('mart_project_id', $project->martProject()->id)->get() as $schedule) {
            $timing = $schedule->timing_config;
            // Data donation questionnaires have no dates: the participant starts them.
            if (empty($timing['start_date_time'])) {
                continue;
            }
            [$timing['start_date_time'], $timing['end_date_time']] = $this->windowFrom($today, $timing['start_date_time'], $timing['end_date_time'] ?? null);
            $schedule->update(['timing_config' => $timing]);
        }
    }

    /**
     * A start and end, moved to start on $day at midnight, as many days long
     * as before. No end stays no end.
     */
    private function windowFrom(Carbon $day, array $start, ?array $end): array
    {
        $newStart = ['date' => $day->format('Y-m-d'), 'time' => '00:00'];
        if ($end === null) {
            return [$newStart, null];
        }

        $days = (int) Carbon::parse($start['date'])->diffInDays(Carbon::parse($end['date']));

        return [$newStart, ['date' => $day->copy()->addDays($days)->format('Y-m-d'), 'time' => $end['time']]];
    }

    /**
     * The accounts, asked for in the terminal. Without a terminal there is no
     * email address, so it stops.
     */
    private function askForAccounts(): array
    {
        $userCount = null;
        while ($userCount === null) {
            $input = $this->command->ask('How many users do you want to create?', '1');
            if (is_numeric($input) && $input >= 1) {
                $userCount = (int) $input;
            } else {
                $this->command->error('Please enter a valid number (1 or greater)');
            }
        }

        $accounts = [];
        $projectNumber = 1;

        for ($i = 0; $i < $userCount; $i++) {
            $this->command->info('User ' . ($i + 1) . ':');
            $email = $this->command->ask('  Email address');
            if (! $email) {
                throw new RuntimeException(
                    'StagingMartSeeder needs an email address for each account, so run it in a terminal. '
                    . 'It never creates accounts with a known password.'
                );
            }
            $password = $this->command->ask('  Password (leave empty for a random one)');

            $projectCount = null;
            while ($projectCount === null) {
                $input = $this->command->ask('  How many MART projects for this user?', '1');
                if (is_numeric($input) && $input >= 1) {
                    $projectCount = (int) $input;
                } else {
                    $this->command->error('  Please enter a valid number (1 or greater)');
                }
            }

            $projects = [];
            for ($j = 0; $j < $projectCount; $j++) {
                $projects[] = $this->command->ask('    Project name', "Staging MART Project {$projectNumber}");
                $projectNumber++;
            }

            $accounts[] = ['email' => $email, 'password' => $password ?: null, 'projects' => $projects];
            $this->command->newLine();
        }

        return $accounts;
    }

    /**
     * The account and the password to show, which is null for an account that
     * already existed: it keeps its password and gains the researcher role.
     */
    private function account(string $email, ?string $password): array
    {
        $user = User::where('email', $email)->first();
        $shownPassword = null;

        if ($user) {
            $this->command->warn("  {$email} already exists: password and roles unchanged");
        } else {
            $shownPassword = $password ?: Str::password(20, symbols: false);
            $user = User::create([
                'email' => $email,
                'password' => bcrypt($shownPassword),
                'deviceID' => 'STAGING_' . Str::random(10),
            ]);
            $user->forceFill(['email_verified_at' => now()])->save();
            $this->command->info("  ✓ {$email} created with researcher role");
        }

        $researcherRole = Role::where('name', 'researcher')->first();
        if ($researcherRole) {
            $user->roles()->syncWithoutDetaching([$researcherRole->id]);
        }

        return [$user, $shownPassword];
    }

    /**
     * Display summary of created data
     */
    private function displaySummary($users, $projects, $cases)
    {
        $this->command->newLine();
        $this->command->info('======================================');
        $this->command->info('   SUMMARY');
        $this->command->info('======================================');
        $this->command->newLine();

        // Users
        $this->command->info('USERS:');
        foreach ($users as $userData) {
            $this->command->line("  • Email: {$userData['email']}");
            $this->command->line('    Password: ' . ($userData['password'] ?? 'unchanged (existing account)'));
            $this->command->line("    User ID: {$userData['user']->id}");
            $this->command->line('    Projects owned: ' . count($userData['projects']));
        }
        $this->command->newLine();

        // Projects
        $this->command->info('MART PROJECTS CREATED:');
        foreach ($projects as $project) {
            $this->command->line("  • {$project->name}");
            $this->command->line("    Project ID: {$project->id}");
        }
        $this->command->newLine();

        // Cases
        $this->command->info('CASES CREATED (Participant Links):');
        foreach ($cases as $caseData) {
            $this->command->line("  • Case ID: {$caseData['case']->id}");
            $this->command->line("    Participant: {$caseData['case']->name}");
            $this->command->line("    User: {$caseData['user_email']}");
            $this->command->line("    Project: {$caseData['project_name']}");
            $this->command->newLine();
        }

        // MART API Token
        $this->command->info('MART API AUTHENTICATION:');
        $this->command->line('  Bearer Token: mart_test_token_2025');
        $this->command->comment('  (This token is created by MartAuthSeeder - run it if not already done)');
        $this->command->newLine();

        // Example API calls
        $this->command->info('EXAMPLE API REQUESTS:');
        $this->command->newLine();

        if (! empty($cases)) {
            $firstCase = $cases[0];
            $projectId = $firstCase['case']->project_id;
            $caseId = $firstCase['case']->id;
            $userId = $firstCase['user_email'];
            $participantId = $firstCase['case']->name;

            // Get structure
            $this->command->comment('1. Get Project Structure:');
            $this->command->line("curl -X GET 'https://metagtest.uni-bremen.de/mart-api/projects/{$projectId}/structure' \\");
            $this->command->line("  -H 'Authorization: Bearer mart_test_token_2025'");
            $this->command->newLine();

            // Submit entry: questionnaire 2 is the repeating one, with a text
            // item and three single-choice questions, so items 2 to 4.
            $this->command->comment('2. Submit Entry:');
            $this->command->line("curl -X POST 'https://metagtest.uni-bremen.de/mart-api/cases/{$caseId}/submit' \\");
            $this->command->line("  -H 'Authorization: Bearer mart_test_token_2025' \\");
            $this->command->line("  -H 'Content-Type: application/json' \\");
            $this->command->line("  -d '{");
            $this->command->line("    \"projectId\": {$projectId},");
            $this->command->line('    "questionnaireId": 2,');
            $this->command->line("    \"userId\": \"{$userId}\",");
            $this->command->line("    \"participantId\": \"{$participantId}\",");
            $this->command->line('    "questionnaireStarted": ' . (now()->timestamp * 1000) . ',');
            $this->command->line('    "questionnaireDuration": 120000,');
            $this->command->line('    "answers": {"2": [5], "3": [1], "4": [2]},');
            $this->command->line('    "timestamp": ' . (now()->timestamp * 1000) . ',');
            $this->command->line('    "timezone": "Europe/Berlin"');
            $this->command->line("  }'");
            $this->command->newLine();

            // Device info
            $this->command->comment('3. Submit Device Info:');
            $this->command->line("curl -X POST 'https://metagtest.uni-bremen.de/mart-api/device-infos' \\");
            $this->command->line("  -H 'Authorization: Bearer mart_test_token_2025' \\");
            $this->command->line("  -H 'Content-Type: application/json' \\");
            $this->command->line("  -d '{");
            $this->command->line("    \"projectId\": {$projectId},");
            $this->command->line("    \"userId\": \"{$userId}\",");
            $this->command->line("    \"participantId\": \"{$participantId}\",");
            $this->command->line('    "os": "android",');
            $this->command->line('    "osVersion": "14",');
            $this->command->line('    "model": "Pixel 7",');
            $this->command->line('    "manufacturer": "Google",');
            $this->command->line('    "timestamp": ' . (now()->timestamp * 1000) . ',');
            $this->command->line('    "timezone": "Europe/Berlin"');
            $this->command->line("  }'");
        }

        $this->command->newLine();
        $this->command->info('======================================');
        $this->command->info('Staging MART setup complete!');
        $this->command->info('======================================');
    }
}
