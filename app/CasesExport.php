<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Storage;
use App\Cases;
use App\Entry;
use App\Media;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;

class CasesExport implements FromCollection, WithMapping, WithHeadings
{
    use Exportable;

    public function __construct($id)
    {
        $this->id = $id;
    }

    public function headings(): array
    {
        return [
            '#',
            'additional inputs',
            'media',
            'start',
            'end'
        ];
    }

    /**
     * @return array
     * @var Interview $entry
     */
    public function map($entry): array
    {
        if($this->invalidData($entry)) return [];

        $case = Cases::where('id',$entry->case_id)->first();

        return [
            $entry->id,
            $entry->inputs,
            Media::where('id',$entry->media_id)->first()->name,
            $entry->begin,
            $entry->end
        ];
    }

    public function collection()
    {
        return Entry::where('case_id',$this->id)->get();
    }

    /**
     * @param $case
     * @return bool
     */
    private function invalidData($case): bool
    {
        return false;
    }

}
