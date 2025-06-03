<?php

namespace App\Http\Controllers;

use App\Http\Resources\Mart\MartStructureResource;
use App\Project;
use App\Entry;
use App\Cases;
use Illuminate\Http\Request;

class MartApiController extends Controller
{
    /**
     * Get project structure for mobile app
     */
    public function getProjectStructure(Project $project)
    {
        return new MartStructureResource($project);
    }
    
    /**
     * Handle submission from mobile app
     */
    public function submitEntry(Request $request, Cases $case)
    {
        // Validate request
        $request->validate([
            'projectId' => 'required|numeric',
            'uuid' => 'required|string',
            'userId' => 'required|string',
            'participantId' => 'required|string',
            'sheetId' => 'required|numeric',
            'sheetStarted' => 'required|numeric',
            'sheetSubmitted' => 'required|numeric',
            'sheetDuration' => 'required|numeric',
            'answers' => 'required|array'
        ]);
        
        // Transform to Entry format
        $entryData = [
            'begin' => date('Y-m-d H:i:s', $request->sheetStarted/1000),
            'end' => date('Y-m-d H:i:s', $request->sheetSubmitted/1000),
            'case_id' => $case->id,
            'entity_id' => 1, // Use default entity or adjust as needed
            'inputs' => json_encode($request->answers)
        ];
        
        // Create entry
        $entry = Entry::create($entryData);
        
        return response()->json([
            'success' => true,
            'entry_id' => $entry->id
        ]);
    }
}