<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Cases;
use App\Files;

class FileCasesController extends Controller
{


    public function index(Cases $case)
    {
        $caseFiles = Files::where('case_id', '=', $case->id)->get();

        $data['files'] = $caseFiles;


        foreach ($data['files'] as $file) {
            $file['audiofile'] = decrypt(file_get_contents($file['path']));
        }

        return view('files.index', $data);
    }
}
