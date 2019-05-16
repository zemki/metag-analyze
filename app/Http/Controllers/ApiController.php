<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Role;
use App\Project;
use Auth;
use DB;

class ApiController extends Controller
{
    public function returnUser($id) {


        if($id == 0){
            $user = new User;
            return response($user, 200);

        }

        $user = User::where('id',$id)->with('profile')->first();

        $user['roles'] = $user->roles()->pluck('roles.name','roles.id')->toArray();
        $user['profile'] = $user->profile()->first();

        return response($user->jsonSerialize(), 200);
    }
    public function returnAllUsers(){
       return response(User::all()->jsonSerialize(), 200);
   }

   public function returnAllRoles(){
       return response(Role::all()->jsonSerialize(), 200);
   }

    /**
     * Handles Login Request
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {


        $credentials = [
            'email' => $request->email,
            'password' => $request->password
        ];

        if (auth()->attempt($credentials)) {

        $token = str_random(60);

        auth()->user()->forceFill([
            'api_token' => hash('sha256', $token),
        ])->save();

          $response = auth()->user()->latestCase;
          if(!$response){
             $response = 'No cases';
             return response()->json(['case' => $response], 200);

         }
         else{
            $inputs['inputs']['media'] = $response->project->media;
            $nullItem = (object)array('id' => 0,'name' => '');
            $inputs['inputs']['media']->prepend($nullItem);
            $inputs['inputs']['places'] = $response->project->places;
            $inputs['inputs']['places']->prepend($nullItem);
            $inputs['inputs']['communication_partners'] = $response->project->communication_partners;
            $inputs['inputs']['communication_partners']->prepend($nullItem);
            $inputs['inputs']['custominputs'] = $response->project->inputs;

            return response()->json([
                'inputs' => $inputs['inputs'],
                'case' => $response,
                'token' => $token,
                'custominputs' => $inputs['inputs']['custominputs']
                ], 200);

        }

    } else {
        return response()->json(['error' => 'invalid credentials'], 401);
    }
}




public function register(Request $request)
{
    dd("NOT POSSIBLE");
    $request->validate([
        'name' => 'required|string|max:255',
        'email' => 'required|string|email|max:255|unique:users',
        'password' => 'required|string|min:6',
    ]);
    return User::create([
        'name' => $request->name,
        'email' => $request->email,
        'password' => Hash::make($request->password),
    ]);
}
public function logout()
{
    auth()->user()->tokens->each(function ($token, $key) {
        $token->delete();
    });
    return response()->json('Logged out successfully', 200);
}

public function a(Project $p)
{
    return response()->json(compact($p),200);
}

    /**
     * Update the authenticated user's API token.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function update(Request $request)
    {
        $token = Str::random(60);

        $request->user()->forceFill([
            'api_token' => hash('sha256', $token),
        ])->save();

        return ['token' => $token];
    }

public function getInputs(Project $project)
{

            $data['media'] = $project->media;
            $data['places'] = $project->places;
            $data['communication_partners'] = $project->communication_partners;

            return response()->json($data, 200);

}


}
