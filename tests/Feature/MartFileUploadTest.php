<?php

namespace Tests\Feature;

use App\Cases;
use App\Http\Controllers\MartFileController;
use App\Mart\MartFile;
use App\Mart\MartProject;
use App\Project;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class MartFileUploadTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Fake storage to prevent actual file writes
        Storage::fake('local');

        // Update project to be MART project
        $this->project->update([
            'inputs' => json_encode([
                ['type' => 'mart', 'projectOptions' => [
                    'startDateAndTime' => ['date' => '2025-01-01', 'time' => '00:00'],
                    'endDateAndTime' => ['date' => '2025-12-31', 'time' => '23:59'],
                ]],
            ]),
        ]);

        // Create MART project in MART database (use firstOrCreate since MART DB doesn't rollback)
        MartProject::firstOrCreate(['main_project_id' => $this->project->id]);
    }

    /**
     * Get a small 1x1 pixel PNG image as base64
     */
    private function getTestPngBase64(): string
    {
        return 'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';
    }

    /**
     * Get a small 1x1 pixel JPEG image as base64
     */
    private function getTestJpegBase64(): string
    {
        return '/9j/4AAQSkZJRgABAQEASABIAAD/2wBDAAgGBgcGBQgHBwcJCQgKDBQNDAsLDBkSEw8UHRofHh0aHBwgJC4nICIsIxwcKDcpLDAxNDQ0Hyc5PTgyPC4zNDL/2wBDAQkJCQwLDBgNDRgyIRwhMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjIyMjL/wAARCAABAAEDASIAAhEBAxEB/8QAFQABAQAAAAAAAAAAAAAAAAAAAAn/xAAUEAEAAAAAAAAAAAAAAAAAAAAA/8QAFQEBAQAAAAAAAAAAAAAAAAAAAAX/xAAUEQEAAAAAAAAAAAAAAAAAAAAA/9oADAMBEQCEAwEPwAB//9k=';
    }

    /** @test */
    public function it_can_upload_a_photo_file_via_controller()
    {
        $controller = new MartFileController();

        $request = new Request([
            'file_type' => 'photo',
            'file' => $this->getTestPngBase64(),
            'original_name' => 'test-image.png',
        ]);

        $response = $controller->store($request, $this->case);
        $responseData = $response->getData(true);

        $this->assertTrue($responseData['success']);
        $this->assertEquals('photo', $responseData['file_type']);
        $this->assertEquals('image/png', $responseData['mime_type']);
        $this->assertArrayHasKey('file_id', $responseData);

        // Verify file was saved to database
        $this->assertDatabaseHas('mart_files', [
            'case_id' => $this->case->id,
            'project_id' => $this->project->id,
            'file_type' => 'photo',
            'mime_type' => 'image/png',
        ], 'mart');
    }

    /** @test */
    public function it_rejects_upload_to_non_mart_project_via_controller()
    {
        // Create a non-MART project
        $standardProject = Project::factory()->create([
            'created_by' => $this->user->id,
            'inputs' => json_encode([
                ['type' => 'text', 'name' => 'Question 1'],
            ]),
        ]);

        $standardCase = Cases::factory()->create([
            'project_id' => $standardProject->id,
            'user_id' => $this->user->id,
        ]);

        $controller = new MartFileController();

        $request = new Request([
            'file_type' => 'photo',
            'file' => $this->getTestPngBase64(),
        ]);

        $response = $controller->store($request, $standardCase);

        $this->assertEquals(400, $response->getStatusCode());
        $responseData = $response->getData(true);
        $this->assertFalse($responseData['success']);
        $this->assertEquals('File uploads are only supported for MART projects', $responseData['message']);
    }

    /** @test */
    public function it_rejects_invalid_mime_type_via_controller()
    {
        $controller = new MartFileController();

        $request = new Request([
            'file_type' => 'photo',
            'file' => base64_encode('This is not an image file'),
        ]);

        $response = $controller->store($request, $this->case);

        $this->assertEquals(422, $response->getStatusCode());
        $responseData = $response->getData(true);
        $this->assertFalse($responseData['success']);
        $this->assertStringContains('not allowed for file type', $responseData['message']);
    }

    /** @test */
    public function it_rejects_mismatched_file_type_via_controller()
    {
        $controller = new MartFileController();

        // Try to upload image as video
        $request = new Request([
            'file_type' => 'video',
            'file' => $this->getTestPngBase64(),
        ]);

        $response = $controller->store($request, $this->case);

        $this->assertEquals(422, $response->getStatusCode());
        $responseData = $response->getData(true);
        $this->assertFalse($responseData['success']);
    }

    /** @test */
    public function it_rejects_invalid_base64_via_controller()
    {
        $controller = new MartFileController();

        $request = new Request([
            'file_type' => 'photo',
            'file' => 'not-valid-base64!!!',
        ]);

        $response = $controller->store($request, $this->case);

        $this->assertEquals(400, $response->getStatusCode());
        $responseData = $response->getData(true);
        $this->assertFalse($responseData['success']);
        $this->assertEquals('Invalid base64 encoded file content', $responseData['message']);
    }

    /** @test */
    public function mart_file_model_can_detect_mime_types()
    {
        $pngContent = base64_decode($this->getTestPngBase64());
        $jpegContent = base64_decode($this->getTestJpegBase64());
        $textContent = 'This is text';

        $this->assertEquals('image/png', MartFile::detectMimeType($pngContent));
        $this->assertEquals('image/jpeg', MartFile::detectMimeType($jpegContent));
        $this->assertEquals('text/plain', MartFile::detectMimeType($textContent));
    }

    /** @test */
    public function mart_file_model_validates_allowed_mime_types()
    {
        // Photo type
        $this->assertTrue(MartFile::isAllowedMimeType('photo', 'image/jpeg'));
        $this->assertTrue(MartFile::isAllowedMimeType('photo', 'image/png'));
        $this->assertFalse(MartFile::isAllowedMimeType('photo', 'video/mp4'));
        $this->assertFalse(MartFile::isAllowedMimeType('photo', 'text/plain'));

        // Video type
        $this->assertTrue(MartFile::isAllowedMimeType('video', 'video/mp4'));
        $this->assertTrue(MartFile::isAllowedMimeType('video', 'video/quicktime'));
        $this->assertFalse(MartFile::isAllowedMimeType('video', 'image/png'));

        // Audio type
        $this->assertTrue(MartFile::isAllowedMimeType('audio', 'audio/mpeg'));
        $this->assertTrue(MartFile::isAllowedMimeType('audio', 'audio/wav'));
        $this->assertFalse(MartFile::isAllowedMimeType('audio', 'image/png'));

        // Document type
        $this->assertTrue(MartFile::isAllowedMimeType('document', 'application/pdf'));
        $this->assertTrue(MartFile::isAllowedMimeType('document', 'image/jpeg'));
        $this->assertFalse(MartFile::isAllowedMimeType('document', 'video/mp4'));
    }

    /** @test */
    public function mart_file_model_generates_correct_storage_path()
    {
        $path = MartFile::generateStoragePath(123, 456, 'abc-def-123');

        $this->assertEquals('mart/project123/files/456/abc-def-123.mfile', $path);
    }

    /** @test */
    public function link_files_to_entry_works_correctly()
    {
        // First create a file
        $controller = new MartFileController();
        $request = new Request([
            'file_type' => 'photo',
            'file' => $this->getTestPngBase64(),
        ]);
        $response = $controller->store($request, $this->case);
        $fileId = $response->getData(true)['file_id'];

        // Verify file is unlinked
        $file = MartFile::find($fileId);
        $this->assertNull($file->mart_entry_id);

        // Link to a fake entry
        $results = MartFileController::linkFilesToEntry([$fileId], 999, $this->case->id);

        $this->assertContains($fileId, $results['linked']);
        $this->assertEmpty($results['errors']);

        // Verify file is now linked
        $file->refresh();
        $this->assertEquals(999, $file->mart_entry_id);
    }

    /** @test */
    public function link_files_rejects_wrong_case()
    {
        // Create a file for one case
        $controller = new MartFileController();
        $request = new Request([
            'file_type' => 'photo',
            'file' => $this->getTestPngBase64(),
        ]);
        $response = $controller->store($request, $this->case);
        $fileId = $response->getData(true)['file_id'];

        // Try to link with wrong case ID
        $results = MartFileController::linkFilesToEntry([$fileId], 999, 9999);

        $this->assertEmpty($results['linked']);
        $this->assertNotEmpty($results['errors']);
        $this->assertStringContains('does not belong to this case', $results['errors'][0]);
    }

    /**
     * Helper to check if string contains substring
     */
    private function assertStringContains(string $needle, string $haystack): void
    {
        $this->assertTrue(
            str_contains($haystack, $needle),
            "Failed asserting that '$haystack' contains '$needle'"
        );
    }
}
