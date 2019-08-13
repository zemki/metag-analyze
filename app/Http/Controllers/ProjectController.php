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

        return view('projects.index', compact('projects'));
    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show(Project $project)
    {

        $this->authorize('update', $project);
        $data['breadcrumb'] = [url('/') => 'Projects', '#' => $project->name];

        $project->media = $project->media()->pluck('media.name')->toArray();

        $data['data']['media'] = Media::all();

        $data['project'] = $project;

        return view('projects.show', $data);
    }

    /**
     * Show Create form
     *
     * @return View return view with the form to insert a new project
     */
    public function create()
    {
        $data['breadcrumb'] = [url('/') => 'Projects', '#' => 'Create'];

        return view('projects.create', $data);
    }


    public function store()
    {
        $media = request()->media;
        $attributes = request()->validate([
            'name' => 'required',
            'description' => 'required',
            'created_by' => 'required',
            'is_locked' => 'nullable ',
            'inputs' => 'nullable'
        ]);

        $inputs = json_decode($attributes['inputs']);
        foreach ($inputs as $input){
            $input->answers = array_filter($input->answers);
        }
        $attributes['inputs'] = json_encode($inputs);

        $project = auth()->user()->projects()->create($attributes);

        $this->syncMedia($media, $project, $mToSync);

        return redirect('projects');

    }


    public function update(Project $project)
    {
        $this->authorize('update', $project);
        $media = request()->media;
        $attributes = request()->validate([
            'name' => 'required',
            'description' => 'required',
            'duration' => 'nullable',
            'is_locked' => 'nullable ',
            'inputs' => 'nullable'
        ]);
        $project->update($attributes);
        $project->save();
        $this->syncMedia($media, $project, $mToSync);
        return response("Updated project successfully");
    }



    public function destroy(Project $project)
    {
        if ($project->isEditable()) {
            $project->delete();
        } else {
            return redirect()->back()->with('message', 'Project has entries, you cannot delete it');
        }

        return redirect(url(''))->with('message', 'Project deleted');

    }

    /**
     * @param $media
     * @param $project
     * @param $mToSync
     */
    protected function syncMedia($media, $project, &$mToSync): void
    {
        if ($media) {
            $mToSync = array();
            foreach (array_filter($media) as $singleMedia) {
                array_push($mToSync, Media::firstOrCreate(['name' => $singleMedia])->id);

            }
            $project->media()->sync(Media::whereIn('id', $mToSync)->get());
        }
    }
}
