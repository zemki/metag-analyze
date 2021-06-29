<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Project;
use DB;
use Helper;

class ProjectNotificationController extends Controller
{
    /**
     * @param Project $project
     */
    public function show(Project $project){


        $data['cases'] = $project->cases;
        $data['project'] = $project;
        $data['casesWithUsers'] = $project->cases()->with('user')->get();



        return view('notifications.show', $data);

    }

    /**
     * Store and/or send a notification
     */
    public function store(){

    }
}
