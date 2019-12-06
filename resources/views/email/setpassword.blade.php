@component('mail::message')


    {!! (new Parsedown)->text($text) !!} <br>
    {{url('setpassword')."?token=".($user->password_token ? $user->password_token : '')}}
    <br>
    @component('mail::button', ['url' => url('setpassword')."?token=".($user->password_token ? $user->password_token : ''), 'color' => 'success'])
        Set {{ config('app.name') }} Password
    @endcomponent
    <br>
    Thanks,
    {{ config('app.name') }} Team


@endcomponent
