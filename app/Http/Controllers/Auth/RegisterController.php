<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Notifications\NotificationFromStaff;
use App\Notifications\UserRegistered;
use App\Role;
use App\User;
use Exception;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;
use Spatie\WebhookServer\WebhookCall;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */
    use RegistersUsers;
    /**
     * Where to redirect users after registration.
     * @var string
     */
    protected $redirectTo = '/';

    /**
     * Create a new controller instance.
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     * @param array $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'email' => ['string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:6', 'confirmed'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     * @param array $data
     * @return User
     * @throws Exception
     */
    protected function create(array $data)
    {

        $userexist = User::where('email', '=', $data['email'])->first();
        if ($userexist) {
            return $this->showRegistrationForm();
        } else {
            $role = Role::where('name', 'researcher')->first();
            $user = new User();
            $user->email = $data['email'];
            $user->password = bcrypt($data['password']);
            $user->password_token = bcrypt(Helper::random_str(60));
            $user->save();
            $user->roles()->sync($role);
            WebhookCall::create()
                ->url('https://chat.zemki.uni-bremen.de/hooks/pggPQhGehrPiRSb2S/3xJ2bPWfYk2pqBBhtGGkgb3Q2JMGvH4DKaPdTANSTdZCtfxk')
                ->payload(['text' => 'User '.$data['email'].' has registered. We have a total of '.User::all()->count().' users!'])
                ->useSecret('pggPQhGehrPiRSb2S/3xJ2bPWfYk2pqBBhtGGkgb3Q2JMGvH4DKaPdTANSTdZCtfxk')
                ->dispatch();

            return $user;
        }
    }

}
