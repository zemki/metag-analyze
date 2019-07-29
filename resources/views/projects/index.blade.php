@extends('layouts.app')

@section('content')

<div class="flex flex-row py-4">
	<a href="{{url('projects/new')}}">
		<button class="button bg-blue-500 hover:bg-blue-700 text-white mr-2 focus:border-gray-100">
			<i class="px-1">+</i> New Project
		</button>
	</a>
@if(Auth::user()->isAdmin())
	<a href="{{url('admin/users/new')}}">
		<button class="button bg-blue-500 hover:bg-blue-700 text-white mr-2 focus:border-gray-100">
			<i class="px-1">+</i> New User
		</button>
	</a>
@endif
</div>

<div class="columns is-multiline subpixel-antialiased">
@forelse($projects as $project)

		<div class="column is-4 overflow-auto ">
			<article class="cards-projects items-stretch .flex-grow-0 h-56 px-2 border-solid border-4 border-gray-100">
				<div class="mb-2">
					<p class="text-2xl font-bold ">
						<a class="align-middle" href="{{url($project->path())}}">
							{{$project->name}}
							<i class="">&rsaquo;</i>
						</a>
					</p>
					<p class="text-sm text-gray-600">
						Created by {{\App\User::where('id',$project->created_by)->first()->email}}
					</p>

				</div>
				<div>
					Cases: {{$project->cases->count()}}
				</div>
				<div class="mb-3">
					<p class="text-base">{{$project->description}} </p>
				</div>

				<div class="" style="float:right;">
					<form action="{{url($project->path())}}" method="POST">
						{{ csrf_field() }}
						{{ method_field('DELETE') }}
						<button type="submit" class="button is-danger text-white">Delete Project</button>
					</form>
				</div>
			</article>
		</div>

@empty
	no projects yet
@endforelse
</div>
@endsection
