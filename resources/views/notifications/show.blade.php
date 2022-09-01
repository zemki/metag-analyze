@extends('layouts.app')

@section('content')
@include('layouts.breadcrumb')
<h1 class="font-serif text-4xl font-bold break-words">{{$project->name}}</h1>

<p class="my-4 break-words">
    {{$project->description}}
</p>
<notification-center :cases="{{$casesWithUsers}}" :notifications="{{$notifications}}"
    :plannednotifications="{{$plannedNotifications}}" :admin="{{auth()->user()->isAdmin() ? "1" : "0"}}">
</notification-center>
@endsection