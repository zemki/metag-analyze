@extends('layouts.app')

@section('content')
	@component('layouts.breadcrumb', ['breadcrumb'=>$breadcrumb])
	@endcomponent


<div class="columns">
	<div class="column">

			<h1 class="title">Create a Case</h1>
			<p class="subtitle text-sm">The duration start from the moment  <strong>when the user log in in the app.</strong> <br>
				The user field define the login username in the app.<br>
				We advice to create the user with a prefix, referring the project. (example: P12_user0)

			</p>


		<form method="POST" action="{{url($project->path().'/cases')}}" class="" style="padding-top: 40px" id="addcase" autocomplete="off">
			@csrf
			<div class="field">
				<label for="name" class="label">
					Case Name
				</label>
				<div class="control">
					<input type="text" class="input" name="name">
					{!! $errors->first('name', '<p class="has-text-danger">:message</p>') !!}

				</div>
			</div>
			<div class="field">
				<label for="duration" class="label">
					Duration
				</label>
				<div class="columns">
					<div class="column">
						<div class="control">
							<input type="text" class="input" v-model="newcase.duration.input" >
						</div>
					</div>
					<div class="column">
						<div class="select">
							<select v-model="newcase.duration.selectedUnit">
								<option>Select a value</option>
								<option value="days">day(s)</option>
								<option value="week">week(s)</option>
							</select>
						</div>
					</div>
					<div class="column" v-show="newcase.duration.message != ''" v-html="'Estimated end (if the user log-in today): ' +newcase.duration.message">

					</div>
				</div>
			</div>
			<input type="hidden"  :value="newcase.duration.value" name="duration">

			<div class="field">
				<div class="control">
					<label for="duration" class="label">
						User
					</label>
					<input type="email" class="input" name="email" list="email" autocomplete="off" required>


					<datalist id="email">
						@foreach($users as $u)
						<option value="{{$u->email}}">{{$u->email}}</option>
						@endforeach
					</datalist>

				</div>
			</div>
		<div class="field">
				<div class="control">
					<button class="button is-link" >Create Case</button>
				</div>
			</div>

	</form>
</div>
</div>
@endsection

@section('pagespecificscripts')
@endsection
