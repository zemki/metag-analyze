<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Metag') }}</title>

    <!-- Scripts -->
    <script src="{{ asset('js/manifest.js') }}"></script>
    <script src="{{ asset('js/vendor.js') }}"></script>
    <script src="{{ asset('js/app.js') }}" defer></script>

    <script type="text/javascript">
        window.inputs = <?php echo json_encode(config('inputs')); ?>;
    </script>


    <!-- Fonts -->
    <link rel="dns-prefetch" href="//fonts.gstatic.com">
    <link rel="stylesheet" href="https://rsms.me/inter/inter.css">
    <script>
        window.trans = [];
        window.trans = <?php
        $json_file = File::get(resource_path() . "/lang/" . App::getLocale() . '.json');
        echo json_decode(json_encode($json_file, true));;
        ?>;
    </script>
    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <style>
        @yield('pagespecificcss')
    </style>
</head>