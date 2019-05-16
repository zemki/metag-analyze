<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Project;
use App\Cases;
use App\User;
use Str;

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

        $users = User::all();

        return view('cases.create',compact('project','users'));

	}

	/**
	 * Store a new case binded to the project
	 * @param  Project $project
	 * @return the project view with the new stored data
	 */
	public function store(Project $project)
	{

	    $this->authorize('update',$project);

		request()->validate(
			['name' => 'required']
		);

		$email = request('email');

		$case = $project->addCase(request('name'));
        $user = User::firstOrNew(['email' => $email]);
        if (!$user->exists)
        {
            $user->username = request('email');
            $user->email = request('email');
            $p = Str::random(2);
            $user->password = bcrypt($p);
            $user->save();
        }

		$case->addUser($user);

		return redirect($project->path())->with(['message' => isset($p)? $user->email.' can now enter with the password: '.$p : 'user was already registered']);
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
