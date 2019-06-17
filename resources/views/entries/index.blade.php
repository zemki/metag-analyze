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
    @if($entriesByMedia != [])
    <timeline :data="{{ json_encode($entriesByMedia) }}" :discrete="true" xtitle="time" ytitle="media" :download="true">
    @else
        No entries for this study
    @endif
    </timeline>

@endsection