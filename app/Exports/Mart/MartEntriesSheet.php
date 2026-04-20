<?php

namespace App\Exports\Mart;

use App\Mart\MartEntry;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithTitle;

class MartEntriesSheet implements FromCollection, WithHeadings, WithMapping, WithTitle
{
    protected Collection $schedules;

    protected array $scheduleIds;

    protected ?string $participantId;

    /**
     * Ordered list of [schedule_name, question_uuid, question_text] used to build
     * column headings and to map each entry's answers into the correct column.
     */
    protected array $questionMap = [];

    public function __construct(Collection $schedules, array $scheduleIds, ?string $participantId = null)
    {
        $this->schedules = $schedules;
        $this->scheduleIds = $scheduleIds;
        $this->participantId = $participantId;

        $this->buildQuestionMap();
    }

    public function title(): string
    {
        return 'Questionnaire Responses';
    }

    public function collection(): Collection
    {
        $query = MartEntry::whereIn('schedule_id', $this->scheduleIds)
            ->with('answers');

        if ($this->participantId) {
            $query->where('participant_id', $this->participantId);
        }

        return $query->orderBy('participant_id')
            ->orderBy('completed_at')
            ->get();
    }

    public function headings(): array
    {
        $headings = [
            'participant_id',
            'user_id',
            'questionnaire',
            'started_at',
            'completed_at',
            'duration_ms',
            'timezone',
        ];

        foreach ($this->questionMap as $item) {
            $headings[] = $item['heading'];
        }

        return $headings;
    }

    /**
     * @param  MartEntry  $entry
     */
    public function map($entry): array
    {
        $schedule = $this->schedules->firstWhere('id', $entry->schedule_id);
        $scheduleName = $schedule ? $schedule->name : '';

        $row = [
            $entry->participant_id,
            $entry->user_id,
            $scheduleName,
            $entry->started_at?->toDateTimeString(),
            $entry->completed_at?->toDateTimeString(),
            $entry->duration_ms,
            $entry->timezone,
        ];

        // Index answers by question_uuid for fast lookup
        $answersByUuid = [];
        foreach ($entry->answers as $answer) {
            $answersByUuid[$answer->question_uuid] = $answer;
        }

        // Fill answer columns in the same order as headings
        foreach ($this->questionMap as $item) {
            $answer = $answersByUuid[$item['uuid']] ?? null;

            if (!$answer) {
                $row[] = '';
                continue;
            }

            $decoded = $answer->decoded_answer;

            // Join arrays (multiple choice) with comma separator
            if (is_array($decoded)) {
                $row[] = implode(', ', $decoded);
            } else {
                $row[] = $decoded;
            }
        }

        return $row;
    }

    /**
     * Build an ordered list of questions across all schedules.
     * Each entry has: uuid, heading (schedule name + question text), schedule name.
     */
    private function buildQuestionMap(): void
    {
        $multipleSchedules = $this->schedules->count() > 1;

        foreach ($this->schedules as $schedule) {
            foreach ($schedule->questions as $question) {
                // Skip display-only questions (no answer expected)
                if ($question->type === 'display') {
                    continue;
                }

                $heading = $multipleSchedules
                    ? $schedule->name . ' - ' . $question->text
                    : $question->text;

                $this->questionMap[] = [
                    'uuid' => $question->uuid,
                    'heading' => $heading,
                ];
            }
        }
    }
}
