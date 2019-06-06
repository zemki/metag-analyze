@extends('layouts.app')

@section('content')
<div class="columns">
	<div class="column is-half">
		<nav class="breadcrumb has-succeeds-separator is-small" aria-label="breadcrumbs">
			<ul>
				<li><a href="#">Metag</a></li>
				<li ><a href="{{url('/')}}">Projects</a></li>
				<li ><a href="{{url($project->path())}}">{{$project->name}}</a></li>
				<li class="is-active" aria-current="page"><a href="#">Create Case</a></li>
			</ul>
		</nav>
	</div>
</div>


<div class="columns">
	<div class="column">
		<h1>Create a Case</h1>
		<form method="POST" action="{{$project->path().'/cases'}}" class="" style="padding-top: 40px" id="addcase" autocomplete="off">
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
					<div class="column" v-show="newcase.duration.message != ''" v-html="'Estimated end: ' +newcase.duration.message">

					</div>
				</div>
			</div>
			<input type="hidden"  :value="newcase.duration.value" name="duration">

			<div class="field">
				<div class="control">
					<input type="text" class="input" name="email" list="email">


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
