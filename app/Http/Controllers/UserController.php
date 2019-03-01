<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use \App\Http\Requests\StoreUser;
use \App\User;
use \App\Profile;
use Illuminate\Support\Facades\Mail;
use App\Mail\VerificationEmail;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreUser $request)
    {


    $validated = $request->validated();


    $user = New User();
    $user->username = $request->get('username');
    $user->email = $request->get('email');
    $user->password =bcrypt("temp");


    Mail::to($user->email)->send(new VerificationEmail($user));
    $user->save();
    $user->roles()->sync($request->get('roles'));
    return response("An email verification was sent to ".$user->email." please let the user open it");
    /// send an email to validate




    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {

        $updateduser = User::where('id', $id)->first();
        $updateduser->username = $request->get('username');
        $updateduser->email = $request->get('email');
        $updateduser->roles()->sync($request->get('roles'));
        $updateduser->profile->update($request->get('profile'));
        $updateduser->save();


        return $updateduser;
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = User::where('id', $id)->first();
        $user->delete();
        return response("user deleted", 200);
    }
}
