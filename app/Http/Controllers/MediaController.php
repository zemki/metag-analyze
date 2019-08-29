<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Media;
use App\Media_group;

class MediaController extends Controller
{
    public function store(){

    	$attributes = request()->validate([
            'name' => 'required',
            'properties' => 'required',
            'description' => 'nullable',
        ]);

        Media::create($attributes);

        return redirect('/media');

    }}
