@extends('auth.app')

@section('content')
    <div class="bg-img"></div>
    <div class="container mx-auto ">
        <div class="columns is-centered">

            <div class="column is-6" style="margin-top: 10%">
                <div class="box" style="top:50%;left: 50%;">
                    <form method="POST" action="{{ route('register') }}">
                        @csrf


                        <div class='column'>

                            <figure class="image is-64x64" style="margin: 0 auto; max-width: 100%;">
                                <img src="{{config('utilities.base64logo')}}" alt="Metag Logo">

                            </figure>
                            <div class="column has-text-centered ">
                                <h1 class="title pb-2" style="margin: 0 auto; max-width: 100%;">Metag Analyze</h1>
                                <h4> {{ __('Register') }} </h4>
                            </div>
                        </div>
                        @if ($errors->has('email'))
                            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded" role="relative px-3 py-3 mb-4 border rounded">
                                <strong>{{ $errors->first('email') }}</strong>
                            </div>
                        @endif
                        <div class="field">
                            <label for="email" class="label">{{ __('E-Mail Address') }}</label>
                            <div class="control">
                                <input id="email" type="email"
                                       class="input {{ $errors->has('email') ? ' bg-red-dark' : '' }}" name="email"
                                       value="{{ old('email') }}" required autofocus>
                            </div>
                        </div>


                        <ul class="w-1/2">
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

                        <div class="field">
                            <label for="password" class="label">{{ __('Password') }}</label>
                            <p class="control has-icon-left">
                                <input id="password" type="password" v-model="registration.password"
                                       @input="checkPassword()"
                                       class="input {{ $errors->has('password') ? ' bg-red-dark' : '' }}" name="password"
                                       required>
                                <span class="icon is-small is-left">
            <i class="fas fa-lock"></i>
          </span>
                            </p>
                        </div>
                        <div class="field">
                            <label for="password-confirm"
                                   class="md:w-1/3 pr-4 pl-4 pt-2 pb-2 mb-0 leading-normal text-md-right">{{ __('Confirm Password') }}</label>
                            <p class="control has-icon-left">
                                <input id="password-confirm" type="password" class="input"
                                       name="password_confirmation" required>
                                <span class="icon is-small is-left">
            <i class="fas fa-lock"></i>
          </span>
                            </p>
                        </div>
                        @if ($errors->has('password'))
                            <div class="notification is-danger  is-small">
                                <strong>{{ $errors->first('password') }}</strong>
                            </div>
                        @endif
                        <div class="field">
                            <p class="control">
                                <button class="button is-dark"
                                        :class="{'opacity-50 cursor-not-allowed opacity-75' : !this.registration.valid_password}"
                                        :disabled="!this.registration.valid_password"
                                >
                                    {{__('Register')}}
                                </button>
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection

