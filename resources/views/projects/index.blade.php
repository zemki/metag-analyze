@extends('layouts.app')

@section('content')
    @if($newsletter)
        <div class="w-full h-auto text-center text-gray-900 bg-green-400 p-4 newsletter transition-all duration-500 ease-in-out opacity-100">
            <p class="w-full">{{__('Would you be interested in receiving e-mails about future features and updates from MeSoftware?')}}</p>
            <button @click="iWantNewsletter('true')" class="sm:m-2 md:mx-1 shadow sm:block sm:w-full md:w-auto md:inline bg-purple-500 hover:bg-purple-400 focus:shadow-outline focus:outline-none text-white font-bold py-2 px-4 rounded" type="button">
                {!!  __('<strong>I want</strong> to receive emails from MeSoftware') !!}
            </button>
            <button @click="iWantNewsletter('false')" class="sm:m-2 md:mx-1 shadow sm:block sm:w-full md:w-auto md:inline bg-purple-500 hover:bg-purple-400 focus:shadow-outline focus:outline-none text-white font-bold py-2 px-4 rounded" type="button">
                {!!  __(' <strong>I don\'t want</strong> to receive emails from MeSoftware')!!}
            </button>
            <p class="block text-blue-500"><a href="https://mesoftware.org/index.php/datenschutzerklaerung-metag/" title="Mesort Privacy Policy" target="_blank">{{__('Privacy Policy')}}</a></p>
        </div>
    @endif

    <div class="mx-auto w-full text-center uppercase font-bold p-2">
        <h4 class="text-4xl font-serif">{{ __("Project you created") }} </h4>
    </div>

    <div class="pb-4">
        <a href="{{url('projects/new')}}">
            <button class="button bg-blue-500 hover:bg-blue-700 text-white mr-2 focus:border-gray-100">
                <i class="px-1">+</i> {{ __('New Project') }}
            </button>
        </a>
    </div>

    <div class="columns is-multiline subpixel-antialiased">
        @forelse($projects as $project)

            <div class="max-w-xs rounded overflow-auto shadow-lg ml-2 mt-2">
                <div class="px-4 py-2">
                    <div class="font-bold text-xl mb-2 block text-center break-words">
                        {{$project->name}}

                    </div>

                    <div class="py-2">
                        <a href="{{url($project->path())}}">
                            <button type="submit"
                                    class="button">

                                {{__('Consult Project')}}
                            </button>
                        </a>
                    </div>
                    <div class="py-2">

                        <a href="{{url($project->path().'/export')}}" title="{{__('from cases that are already closed.')}}">
                            <button class="button is-link is-primary">{{__('Download all the data')}}</button>
                        </a>
                    </div>
                    <div class="py-2">

                        <button class="button is-danger mt-1">
                            <a class="text-gray-100 hover:text-black"
                               @click="confirmDeleteProject({{$project->id}},'{{url('/projects/'.$project->id)}}')">{{__('Delete Project')}}</a>
                        </button>
                    </div>
                </div>
                <div class="px-6 py-4 ">
                    <div class="block bg-gray-200 rounded-full px-3 py-1 text-sm font-semibold text-gray-700"> {{__('Created by')}} {{\App\User::where('id',$project->created_by)->first()->email}}</div>
                    <div class="block bg-gray-200 rounded-full px-3 py-1 text-sm font-semibold text-gray-700">
                        {{__('Cases')}}: {{$project->cases->count()}}</div>
                    <div class="break-words block bg-gray-200 px-3 py-1 text-sm font-semibold text-gray-700">{{$project->description}} </div>

                </div>

            </div>



        @empty
            <div class="column text-center">
                {{__('You don\'t have any personal project.')}}
            </div>
        @endforelse
    </div>


    <div class="columns is-multiline subpixel-antialiased">
        <h4 class="text-4xl mx-auto w-full text-center uppercase font-bold p-2 font-serif">{{ __("Project you're invited to") }} </h4>

        @forelse($invites as $project)

            <div class="column is-4 overflow-auto ">
                <article
                        class="cards-projects items-stretch flex-grow-0 h-56 px-2 border-solid border-4 border-blue-100">
                    <div class="mb-2">
                        <p class="text-2xl font-bold ">
                            <a class="align-middle text-center" href="{{url($project->path())}}">
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
                           @click="confirmLeaveProject({{auth()->user(),$project->id}})">{{__('Leave Project')}}</a>
                    </div>
                </article>
            </div>
        @empty
            <div class="column text-center">
                {{__('You have not received any invite.')}}
            </div>
        @endforelse
    </div>

@endsection
