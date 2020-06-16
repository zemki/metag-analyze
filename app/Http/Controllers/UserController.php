<?php

namespace App\Http\Controllers;

use App\Mail\VerificationEmail;
use App\Project;
use App\Role;
use App\User;
use Helper;
use Illuminate\Contracts\View\Factory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Redirector;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;
use mysql_xdevapi\Exception;

class UserController extends Controller
{
    protected const EMAIL = 'email';
    protected const TOKEN = 'token';
    protected const ERRORS_RESETPASSWORD = 'errors.resetpassword';

    /**
     * Display a listing of the resource.
     * @return Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     * @return Response
     */
    public function create()
    {
        $data['breadcrumb'] = [url('/') => 'Admin', '#' => 'Create User'];
        $data['projects'] = Project::all();
        return view('admin.createUser', $data);
    }

    /**
     * Store a newly created resource in storage.
     * @param Request $request
     * @return Response
     */
    public function store(Request $request)
    {


        request()->validate(
            [self::EMAIL => 'required']
        );
        $email = request(self::EMAIL);
        $user = User::firstOrNew([self::EMAIL => $email]);
        if (!$user->exists) {
            $user->email = request(self::EMAIL);
            $role = Role::where('id', '=', request('role'))->first();
            $user->password = bcrypt(Helper::random_str(60));
            $user->password_token = bcrypt(Helper::random_str(60));
            $user->api_token = Helper::random_str(60);
            $user->save();
            $user->roles()->sync($role);
            Mail::to($user->email)->send(new VerificationEmail($user, $request->emailtext ? $request->emailtext : config('utilities.emailDefaultText')));
        }
        if (request('assignToCase')) {
            $project = Project::where('id', '=', request('project'))->firstOrFail();
            $case = $project->addCase(request('caseName'), request('duration'));
            $case->addUser($user);
        }
        if (!$user->exists) {
            return redirect()->back()->with('message', $user->email . ' will receive an email to set the password.');
        } else {
            return redirect()->back()->with('message', $user->email . ' was invited to the case.');
        }
    }

    /**
     * Display the specified resource.
     * @param int $id
     * @return Response
     */
    public function show()
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     * @param int $id
     * @return Response
     */
    public function edit()
    {
        //
    }

    /**
     * Update the specified resource in storage.
     * @param Request $request
     * @param int     $id
     * @return Response
     */
    public function update(Request $request, $id)
    {

        $updateUser = User::where('id', $id)->first();
        $updateUser->email = $request->get(self::EMAIL);
        $updateUser->roles()->sync($request->get('roles'));
        $updateUser->profile->update($request->get('profile'));
        $updateUser->save();
        return $updateUser;
    }

    /**
     * Remove the specified resource from storage.
     * @param int $id
     * @return Response
     */
    public function destroy($id)
    {
        $user = User::where('id', $id)->first();
        $user->delete();
        return response("user deleted", 200);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     * @todo consider adding filter by role, same email AND role user.
     */
    public function userExists(Request $request)
    {
        return response()->json(!empty(User::where(self::EMAIL, '=', $request[self::EMAIL])->first()), 200);
    }

    /**
     *
     */
    public function sendEmailVerificationNotification()
    {
        $this->notify(new \App\Notifications\CustomVerifyEmail);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function addToNewsletter(Request $request)
    {
        $subscribe = $request->input('subscribed') ? config('enums.newsletter_status.SUBSCRIBED') : config('enums.newsletter_status.NOT SUBSCRIBED');
        try
        {
            if(auth()->user()->profile()->exists())
            {
                auth()->user()->profile->newsletter = $subscribe;
                auth()->user()->profile->save();
            }else{
                $profile = auth()->user()->addProfile(auth()->user());
                $profile->newsletter = $subscribe;
                $profile->save();
            }
            return response()->json(['message' => 'Your preference was saved!','r' => $subscribe], 200);

        }catch (Exception $exception)
        {
            return response()->json(['message' => 'A problem occurred, contact the administrator.'], 500);

        }



    }
}
