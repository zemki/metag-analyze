@extends('admin.layout')

@section('content')
    <div class="rounded-full h-3 w-3 bg-green-300 text-green-200 inline-block mr-2 blink_me ">    </div>
        {{$useronlinecount}} user(s) online.

    <div class="w-full">
        <user-table :users="{{$users}}"></user-table>
    </div>
@endsection
