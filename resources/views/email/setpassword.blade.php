@component('mail::message')


    {!! (new Parsedown)->text($text) !!} <br>
    {{url('setpassword')."?token=".($user->password_token ? $user->password_token : '')."&email=".$user->email}}
    <br>
    @component('mail::button', ['url' => url('password/set')."?token=".($user->password_token ? $user->password_token : '')."&email=".$user->email, 'color' => 'success'])
        {{__('Set Metag Password')}}
    @endcomponent
    <br>
    <br>
    <br>
    {{__('Thanks,
    Metag Team')}}


@endcomponent
