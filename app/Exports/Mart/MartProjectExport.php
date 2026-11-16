<?php

namespace App\Exports\Mart;

use App\Cases;
use App\Project;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithCustomValueBinder;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

/**
 * The MART workbook. Every sheet binds values through FormulaSafeValueBinder,
 * so text typed in the app or the editor that starts with "=" stays text.
 */
class MartProjectExport extends FormulaSafeValueBinder implements WithCustomValueBinder, WithMultipleSheets
{
    use Exportable;

    protected Project $project;

    protected ?Cases $case;

    public function __construct(Project $project, ?Cases $case = null)
    {
        $this->project = $project;
        $this->case = $case;
    }

    public function sheets(): array
    {
        $martProject = $this->project->martProject();

        if (! $martProject) {
            return [];
        }

        $schedules = $martProject->schedules()->with(['questions' => function ($q) {
            $q->orderBy('position');
        }])->get();

        $scheduleIds = $schedules->pluck('id')->toArray();
        $projectId = $martProject->id;
        $mainProjectId = $this->project->id;

        // For single-case export, filter by participant_id and case_id
        $participantId = $this->case?->name;
        $caseId = $this->case?->id;

        return [
            new MartEntriesSheet($schedules, $scheduleIds, $participantId),
            new MartDeviceInfoSheet($scheduleIds, $participantId),
            new MartStatsSheet($projectId, $participantId),
            new MartFilesSheet($mainProjectId, $caseId),
            // Last, so the earlier sheets keep their place for anyone who
            // reads the file by sheet number.
            new MartQuestionsSheet($schedules),
        ];
    }
}
