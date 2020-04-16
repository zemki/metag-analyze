@extends('auth.layouts.app')

@section('content')

    <div class="flex items-center justify-center h-screen z-10">

        <div class="bg-white p-4 rounded overflow-hidden shadow-lg w-1/3">
            @if (session('status'))
                <div class="flex relative px-3 py-3 mb-4 border text-green-800 bg-green-200">
                    {{ session('status') }}
                </div>
            @endif
                @if(session()->has('message') || !empty($message))
                    <div class="bg-blue-100 border-t border-b border-blue-500 text-blue-700 px-4 py-3" role="alert">
                        <p class="font-bold">{{session()->get('message') ? session()->get('message') : $message}}</p>
                    </div>
                @endif
            <div class="text-center">
                <h1 class="text-4xl pb-2 m-auto max-w-full font-extrabold">
                    Metag Analyze
                </h1>
                <h4> {{ __('Reset Password') }} </h4>
                <div class="py-4 w-full text-center">
                    <a class="text-blue-500 hover:text-red-600 block"
                       href="{{url('login')}}">{{__("Return to login")}}</a>
                </div>
            </div>
            <form method="POST" action="{{ route('password.email') }}">
                @csrf

                <div class="mb-4 flex flex-wrap">
                    <label for="email"
                           class="md:w-1/3 pl-4 pt-2 pb-2 mb-0 leading-normal">{{ __('E-Mail Address') }}</label>

                    <div class="md:w-1/2">
                        <input id="email" type="email"
                               class="block appearance-none w-full py-1 px-2 mb-1 text-base leading-normal bg-white text-grey-darker border border-grey rounded{{ $errors->has('email') ? ' bg-red-dark' : '' }}"
                               name="email" value="{{ old('email') }}" required>

                    </div>
                </div>


                @if ($errors->has('email'))
                    <div class="bg-red-700 my-2 pl-2 py-2 text-white font-bold">{{ $errors->first('email') }}
                    </div>
                @endif




                <div class="pr-4 pl-4  text-center">
                    <button type="submit"
                            class="inline-block align-middle text-center select-none border font-normal whitespace-no-wrap py-2 px-4 rounded text-base leading-normal no-underline text-blue-lightest bg-blue hover:bg-blue-light">
                        {{ __('Send Password Reset Link') }}
                    </button>
                </div>

            </form>
        </div>
    </div>


@endsection
