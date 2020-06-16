@extends('auth.layouts.app')

@section('content')
    <div class="flex items-center justify-center h-screen">


        <div class="bg-white p-4 rounded overflow-hidden shadow-lg  w-1/3">
            <form method="POST" action="{{ route('register') }}">
                @csrf

                <figure class="image is-64x64" style="margin: 0 auto; max-width: 100%;">
                    <img src="{{config('utilities.base64logo')}}" alt="Mesort Logo">

                </figure>
                <div class="text-center">
                    <h1 class="title pb-2" style="margin: 0 auto; max-width: 100%;">Metag Analyze</h1>
                    <h4> {{ __('Register') }} </h4>
                    <div class="py-4 w-full text-center ">
                        <a class="text-blue-500 hover:text-red-600" href="{{url('login')}}">{{__("Login Page")}}</a>
                    </div>
                </div>

                <label for="email" class="label">{{ __('E-Mail Address') }}</label>
                <div class="control">
                    <input id="email" type="email"
                           class="input {{ $errors->has('email') ? ' bg-red-dark' : '' }}" name="email"
                           value="{{ old('email') }}" required autofocus v-model="registration.email">
                </div>


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
                    <label class="w-full block font-bold">
                        <input class="mr-2 leading-tight" type="checkbox" checked name="newsletter">
                        <span class="text-sm">
                            Send me your newsletter!
                        </span>
                    </label>

                    <p class="block">{!!__('By registering you confirmed that you read the <a class="text-blue-500" target="_blank" href="https://mesoftware.org/index.php/datenschutzerklaerung-metag/" title="Privacy Policy">Privacy Policy</a>')!!}</p>

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

