<?php

/*
|--------------------------------------------------------------------------
| Blade Icons
|--------------------------------------------------------------------------
|
| Only the keys below override the package defaults
| (vendor/blade-ui-kit/blade-icons/config/blade-icons.php). The merge is
| shallow, so 'components' must list both of its keys.
|
| Icon set components (<x-heroicon-o-check />, <x-fi-... />) are disabled.
| Registering them scanned all heroicon and Filament SVG files on every
| request and boot (an estimated 40 ms per request on production), and
| nothing uses them. Icons still render through svg(), as Filament does. In Blade,
| use @svg('heroicon-o-check') or <x-filament::icon icon="heroicon-o-check" />.
|
*/

return [

    'components' => [
        'disabled' => true,
        'default' => 'icon',
    ],

];
