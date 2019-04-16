@extends('layouts.app')

@section('content')
<div class="columns">
	<div class="column is-half">
<nav class="breadcrumb has-succeeds-separator is-small" aria-label="breadcrumbs">
  <ul>
    <li><a href="#">Metag</a></li>
    <li class="is-active" aria-current="page"><a href="{{url('/media')}}">Media</a></li>
  </ul>
</nav>
</div>
</div>
@if($errors)
@foreach($errors as $error)
{{$error}}
@endforeach
@endif



<table class="table">
	<thead>
		<tr>
			<th><abbr title="id">#</abbr></th>
			<th>Name</th>
			<th><abbr title="Description">Description</abbr></th>
			<th><abbr title="Cases">Properties</abbr></th>
			<th><abbr title="Creator">Media Group</abbr></th>
		</tr>
	</thead>
	<tbody>
@forelse($media as $m)
			<tr>
				<td>{{$m->id}}</td>
				<td><a href="{{url($m->path())}}" target="_blank">{{$m->name}}</a></td>
				<td>{{$m->description}}</td>
				<td>{{$m->properties}}</td>
				<td>{{$m->media_group->name}}</td>
			</tr>
			@empty
no Media yet
@endforelse
	</tbody>
</table>


@endsection
