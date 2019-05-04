<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\Entry as EntryResource;
use App\Entry;
use App\Cases;
use App\Project;

class EntryController extends Controller
{
    public function entriesByCase(Cases $case)
    {
    	return EntryResource::collection($case->entries->sortByDesc('begin'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request,Project $project,Cases $case)
    {

		//$this->authorize('update',[Entry::class,$case]);

     	$attributes = request()->validate([
            'begin' => 'required',
            'end' => 'required',
            'content' => 'required',
            'case_id' => 'required',
            'place_id' => 'required',
            'media_id' => 'required',
            'communication_partner_id' => 'required',
            'comment' => 'nullable',
            'inputs' => 'nullable',
        ]);

        Entry::create($attributes);

    	return response('Entry registered', 200);

    }
}
