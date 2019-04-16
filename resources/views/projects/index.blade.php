@extends('layouts.app')

@section('content')
<div class="columns">
	<div class="column is-half">
		<nav class="breadcrumb has-succeeds-separator is-small" aria-label="breadcrumbs">
			<ul>
				<li><a href="#">Metag</a></li>
				<li class="is-active" aria-current="page"><a href="{{url('/')}}">Projects</a></li>
			</ul>
		</nav>
	</div>
</div>
<div class="columns">
	<passport-clients></passport-clients>
	<passport-authorized-clients></passport-authorized-clients>
	<passport-personal-access-tokens></passport-personal-access-tokens>

</div>



<table class="table">
	<thead>
		<tr>
			<th><abbr title="id">#</abbr></th>
			<th>Project name</th>
			<th><abbr title="Description">Description</abbr></th>
			<th><abbr title="Cases">Number of cases</abbr></th>
			<th><abbr title="Creator">Created by</abbr></th>
		</tr>
	</thead>
	<tbody>
		@forelse($projects as $project)
		<tr>
			<td>{{$project->id}}</td>
			<td><a href="{{url($project->path())}}" target="_blank">{{$project->name}}</a></td>
			<td>{{$project->description}}</td>
			<td>{{$project->cases->count()}}</td>
			<td>{{\App\User::where('id',$project->created_by)->first()->email}}</td>
		</tr>
		@empty
		no projects yet
		@endforelse
	</tbody>
</table>


@endsection
