<?php

namespace App\Http\Controllers;

use App\Role;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

use App\Http\Requests\StoreUser;
use App\User;
use App\Project;
use App\Profile;
use Illuminate\Http\Response;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerificationEmail;
use Helper;
use Illuminate\View\View;

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
        return view('admin.createUser', $data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {


        request()->validate(
            ['email' => 'required']
        );

        $email = request('email');
        $user = User::firstOrNew(['email' => $email]);


        if (!$user->exists) {
            $user->email = request('email');
            $role = Role::where('id', '=', request('role'))->first();
            $user->password = bcrypt(Helper::random_str(60));
            $user->password_token = bcrypt(Helper::random_str(60));
            $user->api_token = str_random(60);
            $user->save();
            $user->roles()->sync($role);

            Mail::to($user->email)->send(new VerificationEmail($user, $request->emailtext ? $request->emailtext : config('utilities.emailDefaultText')));

        }

        if (request('assignToCase')) {
            $project = Project::where('id', '=', request('project'))->firstOrFail();
            $case = $project->addCase(request('caseName'), request('duration'));
            $case->addUser($user);

        }


        return redirect()->back()->with('message', isset($password) ? $user->email . ' can now enter with the password: ' . $password : 'User was already registered');


    }

    /**
     * Display the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function edit()
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param \Illuminate\Http\Request $request
     * @param int $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

        $updateUser = User::where('id', $id)->first();
        $updateUser->email = $request->get('email');
        $updateUser->roles()->sync($request->get('roles'));
        $updateUser->profile->update($request->get('profile'));
        $updateUser->save();


        return $updateUser;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param int $id
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
     * @return \Illuminate\Http\JsonResponse
     * @todo consider adding filter by role, same email AND role user.
     */
    public function userExists(Request $request)
    {
        return response()->json(User::where('email', '=', $request['email'])->first() ? true : false, 200);
    }

    public function showresetpassword(Request $request)
    {

        if ($request->input('token') == "") {
            return view('errors.resetpassword');
        }

        $user = User::where('password_token', '=', $request->input('token'))->first();

        if (!$user) {
            return view('errors.resetpassword');
        }

        $data['user'] = $user;

        return view('auth.passwords.reset', $data);
    }

    /**
     * @param Request $request
     * @return Factory|RedirectResponse|Redirector|View
     */
    public function newpassword(Request $request)
    {
        if ($request->input('token') === "") {
            $data['error'] = "wrong request, contact the administrator.";
            $data['user'] = "";
            return view('errors.resetpassword');
        }
        $user = User::where('password_token', '=', $request->input('token'))->first();
        if (!$user) {
            $data['error'] = "Something went wrong, please contact the administrator.";

            return view('errors.resetpassword');
        }

        $user->password_token = null;
        $user->password = bcrypt($request->input('password'));
        $user->save();

        return redirect('/');
    }

}
