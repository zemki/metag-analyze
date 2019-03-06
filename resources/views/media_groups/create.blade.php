@extends('layouts.app')

@section('content')
<div class="columns">
	<div class="column is-half">
		<nav class="breadcrumb has-succeeds-separator is-small" aria-label="breadcrumbs">
			<ul>
				<li><a href="#">Metag</a></li>
				<li ><a href="{{url('/media_group')}}">Media Groups</a></li>
				<li class="is-active" aria-current="page"><a href="#">Create</a></li>
			</ul>
		</nav>
	</div>
</div>

<h1>Create a Media Group</h1>

<form method="POST" action="/media_groups" class="container" style="padding-top: 40px">
	@csrf
	<div class="field">
		<label for="name" class="label">
			Name
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
			<button class="button is-link">Create Media Group</button>
		</div>
	</div>



</form>
@endsection
