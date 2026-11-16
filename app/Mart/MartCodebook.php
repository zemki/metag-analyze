<?php

namespace App\Mart;

use App\Project;
use Illuminate\Support\Collection;

/**
 * What a MART project's questionnaires ask, and the values their answers are
 * stored as: choices and sliders with labels as option numbers from 1, the
 * "other" option as "text" followed by the typed words. The case page and
 * the Excel export show those values, and this list explains them.
 *
 * It gives only what a researcher reads: question text, type, whether it's
 * required, its version and the coding. No answers, no participant data, no
 * media URLs, no jumps or randomisation. It describes the current version of
 * each question; earlier answers to an edited question can use older options.
 *
 * MART only: MeTag projects keep their questions in the project's inputs.
 */
class MartCodebook
{
    public const TYPE_LABELS = [
        'display' => 'Text only',
        'range' => 'Slider',
        'range values' => 'Slider with labels',
        'one choice' => 'Single choice',
        'multiple choice' => 'Multiple choice',
        'number' => 'Number',
        'text' => 'Short text',
        'textarea' => 'Long text',
        'photo' => 'Photo',
        'audio' => 'Audio',
        'video' => 'Video',
        'file' => 'File',
    ];

    /** Types whose answers are option numbers from 1 (index + 1 in config['options']). */
    private const OPTION_TYPES = ['one choice', 'multiple choice', 'range values'];

    /** Types the app offers an "other" option for. */
    private const OTHER_TYPES = ['one choice', 'multiple choice'];

    private const UPLOAD_TYPES = ['photo', 'audio', 'video', 'file'];

    /**
     * The project's questionnaires in questionnaire order, or none when it
     * isn't a MART project.
     */
    public static function forProject(Project $project): array
    {
        $martProject = $project->martProject();
        if (! $martProject) {
            return [];
        }

        $schedules = MartSchedule::forProject($martProject->id)
            ->with('questions')
            ->orderBy('questionnaire_id')
            ->get();

        return self::forSchedules($schedules);
    }

    /**
     * @param  Collection<int, MartSchedule>  $schedules  with their questions loaded
     * @return list<array{questionnaire_id: int, name: string, questions: list<array>}>
     */
    public static function forSchedules(Collection $schedules): array
    {
        return $schedules->map(fn (MartSchedule $schedule) => [
            'questionnaire_id' => (int) $schedule->questionnaire_id,
            'name' => (string) $schedule->name,
            'questions' => $schedule->questions->sortBy('position')->values()
                ->map(fn (MartQuestion $question, int $index) => self::question($question, $index + 1))
                ->all(),
        ])->values()->all();
    }

    /**
     * @return array{item: int, text: string, type: string, type_label: string, required: bool, version: int, values: list<array{value: int|string, label: string}>, answer: string}
     */
    private static function question(MartQuestion $question, int $item): array
    {
        $config = is_array($question->config) ? $question->config : [];
        $values = self::values($question, $config);

        return [
            'item' => $item,
            'text' => $question->plainText(),
            'type' => (string) $question->type,
            'type_label' => self::TYPE_LABELS[$question->type] ?? (string) $question->type,
            // The app never asks for an answer to a text-only item.
            'required' => $question->type !== 'display' && (bool) $question->is_mandatory,
            'version' => (int) $question->version,
            'values' => $values,
            'answer' => self::answer($question->type, $config, $values),
        ];
    }

    private static function values(MartQuestion $question, array $config): array
    {
        if (! in_array($question->type, self::OPTION_TYPES, true)) {
            return [];
        }

        $values = [];
        foreach (array_values($config['options'] ?? []) as $index => $label) {
            // A blank slider label is stored as null; it's still a step.
            $values[] = ['value' => $index + 1, 'label' => (string) $label];
        }
        if (in_array($question->type, self::OTHER_TYPES, true) && ! empty($config['includeOtherOption'])) {
            $values[] = ['value' => 'text', 'label' => $question->otherOptionLabel()];
        }

        return $values;
    }

    /**
     * The coding in one line, for the export and for types without options.
     */
    private static function answer(string $type, array $config, array $values): string
    {
        if ($values !== []) {
            return implode('; ', array_map(
                fn (array $option) => $option['value'] === 'text'
                    ? "text = {$option['label']}, followed by the typed answer"
                    : "{$option['value']} = {$option['label']}",
                $values,
            ));
        }

        return match (true) {
            $type === 'display' => 'No answer (text only)',
            $type === 'range' => self::range($config),
            $type === 'number' => self::number($config),
            in_array($type, ['text', 'textarea'], true) => 'Typed text',
            in_array($type, self::UPLOAD_TYPES, true) => 'An uploaded file',
            default => '',
        };
    }

    /** The app's limits, with the defaults ScaleResource sends it. */
    private static function range(array $config): string
    {
        $min = $config['min'] ?? 1;
        $max = $config['max'] ?? 10;
        $step = $config['step'] ?? 1;

        return "From {$min} to {$max}" . ($step == 1 ? '' : " in steps of {$step}");
    }

    private static function number(array $config): string
    {
        return 'A number from ' . ($config['min'] ?? 1) . ' to ' . ($config['max'] ?? 10);
    }
}
