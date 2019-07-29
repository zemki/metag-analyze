<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Cases;

class CaseController extends Controller
{
	public function update(){

	}

    /**
     * @param Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function caseExists(Request $request)
    {
        return response()->json(Cases::where('name', '=', $request['name'])->where('project_id','=',$request['project'])->first() ? true : false, 200);
    }
}
