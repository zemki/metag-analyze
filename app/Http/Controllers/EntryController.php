<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Resources\Entry as EntryResource;
use App\Entry;
use App\Cases;

class EntryController extends Controller
{
    public function entriesByCase(Cases $case)
    {
    	return EntryResource::collection($case->entries);
    }
}
