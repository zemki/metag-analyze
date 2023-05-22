@extends('auth.layouts.app')

@section('content')

<div class="relative px-6 isolate pt-14 lg:px-8">
    <div class="absolute inset-x-0 overflow-hidden -top-40 -z-10 transform-gpu blur-3xl sm:-top-80">
        <svg class="relative left-[calc(50%-11rem)] -z-10 h-[21.1875rem] max-w-none -translate-x-1/2 rotate-[30deg] sm:left-[calc(50%-30rem)] sm:h-[42.375rem]"
            viewBox="0 0 1155 678">
            <path fill="url(#45de2b6b-92d5-4d68-a6a0-9b9b2abad533)" fill-opacity=".3"
                d="M317.219 518.975L203.852 678 0 438.341l317.219 80.634 204.172-286.402c1.307 132.337 45.083 346.658 209.733 145.248C936.936 126.058 882.053-94.234 1031.02 41.331c119.18 108.451 130.68 295.337 121.53 375.223L855 299l21.173 362.054-558.954-142.079z" />
            <defs>
                <linearGradient id="45de2b6b-92d5-4d68-a6a0-9b9b2abad533" x1="1155.49" x2="-78.208" y1=".177"
                    y2="474.645" gradientUnits="userSpaceOnUse">
                    <stop stop-color="#3b82f6" />
                    <stop offset="1" stop-color="#fca5a5" />
                </linearGradient>
            </defs>
        </svg>
    </div>
    <div class="max-w-2xl py-32 mx-auto sm:py-48 lg:py-56">
        <div class="text-center">
            <h1 class="text-4xl font-bold tracking-tight text-gray-900 sm:text-6xl">{{ __('Reset Password')}}
            </h1>
            @if (session('status'))
            <div class="relative flex px-3 py-3 mb-4 text-green-800 bg-green-200 border">
                {{ session('status') }}
            </div>
            @endif

            @if(session()->has('message') || !empty($message))
            <div class="px-4 py-3 text-blue-700 bg-blue-100 border-t border-b border-blue-500" role="alert">
                <p class="font-bold">{{session()->get('message') ? session()->get('message') : $message}}</p>
            </div>
            @endif
            <div class="flex items-center justify-center mt-10 gap-x-6">
                <div class="w-full p-4 overflow-hidden text-center bg-white rounded">

                    <form method="POST" action="{{ url('password/new') }}">
                        @csrf
                        <input type="hidden" id="token" name="token" value="{{ $user->password_token }}">
                        <div class="flex justify-center mb-4">
                            <label for="email"
                                class="pt-2 pb-2 pl-1 mb-0 mr-2 leading-normal text-md-right">{{ __('E-Mail Address') }}</label>

                            <div class="md:w-1/2">
                                <input id="email" disabled type="email" value="{{$user->email}}"
                                    class="relative block w-full px-3 py-2 text-gray-900 placeholder-gray-500 border border-gray-300 rounded-none appearance-none
                                    focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm disabled:opacity-50 {{ $errors->has('email') ? ' bg-red-dark' : '' }}" name="email"
                                    value="{{ old('email') }}" required autofocus>

                            </div>
                        </div>
                        <div class="flex justify-center mb-4">
                            <label for="email"
                                class="pt-2 pb-2 pl-1 mb-0 mr-2 leading-normal text-md-right">{{ __('Password') }}</label>

                            <div class="md:w-1/2">
                                <input id="password" type="password"
                                    class="relative block w-full px-3 py-2 text-gray-900 placeholder-gray-500 border border-gray-300 rounded-none appearance-none
                                    focus:outline-none focus:ring-blue-500 focus:border-blue-500 focus:z-10 sm:text-sm {{ $errors->has('password') ? ' bg-red-dark' : '' }}" name="password"
                                    minlength="6" required>

                            </div>
                        </div>

                        @if ($errors->has('password'))
                        <div class="py-2 pl-2 my-2 font-bold text-white bg-red-700">{{ $errors->first('password') }}
                        </div>
                        @endif


                        <div class="pl-4 pr-4 text-center">
                            <button type="submit"
                                class="inline-block px-4 py-2 text-base font-normal leading-normal text-center no-underline align-middle border rounded select-none whitespace-nowrap text-blue-lightest bg-blue hover:bg-blue-light">
                                {{ __('Set password') }}
                            </button>
                        </div>

                    </form>
                </div>
            </div>
        </div>
    </div>
    <div
        class="absolute inset-x-0 top-[calc(100%-13rem)] -z-10 transform-gpu overflow-hidden blur-3xl sm:top-[calc(100%-30rem)]">
        <svg class="relative left-[calc(50%+3rem)] h-[21.1875rem] max-w-none -translate-x-1/2 sm:left-[calc(50%+36rem)] sm:h-[42.375rem]"
            viewBox="0 0 1155 678">
            <path fill="url(#ecb5b0c9-546c-4772-8c71-4d3f06d544bc)" fill-opacity=".3"
                d="M317.219 518.975L203.852 678 0 438.341l317.219 80.634 204.172-286.402c1.307 132.337 45.083 346.658 209.733 145.248C936.936 126.058 882.053-94.234 1031.02 41.331c119.18 108.451 130.68 295.337 121.53 375.223L855 299l21.173 362.054-558.954-142.079z" />
            <defs>
                <linearGradient id="ecb5b0c9-546c-4772-8c71-4d3f06d544bc" x1="1155.49" x2="-78.208" y1=".177"
                    y2="474.645" gradientUnits="userSpaceOnUse">
                    <stop stop-color="#3b82f6" />
                    <stop offset="1" stop-color="#fca5a5" />
                </linearGradient>
            </defs>
        </svg>
    </div>
</div>



@endsection