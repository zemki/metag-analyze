@extends('auth.layouts.app')

@section('content')
    <div class="flex items-center justify-center h-screen">
        <div class="bg-white p-4 rounded overflow-hidden shadow-lg  w-1/3">
            @if(session()->has('message') || !empty($message))
                <div class="bg-blue-100 border-t border-b border-blue-500 text-blue-700 px-4 py-3" role="alert">
                    <p class="font-bold">{{session()->get('message') ? session()->get('message') : $message}}</p>
                </div>
            @endif
            <form method="POST" action="{{ route('login') }}">
                @csrf

                <figure class="w-1/4 m-auto max-w-full">
                    <img src="{{config('utilities.base64logo')}}" alt="Metag Logo">
                </figure>
                <div class="text-center">
                    <h1 class="text-4xl pb-2 m-auto max-w-full font-extrabold">Metag Analyze</h1>
                    <h4> {{ __('Login') }} </h4>
                    <div class="py-4 w-full text-center ">
                        <a class="text-blue-500 hover:text-red-600"
                           href="{{url('register')}}">{{__("Register to use Metag Analyze")}}</a>
                    </div>
                </div>

                <label for="email" class="label">{{ __('E-Mail Address') }}</label>
                <input id="email" type="email"
                       class="input {{ $errors->has('email') ? ' bg-red-800 text-white' : '' }}" name="email"
                       value="{{ old('email') }}" required autofocus>

                <div class="my-2">
                    <label for="password" class="label">{{ __('Password') }}</label>

                    <input id="password" type="password"
                           class="input {{ $errors->has('password') ? ' bg-red-800  text-white' : '' }}"
                           name="password"
                           required>
                </div>
                <a class="text-blue-500 hover:text-red-600 block"
                   href="{{url('password/reset')}}">{{__("Forgot Password?")}}
                </a>
                @if ($errors->has('email'))
                    <div class="bg-red-700 my-2 pl-2 py-2 text-white font-bold">
                        {{ $errors->first('email') }}
                    </div>
                @endif

                @if ($errors->has('password'))
                    <div class="bg-red-700 my-2  pl-2 py-2 text-white font-bold">
                        {{ $errors->first('password') }}
                    </div>
                @endif
                <div class="text-center align-middle">

                    <button class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-l">
                        {{__("Login")}}
                    </button>

                </div>
            </form>
        </div>
    </div>
@endsection
