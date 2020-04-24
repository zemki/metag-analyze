@extends('auth.layouts.app')

@section('content')

    <div class="flex items-center justify-center h-screen z-10">

        <div class="bg-white p-4 rounded overflow-hidden shadow-lg w-1/3">

            <h1 class="text-4xl pb-2 m-auto max-w-full font-extrabold text-center">{{ __('Verify Your Email Address') }}</h1>
            <div class="text-justify">
                {{ __('Before proceeding, please check your email for a verification link.') }}
                <form id="resend-form" method="POST" action="{{route('verification.resend')}}">
                    @csrf
                </form>

                {{ __('If you did not receive the email') }}, <a class="text-red-700"
                                                                 href="{{ route('verification.resend') }}"
                                                                 onclick="event.preventDefault();
                        document.getElementById('resend-form').submit();">
                    {{ __('click here to request another') }}
                </a>.
            </div>


            @if (session('resent'))
                <div class="flex items-center bg-blue-500 text-white text-sm font-bold px-4 py-3"
                     role="alert">
                    <svg class="fill-current w-4 mb-2 font-medium leading-tight text-xl mr-2"
                         xmlns="http://www.w3.org/2000/svg"
                         viewBox="0 0 20 20">
                        <path
                                d="M12.432 0c1.34 0 2.01.912 2.01 1.957 0 1.305-1.164 2.512-2.679 2.512-1.269 0-2.009-.75-1.974-1.99C9.789 1.436 10.67 0 12.432 0zM8.309 20c-1.058 0-1.833-.652-1.093-3.524l1.214-5.092c.211-.814.246-1.141 0-1.141-.317 0-1.689.562-2.502 1.117l-.528-.88c2.572-2.186 5.531-3.467 6.801-3.467 1.057 0 1.233 1.273.705 3.23l-1.391 5.352c-.246.945-.141 1.271.106 1.271.317 0 1.357-.392 2.379-1.207l.6.814C12.098 19.02 9.365 20 8.309 20z"/>
                    </svg>

                    <p> {{ __('A fresh verification link has been sent to your email address.') }} </p>
                </div>
            @endif

        </div>
    </div>
@endsection
