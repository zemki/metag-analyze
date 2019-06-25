@extends('layouts.app')

@section('content')
<div class="columns">
	<div class="column is-half">

		<nav class="breadcrumb has-succeeds-separator is-small" aria-label="breadcrumbs">
			<ul>
				<li>Metag</li>
				<li><a href="{{url('/')}}">Projects</a></li>
				<li class="is-active" aria-current="page"><a href="#">{{$project->name}}</a></li>
			</ul>
		</nav>
	</div>
</div>
<div class="content">
	<b-tabs position="is-centered" class="block">
		<b-tab-item label="Case List"><div class="level">
			<div class="level-left"><h1>{{$project->name}}</h1></div>
			<div class="level-right">
				<div class="field">
					<div class="control">
						<a href="{{$project->path().'/cases/new'}}">
							<button  class="button is-link is-primary">Create Case</button>
						</a>
					</div>
				</div>
			</div>

		</div>
		<div class="level">
			<p>
				{{$project->description}}
			</p>
		</div>

			<div class="columns is-multiline subpixel-antialiased">
				@forelse($project->cases as $case)

					<div class="column is-4 overflow-auto ">
						<article class="cards-projects items-stretch .flex-grow-0 h-56 px-2 border-solid border-4 border-gray-100">
							<div class="mb-2">
								<p class="text-2xl font-bold ">
										<a href="{{$project->id.$case->path()}}">
											{{$case->name}}
										</a>
										<i class="">&rsaquo;</i>
									</a>
								</p>
								<p class="text-sm text-gray-600">
									{{$case->user? $case->user->email : 'no user assigned'}}
								</p>

							</div>
							<div>
								Entries: {{$case->entries->count()}}
							</div>
							<div class="">
								<p class="text-base">{{$case->duration}} </p>
							</div>
							<div class="mt-3" style="float:right;">
								<form action="{{url($project->path().'/cases/'.$case->id)}}" method="POST">
									{{ csrf_field() }}
									{{ method_field('DELETE') }}
									<button type="submit" class="button is-danger text-white">Delete Case</button>
								</form>
							</div>
						</article>
					</div>

				@empty
					no cases yet
				@endforelse
			</div>
	</b-tab-item>

	<b-tab-item label="Inputs">

			@if($project->isEditable())

			@else
			<b-notification
					:active.sync="mainNotification"
					aria-close-label="Close notification"
					type="is-danger"
					role="alert"
			>
				You created a case, project is not editable
			</b-notification>

		@endif

		<edit-project :editable="{{$project->isEditable() ? 'true' : 'false'}}" :project="{{$project}}"></edit-project>
	</b-tab-item>

</b-tabs>




</div>


@endsection
