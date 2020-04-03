@include('layouts.header')
<body>
<div id="app">
    @include('layouts.nav')

    <main class="py-4 container mx-auto mx-auto px-40">
        @if(session()->has('message'))
            <b-notification
                    :active.sync="mainNotification"
                    aria-close-label="Close"
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
