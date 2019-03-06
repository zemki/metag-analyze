@extends('layouts.app')

@section('content')
<div class="columns">
	<div class="column is-half">
		<nav class="breadcrumb has-succeeds-separator is-small" aria-label="breadcrumbs">
			<ul>
				<li><a href="#">Metag</a></li>
				<li ><a href="{{url('/media')}}">Media</a></li>
				<li class="is-active" aria-current="page"><a href="#">Create</a></li>
			</ul>
		</nav>
	</div>
</div>

<h1>Create a Media</h1>
<form method="POST" action="/media" class="container" style="padding-top: 40px">
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
		<label for="properties" class="label">
			Properties
		</label>

		<div class="control">
			<textarea name="properties" id="textarea" class="textarea"></textarea>
		</div>
	</div>

	<div class="field">
		<label for="duration" class="label">
			Media_group_id
		</label>
		<div class="select">
			<select name="media_group_id">
				@foreach($media_groups as $mg)
				<option>Select dropdown</option>
				<option value="{{$mg->id}}">{{$mg->name}}</option>
				@endforeach
			</select>
		</div>
	</div>

	<div class="field">
		<div class="control">
			<button class="button is-link">Create Media</button>
		</div>
	</div>



</form>
@endsection
