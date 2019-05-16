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
	<b-tabs position="is-centered" class="block">
		<b-tab-item label="Details"><div class="level">
			<div class="level-left"><h1>{{$project->name}}</h1></div>
			<div class="level-right">
				<div class="field">
					<div class="control">
						<a href="{{$project->path().'/cases/new'}}"><button  class="button is-link is-primary">Create Case</button></a>
					</div>
				</div>
			</div>

		</div>
		<div class="level">
			<p>
				{{$project->description}}
			</p>
		</div>
		<table class="table">
			<thead>
				<tr>
					<th><abbr title="id">#</abbr></th>
					<th>Case name</th>
					<th>Assigned User</th>
				</tr>
			</thead>
			@forelse($project->cases as $case)


			<tbody>

				<tr>
					<td>{{$case->id}}</td>
					<td><a href="{{$case->path()}}" target="_blank">{{$case->name}}</a></td>
					<td>{{$case->user? $case->user->email : 'no user assigned'}}</td>
				</tr>
			</tbody>
			@empty
			<br>
			no cases yet
			@endforelse
		</table>
	</b-tab-item>

	<b-tab-item label="Inputs"><edit-project :data="{{json_encode($data)}}" :project="{{$project}}"></edit-project></b-tab-item>

</b-tabs>




</div>


@endsection
