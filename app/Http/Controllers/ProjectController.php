<?php

namespace App\Http\Controllers;

use App\Cases;
use Illuminate\Http\Request;
use App\Project;
use App\Media;
use App\Place;
use App\Communication_partner;

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

        $project->media = $project->media()->pluck('media.name')->toArray();

         $data['data']['media'] = Media::all();

        $data['project'] = $project;

        return view('projects.show',$data);
    }

    /**
     * Show Create form
     * @return View return view with the form to insert a new project
     */
    public function create()
    {
        $data['media'] = Media::all();


        return view('projects.create',$data);
    }


    public function store(Request $request){

        $media = request()->media;

        $attributes = request()->validate([
            'name' => 'required',
            'description' => 'required',
            'created_by' => 'required',
            'is_locked' => 'nullable ',
            'inputs' => 'nullable'
        ]);

        $attributes = $this->handleLockedValue($attributes);


        $project = auth()->user()->projects()->create($attributes);

        if($media){
            $mToSync = array();
            foreach (array_filter($media) as $m){
                array_push($mToSync,Media::firstOrCreate(['name' => $m])->id);

            }
            $project->media()->sync(Media::whereIn('id',$mToSync)->get());
        }


        return redirect('/projects');

    }



    public function update(Project $project,Request $request)
    {

        $this->authorize('update',$project);

        $media = request()->media;

        $attributes = request()->validate([
            'name' => 'required',
            'description' => 'required',
            'duration' => 'nullable',
            'is_locked' => 'nullable ',
            'inputs' => 'nullable'
        ]);

        $project->update($request->all());
        $project->save();

        if($media){
            $mToSync = array();
            foreach (array_filter($media) as $m){
                array_push($mToSync,Media::firstOrCreate(['name' => $m])->id);

            }
            $project->media()->sync(Media::whereIn('id',$mToSync)->get());
        }


        return response("Updated project successfully");


    }

    /**
     * @param $attributes
     * @return mixed
     */
    public function handleLockedValue($attributes)
    {
        if (!isset($attributes['is_locked'])) $attributes['is_locked'] = 0;
        else {

            if ($attributes['is_locked'] != 0 && $attributes['is_locked'] != 1) {
                if ($attributes['is_locked'] == "on") $attributes['is_locked'] = 1;
                elseif ($attributes['is_locked'] == "off") $attributes['is_locked'] = 0;
            }
        }
        return $attributes;
    }


    public function destroy(Project $project)
    {
        if($project->isEditable()){
            $project->delete();
        }else{
            return redirect()->back()->with('message', 'Project has entries, you cannot delete it');
        }


        return redirect(url(''))->with('message','Project deleted');

    }
}
