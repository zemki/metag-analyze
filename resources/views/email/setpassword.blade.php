@component('mail::message')


    {!! (new Parsedown)->text($text) !!} <br>
    {{url('setpassword')."?token=".($user->password_token ? $user->password_token : '')."&email=".$user->email}}
    <br>
    @component('mail::button', ['url' => url('password/set')."?token=".($user->password_token ? $user->password_token : '')."&email=".$user->email, 'color' => 'success'])
        {{__('Set Metag Password')}}
    @endcomponent
    <br>

    @if($qrCodeData)
    <br>
    <div style="text-align: center; margin: 30px 0;">
        <h3 style="color: #333; margin-bottom: 15px;">{{__('Quick Mobile Login')}}</h3>
        <p style="color: #666; margin-bottom: 15px;">{{__('Scan this QR code with your mobile device to login instantly:')}}</p>
        <img src="{{ $qrCodeData['qr_image'] }}" alt="QR Code" style="width: 300px; height: 300px; margin: 20px auto; display: block; border: 2px solid #e0e0e0; padding: 10px; background: white;" />
        <p style="color: #666; font-size: 12px; margin-top: 10px;">
            {{__('QR code expires in')}} {{ $qrCodeData['duration_days'] ?? 30 }} {{__('days')}}
        </p>
        <p style="color: #999; font-size: 11px; margin-top: 15px; word-break: break-all;">
            {{__('Or use this link:')}} <a href="{{ $qrCodeData['qr_url'] }}" style="color: #3490dc;">{{ $qrCodeData['qr_url'] }}</a>
        </p>
    </div>
    <br>
    @endif

    <br>
    {{__('Thanks,
    Metag Team')}}


@endcomponent
