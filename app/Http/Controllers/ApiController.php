<?php

namespace App\Http\Controllers;

use App\Cases;
use App\Helpers\Helper;
use App\Project;
use App\Role;
use App\User;
use Exception;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Crypt;

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
     * @return ResponseFactory|Response
     */
    public function returnUser($id)
    {
        if ($id === 0) {
            $user = new User;

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
     *
     * @return JsonResponse
     */
    /**
     * Handles Login Request
     *
     * @return JsonResponse
     */
    public function login(Request $request)
    {
        $credentials = [
            self::EMAIL => $request->email,
            self::PASSWORD => $request->password,
        ];

        if (auth()->attempt($credentials)) {
            $user = auth()->user();

            // Reset any failed login attempts and lockout
            $user->forceFill([
                'failed_login_attempts' => 0,
                'lockout_until' => null,
            ])->save();

            // Generate token with expiration
            $token = Helper::random_str(60, '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ');
            $user->forceFill([
                'api_token' => hash('sha256', $token),
                'token_expires_at' => now()->addDays(config('auth.token_expiration_days', 30)),
            ])->save();

            $userHasACase = $user->latestCase;

            if (! $userHasACase) {
                return response()->json(['case' => 'No cases'], 499);
            }

            if ($userHasACase->isBackend()) {
                return response()->json(['case' => 'No cases'], 499);
            }

            if (! $user->profile()->exists()) {
                $user->addProfile($user);
            }

            User::saveDeviceId($request);
            $lastDayPos = strpos($userHasACase->duration, 'lastDay:');
            $startDay = Helper::get_string_between($userHasACase->duration, 'startDay:', '|');

            $duration = $lastDayPos
                ? substr($userHasACase->duration, $lastDayPos + strlen('lastDay:'), strlen($userHasACase->duration) - 1)
                : Cases::calculateDuration($request->datetime, $userHasACase->duration);

            $userHasACase->duration .= $lastDayPos ? '' : '|lastDay:' . $duration;
            $userHasACase->save();

            $inputs = $this->formatLoginResponse($userHasACase);
            $notStarted = (strtotime(date('d.m.Y')) < strtotime($startDay));

            return response()->json([
                self::INPUTS => $inputs[self::INPUTS],
                'case' => $userHasACase->makeHidden('file_token'),
                self::TOKEN => $token,
                'file_token' => $userHasACase->file_token ? Crypt::decryptString($userHasACase->file_token) : null,
                'duration' => $duration,
                self::CUSTOMINPUTS => $inputs[self::INPUTS][self::CUSTOMINPUTS],
                self::NOTSTARTED => $notStarted,
            ], 200);

        } else {
            // Track failed login attempts
            if ($user = User::where('email', $request->email)->first()) {
                $user->increment('failed_login_attempts');

                if ($user->failed_login_attempts >= config('auth.max_login_attempts', 5)) {
                    $user->lockout_until = now()->addMinutes(30);
                    $user->save();
                }
            }

            return response()->json(['error' => 'invalid credentials'], 401);
        }
    }

    /**
     * @param  $inputs
     * @return mixed
     */
    protected function formatLoginResponse($response)
    {
        $data[self::INPUTS][self::MEDIA] = $response->project->media;
        $nullItem = (object) ['id' => 0, 'name' => ''];
        $data[self::INPUTS][self::MEDIA]->prepend($nullItem);
        $data[self::INPUTS][self::CUSTOMINPUTS] = $response->project->inputs;

        return $data;
    }

    /**
     * @return mixed
     */
    public function register(Request $request)
    {
        abort(403, 'NOT POSSIBLE');
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
     * @return JsonResponse
     */
    public function getProject(Project $project)
    {
        return response()->json(compact($project), 200);
    }

    /**
     * Update the authenticated user's API token.
     *
     * @return array
     *
     * @throws Exception
     */
    public function update(Request $request)
    {
        $token = Helper::random_str(60);
        $request->user()->forceFill([
            'api_token' => hash('sha256', $token),
        ])->save();

        return [self::TOKEN => $token];
    }

    /**
     * @return JsonResponse
     */
    public function getInputs(Project $project)
    {
        $data[self::MEDIA] = $project->media;

        return response()->json($data, 200);
    }
}
