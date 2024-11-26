<?php

namespace App;

use File;
use Helper;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Image;

class Files extends Model
{
    /**
     * @var string
     */
    protected $table = 'files_cases';

    /**
     * @var array
     */
    protected $fillable = [
        'type', 'path', 'size', 'case_id',
    ];

    /**
     * @return mixed
     */
    public static function occupiedStorage()
    {
        return Files::all()->sum('size');
    }

    public static function storeEntryFile($file, $type, Project $project, Cases $case, Entry $entry, &$name): void
    {
        $name = 'interview_' . $case->name . date('dmyhis');
        $extension = Helper::extension($file);
        $relativePath = 'project' . $project->id . '/files/' . $name . '.' . $extension;
        $fullPath = storage_path('app/' . $relativePath);

        // Make sure directory exists
        if (! Storage::exists('project' . $project->id . '/files')) {
            Storage::makeDirectory('project' . $project->id . '/files', 0775, true);
        }
        if ($type === 'audio') {
            $content = base64_decode(substr(explode(',', $file, 2)[1], 0, -1));
            Storage::put($relativePath, $content);

        }

        $arr = explode(',', $file, 2);
        self::SaveEncryptedFile($project, $name, $arr, $notEncryptedContent, $encryptedPath);
        self::SaveFileDbRecord($case->id, $name, $projectPath, $encryptedPath, $entry);
    }

    public static function updateEntryFile($file, $type, Project $project, Cases $case, Entry $entry, &$name, $oldInputs): void
    {
        // Decode old inputs and clean up existing file if present
        $oldInputs = json_decode($oldInputs);
        if (property_exists($oldInputs, 'file')) {
            $existingFile = Files::where('id', '=', $oldInputs->file)->first();
            if ($existingFile) {
                // Delete the file using Storage facade
                Storage::delete(str_replace(storage_path('app/'), '', $existingFile->path));
                $existingFile->delete();
            }
        }

        // Generate new file name
        $name = 'interview_' . $case->name . date('dmyhis');
        $extension = Helper::extension($file);

        // Define relative and full paths
        $relativePath = 'project' . $project->id . '/files';
        $tempFileName = $name . '.' . $extension;

        // Ensure directory exists
        if (! Storage::exists($relativePath)) {
            Storage::makeDirectory($relativePath, 0775, true);
        }

        // Handle file based on type
        if ($type === 'audio') {
            $content = base64_decode(substr(explode(',', $file, 2)[1], 0, -1));

            // Store temporary file
            $tempFilePath = $relativePath . '/' . $tempFileName;
            Storage::put($tempFilePath, $content);

            // Full path needed for encryption process
            $fullTempPath = storage_path('app/' . $tempFilePath);

            // Process and encrypt the file
            $arr = explode(',', $file, 2);
            $encryptedPath = '';

            self::SaveEncryptedFile(
                $project,
                $name,
                $arr,
                $fullTempPath,
                $encryptedPath
            );

            // Save record to database
            self::SaveFileDbRecord(
                $case->id,
                $name,
                storage_path('app/' . $relativePath),
                $encryptedPath,
                $entry
            );

            // Clean up temporary file
            Storage::delete($tempFilePath);
        }
    }

    private static function SaveEncryptedFile(Project $project, &$name, array $arr, string $notEncryptedContent, &$encryptedPath): void
    {
        $base64firstpart = $arr[0];
        $encryptedContent = encrypt($base64firstpart . ',' . base64_encode(file_get_contents($notEncryptedContent)));
        $encryptedPath = 'project' . $project->id . '/files/' . $name . '.mfile';
        // Store the encrypted Content
        Storage::put($encryptedPath, $encryptedContent);
        File::delete($notEncryptedContent);
    }

    private static function SaveFileDbRecord($caseid, &$name, string $projectPath, string $encryptedPath, Entry $entry): void
    {
        $file_interview = new Files;
        // write here if audio or image
        $file_interview->type = 'file_';
        $file_interview->path = $projectPath . $name . '.mfile';
        if (is_file(storage_path('app/' . $encryptedPath))) {
            $file_interview->size = File::size(storage_path('app/' . $encryptedPath));
        } else {
            $file_interview->size = 0;
        }
        $file_interview->case_id = $caseid;
        $file_interview->save();
        $entry->update(
            [
                'inputs->file' => $file_interview->id,
            ],
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function case()
    {
        return $this->belongsTo(Cases::class);
    }
}
