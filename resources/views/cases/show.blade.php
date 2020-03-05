@extends('layouts.app')

@section('content')

    <div class="columns">
        <div class="column is-full">

            <nav class="breadcrumb has-succeeds-separator is-small" aria-label="breadcrumbs">
                <ul>
                    <li><a href="#">Metag</a></li>
                    <li><a href="{{url('/')}}">Projects</a></li>
                    <li><a href="{{url($project->path())}}">{{$project->name}}</a></li>
                    <li class="is-active" aria-current="page"><a href="#">{{$case->name}}</a></li>
                </ul>
            </nav>
        </div>
    </div>






@endsection
@section('pagespecificscripts')
@endsection
