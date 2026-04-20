<?php

namespace App\Exports\Mart;

use App\Mart\MartFile;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class MartFilesSheet implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    protected int $mainProjectId;

    protected ?int $caseId;

    public function __construct(int $mainProjectId, ?int $caseId = null)
    {
        $this->mainProjectId = $mainProjectId;
        $this->caseId = $caseId;
    }

    public function title(): string
    {
        return 'Files';
    }

    public function collection(): Collection
    {
        $query = MartFile::where('project_id', $this->mainProjectId);

        if ($this->caseId) {
            $query->where('case_id', $this->caseId);
        }

        return $query->orderBy('case_id')
            ->orderBy('created_at')
            ->get();
    }

    public function headings(): array
    {
        return [
            'file_id',
            'case_id',
            'question_uuid',
            'file_type',
            'mime_type',
            'original_name',
            'size',
            'created_at',
        ];
    }

    /**
     * @param  MartFile  $file
     */
    public function map($file): array
    {
        return [
            $file->id,
            $file->case_id,
            $file->question_uuid,
            $file->file_type,
            $file->mime_type,
            $file->original_name,
            $file->size,
            $file->created_at?->toDateTimeString(),
        ];
    }
}
