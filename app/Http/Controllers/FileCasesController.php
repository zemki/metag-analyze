<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
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
            $file['entry'] = $case->entries()->whereJsonContains('inputs->file',$file['id'])->first();
            if(!empty($file['entry'])){
                $file['entry']->media_id = Media::where('id',$file['entry']->media_id)->first()->name;
            }
        }

        $data['breadcrumb'] = [url('/projects/'.$case->project->id) => 'Cases', '#' => substr($case->name, 0, 20) . '...'];

        return view('files.index', $data);
    }

    public function destroy(Cases $case, Files $file)
    {
        $project = $case->project;
        if ($project->created_by == auth()->user()->id)
        {
            File::delete($file->path);          
            $file->delete();
        } else
        {
            return response()->json(['message' => 'You can\'t delete this File'], 403);
        }
        return response()->json(['message' => 'File deleted'], 200);

    }
}
