<?php

namespace App\Http\Controllers;

use App\Project;
use Arr;

class ProjectNotificationController extends Controller
{
    public function show(Project $project)
    {
        $data['breadcrumb'] = [url($project->path()) => strlen($project->name) > 20 ? substr($project->name, 0, 20) . '...' : $project->name, '#' => 'Notification Center'];
        $data['cases'] = $project->cases;
        $data['project'] = $project;
        $data['casesWithUsers'] = $project->notBackendcases()->with('user')->get();

        $data['notifications'] = [];
        $data['plannedNotifications'] = [];
        foreach ($data['casesWithUsers'] as $cases) {
            $cases->notifications = $cases->notifications();
            $cases->planned_notifications = $cases->plannedNotifications();
            array_push($data['notifications'], $cases->notifications());
            array_push($data['plannedNotifications'], $cases->plannedNotifications());

            $cases->user->profile = $cases->user->profile;
        }
        $data['notifications'] = json_encode(Arr::flatten($data['notifications']));
        $data['plannedNotifications'] = json_encode(Arr::flatten($data['plannedNotifications']));

        return view('notifications.show', $data);
    }

    public function queryToSQL($query, $logQuery = true)
    {
        $addSlashes = str_replace('?', "'?'", $query->toSql());

        $sql = str_replace('%', '#', $addSlashes);

        $sql = str_replace('?', '%s', $sql);

        $sql = vsprintf($sql, $query->getBindings());

        $sql = str_replace('#', '%', $sql);

        return $sql;
    }

    /**
     * Store and/or send a notification
     */
    public function store()
    {
    }
}
