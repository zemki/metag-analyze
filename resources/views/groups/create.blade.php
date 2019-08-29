@extends('layouts.app')

@section('content')
	@component('layouts.breadcrumb', ['breadcrumb'=>$breadcrumb])
	@endcomponent

	<div class="columns">
		<div class="column">
		<h1 class="text-4xl font-weight-bold uppercase mb-2">Create your group</h1>
		<h3 class="text-lg">
			Groups are made to share the same project between different users and it's mandatory to belong to one. <br>
			You can be the only participant in the group, as well as invite other people to work and share the result of your projects. <br>
			Each project will have the option to hide the project from the group.
		</h3>
		</div>
	</div>


	<form method="POST" action="{{route('store_groups')}}" class="form">

	<div class="columns">
		<div class="column">
			@csrf
				<div class="field">
					<label for="name" class="label">
						Name
					</label>
					<div class="control">
						<input type="text" class="input" name="name">
					</div>
				</div>

		</div>
	</div>

	<div class="level">
		<div class="field">
			<div class="control">
				<button class="button is-primary">Create Group</button>
			</div>
		</div>

	</div>
	</form>
@endsection