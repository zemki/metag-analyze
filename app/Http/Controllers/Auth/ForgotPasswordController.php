<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
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

        $canAuthUserResetPassword = $this->canAuthUserResetPassword();
        if (!$canAuthUserResetPassword) return "You don't have the permissions to send an email to this user.";

        $response = $this->broker()->sendResetLink(
            $this->credentials($request)
        );
        $this->sendResetLinkResponse($request, Password::RESET_LINK_SENT);
        return "email successfully sent";
    }

    /**
     * @return bool
     */
    private function canAuthUserResetPassword(): bool
    {
        $AllProjects = auth()->user()->projects()->with('cases')->get();
        foreach ($AllProjects as $project)
        {
            foreach ($project->cases as $case)
            {
                if (User::where('id', '=', $case->user_id)->first())
                {
                    $userfound = true;
                    break;
                }
            }
        }
        return $userfound;
    }
}
