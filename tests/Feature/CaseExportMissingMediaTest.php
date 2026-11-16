<?php

namespace Tests\Feature;

use App\Entry;
use App\Media;
use Illuminate\Support\Facades\Schema;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Tests\TestCase;

class CaseExportMissingMediaTest extends TestCase
{
    /** @test */
    public function single_case_export_handles_entry_without_media()
    {
        // Projects without entities store entries with media_id = null.
        $this->createEntry(null);

        $sheet = $this->exportCaseSheet();

        $this->assertSame('media', $sheet->getCell('B1')->getValue());
        $this->assertSame('', (string) $sheet->getCell('B2')->getValue());
    }

    /** @test */
    public function single_case_export_handles_entry_whose_media_no_longer_exists()
    {
        $media = Media::factory()->create();
        $missingMediaId = $media->id;
        $media->delete();

        // The foreign key cascade normally removes such entries, so force the state.
        Schema::withoutForeignKeyConstraints(fn () => $this->createEntry($missingMediaId));

        $sheet = $this->exportCaseSheet();

        $this->assertSame('media', $sheet->getCell('B1')->getValue());
        $this->assertSame('', (string) $sheet->getCell('B2')->getValue());
    }

    private function createEntry(?int $mediaId): Entry
    {
        return Entry::factory()->create([
            'case_id' => $this->case->id,
            'media_id' => $mediaId,
            'begin' => now()->subHour()->format('Y-m-d H:i:s.u'),
            'end' => now()->format('Y-m-d H:i:s.u'),
        ]);
    }

    private function exportCaseSheet()
    {
        $response = $this->actingAs($this->user)
            ->get("/cases/{$this->case->id}/export");

        $response->assertOk();
        $response->assertDownload();

        return IOFactory::load($response->getFile()->getPathname())->getActiveSheet();
    }
}
