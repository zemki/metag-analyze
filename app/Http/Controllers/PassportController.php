<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;

class PassportController extends Controller
{
    /**
     * Handles Registration Request
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $this->validate($request, [
            'username' => 'required|min:3',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6',
        ]);

        $user = User::create([
            'username' => $request->username,
            'email' => $request->email,
            'password' => bcrypt($request->password)
        ]);

        $token = $user->createToken('metag')->accessToken;

        return response()->json(['token' => $token], 200);
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
              logger("---------------------------");
              logger($credentials);
              logger("---------------------------");
            $token = auth()->user()->createToken('metag')->accessToken;

            $latestCase = auth()->user()->latestCase;
            if(!$latestCase) $latestCase = 'No cases';
            else{
                $inputs['media'] = $case->project->media;
                $inputs['places'] = $case->project->places;
                $inputs['communication_partners'] = $case->project->communication_partners;
            }

            return response()->json(['case' => $latestCase, 'token' => $token], 200);
        } else {
            return response()->json(['error' => 'Allorasolote'], 401);
        }
    }

    /**
     * Returns Authenticated User Details
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function details()
    {
        return response()->json(['user' => auth()->user()], 200);
    }
}
