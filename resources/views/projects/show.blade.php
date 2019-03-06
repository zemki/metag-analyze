@extends('layouts.app')

@section('content')
<div class="columns">
	<div class="column is-half">

<nav class="breadcrumb has-succeeds-separator is-small" aria-label="breadcrumbs">
  <ul>
    <li><a href="#">Metag</a></li>
    <li><a href="{{url('/')}}">Projects</a></li>
    <li class="is-active" aria-current="page"><a href="#">{{$project->name}}</a></li>
  </ul>
</nav>
</div>
</div>
<div class="content">
	<div class="level">
		<div class="level-left"><h1>{{$project->name}}</h1></div>
		<div class="level-right">
	<div class="field">
		<div class="control">
			<button class="button is-link is-primary">Create Case</button>
		</div>
	</div>
</div>


</div>
<div class="level">
<p>
{{$project->description}}
</p>
</div>

@forelse($project->cases() as $p)
TABLE WITH CASES
<!--
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

			<tr>
				<td>{{$project->id}}</td>
				<td><a href="{{url($project->path())}}" target="_blank">{{$project->name}}</a></td>
				<td>{{$project->description}}</td>
				<td>INSERT NUMBER OF CASES</td>
				<td>{{\App\User::where('id',$project->created_by)->first()->email}}</td>
			</tr>
	</tbody>
</table> -->
@empty
no cases yet
@endforelse
</div>


@endsection
