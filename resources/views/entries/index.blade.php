@extends('layouts.app')

@section('content')
    <div class="columns">
        <div class="column is-half">
            <nav class="breadcrumb has-succeeds-separator is-small" aria-label="breadcrumbs">
                <ul>
                    <li>Metag</li>
                    <li ><a href="{{url('/')}}">Projects</a></li>
                    <li ><a href="{{url($case->project->path())}}">{{$case->project->name}}</a></li>
                    <li class="is-active" aria-current="page"><a href="#">{{$case->name}}</a></li>
                </ul>
            </nav>
        </div>
    </div>

    <div class="mb-3">
    <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
            @click="selectedEntriesData={{json_encode($entriesByMedia)}}"
    >
        Media
    </button>
    <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
    @click="selectedEntriesData={{json_encode($entriesByPlace)}}"
    >
        Places
    </button>
    <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded"
    @click="selectedEntriesData={{json_encode($entriesByCommunicationPartner)}}"
    >
        Communication Partner
    </button>
    </div>


    @if($entriesByMedia != [])
    <timeline :data="selectedEntriesData" :discrete="true" xtitle="time" ytitle="media" :download="true">
    @else
        No entries for this study
    @endif
    </timeline>

@endsection

@section('pagespecificscripts')

@endsection
