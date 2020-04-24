<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Project;
use App\User;
use Illuminate\Foundation\Auth\SendsPasswordResetEmails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

class ForgotPasswordController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Password Reset Controller
    |--------------------------------------------------------------------------
    |
    | This controller is responsible for handling password reset emails and
    | includes a trait which assists in sending these notifications from
    | your application to your users. Feel free to explore this trait.
    |
    */
    use SendsPasswordResetEmails;

    /**
     * Create a new controller instance.
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest')->except('SendsPasswordResetEmailFromCasesList');;
    }

    /**
     * Send a reset link to the given user.
     * @param Request $request
     * @return string
     */
    public function SendsPasswordResetEmailFromCasesList(Request $request)
    {

        $canAuthUserResetPassword = $this->canAuthUserResetPassword($request->email);
        if (!$canAuthUserResetPassword) return "You don't have the permissions to send an email to this user.";
        $response = $this->broker()->sendResetLink(
            $this->credentials($request)
        );
        $this->sendResetLinkResponse($request, Password::RESET_LINK_SENT);
        return "email successfully sent";
    }

    /**
     * Check if you are in contact with that user.
     * @param $email
     * @return bool
     */
    private function canAuthUserResetPassword($email): bool
    {
        return in_array(User::where('email', '=', $email)->first()->id, Project::where('created_by', auth()->user()->id)->join('cases', 'cases.project_id', '=', 'projects.id')->pluck('user_id')->unique()->toArray());
    }
}
