@component('mail::message')
# {{ __('Unverified users report') }}

{{ __('Attached is the list of accounts that never verified their email address.') }}

@isset($filename)
{{ __('File') }}: {{ $filename }}
@endisset

{{ __('Thanks,') }}<br>
{{ config('app.name') }}
@endcomponent
