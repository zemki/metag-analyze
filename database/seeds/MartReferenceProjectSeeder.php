<?php

use App\Cases;
use App\Http\Controllers\MartApiController;
use App\Mart\MartDeviceInfo;
use App\Mart\MartPage;
use App\Mart\MartProject;
use App\Mart\MartQuestion;
use App\Mart\MartSchedule;
use App\Project;
use App\Role;
use App\User;
use Illuminate\Database\Seeder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Directus project 1, "Single and Repeating Questionnaires", rebuilt the way a
 * researcher would build it in the MART editor.
 *
 * The fixtures in fixtures/mart-reference are Helge's snapshot of 27 October
 * 2026, with personal data removed: participants are ref-p1 to ref-p4, uploaded
 * files are skipped answers, and media URLs point at example.test.
 *
 * Questions carry the config the editor saves (AddEditQuestionnaireDialog's
 * save() and MartQuestionnaireController::store). Items the editor can't build
 * yet get the nearest stand-in, so the item order stays the same:
 *  - fileUpload (gap 2) becomes photo upload with file selection
 *  - an item with a scale but no item ID (gap 7) becomes text only
 * posterUrl and randomPositionWithinQuestionnaire (gap 9) are left out until
 * the editor can set them. MartReferenceParityTest lists each difference.
 *
 * Every account gets a random password, shown once. An account that already
 * exists keeps its password and its roles.
 */
class MartReferenceProjectSeeder extends Seeder
{
    public const FIXTURES = __DIR__ . '/fixtures/mart-reference';

    private const PAGE_TYPES = [
        'successPage' => 'success',
        'androidStatsPermissionPage' => 'android_stats_permission',
        'androidNotificationPermissionPage' => 'android_notification_permission',
        'iOSNotificationPermissionPage' => 'ios_notification_permission',
    ];

    /** email => password of the accounts this run created. */
    private array $newAccounts = [];

    public function run(): void
    {
        if (app()->environment('production')) {
            $this->command?->warn('Skipped MartReferenceProjectSeeder: never runs in production.');

            return;
        }

        $owner = $this->account('reference-researcher@example.test', researcher: true);
        $project = $this->buildProject($owner);
        $this->seedSampleData($project);

        $this->command?->info("Seeded the MART reference project (ID: {$project->id}).");
        foreach ($this->newAccounts as $email => $password) {
            $this->command?->line("  {$email}  password: {$password}");
        }
    }

    /**
     * The only place that reads the fixtures, so a newer snapshot is a file swap.
     */
    public static function fixture(string $name): array
    {
        $path = self::FIXTURES . "/{$name}.json";
        if (! is_file($path)) {
            throw new RuntimeException("Missing MART reference fixture: {$path}");
        }

        return json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);
    }

    /**
     * The project, its pages and its questionnaires, without participants.
     */
    public function buildProject(User $owner): Project
    {
        $fixture = self::fixture('project_options');
        $options = $fixture['options'];

        // What ProjectController::store saves for a new MART project.
        $project = Project::create([
            'name' => $fixture['projectName'],
            'description' => 'Directus project 1, rebuilt from a snapshot by MartReferenceProjectSeeder.',
            'created_by' => $owner->id,
            'is_locked' => false,
            'use_entity' => false,
            'inputs' => json_encode([[
                'type' => 'mart',
                'name' => 'MART Configuration',
                'answers' => [],
                'projectOptions' => [
                    'startDateAndTime' => $options['startDateAndTime'],
                    'endDateAndTime' => $options['endDateAndTime'],
                ],
            ]]),
        ]);
        $martProject = MartProject::create(['main_project_id' => $project->id]);

        $this->saveProjectSettings($project, $martProject, $options);

        foreach ($this->questionnairePayloads() as $payload) {
            $this->createQuestionnaire($martProject, $payload);
        }

        return $project;
    }

    /**
     * Each questionnaire as the editor posts it to MartQuestionnaireController::store.
     */
    public function questionnairePayloads(): array
    {
        $options = self::fixture('project_options')['options'];
        $single = collect($options['singleQuestionnaires'] ?? [])->keyBy('questionnaireId');
        $repeating = collect($options['repeatingQuestionnaires'] ?? [])->keyBy('questionnaireId');
        $scales = self::fixture('scales');

        return array_map(fn (array $questionnaire) => [
            'questionnaire_id' => $questionnaire['questionnaireId'],
            'name' => $questionnaire['name'],
            ...$this->schedulePayload($questionnaire, $single, $repeating),
            'questions' => array_map(
                fn (array $item) => $this->questionPayload($item, $scales),
                $questionnaire['items'],
            ),
        ], self::fixture('questionnaires'));
    }

    /**
     * The 12 sample submits, through the same code as the app's submits, and
     * the device infos.
     */
    public function seedSampleData(Project $project): void
    {
        $questionnaires = collect(self::fixture('questionnaires'))->keyBy('questionnaireId');
        $cases = [];

        foreach (self::fixture('submits') as $submit) {
            $case = $cases[$submit['participantId']] ??= $this->participantCase($project, $submit);

            $request = new Request([
                'projectId' => $project->id,
                'questionnaireId' => $submit['questionnaireId'],
                'userId' => $submit['userId'],
                'participantId' => $submit['participantId'],
                'questionnaireStarted' => (int) $submit['questionnaireStarted'],
                'questionnaireDuration' => (int) $submit['questionnaireDuration'],
                'answers' => $this->appAnswers($questionnaires[$submit['questionnaireId']]['items'], $submit['answers']),
                'timestamp' => (int) $submit['timestamp'],
                'timezone' => $submit['timezone'],
            ]);

            $result = (new MartApiController)->submitEntry($request, $case)->getData(true);
            if (! ($result['success'] ?? false)) {
                throw new RuntimeException('A seeded submit was rejected: ' . ($result['message'] ?? 'no message'));
            }
        }

        foreach (self::fixture('device_infos') as $info) {
            // The device-infos endpoint only takes android and ios. Directus also
            // holds a row from its web version, which can't reach us.
            if (! in_array($info['os'], ['android', 'ios'], true)) {
                continue;
            }

            // One row per participant, as the endpoint keeps it.
            MartDeviceInfo::updateOrCreate(
                ['participant_id' => $info['participantId'], 'user_id' => $info['userId']],
                [
                    'os' => $info['os'],
                    'os_version' => $info['osVersion'],
                    'model' => $info['model'],
                    'manufacturer' => $info['manufacturer'],
                    'last_updated' => date('Y-m-d H:i:s', (int) ($info['timestamp'] / 1000)),
                ],
            );
        }
    }

    /**
     * Pages and Android stats settings, as editproject.vue saves them and
     * ProjectController turns the pages into MART rows.
     */
    private function saveProjectSettings(Project $project, MartProject $martProject, array $options): void
    {
        $pagesById = collect(self::fixture('pages'))->keyBy('pageId');
        $typeByPageId = [];
        foreach (self::PAGE_TYPES as $option => $type) {
            if (isset($options[$option])) {
                $typeByPageId[$options[$option]] = $type;
            }
        }

        // Directus lists the pages in the order the app shows them. Our lists
        // follow sort_order, so it comes from that order.
        $pages = [];
        foreach ($options['pages'] as $sortOrder => $pageId) {
            $page = $pagesById[$pageId];
            $pages[] = [
                'id' => null,
                'name' => $page['name'],
                'content' => $page['content'],
                'showOnFirstAppStart' => in_array($pageId, $options['pagesToShowOnFirstAppStart'] ?? [], true),
                'showInMenu' => in_array($pageId, $options['pagesToShowInMenu'] ?? [], true),
                'buttonText' => $page['button_text'],
                'pageType' => $typeByPageId[$pageId] ?? null,
                'sortOrder' => $sortOrder,
            ];
        }

        foreach ($pages as $page) {
            MartPage::create([
                'mart_project_id' => $martProject->id,
                'name' => $page['name'],
                'content' => $page['content'],
                'button_text' => $page['buttonText'],
                'show_on_first_app_start' => $page['showOnFirstAppStart'],
                'show_in_menu' => $page['showInMenu'],
                'page_type' => $page['pageType'],
                'sort_order' => $page['sortOrder'],
            ]);
        }

        $inputs = json_decode($project->inputs, true);
        $inputs[0]['projectOptions'] += [
            'pages' => $pages,
            'collectAndroidStats' => $options['collectAndroidStats'],
            'initialHoursOfAndroidStats' => $options['initialHoursOfAndroidStats'],
            'overlapAndroidStatsHours' => $options['overlapAndroidStatsHours'],
        ];
        $project->update(['inputs' => json_encode($inputs)]);
    }

    private function schedulePayload(array $questionnaire, $single, $repeating): array
    {
        $id = $questionnaire['questionnaireId'];
        $isIos = ! empty($questionnaire['isIosDataDonation']);
        $isAndroid = ! empty($questionnaire['isAndroidDataDonation']);

        // The participant starts a data donation questionnaire, so Directus
        // gives it no schedule.
        if ($isIos || $isAndroid) {
            return [
                'type' => 'single',
                'is_ios_data_donation' => $isIos,
                'is_android_data_donation' => $isAndroid,
                ...$this->notificationPayload([]),
            ];
        }

        if ($schedule = $repeating->get($id)) {
            return [
                'type' => 'repeating',
                'start_date_time' => $schedule['startDateAndTime'],
                'end_date_time' => $schedule['endDateAndTime'],
                'min_break_between' => $schedule['minBreakBetweenQuestionnaire'],
                'daily_interval_duration' => $schedule['dailyIntervalDuration'],
                'max_daily_submits' => $schedule['maxDailySubmits'],
                'daily_start_time' => $schedule['dailyStartTime'],
                'daily_end_time' => $schedule['dailyEndTime'],
                'quest_available_at' => $schedule['questAvailableAt'],
                ...$this->notificationPayload($schedule),
            ];
        }

        if ($schedule = $single->get($id)) {
            return [
                'type' => 'single',
                'start_date_time' => $schedule['startDateAndTime'],
                'show_after_repeating' => $schedule['showAfterRepeatingQ'] ?? null,
                ...$this->notificationPayload($schedule),
            ];
        }

        throw new RuntimeException("Questionnaire {$id} has no schedule in project_options.json.");
    }

    /**
     * The app shows a progress bar unless it's switched off, and notifications
     * only when they're switched on, so a missing key means that.
     */
    private function notificationPayload(array $schedule): array
    {
        return [
            'show_progress_bar' => $schedule['showProgressBar'] ?? true,
            'show_notifications' => $schedule['showNotifications'] ?? false,
            'notification_text' => $schedule['notificationText'] ?? null,
        ];
    }

    private function questionPayload(array $item, array $scales): array
    {
        $itemOptions = $item['options'] ?? [];

        // An item without an item ID is text only. Directus can give one a
        // scale, shown as a disabled selection (gap 7).
        $isTextOnly = ($item['itemId'] ?? null) === null;
        [$type, $config] = $isTextOnly
            ? ['display', []]
            : $this->typeAndConfig($scales[(string) $item['scaleId']]);

        if (! empty($itemOptions['randomizationGroupId'])) {
            $config['randomizationGroupId'] = $itemOptions['randomizationGroupId'];
        }
        if (! empty($itemOptions['randomizeAnswers'])) {
            $config['randomizeAnswers'] = true;
        }
        if (! empty($itemOptions['itemGroup'])) {
            $config['itemGroup'] = (string) $itemOptions['itemGroup'];
        }

        return [
            'text' => $item['text'],
            'image_url' => $item['imageUrl'] ?? null,
            'video_url' => $item['videoUrl'] ?? null,
            'type' => $type,
            'mandatory' => ! $isTextOnly && empty($itemOptions['noValueAllowed']),
            'config' => $config,
        ];
    }

    /**
     * A Directus scale as our question type and config, keys in the order the
     * editor writes them.
     */
    private function typeAndConfig(array $scale): array
    {
        switch ($scale['type']) {
            case 'number':
                $config = ['min' => $scale['minValue'], 'max' => $scale['maxValue']];
                if (! empty($scale['maxDigits'])) {
                    $config['maxDigits'] = $scale['maxDigits'];
                }

                return ['number', $this->withBehaviour($config, $scale)];

            case 'range':
                return ['range', $this->withBehaviour([
                    'min' => $scale['rangeOptions']['minValue'],
                    'max' => $scale['rangeOptions']['maxValue'],
                    'step' => $scale['rangeOptions']['steps'],
                ], $scale)];

            case 'radio':
                return ['one choice', $this->choiceConfig($scale, $scale['radioOptions'])];

            case 'checkbox':
                return ['multiple choice', $this->choiceConfig($scale, $scale['checkboxOptions'])];

            case 'rangeValues':
                return ['range values', $this->choiceConfig($scale, $scale['rangeValueOptions'])];

            case 'text':
            case 'textarea':
                $config = [];
                if (! empty($scale['textOptions']['placeholder'])) {
                    $config['placeholder'] = $scale['textOptions']['placeholder'];
                }

                return [$scale['type'], $this->withBehaviour($config, $scale)];

            case 'photoUpload':
                $options = $scale['photoUploadOptions'] ?? [];

                return ['photo', $this->withBehaviour(['photoUploadOptions' => [
                    'allowFileSelection' => ! empty($options['allowFileSelection']),
                    'allowEditing' => ! empty($options['allowEditing']),
                    'quality' => $options['quality'] ?? null,
                ]], $scale)];

            case 'fileUpload': // Gap 2: photo upload with file selection until the type exists.
                return ['photo', $this->withBehaviour(['photoUploadOptions' => [
                    'allowFileSelection' => true,
                    'allowEditing' => false,
                    'quality' => null,
                ]], $scale)];

            case 'audioUpload':
                $options = $scale['audioUploadOptions'] ?? [];

                return ['audio', $this->withBehaviour(['audioUploadOptions' => [
                    'allowFileSelection' => ! empty($options['allowFileSelection']),
                    'maxFileSize' => $options['maxFileSize'] ?? null,
                    'maxAudioLength' => $options['maxAudioLength'] ?? null,
                ]], $scale)];

            case 'videoUpload':
                $options = $scale['videoUploadOptions'] ?? [];

                return ['video', $this->withBehaviour(['videoUploadOptions' => [
                    'allowFileSelection' => ! empty($options['allowFileSelection']),
                    'maxVideoLength' => $options['maxVideoLength'] ?? null,
                    'maxFileSize' => $options['maxFileSize'] ?? null,
                ]], $scale)];
        }

        throw new RuntimeException("Unknown Directus scale type: {$scale['type']}");
    }

    private function choiceConfig(array $scale, array $options): array
    {
        $texts = [];
        $otherLabel = null;

        foreach ($options as $option) {
            if ($option['value'] === 'text') {
                $otherLabel = $option['text'];

                continue;
            }
            // The API numbers options from 1 in their saved order, so the
            // seeded scale only matches when Directus numbers them that way too.
            if ($option['value'] !== count($texts) + 1) {
                throw new RuntimeException('Directus options must be numbered from 1 in order: ' . json_encode($options));
            }
            $texts[] = $option['text'];
        }

        $config = ['options' => $texts];
        if ($otherLabel !== null) {
            $config['includeOtherOption'] = true;
            $config['otherTextType'] = $scale['textOptions']['type'] ?? 'text';
            $config['otherOptionLabel'] = $otherLabel;
        }

        return $this->withBehaviour($config, $scale, isChoice: true);
    }

    /**
     * Default, timer and jump, in the order the editor adds them. A choice
     * default and jump are saved as the option's key, which starts at 0, and
     * the jump as a string.
     */
    private function withBehaviour(array $config, array $scale, bool $isChoice = false): array
    {
        if (isset($scale['defaultValue'])) {
            $config['defaultValue'] = $isChoice ? $scale['defaultValue'] - 1 : $scale['defaultValue'];
        }
        if (isset($scale['timer'])) {
            $config['timer'] = [
                'time' => $scale['timer']['time'],
                'showCountdown' => $scale['timer']['showCountdown'] ?? false,
            ];
        }
        if ($isChoice && isset($scale['jump'])) {
            $config['jump'] = [
                'jumpCondition' => (string) ($scale['jump']['jumpCondition'] - 1),
                'jumpOver' => $scale['jump']['jumpOver'],
            ];
        }

        return $config;
    }

    /**
     * What MartQuestionnaireController::store saves for this payload.
     * MartReferenceProjectSeederTest posts the same payloads to the real route
     * and compares the rows.
     */
    private function createQuestionnaire(MartProject $martProject, array $payload): void
    {
        DB::connection('mart')->transaction(function () use ($martProject, $payload) {
            $schedule = MartSchedule::create([
                'mart_project_id' => $martProject->id,
                'questionnaire_id' => $payload['questionnaire_id'],
                'name' => $payload['name'],
                'introductory_text' => null,
                'type' => $payload['type'],
                'timing_config' => [
                    'start_date_time' => $payload['start_date_time'] ?? null,
                    'end_date_time' => $payload['end_date_time'] ?? null,
                    'start_on_first_login' => false,
                    'start_hours_after_login' => 0,
                    'use_dynamic_end_date' => false,
                    'daily_interval_duration' => $payload['daily_interval_duration'] ?? null,
                    'min_break_between' => $payload['min_break_between'] ?? null,
                    'max_daily_submits' => $payload['max_daily_submits'] ?? null,
                    'max_total_submits' => null,
                    'daily_start_time' => $payload['daily_start_time'] ?? null,
                    'daily_end_time' => $payload['daily_end_time'] ?? null,
                    'quest_available_at' => $payload['quest_available_at'] ?? null,
                    'show_after_repeating' => $payload['show_after_repeating'] ?? null,
                ],
                'notification_config' => [
                    'show_progress_bar' => $payload['show_progress_bar'],
                    'show_notifications' => $payload['show_notifications'],
                    'notification_text' => $payload['notification_text'],
                ],
                'is_ios_data_donation' => $payload['is_ios_data_donation'] ?? false,
                'is_android_data_donation' => $payload['is_android_data_donation'] ?? false,
            ]);

            foreach ($payload['questions'] as $index => $question) {
                MartQuestion::create([
                    'schedule_id' => $schedule->id,
                    'position' => $index + 1,
                    'text' => $question['text'],
                    'image_url' => $question['image_url'],
                    'video_url' => $question['video_url'],
                    'type' => $question['type'],
                    'config' => $question['config'],
                    'is_mandatory' => $question['mandatory'],
                    'version' => 1,
                ]);
            }
        });
    }

    /**
     * The answers as the app sends them: keyed by our item ID, which is the
     * item's place in the questionnaire, with an empty array for each item
     * left unanswered. Directus keys them by its own item IDs.
     */
    private function appAnswers(array $items, array $directusAnswers): array
    {
        $answers = [];
        $known = [];

        foreach ($items as $index => $item) {
            if (($item['itemId'] ?? null) === null) {
                continue;
            }
            $known[] = (string) $item['itemId'];
            $answers[(string) ($index + 1)] = $directusAnswers[(string) $item['itemId']] ?? [];
        }

        $unknown = array_diff(array_map('strval', array_keys($directusAnswers)), $known);
        if ($unknown) {
            throw new RuntimeException('Submit answers items that the questionnaire lacks: ' . implode(', ', $unknown));
        }

        return $answers;
    }

    /**
     * Each participant signs in with an account of their own, which owns
     * their case, as check-access would set it up.
     */
    private function participantCase(Project $project, array $submit): Cases
    {
        $user = $this->account($submit['userId']);

        return Cases::create([
            'name' => $submit['participantId'],
            'user_id' => $user->id,
            'project_id' => $project->id,
            'duration' => 'startDay:' . now()->format('d.m.Y') . '|',
            'first_login_at' => now(),
        ]);
    }

    /**
     * An account for this run. One that already exists keeps its password and
     * its roles.
     */
    private function account(string $email, bool $researcher = false): User
    {
        $user = User::where('email', $email)->first();

        if (! $user) {
            $password = Str::password(20, symbols: false);
            $user = User::create(['email' => $email, 'password' => bcrypt($password)]);
            $user->forceFill(['email_verified_at' => now()])->save();
            $this->newAccounts[$email] = $password;
        }

        $role = $researcher ? Role::where('name', 'researcher')->first() : null;
        if ($role) {
            $user->roles()->syncWithoutDetaching([$role->id]);
        }

        return $user;
    }
}
