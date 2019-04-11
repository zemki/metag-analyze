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
		<form method="POST" action="{{$project->path().'/cases'}}" class="" style="padding-top: 40px" >
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
			<div class="control">
				<button class="button is-link" >Create Case</button>
			</div>
		</div>
		</div>






	</form>
</div>
</div>
@endsection

@section('pagespecificscripts')
@endsection
