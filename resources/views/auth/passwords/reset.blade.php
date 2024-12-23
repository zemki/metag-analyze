@extends('auth.layouts.app')

@section('content')
    <div class="flex sm:items-start md:items-center md:justify-center h-screen">
        <div class="bg-white p-4 rounded overflow-hidden shadow-lg  md:w-1/2 lg:w-1/3 sm:w-full">
            <form method="POST" action="{{ route('register') }}">
                @csrf

                <figure class="w-1/4 m-auto max-w-full">
                    <img src="{{config('utilities.base64logo')}}" alt="Metag Analyze Logo">
                </figure>

                <div class="text-center">
                    <h1 class="text-4xl pb-2 m-auto max-w-full font-extrabold">Metag Analyze</h1>
                    <h4> {{ __('Register') }} </h4>
                    <div class="py-4 w-full text-center ">
                        <a class="text-blue-500 hover:text-red-600" href="{{url('login')}}">{{__("Login Page")}}</a>
                    </div>
                </div>

                <label for="email" class="label">{{ __('E-Mail Address') }}</label>

                    <input id="email" type="email"
                           class="input {{ $errors->has('email') ? ' bg-red-dark' : '' }}" name="email"
                           value="{{ old('email') }}" required autofocus v-model="registration.email">



                <ul class="w-full my-2">
                    <li class="list-item-registration w-auto"
                        :class="{ is_valid: registration.contains_six_characters }">{{__('6 Characters')}}
                    </li>
                    <li class="list-item-registration" :class="{ is_valid: registration.contains_number }">
                        {{__('Contains Number')}}
                    </li>
                    <li class="list-item-registration" :class="{ is_valid: registration.contains_letters }">
                        {{__('Contains Letters')}}
                    </li>
                </ul>

                <div class="my-2">
                    <label for="password" class="label">{{ __('Password') }}</label>

                    <input id="password" type="password" v-model="registration.password"
                           @input="checkPassword()"
                           class="input {{ $errors->has('password') ? ' bg-red-dark' : '' }}"
                           name="password"
                           required>
                </div>
                <div class="my-2">
                    <label for="password-confirm"
                           class="label">{{ __('Confirm Password') }}</label>

                    <input id="password-confirm" type="password" class="input"
                           name="password_confirmation" required>
                </div>
                @if ($errors->has('email'))
                    <div class="bg-red-700 my-2 pl-2 py-2">
                        <strong>{{ $errors->first('email') }}</strong>
                    </div>
                @endif

                @if ($errors->has('password'))
                    <div class="bg-red-700 my-2  pl-2 py-2">
                        <strong>{{ $errors->first('password') }}</strong>
                    </div>
                @endif
                <div class="text-center align-middle">

                    <button class="bg-gray-300 hover:bg-gray-400 text-gray-800 font-bold py-2 px-4 rounded-l"
                            :class="{'opacity-50 cursor-not-allowed opacity-75' : !this.registration.valid_password}"
                            :disabled="!this.registration.valid_password"
                    >
                        {{__('Register')}}
                    </button>

                </div>
            </form>
        </div>
    </div>

@endsection

