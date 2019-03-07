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

<h1>Create a Case</h1>
<form method="POST" action="{{$project->path().'/cases'}}" class="container" style="padding-top: 40px">
	@csrf
	<div class="field">
		<label for="name" class="label">
			Name
		</label>
		<div class="control">
			<input type="text" class="input" name="name">
		</div>
	</div>

	<h1>INSERT MORE DETAIL FOR THE PROJECT</h1>

	<div class="field">
		<div class="control">
			<button class="button is-link">Create Case</button>
		</div>
	</div>



</form>
@endsection
