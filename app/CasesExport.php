<?php

namespace App;

use Illuminate\Support\Arr;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class CasesExport implements FromCollection, WithHeadings, WithMapping
{
    use Exportable;

    /**
     * CasesExport constructor.
     *
     * @param  array  $headings
     */
    public function __construct($id, $headings = [])
    {
        $this->id = $id;
        $this->head = $headings;
    }

    /**
     * @var Interview
     */
    public function map($entry): array
    {
        if ($this->invalidData($entry)) {
            return [];
        }
        $case = Cases::where('id', $entry->case_id)->first();
        $project = $case->project;
        $tempValuesArray = [];
        $tempValuesArray['#'] = $entry->id;
        if ($project->inputs != '[]') {
            foreach ($project->getProjectInputNames() as $name) {
                $projectInputNames[$name] = $project->getAnswersByQuestion($name);
            }
            $jsonInputs = json_decode($entry->inputs);
            foreach ($this->headings() as $heading) {
                // Initialize all headings with empty string
                $tempValuesArray[$heading] = '';
            }
            $tempValuesArray['#'] = $entry->id;
            foreach ($jsonInputs as $key => $input) {
                if ($key === 'firstValue' || $key === 'file') {
                    continue;
                }

                // If input is an array, join it with commas (for multiple choice)
                // Otherwise, use the value as-is
                if (is_array($input)) {
                    $tempValuesArray[$key] = implode(', ', $input);
                } else {
                    $tempValuesArray[$key] = $input ?? '';
                }
            }
        }
        $tempValuesArray['media'] = Media::where('id', $entry->media_id)->first()->name;
        $tempValuesArray['start'] = $entry->begin;
        $tempValuesArray['end'] = $entry->end;

        return Arr::flatten($tempValuesArray);
    }

    private function invalidData(): bool
    {
        return false;
    }

    public function headings(): array
    {
        $columnNames = ['#'];
        foreach ($this->head as $column) {
            array_push($columnNames, $column);
        }
        array_push($columnNames, 'media');
        array_push($columnNames, 'start');
        array_push($columnNames, 'end');

        return $columnNames;
    }

    public function collection()
    {
        return Entry::where('case_id', $this->id)->get();
    }
}
