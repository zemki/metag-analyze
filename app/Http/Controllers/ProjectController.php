<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Project;

class ProjectController extends Controller
{


    public function index()
    {

        $projects = auth()->user()->projects()->get();

        return view('projects.index',compact('projects'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Project $project)
    {

        $this->authorize('update',$project);


        return view('projects.show',compact('project'));
    }

    /**
     * Show Create form
     * @return View return view with the form to insert a new project
     */
    public function create()
    {
        return view('projects.create');
    }


    public function store(Request $request){

    	$attributes = request()->validate([
            'name' => 'required',
            'description' => 'required',
            'created_by' => 'required',
            'duration' => 'nullable',
            'is_locked' => 'nullable '
        ]);

        if(!isset($attributes['is_locked'])) $attributes['is_locked'] = 0;
        else{

        if($attributes['is_locked'] != 0 && $attributes['is_locked'] != 1)
        {
            if($attributes['is_locked'] == "on")$attributes['is_locked'] = 1;
            elseif($attributes['is_locked'] == "off") $attributes['is_locked'] = 0;
        }
        }


        auth()->user()->projects()->create($attributes);

        return redirect('/projects');

    }

    public function update(Project $project)
    {

        $this->authorize('update',$project);

        $project->update(request(['name']));

        return redirect('/projects');

    }
}
