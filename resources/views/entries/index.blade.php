@extends('layouts.app')

@section('content')


    <timeline :data="{{ json_encode($entries) }}" :discrete="true" xtitle="time" ytitle="media" download="true">

    </timeline>

@endsection