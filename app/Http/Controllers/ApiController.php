<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Role;
use App\Project;
use Auth;
use DB;
use Helper;

class ApiController extends Controller
{
    /**
     * @param $id
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function returnUser($id) {


        if($id === 0){
            $user = new User();
            return response($user, 200);

        }

        $user = User::where('id',$id)->with('profile')->first();

        $user['roles'] = $user->roles()->pluck('roles.name','roles.id')->toArray();
        $user['profile'] = $user->profile()->first();

        return response($user->jsonSerialize(), 200);
    }

    /**
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
    public function returnAllUsers(){
       return response(User::all()->jsonSerialize(), 200);
   }

    /**
     * @return \Illuminate\Contracts\Routing\ResponseFactory|\Illuminate\Http\Response
     */
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

        $token = Helper::random_str(60);

        auth()->user()->forceFill([
            'api_token' => hash('sha256', $token),
        ])->save();

          $userHasACase = auth()->user()->latestCase;

          if(!$userHasACase)
          {
              $response = 'No cases';
              return response()->json(['case' => $response], 200);
          }else{

              $lastDayPos = strpos($userHasACase->duration,"lastDay:");

              if($lastDayPos !== false){
                  $important=substr($userHasACase->duration, $lastDayPos+strlen('lastDay:'), strlen($userHasACase->duration)-1);

                  $duration = $important;
              }else{
                  $duration = $this->calculateDuration($request->datetime,$userHasACase->duration);
                  $userHasACase->duration = $userHasACase->duration."|lastDay:".$duration;
                  $userHasACase->save();
              }


              $inputs = $this->formatLoginResponse($userHasACase);
              return response()->json([
                'inputs' => $inputs['inputs'],
                'case' => $userHasACase,
                'token' => $token,
                'duration' => $duration,
                'custominputs' => $inputs['inputs']['custominputs']
                ], 200);

        }
    } else {
        return response()->json(['error' => 'invalid credentials'], 401);
    }
}


    /**
     * @param Request $request
     * @return mixed
     */
    public function register(Request $request)
{
    abort(403,"NOT POSSIBLE");
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

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
{
    auth()->user()->tokens->each(function ($token, $key) {
        $token->delete();
    });
    return response()->json('Logged out successfully', 200);
}

    /**
     * @param Project $project
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProject(Project $project)
{
    return response()->json(compact($project),200);
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

    /**
     * @param Project $project
     * @return \Illuminate\Http\JsonResponse
     */
    public function getInputs(Project $project)
{

            $data['media'] = $project->media;

            return response()->json($data, 200);

}

    /**
     * @param $response
     * @param $inputs
     * @return mixed
     */
    protected function formatLoginResponse($response)
    {
        $data['inputs']['media'] = $response->project->media;
        $nullItem = (object)array('id' => 0, 'name' => '');
        $data['inputs']['media']->prepend($nullItem);
        $data['inputs']['custominputs'] = $response->project->inputs;
        return $data;
    }

    /**
     * @param $datetime
     * @param $caseDuration
     * @return false|string
     */
    protected function calculateDuration(int $datetime, $caseDuration)
    {

        $sub = substr($caseDuration, strpos($caseDuration,":")+strlen(":"),strlen($caseDuration));
        $realDuration = (int)substr($sub,0,strpos($sub,"|"));
        $finalDuration = date( "d.m.Y",  $datetime  + $realDuration * 3600 );

        return $finalDuration;
    }


}
