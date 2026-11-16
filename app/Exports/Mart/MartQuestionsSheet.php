<?php

namespace App\Exports\Mart;

use App\Mart\MartCodebook;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

/**
 * One row per question with the values its answers are stored as, so the
 * numbers in "Questionnaire Responses" can be read without the editor.
 * MartProjectExport keeps texts starting with "=" as text.
 */
class MartQuestionsSheet implements FromArray, WithHeadings, WithTitle
{
    /**
     * @param  Collection  $schedules  with their questions loaded
     */
    public function __construct(private Collection $schedules) {}

    public function title(): string
    {
        return 'Questions';
    }

    public function headings(): array
    {
        return ['questionnaire', 'questionnaire_id', 'item', 'question', 'type', 'required', 'version', 'answer_values'];
    }

    public function array(): array
    {
        $rows = [];
        foreach (MartCodebook::forSchedules($this->schedules->sortBy('questionnaire_id')) as $questionnaire) {
            foreach ($questionnaire['questions'] as $question) {
                $rows[] = [
                    $questionnaire['name'],
                    $questionnaire['questionnaire_id'],
                    $question['item'],
                    $question['text'],
                    $question['type_label'],
                    $question['required'] ? 'yes' : 'no',
                    $question['version'],
                    $question['answer'],
                ];
            }
        }

        return $rows;
    }
}
