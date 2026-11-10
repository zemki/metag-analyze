<?php

namespace Tests\Feature;

use App\Cases;
use App\Http\Controllers\MartApiController;
use App\Http\Controllers\MartFileController;
use App\Mart\MartFile;
use App\Mart\MartProject;
use App\Mart\MartQuestion;
use App\Mart\MartSchedule;
use App\Project;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;

class MartFileUploadTest extends TestCase
{
    /** The question UUID the app sends with every upload. */
    private const ZERO_UUID = '00000000-0000-0000-0000-000000000000';

    /** What the app puts in front of a returned fileUrl (apiBremenBase). */
    private const APP_API_BASE = 'https://metag.example.test';

    /** The bucket each offered extension's sample file lands in. */
    private const BUCKETS = [
        '.jpg' => 'photo', '.jpeg' => 'photo', '.png' => 'photo', '.gif' => 'photo', '.webp' => 'photo',
        '.heic' => 'photo', '.heif' => 'photo',
        '.pdf' => 'document',
        '.mp4' => 'video', '.mov' => 'video', '.webm' => 'video', '.3gp' => 'video', '.avi' => 'video',
        '.mp3' => 'audio', '.m4a' => 'audio', '.ogg' => 'audio', '.flac' => 'audio',
    ];

    private int $nextQuestionnaireNumber = 100;

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
     * The request as the case's own participant sends it: the bearer token
     * makes them the request's user.
     */
    private function asOwner(Request $request): Request
    {
        $request->setUserResolver(fn () => $this->user);

        return $request;
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

        $response = $controller->store($this->asOwner($request), $this->case);
        $responseData = $response->getData(true);

        $this->assertTrue($responseData['success']);
        $this->assertEquals('photo', $responseData['file_type']);
        $this->assertEquals('image/png', $responseData['mime_type']);
        $this->assertArrayHasKey('file_id', $responseData);
        $this->assertArrayHasKey('fileUrl', $responseData);
        $this->assertEquals("/api/mobile/files/{$responseData['file_id']}", $responseData['fileUrl']);

        // Verify file was saved to database
        $this->assertDatabaseHas('mart_files', [
            'case_id' => $this->case->id,
            'project_id' => $this->project->id,
            'file_type' => 'photo',
            'mime_type' => 'image/png',
        ], 'mart');
    }

    /** @test */
    public function it_can_upload_a_file_via_multipart()
    {
        $controller = new MartFileController();

        $pngContent = base64_decode($this->getTestPngBase64());
        $uploadedFile = UploadedFile::fake()->createWithContent('photo.png', $pngContent);

        $request = Request::create('/mart-api/cases/1/files', 'POST', [
            'file_type' => 'photo',
        ], [], [
            'file' => $uploadedFile,
        ]);

        $response = $controller->store($this->asOwner($request), $this->case);
        $responseData = $response->getData(true);

        $this->assertTrue($responseData['success']);
        $this->assertEquals('photo', $responseData['file_type']);
        $this->assertEquals('image/png', $responseData['mime_type']);
        $this->assertArrayHasKey('file_id', $responseData);
        $this->assertEquals("/api/mobile/files/{$responseData['file_id']}", $responseData['fileUrl']);

        $this->assertDatabaseHas('mart_files', [
            'case_id' => $this->case->id,
            'project_id' => $this->project->id,
            'file_type' => 'photo',
            'mime_type' => 'image/png',
            'original_name' => 'photo.png',
        ], 'mart');
    }

    /** @test */
    public function it_infers_file_type_from_mime_in_multipart_mode()
    {
        $controller = new MartFileController();

        $pngContent = base64_decode($this->getTestPngBase64());
        $uploadedFile = UploadedFile::fake()->createWithContent('photo.png', $pngContent);

        // No file_type sent — controller must infer 'photo' from image/png
        $request = Request::create('/mart-api/cases/1/files', 'POST', [], [], [
            'file' => $uploadedFile,
        ]);

        $response = $controller->store($this->asOwner($request), $this->case);
        $responseData = $response->getData(true);

        $this->assertTrue($responseData['success']);
        $this->assertEquals('photo', $responseData['file_type']);
        $this->assertEquals('image/png', $responseData['mime_type']);
    }

    /** @test */
    public function it_infers_file_type_from_mime_in_base64_mode()
    {
        $controller = new MartFileController();

        // No file_type sent — controller must infer 'photo' from image/png
        $request = new Request([
            'file' => $this->getTestPngBase64(),
            'original_name' => 'photo.png',
        ]);

        $response = $controller->store($this->asOwner($request), $this->case);
        $responseData = $response->getData(true);

        $this->assertTrue($responseData['success']);
        $this->assertEquals('photo', $responseData['file_type']);
        $this->assertEquals('image/png', $responseData['mime_type']);
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

        $response = $controller->store($this->asOwner($request), $standardCase);

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

        $response = $controller->store($this->asOwner($request), $this->case);

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

        $response = $controller->store($this->asOwner($request), $this->case);

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

        $response = $controller->store($this->asOwner($request), $this->case);

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
        $response = $controller->store($this->asOwner($request), $this->case);
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
        $response = $controller->store($this->asOwner($request), $this->case);
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

    /**
     * The editor stores upload questions as photo / audio / video. Submit used
     * to look for photoUpload / audioUpload / videoUpload, so uploaded files
     * were never linked to the entry they answer.
     *
     * @test
     */
    public function a_submitted_photo_answer_links_the_uploaded_file_to_the_entry()
    {
        $martProject = MartProject::where('main_project_id', $this->project->id)->first();
        $questionnaireId = $this->project->id * 1000 + 1;
        $schedule = MartSchedule::create([
            'mart_project_id' => $martProject->id,
            'questionnaire_id' => $questionnaireId,
            'name' => 'Photo diary',
            'type' => 'single',
            'timing_config' => [],
            'notification_config' => [],
        ]);
        MartQuestion::create([
            'schedule_id' => $schedule->id,
            'position' => 0,
            'text' => 'Take a photo of your screen',
            'type' => 'photo',
            'config' => [],
            'is_mandatory' => false,
            'version' => 1,
        ]);

        $fileId = (new MartFileController)->store($this->asOwner(new Request([
            'file_type' => 'photo',
            'file' => $this->getTestPngBase64(),
            'original_name' => 'screen.png',
        ])), $this->case)->getData(true)['file_id'];

        $response = (new MartApiController)->submitEntry(new Request([
            'projectId' => $this->project->id,
            'questionnaireId' => $questionnaireId,
            'userId' => $this->user->email,
            'participantId' => $this->case->name,
            'questionnaireStarted' => now()->timestamp * 1000,
            'questionnaireDuration' => 60000,
            'answers' => ['1' => [$fileId]],
            'timestamp' => now()->timestamp * 1000,
            'timezone' => 'Europe/Berlin',
        ]), $this->case);

        $this->assertTrue($response->getData(true)['success']);
        $this->assertNotNull(MartFile::find($fileId)->mart_entry_id, 'The uploaded photo was not linked to the entry.');
    }

    /**
     * The app's upload for a fileUpload question used to fail validation on
     * file_type "file", and the app then dropped the whole submit.
     *
     * @test
     */
    public function the_apps_upload_for_a_file_question_is_accepted()
    {
        $this->appUpload($this->fixture('sample.pdf'))
            ->assertCreated()
            ->assertJsonPath('success', true);
    }

    /**
     * "file" means any bucket, so the content picks it, as when no file_type
     * is sent.
     *
     * @test
     */
    public function a_file_question_upload_gets_the_bucket_of_its_content()
    {
        $this->appUpload($this->fixture('sample.pdf'))->assertJsonPath('file_type', 'document');
    }

    /**
     * Each format a fileUpload question can allow
     * (MartFile::FILE_UPLOAD_EXTENSIONS), as a real minimal file in
     * tests/fixtures/mart-uploads.
     *
     * @test
     *
     * @dataProvider offeredFormats
     */
    public function an_offered_format_is_stored_in_the_bucket_of_its_content(string $extension, ?string $bucket)
    {
        $fileId = $this->appUpload($this->fixture("sample{$extension}"), 'file', "upload{$extension}")
            ->assertCreated()
            ->json('file_id');

        $this->assertSame($bucket, MartFile::find($fileId)->file_type);
    }

    public static function offeredFormats(): array
    {
        return collect(MartFile::FILE_UPLOAD_EXTENSIONS)
            ->mapWithKeys(fn ($extension) => [$extension => [$extension, self::BUCKETS[$extension] ?? null]])
            ->all();
    }

    /**
     * Two upload questions in one submit: each file gets its own question.
     *
     * @test
     */
    public function each_file_in_a_submit_gets_the_question_it_answers()
    {
        [$first, $second] = $this->uploadQuestions(['file', 'file']);
        $pdf = $this->appUpload($this->fixture('sample.pdf'))->json();
        $photo = $this->appUpload($this->fixture('sample.jpg'), 'file', 'photo.jpg')->json();

        $this->appSubmitAnswers($first->schedule, [
            '1' => $this->appFileAnswer($pdf),
            '2' => $this->appFileAnswer($photo),
        ])->assertOk();

        $this->assertSame(
            [$first->uuid, $second->uuid],
            [MartFile::find($pdf['file_id'])->question_uuid, MartFile::find($photo['file_id'])->question_uuid],
        );
    }

    /**
     * The app uploads with the zero question UUID, so the submit sets the
     * real one.
     *
     * @test
     */
    public function a_submitted_file_answer_sets_the_question_of_its_file()
    {
        $question = $this->uploadQuestion('file');
        $upload = $this->appUpload($this->fixture('sample.pdf'))->json();

        $this->appSubmit($question, $this->appFileAnswer($upload))->assertOk();

        $this->assertSame($question->uuid, MartFile::find($upload['file_id'])->question_uuid);
    }

    /** @test */
    public function a_submitted_file_answer_links_its_file_to_the_entry()
    {
        $question = $this->uploadQuestion('file');
        $upload = $this->appUpload($this->fixture('sample.pdf'))->json();

        $entryId = $this->appSubmit($question, $this->appFileAnswer($upload))->json('entry_id');

        $this->assertSame($entryId, MartFile::find($upload['file_id'])->mart_entry_id);
    }

    /**
     * Photo, audio and video uploads carry the zero UUID too.
     *
     * @test
     */
    public function a_submitted_photo_answer_sets_the_question_of_its_file()
    {
        $question = $this->uploadQuestion('photo');
        $upload = $this->appUpload($this->fixture('sample.jpg'), 'photo', 'photo.jpg')->json();

        $this->appSubmit($question, $this->appFileAnswer($upload))->assertOk();

        $this->assertSame($question->uuid, MartFile::find($upload['file_id'])->question_uuid);
    }

    /**
     * The answer holds the file's id twice, once inside its URL. Only the
     * bare id is linked, so no link fails.
     *
     * @test
     */
    public function a_file_answer_links_its_file_once()
    {
        $question = $this->uploadQuestion('file');
        $upload = $this->appUpload($this->fixture('sample.pdf'))->json();
        Log::spy();

        $this->appSubmit($question, $this->appFileAnswer($upload))->assertOk();

        Log::shouldNotHaveReceived('warning');
    }

    /** @test */
    public function a_submit_leaves_a_file_of_another_case_alone()
    {
        $question = $this->uploadQuestion('file');
        $other = User::factory()->create();
        $otherCase = Cases::factory()->create(['project_id' => $this->project->id, 'user_id' => $other->id]);
        $upload = $this->appUpload($this->fixture('sample.pdf'), 'file', 'upload.pdf', $other, $otherCase)->json();

        $this->appSubmit($question, $this->appFileAnswer($upload))->assertOk();

        $file = MartFile::find($upload['file_id']);
        $this->assertSame([null, self::ZERO_UUID], [$file->mart_entry_id, $file->question_uuid]);
    }

    /**
     * Only a link sets the question, so sending a linked file again doesn't
     * move it to another question.
     *
     * @test
     */
    public function a_linked_file_keeps_its_question_when_it_is_sent_again()
    {
        $first = $this->uploadQuestion('file');
        $second = $this->uploadQuestion('file');
        $upload = $this->appUpload($this->fixture('sample.pdf'))->json();
        $this->appSubmit($first, $this->appFileAnswer($upload))->assertOk();

        $this->appSubmit($second, $this->appFileAnswer($upload))->assertOk();

        $this->assertSame($first->uuid, MartFile::find($upload['file_id'])->question_uuid);
    }

    /**
     * Only the case's own participant may add files to it.
     *
     * @test
     */
    public function another_participant_cannot_upload_to_the_case()
    {
        $this->appUpload($this->fixture('sample.jpg'), 'photo', 'photo.jpg', User::factory()->create())
            ->assertForbidden();

        $this->assertFalse(MartFile::where('case_id', $this->case->id)->exists());
    }

    private function fixture(string $name): string
    {
        return file_get_contents(base_path("tests/fixtures/mart-uploads/{$name}"));
    }

    /**
     * Uploads the way the app's usePost does for a "files" item: a JSON body,
     * raw base64 (cut from a data: URL at the first comma), the zero question
     * UUID, and file_type from the scale type ("file" for fileUpload).
     */
    private function appUpload(
        string $content,
        string $fileType = 'file',
        string $originalName = 'upload.pdf',
        ?User $as = null,
        ?Cases $case = null,
    ): TestResponse {
        $case ??= $this->case;

        return $this->actingAs($as ?? $this->user, 'api')->postJson("/mart-api/cases/{$case->id}/files", [
            'question_uuid' => self::ZERO_UUID,
            'file_type' => $fileType,
            'file' => base64_encode($content),
            'original_name' => $originalName,
        ]);
    }

    /**
     * The answer the app builds from an upload's response: the fileUrl with
     * the app's API base in front, then the file_id.
     */
    private function appFileAnswer(array $upload): array
    {
        return [self::APP_API_BASE . $upload['fileUrl'], $upload['file_id']];
    }

    /** A questionnaire with one mandatory upload question of this type. */
    private function uploadQuestion(string $type): MartQuestion
    {
        return $this->uploadQuestions([$type])[0];
    }

    /**
     * A questionnaire with one mandatory upload question per type, in this
     * order.
     *
     * @return MartQuestion[]
     */
    private function uploadQuestions(array $types): array
    {
        $schedule = MartSchedule::create([
            'mart_project_id' => MartProject::where('main_project_id', $this->project->id)->firstOrFail()->id,
            'questionnaire_id' => $this->project->id * 1000 + $this->nextQuestionnaireNumber++,
            'name' => 'Uploads',
            'type' => 'single',
            'timing_config' => [],
            'notification_config' => [],
        ]);

        return array_map(fn ($type, $index) => MartQuestion::create([
            'schedule_id' => $schedule->id,
            'position' => $index + 1,
            'text' => 'Upload a file ' . ($index + 1),
            'type' => $type,
            'config' => $type === 'file' ? ['fileUploadOptions' => ['maxFileSize' => 20, 'allowedFormats' => ['.pdf', '.jpg']]] : [],
            'is_mandatory' => true,
            'version' => 1,
        ]), $types, array_keys($types));
    }

    /** Submits the question's answer the way the app does, keyed by its item ID. */
    private function appSubmit(MartQuestion $question, array $answer): TestResponse
    {
        return $this->appSubmitAnswers($question->schedule, ['1' => $answer]);
    }

    /** Submits answers keyed by item ID, the way the app does. */
    private function appSubmitAnswers(MartSchedule $schedule, array $answers): TestResponse
    {
        return $this->actingAs($this->user, 'api')->postJson("/mart-api/cases/{$this->case->id}/submit", [
            'userId' => $this->user->email,
            'projectId' => $this->project->id,
            'questionnaireId' => $schedule->questionnaire_id,
            'participantId' => $this->case->name,
            'answers' => $answers,
            'questionnaireStarted' => now()->timestamp * 1000,
            'questionnaireDuration' => 60000,
            'timestamp' => now()->timestamp * 1000,
            'timestampInherited' => 0,
            'timezone' => 'Europe/Berlin',
        ]);
    }
}
