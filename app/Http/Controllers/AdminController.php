<?php

namespace App\Http\Controllers;

use App\Action;
use App\Cases;
use App\Entry;
use App\Files;
use App\Interview;
use App\Permission;
use App\Project;
use App\Role;
use App\Study;
use App\User;
use Illuminate\Http\Request;
use Storage;

class AdminController extends Controller
{
    /**
     * show dashboard.
     * @return [type] [description]
     */
    public function index()
    {

        // gather data for the initial panel
        $data['user'] = auth()->user();
        $data['usercount'] = User::all()->count();
        $data['projectscount'] = Project::all()->count();
        $data['casescount'] = Cases::all()->count();
        $data['actions'] = Action::with('user')->orderBy('id', 'desc')->paginate(15);
        $data['actionscount'] = Action::all()->count();
        $data['entriescount'] = Entry::all()->count();

        return view('admin.dashboard', $data);
    }

    /**
     * show user dashboard.
     * @return [type] [description]
     */
    public function indexUsers()
    {

        // gather data for the initial panel
        $data['user'] = auth()->user();
        $data['usercount'] = User::all()->count();
        $data['users'] = User::with('projects', 'case')->get();

        return view('admin.usersdashboard', $data);
    }


    public function deletedeviceid(User $user)
    {
        $user->update(["deviceID" => ""]);
        return response()->json(['message' => 'Device ID Deleted!'], 200);

    }



    public function addSupervisor(Request $request)
    {
        $role = Role::where('id', $request->role)->first();
        $user = new User();
        $user->email = $request->email;
        $user->password = bcrypt($request->password);
        $user->save();
        $user->attachRole($role);
        $createStudyPermission = Permission::where('name', 'create-studies')
            ->first()->toArray();
        $user->supervised_by = $user->id;
        $user->save();
        $user->attachPermissions($createStudyPermission);
        return view('admin.supervisor', ['message' => 'Supervisor added']);
    }
}
