<?php

namespace App\Mart;

use Illuminate\Support\Collection;

/**
 * The answers of MART submissions, readable for the case page.
 *
 * Each answer keeps the raw value the Excel export writes (MartEntriesSheet:
 * arrays joined with ", "), so the two can be compared, and adds the option
 * label where the question has one: single and multiple choice, sliders with
 * labels, and the "other" option. MART only: MeTag entries keep their inputs.
 */
class MartEntryAnswers
{
    /** Question types whose answer values point at config['options'], from 1. */
    private const OPTION_TYPES = ['one choice', 'multiple choice', 'range values'];

    /**
     * @param  Collection<int, MartEntry>  $entries
     * @return array<int, list<array{question: string, value: string, label: ?string}>> by entry id
     */
    public static function forEntries(Collection $entries): array
    {
        if ($entries->isEmpty()) {
            return [];
        }

        $answers = MartAnswer::whereIn('entry_id', $entries->pluck('id'))->get();
        $questions = MartQuestion::whereIn('uuid', $answers->pluck('question_uuid')->unique())->get()->keyBy('uuid');

        return $answers->groupBy('entry_id')
            ->map(fn (Collection $entryAnswers) => $entryAnswers
                ->sortBy(fn (MartAnswer $answer) => $questions[$answer->question_uuid]->position ?? PHP_INT_MAX)
                ->map(fn (MartAnswer $answer) => self::row($answer, $questions[$answer->question_uuid] ?? null))
                ->values()
                ->all())
            ->all();
    }

    /**
     * @return array{question: string, value: string, label: ?string}
     */
    private static function row(MartAnswer $answer, ?MartQuestion $question): array
    {
        $decoded = $answer->decoded_answer;
        $values = is_array($decoded) ? $decoded : [$decoded];
        $value = implode(', ', array_map(fn ($item) => is_array($item) ? json_encode($item) : (string) $item, $values));

        return [
            'question' => $question ? $question->plainText() : 'Deleted question',
            'value' => $value,
            'label' => $question ? self::label($question, $values, $value) : null,
        ];
    }

    /**
     * The option label for each value, or null when nothing maps (or the
     * question has no options), so the page shows the value alone.
     */
    private static function label(MartQuestion $question, array $values, string $value): ?string
    {
        if (! in_array($question->type, self::OPTION_TYPES, true) || $value === '') {
            return null;
        }

        $config = $question->config ?? [];
        $options = array_values($config['options'] ?? []);

        $labels = array_map(function ($item) use ($question, $options) {
            if ($item === 'text') {
                return $question->otherOptionLabel();
            }
            if (is_int($item) || (is_string($item) && ctype_digit($item))) {
                $index = (int) $item - 1;
                if (array_key_exists($index, $options)) {
                    return (string) $options[$index];
                }
            }

            // Typed "other" text, or a value without an option.
            return is_array($item) ? json_encode($item) : (string) $item;
        }, $values);

        $label = implode(', ', $labels);

        return $label === $value ? null : $label;
    }
}
