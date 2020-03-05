@extends('auth.app')

@section('content')

    <div class="container ">
        <div class="columns is-centered">

            <div class="column is-6" style="margin-top: 10%">
                <div class="box" style="top:50%;left: 50%;">
                    <form method="POST" action="{{ route('login') }}">
                        @csrf


                        <div class='column'>

                            <figure class="image is-64x64" style="margin: 0 auto; max-width: 100%;">
                                <img src="{{config('utilities.base64logo')}}" alt="Metag Logo">
                            </figure>
                            <div class="column has-text-centered ">
                                <h1 class="title" style="margin: 0 auto; max-width: 100%;">Metag Analyze</h1>
                            </div>
                            <div class="py-4 w-100 text-center ">
                                <a class="text-blue-500 hover:text-red-600" href="{{url('register')}}">Register to use
                                    Metag Analyze</a>
                            </div>
                        </div>

                        <div class="field">
                            <label for="email" class="label">{{ __('E-Mail Address') }}</label>
                            <div class="control">
                                <input id="email" type="text"
                                       class="input {{ $errors->has('email') ? ' is-invalid' : '' }}" name="email"
                                       value="{{ old('email') }}" required autofocus>
                            </div>
                        </div>
                        @if ($errors->has('email'))
                            <div class="notification is-danger  is-small">
                                <strong>{{ $errors->first('email') }}</strong>
                            </div>

                        @endif

                        <div class="field">
                            <label for="password" class="label">{{ __('Password') }}</label>
                            <p class="control has-icon-left">
                                <input id="password" type="password"
                                       class="input {{ $errors->has('password') ? ' is-invalid' : '' }}" name="password"
                                       required>
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
                                <button class="button is-dark">
                                    Login
                                </button>
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
