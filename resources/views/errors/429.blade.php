@extends('errors::minimal')

@section('title', 'Error')


@section('message')
<div class="w-1/3 p-2">
    <img src="images/undraw_warning_cyit.svg" style="max-width: 250px;" alt="Warning!">
</div>


{{__('Too many requests. Try again later.')}}

@endsection