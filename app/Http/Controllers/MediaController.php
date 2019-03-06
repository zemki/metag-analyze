<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Media;
use App\Media_group;

class MediaController extends Controller
{

    public function index()
    {
        $media = Media::all();

        return view('media.index',compact('media'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(Project $media)
    {
    	// @TODO
    	// insert role filter for not admin

        return view('media.show',compact('media'));
    }

    /**
     * Show Create form
     * @return View return view with the form to insert a new media
     */
    public function create()
    {
    	$media_groups = Media_group::all();
        return view('media.create',compact('media_groups'));
    }


    public function store(Request $request){

    	$attributes = request()->validate([
            'name' => 'required',
            'properties' => 'required',
            'media_group_id' => 'required',
            'description' => 'nullable',
        ]);

        Media::create($attributes);

        return redirect('/media');

    }}
