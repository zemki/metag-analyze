@extends('auth.layouts.app')

@section('content')
<div class="flex items-center justify-center min-h-full px-4 py-12 sm:px-6 lg:px-8">
    <div class="w-full max-w-md space-y-8">
        <div>
            <img class="w-auto h-24 mx-auto" src="{{ asset('images/mart-logo.svg') }}" alt="MART Analyze">
            <h1 class="mt-6 text-3xl font-extrabold text-center text-gray-900">
                {{ __('Your email was not changed') }}
            </h1>
            <p class="mt-2 text-sm text-center text-gray-600">
                {{ __('This confirmation link could not be applied.') }}
            </p>
        </div>

        @isset($reason)
            <div class="p-4 rounded-md bg-red-50">
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
                        <p class="text-sm text-red-700">{{ $reason }}</p>
                    </div>
                </div>
            </div>
        @endisset

        <p class="text-sm text-center text-gray-600">
            {{ __('The address may already be in use. You can request the change again from your account.') }}
        </p>

        <div class="text-sm text-center">
            <a href="{{ url('/') }}" class="font-medium text-blue-500 hover:text-blue-700">{{ __('Home') }}</a>
        </div>
    </div>
</div>
@endsection
