@extends('layouts.app')

@section('content')
<div class="columns">
	<div class="column is-half">
		<nav class="breadcrumb has-succeeds-separator is-small" aria-label="breadcrumbs">
			<ul>
				<li><a href="#">Metag</a></li>
				<li ><a href="{{url('/')}}">Projects</a></li>
				<li class="is-active" aria-current="page"><a href="#">Create</a></li>
			</ul>
		</nav>
	</div>
</div>

<h1>Create a Project</h1>
<form method="POST" action="/projects" class="container" style="padding-top: 40px">
	@csrf
	<div class="field">
		<label for="name" class="label">
			Title
		</label>
		<div class="control">
			<input type="text" class="input" name="name">
		</div>
	</div>

	<div class="field">
		<label for="description" class="label">
			Description
		</label>

		<div class="control">
			<textarea name="description" id="textarea" class="textarea"></textarea>
		</div>
	</div>

	<div class="field">
		<div class="control">
			<label class="checkbox">
				<input type="checkbox" name="is_locked">
				lock project
			</label>
		</div>
	</div>

	<div class="field">
		<label for="duration" class="label">
			Duration
		</label>
		<div class="control">
			<input type="text" class="input" name="duration">
		</div>
	</div>

	<div class="field">
		<div class="control">
			<button class="button is-link">Create Project</button>
		</div>
	</div>



</form>
@endsection
