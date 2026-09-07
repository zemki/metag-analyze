@extends('auth.layouts.app')

@section('content')
    <div class="flex items-center justify-center min-h-full px-4 py-12 sm:px-6 lg:px-8">
        <div class="w-full max-w-md space-y-8">

            <div>
                <img class="w-auto h-24 mx-auto" src="{{ config('utilities.base64logo') }}" alt="Metag Analyze Logo">
                <h1 class="mt-6 text-3xl font-extrabold text-center text-gray-900">{{ __('Set your password') }}</h1>
                <p class="mt-2 text-sm text-center text-gray-600">
                    {{ __('Choose a password for') }} <span class="font-medium text-gray-900">{{ $user->email }}</span>
                </p>
            </div>

            @if (session('status'))
                <div class="p-4 rounded-md bg-green-50">
                    <p class="text-sm text-green-700">{{ session('status') }}</p>
                </div>
            @endif

            @if(session()->has('message') || !empty($message))
                <div class="p-4 rounded-md bg-blue-50">
                    <p class="text-sm font-medium text-blue-700">
                        {{ session()->get('message') ? session()->get('message') : $message }}
                    </p>
                </div>
            @endif

            <form class="mt-8 space-y-6" method="POST" action="{{ url('password/new') }}">
                @csrf
                <input type="hidden" id="token" name="token" value="{{ $user->password_token }}">

                <div class="-space-y-px rounded-md shadow-xs">
                    <div>
                        <label for="email" class="sr-only">{{ __('E-Mail Address') }}</label>
                        {{-- Disabled: the address is fixed by the token in the link. --}}
                        <input id="email" name="email" type="email" value="{{ $user->email }}" disabled
                               class="relative block w-full px-3 py-2 text-gray-500 bg-gray-50 border border-gray-300 rounded-none appearance-none rounded-t-md sm:text-sm">
                    </div>
                    <div>
                        <label for="password" class="sr-only">{{ __('Password') }}</label>
                        <input id="password" name="password" type="password" autocomplete="new-password"
                               minlength="6" required autofocus
                               placeholder="{{ __('Password') }}"
                               class="relative block w-full px-3 py-2 text-gray-900 placeholder-gray-500 border border-gray-300 rounded-none appearance-none rounded-b-md focus:outline-hidden focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm">
                    </div>
                </div>

                @if ($errors->any())
                    <div class="p-4 mt-5 rounded-md bg-red-50">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="w-5 h-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                     fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd"
                                          d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                          clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="ml-3">
                                <h3 class="text-sm font-medium text-red-800">
                                    {{ __('Whoops! Something went wrong.') }}
                                </h3>
                                <ul class="mt-2 text-sm text-red-600 list-disc list-inside">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                @endif

                <div>
                    <button type="submit"
                            class="relative flex justify-center w-full px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md group hover:bg-blue-700 focus:outline-hidden focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3">
                            <svg class="w-5 h-5 text-blue-500 group-hover:text-blue-400"
                                 xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor"
                                 aria-hidden="true">
                                <path fill-rule="evenodd"
                                      d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"
                                      clip-rule="evenodd"/>
                            </svg>
                        </span>
                        {{ __('Set password') }}
                    </button>
                </div>
            </form>

            <div class="text-sm text-center">
                <a href="{{ url('login') }}" class="font-medium text-blue-500 hover:text-blue-700">
                    {{ __('Return to login') }}
                </a>
            </div>

        </div>
    </div>
@endsection
