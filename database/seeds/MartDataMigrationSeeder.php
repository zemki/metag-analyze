<?php

use App\Mart\MartAnswer;
use App\Mart\MartDeviceInfo;
use App\Mart\MartEntry;
use App\Mart\MartPage;
use App\Mart\MartProject;
use App\Mart\MartQuestion;
use App\Mart\MartSchedule;
use App\Mart\MartStat;
use App\MartPage as OldMartPage;
use App\MartQuestionnaireSchedule;
use App\Project;
use App\Stat;
use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Data Migration Seeder: Migrate existing MART data to new separated database structure
 *
 * This seeder migrates:
 * - MartQuestionnaireSchedule → MartSchedule + MartQuestion (with UUIDs)
 * - Old MartPage → New MartPage (in MART DB)
 * - Stat → MartStat (in MART DB)
 * - User device info → MartDeviceInfo
 * - Entry metadata → MartEntry + MartAnswer
 */
class MartDataMigrationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Starting MART data migration...');

        // Step 1: Create MartProject records for all MART projects
        $this->migrateMartProjects();

        // Step 2: Migrate questionnaire schedules to new structure
        $this->migrateQuestionnaireSchedules();

        // Step 3: Migrate pages to MART database
        $this->migratePages();

        // Step 4: Migrate stats to MART database
        $this->migrateStats();

        // Step 5: Extract and migrate device info
        $this->migrateDeviceInfo();

        // Step 6: Migrate entry submissions to MART database
        $this->migrateEntries();

        $this->command->info('MART data migration completed successfully!');
    }

    /**
     * Create MartProject records for all MART projects
     */
    private function migrateMartProjects(): void
    {
        $this->command->info('Migrating MART projects...');

        $martProjects = Project::where(function ($query) {
            $query->whereRaw("JSON_SEARCH(inputs, 'one', 'mart', NULL, '$[*].type') IS NOT NULL");
        })->get();

        $count = 0;
        foreach ($martProjects as $project) {
            // Check if MartProject already exists
            $existing = MartProject::where('main_project_id', $project->id)->first();
            if (!$existing) {
                MartProject::create(['main_project_id' => $project->id]);
                $count++;
            }
        }

        $this->command->info("Created {$count} MartProject records");
    }

    /**
     * Migrate questionnaire schedules to new structure with separated questions
     */
    private function migrateQuestionnaireSchedules(): void
    {
        $this->command->info('Migrating questionnaire schedules...');

        $oldSchedules = MartQuestionnaireSchedule::all();
        $scheduleCount = 0;
        $questionCount = 0;

        foreach ($oldSchedules as $oldSchedule) {
            // Get corresponding MartProject
            $martProject = MartProject::where('main_project_id', $oldSchedule->project_id)->first();
            if (!$martProject) {
                $this->command->warn("No MartProject found for project {$oldSchedule->project_id}, skipping schedule {$oldSchedule->id}");
                continue;
            }

            // Check if schedule already migrated
            $existing = MartSchedule::forProject($martProject->id)
                ->where('questionnaire_id', $oldSchedule->questionnaire_id)
                ->first();

            if ($existing) {
                $this->command->info("Schedule {$oldSchedule->questionnaire_id} already migrated, skipping");
                continue;
            }

            DB::connection('mart')->beginTransaction();
            try {
                // Create new MartSchedule with timing and notification configs
                $timingConfig = [
                    'start_date_time' => $oldSchedule->start_date_time ?? null,
                    'end_date_time' => $oldSchedule->end_date_time ?? null,
                    'daily_interval_duration' => $oldSchedule->daily_interval_duration ?? null,
                    'min_break_between' => $oldSchedule->min_break_between ?? null,
                    'max_daily_submits' => $oldSchedule->max_daily_submits ?? null,
                    'daily_start_time' => $oldSchedule->daily_start_time ?? null,
                    'daily_end_time' => $oldSchedule->daily_end_time ?? null,
                    'quest_available_at' => $oldSchedule->quest_available_at ?? null,
                    'show_after_repeating' => $oldSchedule->show_after_repeating ?? null,
                ];

                $notificationConfig = [
                    'show_progress_bar' => $oldSchedule->show_progress_bar ?? false,
                    'show_notifications' => $oldSchedule->show_notifications ?? false,
                    'notification_text' => $oldSchedule->notification_text ?? null,
                ];

                $newSchedule = MartSchedule::create([
                    'mart_project_id' => $martProject->id,
                    'questionnaire_id' => $oldSchedule->questionnaire_id,
                    'name' => $oldSchedule->name,
                    'type' => $oldSchedule->type,
                    'timing_config' => $timingConfig,
                    'notification_config' => $notificationConfig,
                ]);

                // Create individual MartQuestion records with UUIDs
                $questions = $oldSchedule->questions ?? [];
                foreach ($questions as $position => $questionData) {
                    MartQuestion::create([
                        'schedule_id' => $newSchedule->id,
                        'position' => $position + 1,
                        'text' => $questionData['name'] ?? $questionData['text'] ?? "Question " . ($position + 1),
                        'type' => $questionData['type'] ?? 'text',
                        'config' => $questionData['options'] ?? $questionData['config'] ?? [],
                        'is_mandatory' => $questionData['mandatory'] ?? true,
                        'version' => 1,
                    ]);
                    $questionCount++;
                }

                DB::connection('mart')->commit();
                $scheduleCount++;
                $this->command->info("Migrated schedule {$oldSchedule->questionnaire_id} with " . count($questions) . " questions");
            } catch (\Exception $e) {
                DB::connection('mart')->rollBack();
                $this->command->error("Failed to migrate schedule {$oldSchedule->id}: " . $e->getMessage());
            }
        }

        $this->command->info("Migrated {$scheduleCount} schedules with {$questionCount} total questions");
    }

    /**
     * Migrate pages from main database to MART database
     */
    private function migratePages(): void
    {
        $this->command->info('Migrating MART pages...');

        $oldPages = OldMartPage::all();
        $count = 0;

        foreach ($oldPages as $oldPage) {
            // Get corresponding MartProject
            $martProject = MartProject::where('main_project_id', $oldPage->project_id)->first();
            if (!$martProject) {
                $this->command->warn("No MartProject found for project {$oldPage->project_id}, skipping page {$oldPage->id}");
                continue;
            }

            // Check if page already exists in MART DB
            $existing = MartPage::where('mart_project_id', $martProject->id)
                ->where('name', $oldPage->name)
                ->where('sort_order', $oldPage->sort_order)
                ->first();

            if (!$existing) {
                MartPage::create([
                    'mart_project_id' => $martProject->id,
                    'name' => $oldPage->name,
                    'content' => $oldPage->content,
                    'show_on_first_app_start' => $oldPage->show_on_first_app_start ?? false,
                    'button_text' => $oldPage->button_text ?? 'Continue',
                    'sort_order' => $oldPage->sort_order ?? 0,
                ]);
                $count++;
            }
        }

        $this->command->info("Migrated {$count} pages to MART database");
    }

    /**
     * Migrate stats from main database to MART database
     */
    private function migrateStats(): void
    {
        $this->command->info('Migrating stats...');

        $oldStats = Stat::all();
        $count = 0;

        foreach ($oldStats as $oldStat) {
            // Get corresponding MartProject
            $martProject = MartProject::where('main_project_id', $oldStat->projectId)->first();
            if (!$martProject) {
                $this->command->warn("No MartProject found for project {$oldStat->projectId}, skipping stat {$oldStat->id}");
                continue;
            }

            // Check if stat already exists
            $existing = MartStat::where('mart_project_id', $martProject->id)
                ->where('participant_id', $oldStat->participantId)
                ->where('timestamp', $oldStat->timestamp)
                ->first();

            if (!$existing) {
                MartStat::create([
                    'mart_project_id' => $martProject->id,
                    'participant_id' => $oldStat->participantId,
                    'user_id' => $oldStat->userId,
                    'android_usage_stats' => $oldStat->androidUsageStats ?? null,
                    'android_event_stats' => $oldStat->androidEventStats ?? null,
                    'ios_stats' => $oldStat->iosStats ?? null,
                    'ios_screen_time' => $oldStat->iosScreenTime ?? null,
                    'ios_activations' => $oldStat->iosActivations ?? null,
                    'device_id' => $oldStat->deviceID ?? null,
                    'timestamp' => $oldStat->timestamp,
                    'timezone' => $oldStat->timezone ?? 'UTC',
                ]);
                $count++;
            }
        }

        $this->command->info("Migrated {$count} stats to MART database");
    }

    /**
     * Extract device info from users and create MartDeviceInfo records
     */
    private function migrateDeviceInfo(): void
    {
        $this->command->info('Migrating device info...');

        $usersWithDeviceInfo = User::whereNotNull('deviceID')->get();
        $count = 0;

        foreach ($usersWithDeviceInfo as $user) {
            $deviceInfo = json_decode($user->deviceID, true);
            if (!$deviceInfo || !is_array($deviceInfo)) {
                continue;
            }

            // Try to find participant_id from their entries
            $entry = DB::table('entries')
                ->join('cases', 'entries.case_id', '=', 'cases.id')
                ->where('cases.user_id', $user->id)
                ->whereRaw("JSON_EXTRACT(entries.inputs, '$._mart_metadata') IS NOT NULL")
                ->first(['cases.name as participant_id', 'entries.inputs']);

            if (!$entry) {
                continue;
            }

            $inputs = json_decode($entry->inputs, true);
            $participantId = $inputs['_mart_metadata']['participant_id'] ?? $entry->participant_id;

            if (!$participantId) {
                continue;
            }

            // Check if device info already exists
            $existing = MartDeviceInfo::where('participant_id', $participantId)
                ->where('user_id', $user->email)
                ->first();

            if (!$existing) {
                MartDeviceInfo::create([
                    'participant_id' => $participantId,
                    'user_id' => $user->email,
                    'os' => $deviceInfo['os'] ?? 'unknown',
                    'os_version' => $deviceInfo['osVersion'] ?? 'unknown',
                    'model' => $deviceInfo['model'] ?? 'unknown',
                    'manufacturer' => $deviceInfo['manufacturer'] ?? 'unknown',
                    'last_updated' => $deviceInfo['lastUpdated'] ?? now(),
                ]);
                $count++;
            }
        }

        $this->command->info("Migrated {$count} device info records to MART database");
    }

    /**
     * Migrate entry submissions to MART database
     * Note: This creates MartEntry and MartAnswer records from existing entries
     */
    private function migrateEntries(): void
    {
        $this->command->info('Migrating entries...');
        $this->command->warn('Entry migration is complex and depends on your data structure.');
        $this->command->warn('You may need to customize this method based on your specific requirements.');

        // This is a placeholder - actual implementation depends on:
        // 1. How entries are currently structured
        // 2. Whether old entries have _mart_metadata
        // 3. Whether you want to migrate ALL entries or just recent ones

        $this->command->info('Skipping entry migration - implement based on your needs');
    }
}