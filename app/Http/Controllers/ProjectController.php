<?php

namespace App\Http\Controllers;

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

        $project->media = $project->media()->pluck('media.id')->toArray();
        $project->places = $project->places()->pluck('places.id')->toArray();
        $project->communication_partners = $project->communication_partners()->pluck('communication_partners.id')->toArray();

        $data['data']['media'] = Media::all();
        $data['data']['places'] = Place::all();
        $data['data']['cp'] = Communication_partner::all();
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
        $data['places'] = Place::all();
        $data['cp'] = Communication_partner::all();

        return view('projects.create',$data);
    }


    public function store(Request $request){

        $cp = request()->cp;
        $media = request()->media;
        $places = request()->places;

        $attributes = request()->validate([
            'name' => 'required',
            'description' => 'required',
            'created_by' => 'required',
            'duration' => 'nullable',
            'is_locked' => 'nullable ',
            'inputs' => 'nullable'
        ]);


        if(!isset($attributes['is_locked'])) $attributes['is_locked'] = 0;
        else{

            if($attributes['is_locked'] != 0 && $attributes['is_locked'] != 1)
            {
                if($attributes['is_locked'] == "on")$attributes['is_locked'] = 1;
                elseif($attributes['is_locked'] == "off") $attributes['is_locked'] = 0;
            }
        }

        $project = auth()->user()->projects()->create($attributes);

        $project->media()->sync(Media::whereIn('id',$media)->get());
        $project->places()->sync(Place::whereIn('id',$places)->get());
        $project->communication_partners()->sync(Communication_partner::whereIn('id',$cp)->get());


        return redirect('/projects');

    }

    public function update(Project $project,Request $request)
    {

        $this->authorize('update',$project);

        $cp = request()->cp;
        $media = request()->media;
        $places = request()->places;

        $attributes = request()->validate([
            'name' => 'required',
            'description' => 'required',
            'duration' => 'nullable',
            'is_locked' => 'nullable ',
            'inputs' => 'nullable'
        ]);
        $project->update($request->all());
        $project->save();

        $project->media()->sync(Media::whereIn('id',$media)->get());
        $project->places()->sync(Place::whereIn('id',$places)->get());
        $project->communication_partners()->sync(Communication_partner::whereIn('id',$cp)->get());



        return response("Updated project successfully");


    }
}
