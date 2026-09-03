@include('auth.layouts.header')

<body class="h-full">
<div id="app">

    @yield('content')

    @include('layouts.footer')
    
</div>
@yield('pagespecificscripts')
</body>

</html>
