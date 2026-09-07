@include('auth.layouts.header')

<body class="h-full">
<div id="app" class="flex flex-col min-h-screen">

    @yield('content')

    @include('layouts.footer')
    
</div>
@yield('pagespecificscripts')
</body>

</html>
