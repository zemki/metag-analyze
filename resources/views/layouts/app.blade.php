<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Laravel') }}</title>

    <!-- Scripts -->
    <script src="{{ asset('js/manifest.js') }}" ></script>
    <script src="{{ asset('js/vendor.js') }}" ></script>
    <script src="{{ asset('js/app.js') }}" defer></script>
    <script src="https://www.gstatic.com/charts/loader.js"></script>

    <script type="text/javascript">
    window.inputs = <?php echo json_encode(config('inputs')); ?>;
    </script>


    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link href="https://fonts.googleapis.com/css?family=Nunito" rel="stylesheet" type="text/css">

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>
<body>
    <div id="app">
        @include('layouts.nav')

        <main class="py-4 container mx-auto px-40">
            @if(session()->has('message'))
                <b-notification
                        :active.sync="mainNotification"
                        aria-close-label="Close notification"
                        type="is-danger"
                        role="alert"
                >
                    {{session()->get('message')}}
                </b-notification>

            @endif
            @yield('content')
        </main>
    </div>

    @yield('pagespecificscripts')
</body>
</html>
