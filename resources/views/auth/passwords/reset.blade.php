@extends('auth.app')

@section('content')
    <div class="bg-img"></div>
    <div class="container mx-auto ">
        <div class="columns is-centered">

            <div class="column is-half">
                <div class="box" style="top:50%;left: 50%">
                    <form method="POST" action="{{ url('newpassword') }}">
                        @csrf
                        <input type="hidden" id="token" name="token" value="{{ $user->password_token }}"/>
                        <div class="field">
                            <label for="email" class="label">{{ __('E-Mail Address') }}</label>
                            <div class="control">
                                <input id="email" disabled type="email" value="{{$user->email}}"
                                       class="input {{ $errors->has('email') ? ' bg-red-dark' : '' }}" name="email"
                                       value="{{ old('email') }}" required autofocus>
                            </div>
                        </div>

                        <div class="field">
                            <label for="password" class="label">New Password</label>
                            <p class="control has-icon-left">
                                <input id="password" type="password"
                                       class="input {{ $errors->has('password') ? ' bg-red-dark' : '' }}" name="password"
                                       minlength="6"
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
                                    Set Password
                                </button>
                            </p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
