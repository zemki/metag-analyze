@extends('layouts.app')

@section('content')
<div class="columns">
	<div class="column is-half">

		<nav class="breadcrumb has-succeeds-separator is-small" aria-label="breadcrumbs">
			<ul>
				<li><a href="#">Metag</a></li>
				<li ><a href="{{url('/')}}">Projects</a></li>
				<li ><a href="{{url($project->path())}}">{{$project->name}}</a></li>
				<li class="is-active" aria-current="page"><a href="#">{{$case->name}}</a></li>
			</ul>
		</nav>
	</div>
</div>
<div class="content">
	<div class="level">
		<div class="level-left"><h1>{{$case->name}}</h1></div>
		<div class="level-right">
			<div class="field">
			</div>
		</div>


	</div>
	<div class="level">
		<p> CASE'S DETAILS</p>
	</div>
</div>


@endsection
