<?php

namespace App;

use Illuminate\Support\Arr;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class AllCasesExport implements FromCollection, WithHeadings, WithMapping
{
    use Exportable;

    protected const ENTRY_ID = 'entry_id';

    /**
     * AllCasesExport constructor.
     *
     * @param  array  $headings
     */
    public function __construct($id, $headings = [])
    {
        $this->id = $id;
        $this->head = $headings;
    }

    public function map($project): array
    {
        if ($this->invalidData($project)) {
            return [];
        }
        $allEntries = [];
        foreach ($project->cases as $case) {
            if (! $case->isConsultable()) {
                continue;
            }
            foreach ($case->entries as $entry) {
                $tempValuesArray = [];
                $ifCaseHasAdditionalInputs = $project->inputs !== '[]';
                if ($ifCaseHasAdditionalInputs) {
                    [$jsonInputs, $tempValuesArray] = $this->formatAssociativeNamesAccordingToHeadings($entry, $tempValuesArray);

                    //$tempValuesArray[self::ENTRY_ID] = $entry->id;
                    $tempValuesArray = $this->printValuesInArray($project, $jsonInputs, $tempValuesArray);
                }
                $tempValuesArray[self::ENTRY_ID] = $entry->id;
                
                // Handle missing media safely
                $media = $entry->media_id ? Media::where('id', $entry->media_id)->first() : null;
                $tempValuesArray['media'] = $media ? $media->name : '';
                
                $tempValuesArray['start'] = $entry->begin;
                $tempValuesArray['end'] = $entry->end;
                $tempValuesArray['user_id'] = $case->user_id;
                $tempValuesArray['case_id'] = $case->id;

                $tempValuesArray = Arr::flatten($tempValuesArray);

                array_push($allEntries, $tempValuesArray);
            }
        }

        return $allEntries;
    }

    private function invalidData(): bool
    {
        return false;
    }

    private function formatAssociativeNamesAccordingToHeadings($entry, array $tempValuesArray): array
    {
        $jsonInputs = json_decode($entry->inputs, true);
        $headings = $this->headings();
        foreach ($headings as $heading) {
            // Initialize all headings with empty string
            $tempValuesArray[$heading] = '';
        }

        return [$jsonInputs, $tempValuesArray];
    }

    /**
     * @return string[]
     */
    public function headings(): array
    {
        $columnNames = [self::ENTRY_ID];
        foreach ($this->head as $column) {
            array_push($columnNames, $column);
        }
        array_push($columnNames, 'media');
        array_push($columnNames, 'start');
        array_push($columnNames, 'end');
        array_push($columnNames, 'user_id');
        array_push($columnNames, 'case_id');

        return $columnNames;
    }

    /**
     * This function prints the values in the correct column
     *
     * @return mixed
     */
    private function printValuesInArray($project, $jsonInputs, $tempValuesArray)
    {
        // Process ALL project input names to ensure all columns are filled
        foreach ($project->getProjectInputNames() as $inputName) {
            if ($inputName === 'firstValue' || $inputName === 'file') {
                continue;
            }

            // Get the input value from jsonInputs, or use empty string if not present
            $input = $jsonInputs[$inputName] ?? '';

            // If input is an array, join it with commas (for multiple choice)
            // Otherwise, use the value as-is
            if (is_array($input)) {
                $tempValuesArray[$inputName] = implode(', ', $input);
            } else {
                $tempValuesArray[$inputName] = $input;
            }
        }

        return $tempValuesArray;
    }

    /**
     * @return Project[]|\Illuminate\Support\Collection|\LaravelIdea\Helper\App\_IH_Project_C
     */
    public function collection()
    {
        return Project::where('id', $this->id)->get();
    }
}
