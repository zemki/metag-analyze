@extends('layouts.app')

@section('content')
@include('layouts.breadcrumb')

<div class="my-2">
    <h1 class="text-4xl font-extrabold text-gray-900 sm:text-5xl sm:tracking-tight lg:text-6xl">
        {{$project->name}}
    </h1>
    <p class="mt-5 text-xl text-gray-500">{{$project->description}}</p>
</div>

<notification-center :cases="{{$casesWithUsers}}" :notifications="{{$notifications}}"
    :plannednotifications="{{$plannedNotifications}}" :admin="{{auth()->user()->isAdmin() ? "1" : "0"}}">
</notification-center>
@endsection