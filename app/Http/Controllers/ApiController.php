<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\User;
use App\Role;
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

   public function login(Request $request)
   {

    // get client id and password of the user or return an error

    $client = DB::table('oauth_clients')
                     ->select(DB::raw('id, secret'))
                     ->where('user_id', '=', $request->id)
                     ->first();


    // return error if $client has error or client_id is empty
    $http = new \GuzzleHttp\Client;
    try {

        $response = $http->post(config('services.passport.login_endpoint'), [
            'form_params' => [
                'grant_type' => 'password',
                'client_id' => $client->id,
                'client_secret' => $client->secret,
                'username' => $request->username,
                'password' => $request->password,
            ]
        ]);

        return $response->getBody();
    } catch (\GuzzleHttp\Exception\BadResponseException $e) {
        if ($e->getCode() === 400) {
            return response()->json('Invalid Request. Please enter a username or a password.', $e->getCode());
        } else if ($e->getCode() === 401) {
            return response()->json('Your credentials are incorrect. Please try again', $e->getCode());
        }
        return response()->json('Something went wrong on the server.', $e->getCode());
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


}
