{{--
    Site footer. Pinned to the bottom of the viewport on short pages by the
    `mt-auto` below, which requires the surrounding layout to be a flex column
    of at least screen height - see layouts/app.blade.php and
    auth/layouts/app.blade.php. Deliberately no fixed positioning and no
    hard-coded height, so nothing else has to reserve space for it.

    Data comes from the view composer in AppServiceProvider.
--}}
<footer class="mt-auto border-t border-gray-200 bg-white print:hidden">
    <div class="max-w-7xl px-2 py-4 mx-auto sm:px-4 lg:px-8">
        <p class="text-sm text-center text-gray-500">
            <span class="font-medium text-gray-600">{{ $appName }}</span>
            <span class="mx-1.5 text-gray-300" aria-hidden="true">&middot;</span>
            {{ __('Version') }} {{ $appVersion }}@if($appBuild)<span class="text-gray-400"> / {{ __('Build') }} {{ $appBuild }}</span>@endif
            <span class="mx-1.5 text-gray-300" aria-hidden="true">&middot;</span>
            &copy; {{ date('Y') }} {{ $appOwner }}
        </p>
    </div>
</footer>
