<?php

namespace App\Http\Controllers;

use App\Cases;
use App\Files;
use App\Media;
use File;

class FileCasesController extends Controller
{
    public function index(Cases $case)
    {
        $caseFiles = Files::where('case_id', '=', $case->id)->get();

        $data['files'] = $caseFiles;
        $data['case'] = $case;

        $entries = $case->entries();
        foreach ($data['files'] as $file) {
            $file['audiofile'] = decrypt(file_get_contents($file['path']));
            $file['entry'] = $case->entries()->whereJsonContains('inputs->file', $file['id'])->first();
            if (! empty($file['entry'])) {
                $file['entry']->media_id = Media::where('id', $file['entry']->media_id)->first()->name;
            }
        }

        $data['breadcrumb'] = [url('/projects/' . $case->project->id) => 'Cases', '#' => substr($case->name, 0, 20) . '...'];

        return view('files.index', $data);
    }

    /**
     * Get a specific file for audio playback (web frontend)
     * Similar to Api\V2\FileController@show but uses web session auth
     */
    public function show(Files $file)
    {
        try {
            // Get the case this file belongs to
            $case = $file->case;

            if (!$case) {
                return response()->json([
                    'error' => 'File not found',
                ], 404);
            }

            // Check if user has access to this case
            // User must be assigned to this case or be the project owner
            $hasAccess = $case->user_id === auth()->id() ||
                        $case->project->created_by === auth()->id();

            if (!$hasAccess) {
                return response()->json([
                    'error' => 'Access denied',
                ], 403);
            }

            // Check if file exists
            if (!file_exists($file->path)) {
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

        } catch (\Exception $e) {
            \Log::error('FileCasesController@show error: ' . $e->getMessage());

            return response()->json([
                'error' => 'Failed to retrieve file',
            ], 500);
        }
    }

    public function destroy(Cases $case, Files $file)
    {
        $project = $case->project;
        if ($project->created_by == auth()->user()->id) {
            File::delete($file->path);
            $file->delete();
        } else {
            return response()->json(['message' => 'You can\'t delete this File'], 403);
        }

        return response()->json(['message' => 'File deleted'], 200);

    }
}
