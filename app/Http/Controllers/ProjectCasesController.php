<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Project;
use App\Cases;
use App\User;
use App\Role;
use Helper;

class ProjectCasesController extends Controller
{

    /**
     * @param Project $project
     * @param Cases $case
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function show(Project $project, Cases $case)
	{
		if(auth()->user()->isNot($project->created_by()->first())) {
			abort(403);
		}
        $data['entriesByMedia'] = $case->entries()
            ->join('media','entries.media_id','=','media.id')
            ->get()
            ->map
            ->only(['name','begin','end'])
            ->flatten()
            ->chunk(3)
            ->toArray();
        // THEN GET ENTRIES BY INPUTS - EXTRACT FROM ENTRIES->INPUT THE LIST OF THEM, THEN DO THE SAME THING - DYNAMIC VAR NAME?

        $data['entriesByMedia'] = array_map('array_values', $data['entriesByMedia']);
        $data['case'] = $case;
        return view('entries.index',$data);
	}


    /**
     * @param Project $project
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function create(Project $project)
	{
        $data['breadcrumb'] = [
            url('/') => 'Projects',
            $project->path() => $project->name,
            '#' => 'Create Case'
            ];

        if(auth()->user()->isNot($project->created_by()->first())) {
            abort(403);
        }

        $data['users'] = User::all();
        $data['project'] = $project;

        return view('cases.create',$data);

	}

	/**
	 * Store a new case binded to the project
     *
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
		$case = $project->addCase(request('name'),request('duration'));
        $user = User::firstOrNew(['email' => $email]);

        $this->createUserIfDoesNotExists($user, $password);

        $case->addUser($user);

		return redirect($project->path())->with(['message' => isset($password)? $user->email.' can now enter with the password: '.$password : 'user was already registered']);
	}

    /**
     * @param Project $project
     * @param Cases $case
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function update(Project $project, Cases $case)
	{
        $this->authorize('update',$case->project);
		request()->validate(['name' => 'required']);
		$case->update([
			'name' => request('name')
		]);
		return redirect($project->path());
	}

    /**
     * @param Project $project
     * @param Cases $case
     * @return \Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector
     * @throws \Exception
     */
    public function destroy(Project $project, Cases $case)
    {
     if($case->isEditable()){
         $case->delete();
     }else{
         return redirect()->back()->with('message', 'case has entries, you cannot delete it');
     }

        return redirect($project->path())->with('message','case deleted');

    }

    /**
     * @param $user
     * @param $password
     */
    protected function createUserIfDoesNotExists($user, &$password): void
    {
        if (!$user->exists) {
            $user->username = request('email');
            $user->email = request('email');
            $role = Role::where('name', '=', 'user')->first();
            $password = Helper::random_str(request('passwordLength'));
            $user->password = bcrypt($password);

            $user->save();
            $user->roles()->sync($role);
        }
    }
}
