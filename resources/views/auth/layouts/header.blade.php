<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Metag') }}</title>

    <!-- Scripts -->
    @vite(['resources/sass/app.scss', 'resources/js/app.js'])

    <script>
        window.trans = [];
        window.trans = <?php
                       if (File::exists(resource_path() . "/lang/" . App::getLocale() . '.json')) {
                           $json_file = File::get(resource_path() . "/lang/" . App::getLocale() . '.json');
                           echo json_decode(json_encode($json_file, true));
                       } else {
                           echo "[]";
                       }
                       ?>;
    </script>

    <style>
        @yield('pagespecificcss')
    </style>
</head>
