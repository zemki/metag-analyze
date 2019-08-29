@component('mail::message')
	@component('mail::panel')

		{!! (new Parsedown)->text($text) !!}

		{{url('setpassword')."?token=".($user->password_token ? $user->password_token : '')}}

		@component('mail::button', ['url' => url('setpassword')."?token=".($user->password_token ? $user->password_token : '')])
			Set Mesort Password
		@endcomponent

		Thanks,
		{{ config('app.name') }} Team

	@endcomponent


@endcomponent
