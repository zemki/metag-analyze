@include('layouts.header')
<body>
<div id="app">
    @include('layouts.nav')

    <main class="py-4 container mx-auto mx-auto px-40">
        @if(session()->has('message') || !empty($message))
            <div class="bg-blue-100 border-t border-b border-blue-500 text-blue-700 px-4 py-3" role="alert">
                <p class="font-bold">{{session()->get('message') ? session()->get('message') : $message}}</p>
            </div>
        @endif

        @yield('content')
    </main>
</div>

@yield('pagespecificscripts')
</body>
</html>
