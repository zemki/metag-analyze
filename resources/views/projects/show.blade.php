@extends('layouts.app')

@section('content')



    <div class="content">
        <div class="block"><h1 class="break-words">{{$project->name}}</h1></div>
        <div class="break-words">
            <p>
                {{$project->description}}
            </p>
        </div>
        <div class="field mt-2">
            <div class="control">
                <a href="{{url($project->path().'/cases/new')}}">
                    <button class="button is-link is-primary">Create Case</button>
                </a>
            </div>
        </div>

        <b-tabs class="block w-full" expanded>
            <b-tab-item label="Cases List">
                <div class="columns is-multiline subpixel-antialiased">
                    @forelse($project->cases as $case)

                        <div class="max-w-xs rounded overflow-hidden shadow-lg ml-2 mt-2">
                            <div class="px-4 py-2">
                                <div class="font-bold text-xl mb-2 flex items-center">
                                    @if(!$case->isConsultable())
                                        <svg class="fill-current text-gray-500 w-3 h-3 mr-2"
                                             xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                            <path d="M4 8V6a6 6 0 1 1 12 0v2h1a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2v-8c0-1.1.9-2 2-2h1zm5 6.73V17h2v-2.27a2 2 0 1 0-2 0zM7 6v2h6V6a3 3 0 0 0-6 0z"/>
                                        </svg>
                                    @endif
                                    {{$case->name}}

                                </div>
                                <p class="text-gray-700 text-base">
                                    <a href="{{$case->isConsultable() && $case->entries()->count() > 0 ? $project->id.$case->path() : '#' }}">
                                    <button
                                            class="button text-gray-700 {{$case->isConsultable() ? '' : 'opacity-50 cursor-not-allowed'}}">
                                            {{$case->isConsultable() && $case->entries()->count() > 0 ? 'View
                                            Entries' : 'User is sending the data . . .'}}
                                       </button>
                                    </a>

                                <form action="" method="POST">
                                    {{ csrf_field() }}
                                    {{ method_field('DELETE') }}
                                </form>
                                <button type="submit" class="button is-danger text-white"
                                        @click="confirmdeletecase('{{url($project->path().'/cases/'.$case->id)}}')">
                                    Delete Case
                                </button>

                                </p>
                            </div>
                            <div class="px-6 py-4 ">
                                <div class="block bg-gray-200 rounded-full px-3 py-1 text-sm font-semibold text-gray-700">
                                    User: {{$case->user? $case->user->email : 'no user assigned'}}</div>
                                <div class="block bg-gray-200 rounded-full px-3 py-1 text-sm font-semibold text-gray-700">
                                    Entries: {{$case->entries->count()}}</div>
                                <div class="block bg-gray-200 rounded-full px-3 py-1 text-sm font-semibold text-gray-700">
                                    Last day: {{ $case->lastDay() }}</div>
                            </div>
                        </div>

                    @empty
                        <p class="mt-2">There are no cases for this project</p>
                    @endforelse
                </div>
            </b-tab-item>

            <b-tab-item label="Inputs">

                @if(!$project->isEditable())
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
