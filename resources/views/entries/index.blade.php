@extends('layouts.app')

@section('content')

    @if($entries != [])
    <timeline :data="{{ json_encode($entries) }}" :discrete="true" xtitle="time" ytitle="media" :download="true">
    @else
        No entries for this study
    @endif
    </timeline>

@endsection