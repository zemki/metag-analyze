@extends('layouts.app')

@section('content')
	<h4 class="text-4xl block w-full">{{ __("Project you created") }} </h4>

	<div class="flex flex-row pb-4">
		<a href="{{url('projects/new')}}">
			<button class="button bg-blue-500 hover:bg-blue-700 text-white mr-2 focus:border-gray-100">
				<i class="px-1">+</i> {{ __('New Project') }}
			</button>
		</a>
		@if(Auth::user()->isAdmin())
			<a href="{{url('admin/users/new')}}">
				<button class="button bg-blue-500 hover:bg-blue-700 text-white mr-2 focus:border-gray-100">
					<i class="px-1">+</i> {{ __('New User') }}
				</button>
			</a>
		@endif
	</div>

	<div class="columns is-multiline subpixel-antialiased">
		@forelse($projects as $project)

			<div class="column is-4 overflow-auto ">
				<article
						class="cards-projects items-stretch .flex-grow-0 h-56 px-2 border-solid border-4 border-gray-100">
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
						<a class="button is-danger text-white"
						   @click="confirmDeleteProject({{$project->id}})">Delete Project</a>
					</div>
				</article>
			</div>

		@empty
			<div class="column text-center">
				You don't have any personal project.
			</div>
		@endforelse
	</div>


	<div class="columns is-multiline subpixel-antialiased">
		<h4 class="text-4xl block w-full p-2">{{ __("Project you're invited to") }} </h4>

		@forelse($invites as $project)

			<div class="column is-4 overflow-auto ">
				<article
						class="cards-projects items-stretch flex-grow-0 h-56 px-2 border-solid border-4 border-blue-100">
					<div class="mb-2">
						<p class="text-2xl font-bold ">
							<a class="align-middle" href="{{url($project->path())}}">
								{{$project->name}}
								<i class="">&rsaquo;</i>
							</a>
						</p>
						<p class="text-sm text-gray-600">
							{{ __('Created by') }} {{\App\User::where('id',$project->created_by)->first()->email}}
						</p>

					</div>
					<div>
						{{ __('Cases: ') }} {{$project->cases->count()}}
					</div>
					<div class="mb-3">
						<p class="text-base">{{$project->description}} </p>
					</div>

					<div class="" style="float:right;">
						<a class="button is-danger text-white"
						   @click="confirmLeaveProject({{auth()->user(),$project->id}})">Leave Project</a>
					</div>
				</article>
			</div>
		@empty
			<div class="column text-center">
				You have not received any invite.
			</div>
		@endforelse
	</div>
@endsection
