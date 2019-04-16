@extends('layouts.app')

@section('content')
<div class="columns">
	<div class="column is-half">
		<nav class="breadcrumb has-succeeds-separator is-small" aria-label="breadcrumbs">
			<ul>
				<li><a href="#">Metag</a></li>
				<li class="is-active" aria-current="page"><a href="{{url('/media_group')}}">Media Groups</a></li>
			</ul>
		</nav>
	</div>
</div>



<table class="table">
	<thead>
		<tr>
			<th><abbr title="id">#</abbr></th>
			<th>Name</th>
			<th><abbr title="Description">Description</abbr></th>
		</tr>
	</thead>
	<tbody>
		@forelse($media_group as $mg)
		<tr>
			<td>{{$mg->id}}</td>
			<td><a href="{{url($mg->path())}}" target="_blank">{{$mg->name}}</a></td>
			<td>{{$mg->description}}</td>
		</tr>
		@empty
		no Media Group yet
		@endforelse
	</tbody>
</table>


@endsection
