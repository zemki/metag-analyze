<?php

namespace App\Exports\Mart;

use App\Project;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class MartProjectExport implements WithMultipleSheets
{
    use Exportable;

    protected Project $project;

    public function __construct(Project $project)
    {
        $this->project = $project;
    }

    public function sheets(): array
    {
        $martProject = $this->project->martProject();

        if (!$martProject) {
            return [];
        }

        $schedules = $martProject->schedules()->with(['questions' => function ($q) {
            $q->orderBy('position');
        }])->get();

        $scheduleIds = $schedules->pluck('id')->toArray();
        $projectId = $martProject->id;
        $mainProjectId = $this->project->id;

        return [
            new MartEntriesSheet($schedules, $scheduleIds),
            new MartDeviceInfoSheet($scheduleIds),
            new MartStatsSheet($projectId),
            new MartFilesSheet($mainProjectId),
        ];
    }
}
