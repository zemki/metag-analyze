<?php

namespace App\Http\Controllers;

use App\Notifications\researcherNotificationToUser;
use App\Notifications\VerificationEmail;
use App\Project;
use App\User;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use mysql_xdevapi\Exception;

class UserController extends Controller
{
    protected const EMAIL = 'email';

    protected const TOKEN = 'token';

    protected const ERRORS_RESETPASSWORD = 'errors.resetpassword';

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $data['breadcrumb'] = [url('/') => 'Admin', '#' => 'Create User'];
        $data['projects'] = Project::all();

        return view('admin.createUser', $data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit()
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        $updateUser = User::where('id', $id)->first();
        $updateUser->email = $request->get(self::EMAIL);
        $updateUser->roles()->sync($request->get('roles'));
        $updateUser->profile->update($request->get('profile'));
        $updateUser->save();

        return $updateUser;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        $user = User::where('id', $id)->first();
        $user->delete();

        return response('user deleted', 200);
    }

    /**
     * @return JsonResponse
     */
    public function userExists(Request $request)
    {
        return response()->json(! empty(User::where(self::EMAIL, '=', $request[self::EMAIL])->first()), 200);
    }

    public function sendEmailVerificationNotification()
    {
        $this->notify(new VerificationEmail);
    }

    /**
     * @return JsonResponse
     */
    public function addToNewsletter(Request $request)
    {
        $subscribe = $request->input('subscribed') ? config('enums.newsletter_status.SUBSCRIBED') : config('enums.newsletter_status.NOT SUBSCRIBED');
        try {
            if (auth()->user()->profile()->exists()) {
                auth()->user()->profile->newsletter = $subscribe;
                auth()->user()->profile->save();
            } else {
                $profile = auth()->user()->addProfile(auth()->user());
                $profile->newsletter = $subscribe;
                $profile->save();
            }

            return response()->json(['message' => 'Your preference was saved!', 'r' => $subscribe], 200);
        } catch (Exception $exception) {
            return response()->json(['message' => 'A problem occurred, contact the administrator.'], 500);
        }
    }

    public function notifyDevice(Request $request)
    {
        $user = User::where('id', $request->input('user')['id'])->first();
        $user->profile->last_notification_at = date('Y-m-d H:i:s');
        $user->profile->save();

        $user->notify((new researcherNotificationToUser(['title' => $request->input('title'), 'message' => $request->input('message'), 'case' => $request->input('cases')])));

        return response()->json(['message' => 'Notification Sent!', 'notified' => date('Y-m-d H:i:s')], 200);
    }

    public function planNotification(Request $request): JsonResponse
    {
        $user = User::where('id', $request->input('user')['id'])->first();
        //   $user->profile->last_notification_at = date("Y-m-d H:i:s");
        $user->profile->save();
        $user->notify(new researcherNotificationToUser(['title' => $request->input('title'), 'message' => $request->input('message'), 'case' => $request->input('cases'), 'planning' => $request->input('planning')]));
        $notification = DB::table('notifications')->latest('created_at')->first();

        return response()->json(['message' => 'Notification Planned!', 'notification' => $notification], 200);
    }

    public function deletePlannedNotification(Request $request)
    {
        DB::table('notifications')->where('id', '=', $request->input('notification')['id'])->delete();

        return response()->json(['message' => 'Notification Deleted!'], 200);
    }

    public function cleanupNotifications(Request $request)
    {
        if (! auth()->user()->isAdmin()) {
            return response()->json(['message' => 'Unauthorized access.'], 403);
        } else {
            $user = User::where('id', $request->input('user')['id'])->first();
            $user->profile->last_notification_at = null;
            $user->profile->save();
            $notification = DB::table('notifications')->where('notifiable_id', $request->input('user')['id'])->where('data->case', $request->input('cases')['id'])->where('data->planned', false)->latest('created_at')->limit(1)->delete();

            return response()->json(['message' => 'Deleted last notification!'], 200);
        }
    }
}
