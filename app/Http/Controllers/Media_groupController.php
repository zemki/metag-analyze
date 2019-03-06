<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Media_group;
use App\Media;

class Media_groupController extends Controller
{

    public function index()
    {
        $media_group = Media_group::all();

        return view('media_groups.index',compact('media_group'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Media_group $media_group)
    {
    	// @TODO
    	// insert role filter for not admin

        return view('media_groups.show',compact('media_group'));
    }

    /**
     * Show Create form
     * @return View return view with the form to insert a new media group
     */
    public function create()
    {
        return view('media_groups.create');
    }


    public function store(Request $request){

    	$attributes = request()->validate([
            'name' => 'required',
            'description' => 'nullable',
        ]);


        Media_group::create($attributes);

        return redirect('/media_groups');

    }
}
