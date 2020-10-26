@component('mail::message')


    {!! (new Parsedown)->text($text) !!} <br>
    {{url('setpassword')."?token=".($user->password_token ? $user->password_token : '')}}
    <br>
    @component('mail::button', ['url' => url('password/set')."?token=".($user->password_token ? $user->password_token : ''), 'color' => 'success'])
        {{__('Set Metag Password')}}
    @endcomponent
    {{__('This link will expire in 60 minutes.')}}
    <br>
    {{__('Thanks,
    Metag Team')}}


@endcomponent
