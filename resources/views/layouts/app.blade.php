@include('layouts.header')
<body>
<div id="app">
    @include('layouts.nav')

    @if(session()->has('message') || !empty($message))
        <div class="w-auto mx-auto bg-yellow-100 border-t border-b border-blue-500 text-blue-700 px-4 py-3 text-center" role="alert">

            <p class="font-bold">
            <span class="relative h-3 w-3">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-pink-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-3 w-3 bg-pink-500"></span>
            </span>
                {{session()->get('message') ? session()->get('message') : $message}}</p>
        </div>
    @endif
    <div class="w-2/3 mx-auto pt-2">
        @yield('content')
    </div>

</div>

@yield('pagespecificscripts')
</body>
</html>
