<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Notifications\EmailChangeNotification;
use App\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Validator;
use Throwable;

class EmailChangeController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Email Change Controller
    |--------------------------------------------------------------------------
    |
    | This controller allows the user to change his email address after he
    | verifies it through a message delivered to the enew email address.
    | This uses a temporarily signed url to validate the email change.
    |
    */

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        // Only the authenticated user can change its email, but he should be able
        // to verify his email address using other device without having to be
        // authenticated. This happens a lot when they confirm by phone.
        $this->middleware('auth')->only('change');

        // A signed URL will prevent anyone except the User to change his email.
        $this->middleware('signed')->only('verify');
    }

    public function show()
    {
        return view('auth.emails.changeemail');
    }

    /**
     * Changes the user Email Address for a new one
     *
     * @return RedirectResponse
     */
    public function change(Request $request)
    {
        $request->validate([
            'email' => 'required|email|unique:users',
        ]);

        // Send the email to the user
        try {
            Notification::route('mail', $request->email)
                ->notify(new EmailChangeNotification(Auth::user()->id));
        } catch (Throwable $e) {
            Log::error('Failed to send the email-change confirmation', [
                'user_id' => Auth::user()->id,
                'new_email' => $request->email,
                'error' => $e->getMessage(),
            ]);

            // Nothing has changed yet - the address only changes once the
            // signed link is followed - so this is a plain failure and the
            // user should retry rather than wait for a mail that never comes.
            return response(
                __('The confirmation email could not be sent. Please try again later or contact an administrator.'),
                500
            );
        }

        return response(__('An email has been sent, please check your inbox. Link is valid for 60 minutes'), 200);
    }

    /**
     * Verifies and completes the Email change
     *
     * @param  string  $email
     * @return RedirectResponse|Response
     */
    public function verify(Request $request)
    {
        // `user` and `email` arrive as query parameters, because the
        // verifyNewEmail route has no {user} segment. They are looked up
        // explicitly here.
        //
        // Tampering is not a concern: the 'signed' middleware verifies a
        // signature computed over the whole query string, so neither `user`
        // nor `email` can have been altered after the link was issued.
        $validator = Validator::make($request->query(), [
            'user' => 'required|integer|exists:users,id',
            'email' => 'required|email|unique:users,email',
        ]);

        if ($validator->fails()) {
            // Most likely the address was claimed by someone else between the
            // request and the click, or the account no longer exists. Either
            // way the change cannot be applied, and saying so beats showing a
            // success page.
            return response()->view('auth.emails.change-failed', [
                'reason' => $validator->errors()->first(),
            ], 422);
        }

        $user = User::findOrFail($request->query('user'));
        $oldEmail = $user->email;
        $newEmail = $request->query('email');

        $user->update(['email' => $newEmail]);

        // Logged with the new address; this used to read the empty model's
        // email and so always recorded an empty destination.
        $user->addAction('Email change from ' . $oldEmail . ' to ' . $newEmail, 'from email link');

        return response()->view('auth.emails.change-complete');
    }
}
