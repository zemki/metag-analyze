@include('layouts.header')

<body>
    <div id="app">
        @include('layouts.nav')

        @if(session()->has('message') || !empty($message))
        @if(session()->has('message_type') || !empty($message))
        @if(session()->get('message_type') == 'success' || !empty($message_type) && $message_type == 'success')
        <div class="p-4 rounded-md bg-green-50">
            <div class="flex w-64 mx-auto">
                <div class="flex-shrink-0">
                    <!-- Heroicon name: solid/check-circle -->
                    <svg class="w-5 h-5 text-green-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                        fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="flex-shrink-0 ml-3">
                    <p class="text-sm font-medium text-green-800">
                        {!! session()->get('message') ? session()->get('message') : $message !!}
                    </p>
                </div>

            </div>
        </div>
        @elseif(session()->get('message_type') == 'error' || !empty($message_type) && $message_type == 'error')
        <div class="p-4 rounded-md bg-red-50">
            <div class="flex w-64 mx-auto ">
                <div class="flex-shrink-0">
                    <!-- Heroicon name: solid/times-circle -->
                    <svg class="w-5 h-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                        fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414-1.414L4 10l3.293 3.293a1 1 0 101.414-1.414L8 10l-1.293-1.293z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="flex-shrink-0 ml-3">
                    <p class="text-sm font-medium text-red-800">
                        {!!session()->get('message') ? session()->get('message') : $message!!}
                    </p>
                </div>
            </div>
        </div>
        @else
        <div class="p-4 rounded-md bg-blue-50">
            <div class="flex w-64 mx-auto">
                <div class="flex-shrink-0">
                    <!-- Heroicon name: solid/information-circle -->
                    <svg class="w-5 h-5 text-blue-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                        fill="currentColor" aria-hidden="true">
                        <path fill-rule="evenodd"
                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                            clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="flex-shrink-0 ml-3 md:flex md:justify-between">
                    <p class="text-sm text-blue-700">
                        {!!session()->get('message') ? session()->get('message') : $message!!}
                    </p>
                </div>
            </div>
        </div>

        @endif

        @endif

        @endif
        <div class="w-2/3 pt-2 mx-auto">
            @yield('content')
        </div>

    </div>

    @yield('pagespecificscripts')
</body>

</html>