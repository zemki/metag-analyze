<?php

namespace App\Exports\Mart;

use App\Mart\MartStat;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class MartStatsSheet implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    protected int $martProjectId;

    public function __construct(int $martProjectId)
    {
        $this->martProjectId = $martProjectId;
    }

    public function title(): string
    {
        return 'Stats';
    }

    public function collection(): Collection
    {
        return MartStat::forProject($this->martProjectId)
            ->orderBy('participant_id')
            ->orderBy('timestamp')
            ->get();
    }

    public function headings(): array
    {
        return [
            'participant_id',
            'user_id',
            'timestamp',
            'timezone',
            'android_usage_stats',
            'android_event_stats',
            'ios_activations',
            'ios_screen_time',
            'ios_stats',
        ];
    }

    /**
     * @param  MartStat  $stat
     */
    public function map($stat): array
    {
        return [
            $stat->participant_id,
            $stat->user_id,
            $stat->timestamp,
            $stat->timezone,
            $stat->android_usage_stats ? json_encode($stat->android_usage_stats) : '',
            $stat->android_event_stats ? json_encode($stat->android_event_stats) : '',
            $stat->ios_activations,
            $stat->ios_screen_time,
            $stat->ios_stats ? json_encode($stat->ios_stats) : '',
        ];
    }
}
