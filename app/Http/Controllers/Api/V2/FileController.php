<?php

namespace App\Http\Controllers\Api\V2;

use App\Files;
use App\Http\Controllers\Controller;
use Exception;
use Illuminate\Http\JsonResponse;
use Log;

class FileController extends Controller
{
    /**
     * Get a specific file by ID for mobile app audio playback
     *
     * @return JsonResponse
     */
    public function show(Files $file)
    {
        try {
            // Get the authenticated user
            $user = auth()->user();

            // Get the case this file belongs to
            $case = $file->case;

            if (! $case) {
                return response()->json([
                    'error' => 'File not found',
                ], 404);
            }

            // Check if user has access to this case
            // User must be assigned to this case or be the project owner
            $hasAccess = $case->users->contains($user->id) ||
                        $case->project->created_by === $user->id;

            if (! $hasAccess) {
                return response()->json([
                    'error' => 'Access denied',
                ], 403);
            }

            // Check if file exists
            if (! file_exists($file->path)) {
                return response()->json([
                    'error' => 'File not found on disk',
                ], 404);
            }

            // Decrypt and return the file content
            $decryptedContent = decrypt(file_get_contents($file->path));

            return response()->json([
                'data' => $decryptedContent,
                'file_id' => $file->id,
                'case_id' => $case->id,
                'size' => $file->size,
            ]);

        } catch (Exception $e) {
            Log::error('FileController@show error: ' . $e->getMessage());

            return response()->json([
                'error' => 'Failed to retrieve file',
            ], 500);
        }
    }
}
