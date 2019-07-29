<?php

namespace App\Http\Controllers;

use App\Role;
use Illuminate\Http\Request;

use App\Http\Requests\StoreUser;
use App\User;
use App\Project;
use App\Profile;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerificationEmail;
use Helper;

class UserController extends Controller
{


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $data['breadcrumb'] = [url('/') => 'Admin', '#' => 'Create User'];
        $data['projects'] = Project::all();
        return view('admin.createUser',$data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {


        request()->validate(
            ['email' => 'required']
        );

        $email = request('email');
        $user = User::firstOrNew(['email' => $email]);
        $project = Project::where('id', '=', request('project'))->firstOrFail();

        if (!$user->exists) {
            $user->username = request('email');
            $user->email = request('email');
            $role = Role::where('id', '=', request('role'))->first();
            $password = Helper::random_str(request('passwordLength'));
            $user->password = bcrypt($password);
            $user->api_token = str_random(60);
            $user->save();
            $user->roles()->sync($role);

        }

        if(request('assignToCase'))
        {
               $case = $project->addCase(request('caseName'),request('duration'));
               $case->addUser($user);

        }

        return redirect()->back()->with('message',isset($password)? $user->email.' can now enter with the password: '.$password : 'User was already registered');




    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit()
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

        $updateUser = User::where('id', $id)->first();
        $updateUser->username = $request->get('username');
        $updateUser->email = $request->get('email');
        $updateUser->roles()->sync($request->get('roles'));
        $updateUser->profile->update($request->get('profile'));
        $updateUser->save();


        return $updateUser;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = User::where('id', $id)->first();
        $user->delete();
        return response("user deleted", 200);
    }

    /**
     * @param Request $request
     * @todo consider adding filter by role, same email AND role user.
     * @return \Illuminate\Http\JsonResponse
     */
    public function userExists(Request $request)
    {
        return response()->json(User::where('email', '=', $request['email'])->first() ? true : false, 200);
    }

}
