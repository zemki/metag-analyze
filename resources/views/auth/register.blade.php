@extends('auth.layouts.app')

@section('content')

<div class="flex items-center justify-center min-h-full px-4 py-12 sm:px-6 lg:px-8">
  <div class="w-full max-w-md space-y-8">
    <div>
      <img class="w-auto h-24 mx-auto" src="{{config('utilities.base64logo')}}" alt="Metag Analyze Logo">
      <h1 class="mt-6 text-3xl font-extrabold text-center text-gray-900">Register to Metag Analyze</h1>
      <p class="mt-2 text-sm text-center text-gray-600">
        Or
        <a href="{{url('login')}}" class="font-medium text-blue-600 hover:text-blue-500"> Login </a>
      </p>
    </div>
    <form class="mt-8 space-y-6" method="POST" action="{{ route('register') }}">
      <input type="hidden" name="remember" value="true">
      @csrf
      <div class="-space-y-px rounded-md shadow-sm">
        <div>
          <label for="email-address" class="sr-only">{{ __('E-Mail Address') }}</label>
          <input id="email-address" name="email" type="email" autocomplete="email" required
            class="relative block w-full px-3 py-2 text-gray-900 placeholder-gray-500 border border-gray-300 rounded-none appearance-none focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
            placeholder="Email address">
        </div>

        <ul class="w-full my-2">
          <li class="mb-2 text-sm font-bold break-words whitespace-normal list-item-registration"
            :class="{ is_valid: registration.contains_six_characters }">{{__('6
            Characters')}}
          </li>
          <li class="mb-2 text-sm font-bold break-words whitespace-normal list-item-registration"
            :class="{ is_valid: registration.contains_number }">
            {{__('Contains Number')}}
          </li>
          <li class="text-sm font-bold break-words whitespace-normal list-item-registration"
            :class="{ is_valid: registration.contains_letters }">
            {{__('Contains Letters')}}
          </li>
        </ul>
        <div>
          <label for="password" class="sr-only">{{ __('Password') }}</label>
          <input id="password" type="password" v-model="registration.password" @input="checkPassword()" name="password"
            required autocomplete="new-password" required
            class="{{ $errors->has('password') ? ' bg-red-100 text-white' : '' }} relative block w-full px-3 py-2 text-gray-900 placeholder-gray-500 border border-gray-300 rounded-none appearance-none rounded-t-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
            placeholder="{{ __('Password') }}">
        </div>
        <div>
          <input id="password-confirm" type="password" autocomplete="new-password"
            placeholder="{{ __('Confirm Password') }}"
            class="{{ $errors->has('password') ? ' bg-red-100 text-white' : '' }} relative block w-full px-3 py-2 text-gray-900 placeholder-gray-500 border border-gray-300 rounded-none appearance-none rounded-b-md focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm"
            name="password_confirmation" required>
        </div>
        @if ($errors->has('email'))
        <div class="py-2 pl-2 my-2 text-white bg-red-500">
          {{ $errors->first('email') }}
        </div>
        @endif

        @if ($errors->has('password'))
        <div class="py-2 pl-2 my-2 text-white bg-red-500">
          {{ $errors->first('password') }}
        </div>
        @endif
        <div class="relative flex items-start my-2">
          <div class="flex items-center h-5">
            <input checked aria-describedby="newsletter-subscription" name="newsletter" type="checkbox"
              class="w-4 h-4 text-blue-500 border-gray-300 rounded focus:ring-blue-500">
          </div>
          <div class="ml-3 text-sm">
            <label for="comments" class="font-medium text-gray-700">{{__('Send me your newsletter!')}}</label>
          </div>
        </div>



        <div>

          <p class="block">{!!__('By registering you confirmed that you read the <a class="text-blue-500"
              target="_blank" href="https://mesoftware.org/index.php/datenschutzerklaerung-metag/"
              title="Privacy Policy">Privacy Policy</a>')!!}</p>
          <button type="submit"
            class="relative flex justify-center w-full px-4 py-2 text-sm font-medium text-white bg-blue-600 border border-transparent rounded-md group hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
            :class="{'opacity-50 cursor-not-allowed opacity-75' : !this.registration.valid_password}"
            :disabled="!this.registration.valid_password">
            <span class="absolute inset-y-0 left-0 flex items-center pl-3">
              <!-- Heroicon name: solid/lock-closed -->
              <svg class="w-5 h-5 text-blue-500 group-hover:text-blue-400" xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                <path fill-rule="evenodd"
                  d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z"
                  clip-rule="evenodd" />
              </svg>
            </span>
            {{__('Register')}}
          </button>
        </div>
    </form>
  </div>
</div>



@endsection