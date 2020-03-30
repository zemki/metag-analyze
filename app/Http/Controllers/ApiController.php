<?php

namespace App\Http\Controllers;

use App\Cases;
use App\Project;
use App\Role;
use App\User;
use Auth;
use DB;
use Helper;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class ApiController extends Controller
{
    const EMAIL = 'email';
    const PASSWORD = 'password';
    const INPUTS = 'inputs';
    const TOKEN = 'token';
    const CUSTOMINPUTS = 'custominputs';
    const NOTSTARTED = 'notstarted';
    const MEDIA = 'media';

    /**
     * @param $id
     * @return ResponseFactory|Response
     */
    public function returnUser($id)
    {
        if ($id === 0) {
            $user = new User();
            return response($user, 200);
        }
        $user = User::where('id', $id)->with('profile')->first();
        $user['roles'] = $user->roles()->pluck('roles.name', 'roles.id')->toArray();
        $user['profile'] = $user->profile()->first();
        return response($user->jsonSerialize(), 200);
    }

    /**
     * @return ResponseFactory|Response
     */
    public function returnAllUsers()
    {
        return response(User::all()->jsonSerialize(), 200);
    }

    /**
     * @return ResponseFactory|Response
     */
    public function returnAllRoles()
    {
        return response(Role::all()->jsonSerialize(), 200);
    }

    /**
     * Handles Login Request
     * @param Request $request
     * @return JsonResponse
     */
    public function login(Request $request)
    {
        $credentials = [
            self::EMAIL => $request->email,
            self::PASSWORD => $request->password
        ];
        if (auth()->attempt($credentials)) {
            $token = Helper::random_str(60);
            auth()->user()->forceFill([
                'api_token' => hash('sha256', $token),
            ])->save();
            $userHasACase = auth()->user()->latestCase;
            if (!$userHasACase) {
                $response = 'No cases';
                return response()->json(['case' => $response], 200);
            } else {

                //User::saveDeviceId($request);
                $lastDayPos = strpos($userHasACase->duration, "lastDay:");
                $startDay = Helper::get_string_between($userHasACase->duration, "startDay:", "|");
                $duration = $lastDayPos ? substr($userHasACase->duration, $lastDayPos + strlen('lastDay:'), strlen($userHasACase->duration) - 1) : Cases::calculateDuration($request->datetime, $userHasACase->duration);
                $userHasACase->duration .= $lastDayPos ? "" :  "|lastDay:" . $duration;
                $userHasACase->save();
                $inputs = $this->formatLoginResponse($userHasACase);
                $notStarted = (strtotime(date("d.m.Y")) < strtotime($startDay));
                return response()->json([
                    self::INPUTS => $inputs[self::INPUTS],
                    'case' => $userHasACase,
                    self::TOKEN => $token,
                    'duration' => $duration,
                    self::CUSTOMINPUTS => $inputs[self::INPUTS][self::CUSTOMINPUTS],
                    self::NOTSTARTED => $notStarted
                ], 200);
            }
        } else {
            return response()->json(['error' => 'invalid credentials'], 401);
        }
    }

    /**
     * @param $response
     * @param $inputs
     * @return mixed
     */
    protected function formatLoginResponse($response)
    {
        $data[self::INPUTS][self::MEDIA] = $response->project->media;
        $nullItem = (object)array('id' => 0, 'name' => '');
        $data[self::INPUTS][self::MEDIA]->prepend($nullItem);
        $data[self::INPUTS][self::CUSTOMINPUTS] = $response->project->inputs;
        return $data;
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function register(Request $request)
    {
        abort(403, "NOT POSSIBLE");
        $request->validate([
            'name' => 'required|string|max:255',
            self::EMAIL => 'required|string|email|max:255|unique:users',
            self::PASSWORD => 'required|string|min:6',
        ]);
        return User::create([
            'name' => $request->name,
            self::EMAIL => $request->email,
            self::PASSWORD => Hash::make($request->password),
        ]);
    }

    /**
     * @return JsonResponse
     */
    public function logout()
    {
        auth()->user()->tokens->each(function ($token) {
            $token->delete();
        });
        return response()->json('Logged out successfully', 200);
    }

    /**
     * @param Project $project
     * @return JsonResponse
     */
    public function getProject(Project $project)
    {
        return response()->json(compact($project), 200);
    }

    /**
     * Update the authenticated user's API token.
     * @param Request $request
     * @return array
     */
    public function update(Request $request)
    {
        $token = Str::random(60);
        $request->user()->forceFill([
            'api_token' => hash('sha256', $token),
        ])->save();
        return [self::TOKEN => $token];
    }

    /**
     * @param Project $project
     * @return JsonResponse
     */
    public function getInputs(Project $project)
    {

        $data[self::MEDIA] = $project->media;
        return response()->json($data, 200);
    }
}
