<?php

namespace App\Exports\Mart;

use App\Mart\MartDeviceInfo;
use App\Mart\MartEntry;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class MartDeviceInfoSheet implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    protected array $scheduleIds;

    protected ?string $participantId;

    public function __construct(array $scheduleIds, ?string $participantId = null)
    {
        $this->scheduleIds = $scheduleIds;
        $this->participantId = $participantId;
    }

    public function title(): string
    {
        return 'Device Info';
    }

    public function collection(): Collection
    {
        // Get participant IDs: single participant or all from project's schedules
        $participantIds = $this->participantId
            ? [$this->participantId]
            : MartEntry::whereIn('schedule_id', $this->scheduleIds)
                ->distinct()
                ->pluck('participant_id')
                ->toArray();

        return MartDeviceInfo::whereIn('participant_id', $participantIds)
            ->orderBy('participant_id')
            ->get();
    }

    public function headings(): array
    {
        return [
            'participant_id',
            'user_id',
            'os',
            'os_version',
            'model',
            'manufacturer',
            'last_updated',
        ];
    }

    /**
     * @param  MartDeviceInfo  $deviceInfo
     */
    public function map($deviceInfo): array
    {
        return [
            $deviceInfo->participant_id,
            $deviceInfo->user_id,
            $deviceInfo->os,
            $deviceInfo->os_version,
            $deviceInfo->model,
            $deviceInfo->manufacturer,
            $deviceInfo->last_updated?->toDateTimeString(),
        ];
    }
}
