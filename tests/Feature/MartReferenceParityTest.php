<?php

namespace Tests\Feature;

use App\Project;
use App\User;
use Illuminate\Console\Command;
use Illuminate\Console\OutputStyle;
use MartReferenceProjectSeeder;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\NullOutput;
use Tests\TestCase;

/**
 * Directus project 1 against the same project seeded here and read from the
 * structure endpoint, the way the app reads it.
 *
 * The reference is the fixtures in database/seeds/fixtures/mart-reference:
 * Helge's snapshot of Directus from 27 October 2026, with personal data
 * removed. Live Directus had 5 more items in November, so a newer snapshot
 * may add lines here.
 *
 * Both sides are normalised before the comparison:
 * - IDs are left out: pages are matched by name, scales by the item using them,
 *   items by their place in the questionnaire.
 * - Dates use the app's DD.MM.YYYY.
 * - A key that Directus leaves out counts as the app's default (24 August
 *   build), and a null counts as a missing key.
 * - When a scale's type differs, only the types are compared.
 * - Points the app settles are dropped: it never reads an item's buttonText,
 *   a questionnaire's collectDeviceInfos, or fileRequired.
 */
class MartReferenceParityTest extends TestCase
{
    /**
     * Every way the seeded project differs from Directus. Fixing a gap removes
     * its lines, and the test fails until they're gone, so this list only
     * shrinks.
     */
    private const EXPECTED_DIFFERENCES = [
        // Gap 8: neither minimum break is stored or sent.
        'projectOptions.minBreakBetweenAndroidStats: only in Directus (6)',
        'projectOptions.minBreakBetweenDataDonationQuestionnaires: only in Directus (1)',
        // Sent by MART only: textOptions.type on a text scale. The app reads it
        // only for an "other" answer.
        'questionnaires.1.items.6.scale.textOptions.type: only in MART ("textarea")',
        // Not in the gap list: Directus scales 12 and 13 have an "other" option
        // without textOptions, so the app shows no field for it. The editor
        // always adds a field.
        'questionnaires.1.items.17.scale.textOptions.type: only in MART ("text")',
        'questionnaires.1.items.18.scale.textOptions.type: only in MART ("text")',
        // Sent by MART only: martTypes.ts requires rangeOptions.defaultValue.
        'questionnaires.1.items.28.scale.rangeOptions.defaultValue: only in MART (55)',
        // Not offered by design: Directus scale 501 allows .svg, which stays
        // out because an inline SVG can run script on our origin. The scale
        // is used by six items, hence six lines.
        'questionnaires.1.items.34.scale.fileUploadOptions.allowedFormats: Directus [".jpg",".jpeg",".pdf",".svg",".mp4"], MART [".jpg",".jpeg",".pdf",".mp4"]',
        'questionnaires.1.items.35.scale.fileUploadOptions.allowedFormats: Directus [".jpg",".jpeg",".pdf",".svg",".mp4"], MART [".jpg",".jpeg",".pdf",".mp4"]',
        'questionnaires.5.items.2.scale.fileUploadOptions.allowedFormats: Directus [".jpg",".jpeg",".pdf",".svg",".mp4"], MART [".jpg",".jpeg",".pdf",".mp4"]',
        'questionnaires.5.items.3.scale.fileUploadOptions.allowedFormats: Directus [".jpg",".jpeg",".pdf",".svg",".mp4"], MART [".jpg",".jpeg",".pdf",".mp4"]',
        'questionnaires.6.items.6.scale.fileUploadOptions.allowedFormats: Directus [".jpg",".jpeg",".pdf",".svg",".mp4"], MART [".jpg",".jpeg",".pdf",".mp4"]',
        'questionnaires.7.items.5.scale.fileUploadOptions.allowedFormats: Directus [".jpg",".jpeg",".pdf",".svg",".mp4"], MART [".jpg",".jpeg",".pdf",".mp4"]',
    ];

    /** What the app assumes when Directus leaves a key out. */
    private const APP_DEFAULTS = [
        'item' => ['randomizeAnswers' => false, 'randomPositionWithinQuestionnaire' => false, 'noValueAllowed' => false],
        'schedule' => ['showProgressBar' => true, 'showNotifications' => false],
        'photoUploadOptions' => ['allowFileSelection' => false, 'allowEditing' => false],
        'videoUploadOptions' => ['allowFileSelection' => false],
        'audioUploadOptions' => ['allowFileSelection' => false],
        // A missing maxFileSize or allowedFormats means no limit, the same as
        // leaving the key out.
        'fileUploadOptions' => [],
    ];

    private const PAGE_REFERENCES = [
        'successPage', 'androidStatsPermissionPage', 'androidNotificationPermissionPage', 'iOSNotificationPermissionPage',
    ];

    protected function connectionsToTransact()
    {
        return ['mysql', 'mart'];
    }

    /** @test */
    public function the_seeded_project_matches_directus_apart_from_the_listed_differences()
    {
        $project = $this->seedReferenceProject();
        $participant = User::where('email', 'ref-p1@example.test')->firstOrFail();

        $structure = $this->actingAs($participant, 'api')
            ->getJson("/api/mobile/projects/{$project->id}/structure")
            ->assertOk()
            ->json('data');

        $this->assertSame(self::EXPECTED_DIFFERENCES, $this->differences($this->directus(), $this->ours($structure)));
    }

    /**
     * Directus project 1 from the fixtures, normalised.
     */
    private function directus(): array
    {
        $fixture = MartReferenceProjectSeeder::fixture('project_options');
        $options = $fixture['options'];
        $pages = collect(MartReferenceProjectSeeder::fixture('pages'))->keyBy('pageId');
        $scales = MartReferenceProjectSeeder::fixture('scales');
        $pageName = fn ($id) => $id === null ? null : $pages[$id]['name'];

        $projectOptions = array_diff_key($options, array_flip(['singleQuestionnaires', 'repeatingQuestionnaires']));
        $projectOptions['startDateAndTime'] = $this->appDate($options['startDateAndTime']);
        $projectOptions['endDateAndTime'] = $this->appDate($options['endDateAndTime']);
        foreach (['pages', 'pagesToShowInMenu', 'pagesToShowOnFirstAppStart'] as $list) {
            $projectOptions[$list] = array_map($pageName, $options[$list] ?? []);
        }
        foreach (self::PAGE_REFERENCES as $reference) {
            $projectOptions[$reference] = $pageName($options[$reference] ?? null);
        }
        foreach (['singleQuestionnaires', 'repeatingQuestionnaires'] as $list) {
            $projectOptions[$list] = collect($options[$list] ?? [])
                ->mapWithKeys(fn ($schedule) => [$schedule['questionnaireId'] => $this->directusSchedule($schedule)])
                ->all();
        }

        return [
            'projectName' => $fixture['projectName'],
            'projectOptions' => $projectOptions,
            'pages' => $pages->mapWithKeys(fn ($page) => [$page['name'] => [
                'content' => $page['content'],
                'buttonText' => $page['button_text'],
            ]])->all(),
            'questionnaires' => collect(MartReferenceProjectSeeder::fixture('questionnaires'))
                ->mapWithKeys(fn ($questionnaire) => [$questionnaire['questionnaireId'] => [
                    'name' => $questionnaire['name'],
                    'items' => $this->numbered(array_map(fn ($item) => $this->item(
                        $item,
                        $item['scaleId'] === null ? null : $this->directusScale($scales[(string) $item['scaleId']]),
                    ), $questionnaire['items'])),
                ]])->all(),
        ];
    }

    /**
     * The structure endpoint's answer, normalised the same way.
     */
    private function ours(array $structure): array
    {
        $options = $structure['projectOptions']['options'];
        $pages = collect($structure['pages'])->keyBy('id');
        $scales = collect($structure['scales'])->keyBy('scaleId');
        $pageName = fn ($id) => $id === null ? null : $pages[$id]['name'];

        $projectOptions = $options;
        foreach (['pages', 'pagesToShowInMenu', 'pagesToShowOnFirstAppStart'] as $list) {
            $projectOptions[$list] = array_map($pageName, $options[$list]);
        }
        foreach (self::PAGE_REFERENCES as $reference) {
            $projectOptions[$reference] = $pageName($options[$reference]);
        }
        foreach (['singleQuestionnaires', 'repeatingQuestionnaires'] as $list) {
            $projectOptions[$list] = collect($options[$list])->keyBy('questionnaireId')->all();
        }

        return [
            'projectName' => $structure['projectOptions']['projectName'],
            'projectOptions' => $projectOptions,
            'pages' => $pages->mapWithKeys(fn ($page) => [$page['name'] => [
                'content' => $page['content'],
                'buttonText' => $page['options']['buttonText'],
            ]])->all(),
            'questionnaires' => collect($structure['questionnaires'])
                ->mapWithKeys(fn ($questionnaire) => [$questionnaire['questionnaireId'] => [
                    'name' => $questionnaire['name'],
                    'items' => $this->numbered(array_map(fn ($item) => $this->item(
                        $item,
                        $item['scaleId'] === null ? null : $scales[$item['scaleId']]['options'],
                    ), $questionnaire['items'])),
                ]])->all(),
        ];
    }

    /**
     * One item, the same shape on both sides. Only an item with an item ID can
     * be answered, so only that one has a meaningful noValueAllowed.
     */
    private function item(array $item, ?array $scale): array
    {
        $isAnswerable = $item['itemId'] !== null;
        $options = ($item['options'] ?? []) + self::APP_DEFAULTS['item'];
        if (! $isAnswerable) {
            unset($options['noValueAllowed']);
        }

        return [
            'text' => $item['text'],
            'answerable' => $isAnswerable,
            'imageUrl' => $item['imageUrl'] ?? null,
            'videoUrl' => $item['videoUrl'] ?? null,
            'posterUrl' => $item['posterUrl'] ?? null,
            'options' => $options,
            'scale' => $scale,
        ];
    }

    private function directusScale(array $scale): array
    {
        foreach (['photoUploadOptions', 'videoUploadOptions', 'audioUploadOptions', 'fileUploadOptions'] as $key) {
            if (isset($scale[$key])) {
                unset($scale[$key]['fileRequired']);
                $scale[$key] += self::APP_DEFAULTS[$key];
            }
        }

        return $scale;
    }

    private function directusSchedule(array $schedule): array
    {
        unset($schedule['collectDeviceInfos']);
        $schedule['startDateAndTime'] = $this->appDate($schedule['startDateAndTime']);
        if (isset($schedule['endDateAndTime'])) {
            $schedule['endDateAndTime'] = $this->appDate($schedule['endDateAndTime']);
        }

        return $schedule + self::APP_DEFAULTS['schedule'];
    }

    private function appDate(array $dateAndTime): array
    {
        return ['date' => date('d.m.Y', strtotime($dateAndTime['date'])), 'time' => $dateAndTime['time']];
    }

    /** Items keyed by their place in the questionnaire, from 1. */
    private function numbered(array $items): array
    {
        return array_combine(range(1, count($items)), $items);
    }

    /**
     * One line per difference, in natural path order.
     */
    private function differences(array $directus, array $ours): array
    {
        // A stand-in has other options anyway, so a type mismatch is one line.
        foreach ($directus['questionnaires'] as $id => $questionnaire) {
            foreach ($questionnaire['items'] as $number => $item) {
                $ourScale = $ours['questionnaires'][$id]['items'][$number]['scale'] ?? null;
                if (($item['scale']['type'] ?? null) !== ($ourScale['type'] ?? null)) {
                    $directus['questionnaires'][$id]['items'][$number]['scale'] = $item['scale']['type'] ?? null;
                    $ours['questionnaires'][$id]['items'][$number]['scale'] = $ourScale['type'] ?? null;
                }
            }
        }

        $theirs = $this->flatten($directus);
        $mine = $this->flatten($ours);
        $lines = [];

        foreach ($theirs as $path => $value) {
            if (! array_key_exists($path, $mine)) {
                $lines[] = "{$path}: only in Directus ({$value})";
            } elseif ($mine[$path] !== $value) {
                $lines[] = "{$path}: Directus {$value}, MART {$mine[$path]}";
            }
        }
        foreach (array_diff_key($mine, $theirs) as $path => $value) {
            $lines[] = "{$path}: only in MART ({$value})";
        }

        sort($lines, SORT_NATURAL);

        return $lines;
    }

    /**
     * Dotted paths to JSON-encoded values. Nulls are dropped, and a list of
     * plain values stays one value.
     */
    private function flatten(array $data, string $prefix = ''): array
    {
        $flat = [];

        foreach ($data as $key => $value) {
            $path = $prefix === '' ? (string) $key : "{$prefix}.{$key}";

            if ($value === null) {
                continue;
            }
            if (is_array($value) && $value !== [] && ! $this->isPlainList($value)) {
                $flat += $this->flatten($value, $path);
            } else {
                $flat[$path] = json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            }
        }

        return $flat;
    }

    private function isPlainList(array $value): bool
    {
        return array_is_list($value) && collect($value)->every(fn ($entry) => ! is_array($entry));
    }

    private function seedReferenceProject(): Project
    {
        $input = new ArrayInput([]);
        $input->setInteractive(false);
        $command = new Command;
        $command->setOutput(new OutputStyle($input, new NullOutput));

        (new MartReferenceProjectSeeder)->setContainer($this->app)->setCommand($command)->__invoke();

        return Project::where('name', MartReferenceProjectSeeder::fixture('project_options')['projectName'])
            ->latest('id')
            ->firstOrFail();
    }
}
