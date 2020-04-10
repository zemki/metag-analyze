@extends('auth.app')

@section('content')
    <div class="flex items-center justify-center h-screen">


        <div class="bg-white p-4 rounded overflow-hidden shadow-lg  w-1/3">
            <form method="POST" action="{{ route('login') }}">
                @csrf

                <figure class="image is-64x64" style="margin: 0 auto; max-width: 100%;">
                    <img src="{{config('utilities.base64logo')}}" alt="Metag Logo">

                </figure>
                <div class="text-center">
                    <h1 class="title pb-2" style="margin: 0 auto; max-width: 100%;">Metag Analyze</h1>
                    <h4> {{ __('Login') }} </h4>
                    <div class="py-4 w-full text-center ">
                        <a class="text-blue-500 hover:text-red-600" href="{{url('register')}}">{{__("Register to use Metag Analyze")}}</a>
                    </div>
                </div>

                <label for="email" class="label">{{ __('E-Mail Address') }}</label>
                <div class="control">
                    <input id="email" type="email"
                           class="input {{ $errors->has('email') ? ' bg-red-800 text-white' : '' }}" name="email"
                           value="{{ old('email') }}" required autofocus>
                </div>

                <div class="my-2">
                    <label for="password" class="label">{{ __('Password') }}</label>

                    <input id="password" type="password"
                           class="input {{ $errors->has('password') ? ' bg-red-800  text-white' : '' }}"
                           name="password"
                           required>
                </div>

                @if ($errors->has('email'))
                    <div class="bg-red-700 my-2 pl-2 py-2"
                         role="relative px-3 py-3 mb-4 border rounded">
                        <strong>{{ $errors->first('email') }}</strong>
                    </div>
                @endif

                @if ($errors->has('password'))
                    <div class="bg-red-700 my-2  pl-2 py-2">
                        <strong>{{ $errors->first('password') }}</strong>
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
