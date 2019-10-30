@component('mail::message')



		{!! (new Parsedown)->text($text) !!}

		{{url('setpassword')."?token=".($user->password_token ? $user->password_token : '')}}

		@component('mail::button', ['url' => url('setpassword')."?token=".($user->password_token ? $user->password_token : '')])
			Set Mesort Password
		@endcomponent

		Thanks,
		{{ config('app.name') }} Team




@endcomponent
