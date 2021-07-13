@extends('layouts.app')

@section('content')
    <h1 class="break-words text-4xl font-bold font-serif">{{$project->name}}</h1>

    <p class="break-words my-4">
        {{$project->description}}
    </p>

    <notification-center :cases="{{$casesWithUsers}}" :notifications="{{$notifications}}" :plannednotifications="{{$plannedNotifications}}"></notification-center>





@endsection
