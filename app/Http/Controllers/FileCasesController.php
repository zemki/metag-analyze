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
        return view('files.index', $data);
    }
}
