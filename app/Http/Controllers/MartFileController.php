<?php

namespace App\Http\Controllers;

use App\Cases;
use App\Mart\MartFile;
use App\Project;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MartFileController extends Controller
{
    /**
     * Upload a file for a MART questionnaire answer.
     *
     * Files are uploaded before submission, then referenced by UUID in the submit.
     */
    public function store(Request $request, Cases $case): JsonResponse
    {
        // Validate request
        $request->validate([
            'question_uuid' => 'nullable|string', // Optional - can link later
            'file_type' => 'required|in:photo,video,audio,document',
            'file' => 'required|string', // Base64 encoded content
            'original_name' => 'nullable|string|max:255',
        ]);

        // Verify case belongs to a valid project
        $project = $case->project;
        if (!$project) {
            return response()->json([
                'success' => false,
                'message' => 'Project not found for this case',
            ], 404);
        }

        // Verify this is a MART project
        if (!$project->isMartProject()) {
            return response()->json([
                'success' => false,
                'message' => 'File uploads are only supported for MART projects',
            ], 400);
        }

        // Decode base64 file content
        $fileContent = base64_decode($request->file, true);
        if ($fileContent === false) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid base64 encoded file content',
            ], 400);
        }

        // Validate file size (50MB max)
        $fileSize = strlen($fileContent);
        if ($fileSize > MartFile::MAX_FILE_SIZE) {
            return response()->json([
                'success' => false,
                'message' => 'File exceeds maximum size of 50MB',
                'max_size_bytes' => MartFile::MAX_FILE_SIZE,
                'actual_size_bytes' => $fileSize,
            ], 422);
        }

        // Detect actual MIME type from content
        $detectedMimeType = MartFile::detectMimeType($fileContent);
        if (!$detectedMimeType) {
            return response()->json([
                'success' => false,
                'message' => 'Could not detect file MIME type',
            ], 400);
        }

        // Validate MIME type against allowed types for the file type
        if (!MartFile::isAllowedMimeType($request->file_type, $detectedMimeType)) {
            return response()->json([
                'success' => false,
                'message' => "MIME type '{$detectedMimeType}' is not allowed for file type '{$request->file_type}'",
                'allowed_types' => MartFile::ALLOWED_MIME_TYPES[$request->file_type] ?? [],
            ], 422);
        }

        // Generate UUID and storage path
        $fileId = (string) \Illuminate\Support\Str::uuid();
        $storagePath = MartFile::generateStoragePath($project->id, $case->id, $fileId);

        // Create the MartFile record
        $martFile = new MartFile([
            'id' => $fileId,
            'case_id' => $case->id,
            'project_id' => $project->id,
            'question_uuid' => $request->question_uuid,
            'file_type' => $request->file_type,
            'mime_type' => $detectedMimeType,
            'original_name' => $request->original_name,
            'storage_path' => $storagePath,
            'size' => $fileSize,
            'metadata' => $this->extractMetadata($fileContent, $request->file_type, $detectedMimeType),
        ]);

        // Store encrypted file
        if (!$martFile->storeEncrypted($fileContent)) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to store file',
            ], 500);
        }

        // Save the record
        $martFile->save();

        Log::info('MART file uploaded', [
            'file_id' => $martFile->id,
            'case_id' => $case->id,
            'project_id' => $project->id,
            'file_type' => $request->file_type,
            'mime_type' => $detectedMimeType,
            'size' => $fileSize,
        ]);

        return response()->json([
            'success' => true,
            'file_id' => $martFile->id,
            'file_type' => $martFile->file_type,
            'mime_type' => $martFile->mime_type,
            'size' => $martFile->size,
        ], 201);
    }

    /**
     * Retrieve a file by ID.
     */
    public function show(Request $request, MartFile $martFile): \Symfony\Component\HttpFoundation\Response
    {
        // Verify the authenticated user has access to this file
        // The user should have a case linked to the same project
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required',
            ], 401);
        }

        // Check if user has access to the project through a case
        $hasAccess = Cases::where('project_id', $martFile->project_id)
            ->where('user_id', $user->id)
            ->exists();

        // Also allow project owners/team members via policy
        if (!$hasAccess) {
            $project = Project::find($martFile->project_id);
            if ($project) {
                try {
                    $this->authorize('view', $project);
                    $hasAccess = true;
                } catch (\Illuminate\Auth\Access\AuthorizationException $e) {
                    // User doesn't have policy-based access either
                }
            }
        }

        if (!$hasAccess) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied to this file',
            ], 403);
        }

        // Get decrypted content
        $content = $martFile->getDecryptedContent();
        if ($content === null) {
            return response()->json([
                'success' => false,
                'message' => 'File not found or could not be decrypted',
            ], 404);
        }

        // Determine filename for download
        $extension = MartFile::getExtensionFromMime($martFile->mime_type);
        $filename = $martFile->original_name ?? "file-{$martFile->id}.{$extension}";

        // Return file with appropriate headers
        return response($content)
            ->header('Content-Type', $martFile->mime_type)
            ->header('Content-Length', $martFile->size)
            ->header('Content-Disposition', 'inline; filename="' . $filename . '"')
            ->header('Cache-Control', 'private, max-age=3600');
    }

    /**
     * Delete a file (only if not yet linked to an entry).
     */
    public function destroy(Request $request, MartFile $martFile): JsonResponse
    {
        // Verify access
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication required',
            ], 401);
        }

        // Check if file is linked to an entry - cannot delete if already submitted
        if ($martFile->mart_entry_id !== null) {
            return response()->json([
                'success' => false,
                'message' => 'Cannot delete file that has been submitted',
            ], 400);
        }

        // Verify the user owns the case for this file
        $case = Cases::where('id', $martFile->case_id)
            ->where('user_id', $user->id)
            ->first();

        if (!$case) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied to this file',
            ], 403);
        }

        // Delete the file from storage
        $martFile->deleteFile();

        // Delete the record
        $martFile->delete();

        Log::info('MART file deleted', [
            'file_id' => $martFile->id,
            'case_id' => $martFile->case_id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'File deleted successfully',
        ]);
    }

    /**
     * Extract metadata from file content.
     */
    private function extractMetadata(string $content, string $fileType, string $mimeType): array
    {
        $metadata = [
            'extracted_at' => now()->toIso8601String(),
        ];

        // For images, try to get dimensions
        if ($fileType === 'photo') {
            $imageInfo = @getimagesizefromstring($content);
            if ($imageInfo) {
                $metadata['width'] = $imageInfo[0];
                $metadata['height'] = $imageInfo[1];
            }
        }

        // Note: Video/audio duration extraction would require ffprobe
        // which may not be available on all systems. Skip for now.

        return $metadata;
    }

    /**
     * Link uploaded files to a MART entry after submission.
     * Called from MartApiController::submitEntry
     *
     * @param array $fileIds Array of file UUIDs
     * @param int $entryId The MART entry ID
     * @param int $caseId The case ID for verification
     * @return array Results with success/error info
     */
    public static function linkFilesToEntry(array $fileIds, int $entryId, int $caseId): array
    {
        $results = [
            'linked' => [],
            'errors' => [],
        ];

        foreach ($fileIds as $fileId) {
            $file = MartFile::find($fileId);

            if (!$file) {
                $results['errors'][] = "File {$fileId} not found";
                continue;
            }

            if ($file->case_id !== $caseId) {
                $results['errors'][] = "File {$fileId} does not belong to this case";
                continue;
            }

            if ($file->mart_entry_id !== null) {
                $results['errors'][] = "File {$fileId} is already linked to an entry";
                continue;
            }

            $file->linkToEntry($entryId);
            $results['linked'][] = $fileId;
        }

        return $results;
    }
}
