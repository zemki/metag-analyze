<?php

namespace App\Http\Controllers;

use Arr;
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
        $data['notifications'] = [];
        $data['plannedNotifications'] = [];
        foreach ($data['casesWithUsers'] as $cases)
        {
            $cases->notifications = $cases->notifications();
            $cases->planned_notifications = $cases->plannedNotifications();
            array_push($data['notifications'],$cases->notifications());
            array_push($data['plannedNotifications'],$cases->plannedNotifications());

            $cases->user->profile = $cases->user->profile;

        }
        $data['notifications'] = json_encode(Arr::flatten($data['notifications']));
        $data['plannedNotifications'] = json_encode(Arr::flatten($data['plannedNotifications']));
        return view('notifications.show', $data);

    }

    /**
     * Store and/or send a notification
     */
    public function store(){

    }
}
