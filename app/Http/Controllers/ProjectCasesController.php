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
	 * @return the project view with the new stored data
	 */
	public function store(Project $project)
	{

		$this->authorize('update',$project);

		request()->validate(['name' => 'required']);
		$project->addCase(request('name'));

		return redirect($project->path());
	}

	public function update(Project $project,Cases $case)
	{
        $this->authorize('update',$case->project);


		request()->validate(['name' => 'required']);

		$case->update([
			'name' => request('name')
		]);

		return redirect($project->path());
	}
}
