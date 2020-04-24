@include('layouts.header')
<body>
<div id="app" class="w-full">
    @include('layouts.nav')

        @if(session()->has('message') || !empty($message))
            <div class="bg-blue-100 border-t border-b border-blue-500 text-blue-700 px-4 py-3" role="alert">
                <p class="font-bold">{{session()->get('message') ? session()->get('message') : $message}}</p>
            </div>
        @endif
        <div class="w-2/3 mx-auto pt-2">
        @yield('content')
        </div>
</div>

@yield('pagespecificscripts')
</body>
</html>
