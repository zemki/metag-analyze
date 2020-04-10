@extends('layouts.app')

@section('content')

        <h1 class="break-words text-4xl font-bold font-serif">{{$project->name}}</h1>

            <p class="break-words my-4">
                {{$project->description}}
            </p>

        <div class="block">
            <div class="inline">
                <a href="{{url($project->path().'/cases/new')}}">
                    <button class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">{{__('Create Case')}}</button>
                </a>

            </div>
            <div class="inline">
                <a href="{{url($project->path().'/export')}}" title="{{__('from cases that are already closed.')}}">
                    <button class="bg-green-500 hover:bg-green-700 text-white font-bold py-2 px-4 rounded">{{__('Download all the data from this project')}}</button>
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
                                        <svg class="fill-current text-gray-500 w-3 mb-2 font-medium leading-tight text-2xl mr-2"
                                             xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                            <path d="M4 8V6a6 6 0 1 1 12 0v2h1a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2v-8c0-1.1.9-2 2-2h1zm5 6.73V17h2v-2.27a2 2 0 1 0-2 0zM7 6v2h6V6a3 3 0 0 0-6 0z"/>
                                        </svg>
                                    @endif
                                    {{$case->name}}

                                </div>
                                <div class="py-2">
                                    <a href="{{$case->isConsultable() && $case->entries()->count() > 0 ? $project->id.$case->path() : '#' }}">
                                        <button
                                                class="block button text-gray-700 {{$case->isConsultable() ? '' : 'opacity-50 cursor-not-allowed'}}">
                                            {{$case->isConsultable() && $case->entries()->count() > 0 ? trans('View Entries') : trans('User is sending the data . . .')}}
                                        </button>
                                    </a>
                                </div>


                                @if($case->isConsultable() && $case->entries()->count() > 0)
                                    <div class="py-2">
                                        <a href="{{url('cases/'.$case->id.'/export')}}" target="_blank">
                                            <button class="block  bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-2 rounded">
                                                {{__('Download')}}
                                            </button>
                                        </a>
                                    </div>
                                @endif

                                <div class="py-2">
                                    <button type="submit" class="block button is-danger text-white"
                                            @click="confirmdeletecase('{{url('/cases/'.$case->id)}}')">
                                        {{__('Delete Case')}}
                                    </button>
                                </div>


                            </div>
                            <div class="px-6 py-4 ">
                                <div class="block bg-gray-200 rounded-full px-3 py-1 text-sm font-semibold text-gray-700">
                                    {{__('User')}}: {{$case->user? $case->user->email : 'no user assigned'}}</div>
                                <div class="block bg-gray-200 rounded-full px-3 py-1 text-sm font-semibold text-gray-700">
                                    {{__('Entries')}}: {{$case->entries->count()}}</div>
                                <div class="block bg-gray-200 rounded-full px-3 py-1 text-sm font-semibold text-gray-700">
                                    {{__('Last day')}}: {{ $case->lastDay() }}</div>
                            </div>
                        </div>

                    @empty
                        <p class="mt-2">{{__('There are no cases for this project')}}</p>
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
                        {{__('You created a case, project is not editable')}}
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


@endsection
