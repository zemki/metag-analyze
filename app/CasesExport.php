<?php

namespace App\Exports;

use App\Cases;
use App\Entry;
use App\Media;
use Illuminate\Support\Arr;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Storage;

class CasesExport implements FromCollection, WithMapping, WithHeadings
{
    use Exportable;

    /**
     * CasesExport constructor.
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
     * @var Interview $entry
     */
    public function map($entry): array
    {
        if ($this->invalidData($entry)) {
            return [];
        }
        $case = Cases::where('id', $entry->case_id)->first();
        $project = $case->project;
        $tempValuesArray = [];
        $tempValuesArray["#"] = $entry->id;
        if ($project->inputs != "[]") {
            foreach ($project->getProjectInputNames() as $name) {
                $projectInputNames[$name] = $project->getAnswersByQuestion($name);
            }
            $jsonInputs = json_decode($entry->inputs);
            foreach ($this->headings() as $heading) {
                // print the question as many times as you have answer to question
                if (count(array_keys($this->headings(), $heading)) > 1) {
                    $tempValuesArray[$heading] = [];
                    foreach (array_keys($this->headings(), $heading) as $key) {
                        array_push($tempValuesArray[$heading], $this->headings()[$key]);
                    }
                    $tempValuesArray = array_unique($tempValuesArray[$heading]);
                } else {
                    $tempValuesArray[$heading] = "";
                }
            }
            $tempValuesArray["#"] = $entry->id;
            foreach ($jsonInputs as $key => $input) {
                $index = [];
                $numberOfAnswersByQuestion = $project->getNumberOfAnswersByQuestion($key);
                if ($numberOfAnswersByQuestion > 0) {

                    if ($input != null) {
                        foreach ($input as $value) {
                            $index[array_search($value, $projectInputNames[$key])] = $value;
                        }
                        for ($i = 0; $i < $numberOfAnswersByQuestion; $i++) {
                            $tempValuesArray[$key][$i] = [];
                            if (array_key_exists($i, $index)) {
                                array_push($tempValuesArray[$key][$i], $index[$i]);
                            } else {
                                array_push($tempValuesArray[$key][$i], "");
                            }
                        }
                    } else {
                        for ($i = 0; $i < $numberOfAnswersByQuestion; $i++) {
                            $tempValuesArray[$key][$i] = [];
                            array_push($tempValuesArray[$key][$i], "");
                        }
                    }
                } else {
                    $tempValuesArray[$key] = $input;
                }
            }
        }
        $tempValuesArray["media"] = Media::where('id', $entry->media_id)->first()->name;
        $tempValuesArray["start"] = $entry->begin;
        $tempValuesArray["end"] = $entry->end;
        return Arr::flatten($tempValuesArray);
    }

    /**
     * @return bool
     */
    private function invalidData(): bool
    {
        return false;
    }

    public function headings(): array
    {
        $columnNames = ["#"];
        foreach ($this->head as $column) {
            array_push($columnNames, $column);
        }
        array_push($columnNames, "media");
        array_push($columnNames, "start");
        array_push($columnNames, "end");
        return $columnNames;
    }

    public function collection()
    {
        return Entry::where('case_id', $this->id)->get();
    }
}
