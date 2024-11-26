<?php

namespace App\Http\Controllers\Auth;

/**
 * Class NoGuestForgotPasswordController.
 */
class NoGuestForgotPasswordController extends ForgotPasswordController
{
    /**
     * NoGuestForgotPasswordController constructor.
     */
    public function __construct()
    {
        //Let authenticated users use this controller.
        //        $this->middleware('guest');
    }
}
