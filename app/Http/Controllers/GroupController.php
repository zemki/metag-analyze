<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Group;

class GroupController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        $data['breadcrumb'] = [url('/') => 'Groups', '#' => 'list'];
        $data['groups'] = auth()->user()->groups()->get();

        return view('groups.index',$data);

    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        $data['breadcrumb'] = [url('/') => 'Groups', '#' => 'create'];

        return view('groups.create',$data);

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param Request $request
     * @return void
     */
    public function store(Request $request)
    {
        $data['breadcrumb'] = [url('/') => 'Group', '#' => 'create'];

        $attributes = request()->validate([
            'name' => 'required'
        ]);

        $attributes['is_active'] = 1;
        $attributes['owner_id'] = auth()->user()->id;

        $group = Group::create($attributes);

        auth()->user()->groups()->sync([$group->id]);
        $request->session()->flash('message','Group '.$request->input('name').' created.');

        return redirect(url('/home'));

    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param Request $request
     * @param  int  $id
     * @return Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }
}
