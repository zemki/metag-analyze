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
use Carbon\Carbon;
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
        $data['useronlinecount'] = User::all()->where('latest_activity','>', Carbon::now()->subMinute(10)->toDateTimeString())->count();
        $data['users'] = User::with('projects', 'case')->get();

        return view('admin.usersdashboard', $data);
    }

    /**
     * @param User $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function deletedeviceid(User $user)
    {
        $user->update(["deviceID" => []]);
        return response()->json(['message' => 'Device ID deleted!'], 200);

    }


    public function indexCases()
    {
        $data['projects'] = Project::with("cases","invited")->orderBy('created_at','DESC')->paginate(15);
        $data['cases'] = Cases::all();


        return view('admin.cases', $data);

    }

    /**
     * @param User $user
     * @return \Illuminate\Http\JsonResponse
     */
    public function resetapitoken(User $user)
    {
        $user->api_token = "";
        $user->save();
        if($user->api_token == NULL)return response()->json(['message' => 'Api Token deleted!'], 200);
        else return response()->json(['message' => 'That didn\'t work, check log!'], 500);


    }

    public function sendEmailVerificationNotification()
    {
        $this->notify(new \App\Notifications\CustomVerifyEmail);
    }

    public function listForNewsletter()
    {

        $users = User::join('users_profiles', 'users.id', '=', 'user_id')->where('newsletter', '=', 2)->get();
        return view('admin.newsletter', ['users' => $users]);
    }

}
