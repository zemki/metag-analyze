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
          if(!$latestCase){
             $latestCase = 'No cases';
             return response()->json(['case' => $latestCase], 200);

         }
         else{
            $inputs['media'] = $latestCase->project->media->pluck('name');
            $inputs['places'] = $latestCase->project->places;
            $inputs['communication_partners'] = $latestCase->project->communication_partners;
            return response()->json(['media' => $inputs['media'],'case' => $latestCase, 'token' => $token], 200);

        }

    } else {
        return response()->json(['error' => 'credentials not valid'], 401);
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
