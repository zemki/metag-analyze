@extends('errors::layout')

@section('title', 'Error')


@section('message')
    <div class="w-1/3 p-2">
    <img src="images/undraw_warning_cyit.svg" ì style="max-width: 150px;" alt="Warning!">
    </div>
    {{__('This is the backend, you are not authorized to see this content.')}}
    {{__('You can now login on the mobile app.')}}
    <a class="font-bold no-underline"
       style="background: whitesmoke round;border-radius: 10px;padding: 3px;color: lightskyblue;text-decoration: none;display: block;"
       href="{{ route('logout') }}"
       onclick="event.preventDefault();
     document.getElementById('logout-form').submit();">
        {{ __('Logout') }}
    </a>

    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
        @csrf
    </form>

@endsection
