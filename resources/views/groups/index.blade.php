@extends('layouts.app')

@section('content')



<table class="table">
	<thead>
	<tr>
		<th><abbr title="number">#</abbr></th>
		<th>Name</th>
		<th><abbr title="Number of Projects">Number of Projects</abbr></th>
		<th><abbr title="Number of Participants">Number of Participants</abbr></th>
		<th><abbr title="Actions">Actions</abbr></th>
	</tr>
	</thead>
	<tfoot>
	<tr>
		<th><abbr title="number">#</abbr></th>
		<th>Name</th>
		<th><abbr title="Number of Projects">Number of Projects</abbr></th>
		<th><abbr title="Number of Participants">Number of Participants</abbr></th>
		<th><abbr title="Actions">Actions</abbr></th>
	</tr>
	</tfoot>
	<tbody>
	@foreach($groups as $group)
	<tr>
		<td>{{$group->id}}</td>
		<td>{{$group->name}}</td>
		<td>{{$group->users()->count()}}</td>
		<td>{{$group->users()->count()}}</td>
		<td>delete</td>
	</tr>
		@endforeach
	</tbody>
</table>

	@endsection
