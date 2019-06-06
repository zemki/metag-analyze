<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\Entry as EntryResource;
use App\Entry;
use App\Cases;
use App\Project;
use App\Media;

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
    public function store(Request $request,Cases $case)
    {

		$this->authorize('update',[Entry::class,$case]);

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
     	$attributes['inputs'] = json_encode($attributes['inputs']);
        Entry::create($attributes);

    	return response('Entry registered', 200);

    }

    /**
     * Test function to consult data with d3js
     */
    public function consult(Cases $case)
    {
        $i = 0;
        //$data['entries'] = $case->entries->map->only(['media_id','begin','end'])->flatten()->chunk(3);
        $data['entries'] = $case->entries()->join('media','entries.media_id','=','media.id')->get()->map->only(['name','begin','end'])
            ->flatten()->chunk(3)->toArray();
        $data['entries'] = array_map('array_values', $data['entries']);



        return view('entries.index',$data);

    }

    public function destroy(Cases $case,Entry $entry)
    {
      //  $entry = Entry::where('id','=',$entryid)->first();

        try {
            $entry->delete();
        } catch (Exception $e) {
            echo 'Caught exception: ',  $e->getMessage(), "\n";
        }


        return response("entry deleted", 200);

    }
}
