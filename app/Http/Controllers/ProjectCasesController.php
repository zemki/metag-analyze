<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Project;
use App\Cases;

class ProjectCasesController extends Controller
{

	public function show(Project $project,Cases $case)
	{

		if(auth()->user()->isNot($project->created_by()->first())) {
			abort(403);
		}

		return view('cases.show',compact('project','case'));

	}

	public function create(Project $project)
	{

        if(auth()->user()->isNot($project->created_by()->first())) {
            abort(403);
        }

        return view('cases.create',compact('project'));

	}

	/**
	 * Store a new case binded to the project
	 * @param  Project $project
	 * @return the project view with the updated data
	 */
	public function store(Project $project)
	{
		if(auth()->user()->isNot($project->created_by()->first())) abort(403);
		request()->validate(['name' => 'required']);
		$project->addCase(request('name'));

		return redirect($project->path());
	}
}
