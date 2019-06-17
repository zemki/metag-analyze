<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Project;
use App\Cases;
use App\User;
use App\Role;
use Str;

class ProjectCasesController extends Controller
{

	public function show(Project $project,Cases $case)
	{

		if(auth()->user()->isNot($project->created_by()->first())) {
			abort(403);
		}
        $i = 0;

        $data['entriesByMedia'] = $case->entries()
            ->join('media','entries.media_id','=','media.id')
            ->get()
            ->map
            ->only(['name','begin','end'])
            ->flatten()
            ->chunk(3)
            ->toArray();
        $data['entriesByPlace'] = $case->entries()
            ->join('places','entries.place_id','=','places.id')
            ->get()
            ->map
            ->only(['name','begin','end'])
            ->flatten()
            ->chunk(3)
            ->toArray();
        $data['entriesByCommunicationPartner'] = $case->entries()
            ->join('communication_partners','entries.communication_partner_id','=','communication_partners.id')
            ->get()
            ->map
            ->only(['name','begin','end'])
            ->flatten()
            ->chunk(3)
            ->toArray();
        // GET MEDIA, PLACES, COMMUNICATION PARTNERS

        $data['entriesByMedia'] = array_map('array_values', $data['entriesByMedia']);
        $data['entriesByPlace'] = array_map('array_values', $data['entriesByPlace']);
        $data['entriesByCommunicationPartner'] = array_map('array_values', $data['entriesByCommunicationPartner']);
        $data['case'] = $case;


        return view('entries.index',$data);


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

		$case = $project->addCase(request('name'),request('duration'));
        $user = User::firstOrNew(['email' => $email]);

        if (!$user->exists)
        {
            $user->username = request('email');
            $user->email = request('email');
            $role = Role::where('name','=','user')->first();
            $p = Str::random(request('passwordLength'));
            $user->password = bcrypt($p);
            $user->save();
            $user->roles()->sync($role);
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
