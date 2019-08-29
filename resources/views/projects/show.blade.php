@extends('layouts.app')

@section('content')
	@component('layouts.breadcrumb', ['breadcrumb'=>$breadcrumb])
	@endcomponent

	<div class="content">
		<div class="level">
			<div class="level-left"><h1>{{$project->name}}</h1></div>
			<div class="level-right">
				<div class="field">
					<div class="control">
						<a href="{{url($project->path().'/cases/new')}}">
							<button class="button is-link is-primary">Create Case</button>
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

		<b-tabs class="block w-full" expanded>
			<b-tab-item label="Case List">


				<div class="columns is-multiline subpixel-antialiased">
					@forelse($project->cases as $case)

						<div class="column is-4 overflow-auto ">
							<article
									class="cards-projects items-stretch flex-grow-0 h-auto px-2 py-2 border-solid border-4 border-gray-100">
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
									<p class="text-base">{!!$case->formattedDuration()!!} </p>
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
						<p class="mt-2">There are no cases for this project</p>
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

				<edit-project :editable="{{$project->isEditable() ? 'true' : 'false'}}"
							  :project="{{$project}}"></edit-project>
			</b-tab-item>

			@if($project->created_by == auth()->user()->id)
				<b-tab-item label="Invites">
					<project-invites :invitedlist="{{$invites}}" :project="{{$project->id}}"></project-invites>
				</b-tab-item>
			@endif
		</b-tabs>


	</div>


@endsection
<script>
    import ProjectsInvites from "../../js/components/projectsInvites";

    export default {
        components: {ProjectsInvites}
    }
</script>