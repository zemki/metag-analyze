<?php

namespace App;

use Illuminate\Support\Arr;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Storage;

class AllCasesExport implements FromCollection, WithMapping, WithHeadings
{
    use Exportable;
    protected const ENTRY_ID = "entry_id";

    /**
     * AllCasesExport constructor.
     * @param       $id
     * @param array $headings
     */
    public function __construct($id, $headings = [])
    {
        $this->id = $id;
        $this->head = $headings;
    }

    /**
     * @return array
     * @var  $project
     */
    public function map($project): array
    {
        if ($this->invalidData($project)) {
            return [];
        }
        $allEntries = [];
        foreach ($project->cases as $case) {
            if (!$case->isConsultable()) {
                continue;
            }
            foreach ($case->entries as $entry) {
                $tempValuesArray = [];
                $ifCaseHasAdditionalInputs = $project->inputs !== "[]";
                if ($ifCaseHasAdditionalInputs) {
                    list($jsonInputs, $tempValuesArray) = $this->formatAssociativeNamesAccordingToHeadings($entry, $tempValuesArray);

                    //$tempValuesArray[self::ENTRY_ID] = $entry->id;
                    $tempValuesArray = $this->printValuesInArray($project, $jsonInputs, $tempValuesArray);
                }
                $tempValuesArray[self::ENTRY_ID] = $entry->id;
                $tempValuesArray["media"] = Media::where('id', $entry->media_id)->first()->name;
                $tempValuesArray["start"] = $entry->begin;
                $tempValuesArray["end"] = $entry->end;
                $tempValuesArray["user_id"] = $case->user_id;
                $tempValuesArray["case_id"] = $case->id;

                $tempValuesArray = Arr::flatten($tempValuesArray);

                array_push($allEntries, $tempValuesArray);
            }
        }
        return $allEntries;
    }

    /**
     * @return bool
     */
    private function invalidData(): bool
    {
        return false;
    }

    /**
     * @param       $entry
     * @param array $tempValuesArray
     * @return array
     */
    private function formatAssociativeNamesAccordingToHeadings($entry, array $tempValuesArray): array
    {
        $jsonInputs = json_decode($entry->inputs);
        foreach ($this->headings() as $heading) {
            // print the question as many times as you have answer to question
            if (count(array_keys($this->headings(), $heading)) > 1) {
                $tempValuesArray[$heading] = [];
                foreach (array_keys($this->headings(), $heading) as $key) {
                    array_push($tempValuesArray[$heading], $this->headings()[$key]);
                }

            //$tempValuesArray = array_unique($tempValuesArray[$heading]);
            } else {
                $tempValuesArray[$heading] = "";
            }
        }

        return array($jsonInputs, $tempValuesArray);
    }

    public function headings(): array
    {
        $columnNames = [self::ENTRY_ID];
        foreach ($this->head as $column) {
            array_push($columnNames, $column);
        }
        array_push($columnNames, "media");
        array_push($columnNames, "start");
        array_push($columnNames, "end");
        array_push($columnNames, "user_id");
        array_push($columnNames, "case_id");
        return $columnNames;
    }

    /**
     * @param $project
     * @param $jsonInputs
     * @param $tempValuesArray
     * @return mixed
     */
    private function printValuesInArray($project, $jsonInputs, $tempValuesArray)
    {
        // build index array to then print the answers in the correct column
        foreach ($project->getProjectInputNames() as $name) {
            $projectInputNames[$name] = $project->getAnswersByQuestion($name);
        }
        foreach ($jsonInputs as $key => $input) {
            $index = [];
            $numberOfAnswersByQuestion = $project->getNumberOfAnswersByQuestion($key);
            $questionIsMultipleOrOneChoice = $numberOfAnswersByQuestion > 0;
            if ($questionIsMultipleOrOneChoice) {
                $this->formatMultipleAndOneChoiceValues($tempValuesArray, $input, $index, $projectInputNames, $key, $numberOfAnswersByQuestion);
            } else {
                $tempValuesArray[$key] = $input;
            }
        }


        return $tempValuesArray;
    }

    /**
     * @param       $tempValuesArray
     * @param       $input
     * @param array $index
     * @param       $projectInputNames
     * @param       $key
     * @param       $numberOfAnswersByQuestion
     */
    private function formatMultipleAndOneChoiceValues(&$tempValuesArray, $input, array $index, $projectInputNames, $key, $numberOfAnswersByQuestion): void
    {
        if (!is_null($input)) {
            ddd($input);

            foreach ($input as $value) {
                $index[array_search($value, $projectInputNames[$key])] = $value;
            }
            // print values in the same column in multiple choice or one choice answers
            for ($i = 0; $i < $numberOfAnswersByQuestion; $i++) {
                $tempValuesArray[$key][$i] = [];
                if (array_key_exists($i, $index)) {
                    array_push($tempValuesArray[$key][$i], $index[$i]);
                } else {
                    array_push($tempValuesArray[$key][$i], "");
                }
            }
        } else {
            // print empty value
            for ($i = 0; $i < $numberOfAnswersByQuestion; $i++) {
                $tempValuesArray[$key][$i] = [];
                array_push($tempValuesArray[$key][$i], "");
            }
        }
    }

    public function collection()
    {
        return Project::where('id', $this->id)->get();
    }
}
