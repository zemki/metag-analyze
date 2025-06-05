@include('layouts.header')

<body>
<div id="app">
    @include('layouts.nav')

   @if ($errors->any())
        @foreach ($errors->all() as $error)
            <div class="bg-blue-100 border-t border-b border-blue-500 text-blue-700 px-4 py-3 text-center" role="alert">
                <p class="font-bold">{{ $error }}</p>
            </div>
         @endforeach
        @endif
    <div class="w-2/3 pt-2 mx-auto">

        @yield('content')
        <snackbar ref="snackbar" :message="snackbarMessage"></snackbar>
    </div>
    <custom-dialogue v-if="dialog.show" :message="customDialogue" ref="dialogue" :title="dialog.title"
                     :message="dialog.message"
                     :confirm-text="dialog.confirmText" :on-confirm="dialog.onConfirm"
                     ref="customDialogue"
                     :on-cancel="dialog.onCancel"/>
    
    <debug-panel />


</div>

@yield('pagespecificscripts')
</body>

</html>
