@extends('layouts.app')

@section('content')
<div class="flex-grow w-full mx-auto max-w-7xl xl:px-8 lg:flex">
    <div class="flex-1 min-w-0 bg-white xl:flex">
        <div class="bg-white border-r xl:flex-shrink-0 xl:w-64 xl:border-gray-200">
            <div class="py-6 pl-4 pr-6 sm:pl-6 lg:pl-8 xl:pl-0">
                <div class="flex items-center justify-between">
                    <div class="flex-1 space-y-8">
                        <div
                            class="space-y-8 sm:space-y-0 sm:flex sm:justify-between sm:items-center xl:block xl:space-y-8">
                            <div class="flex items-center space-x-3">
                                <div class="flex-shrink-0 w-12 h-12">
                                    <img alt="{{__('Your Gravatar User Profile')}}" class="w-12 h-12 rounded-full"
                                        src="{{\Gravatar::get(Auth::user()->email)}}" alt="">
                                </div>
                                <div class="w-32 space-x-1 break-words">
                                    <div class="text-sm font-medium text-gray-900"><span
                                            class="sr-only">{{__('Your Email')}}</span>{{
                    Auth::user()->email }}</div>
                                </div>
                            </div>

                            <div class="flex flex-col sm:flex-row xl:flex-col">
                                <a title="{{__('Create a new Project')}}"
                                    href="{{session('hasReachMaxNumberOfStudies') ? '#' : url('/projects/new')}}"><button
                                        type="button" class="{{session('hasReachMaxNumberOfStudies') ? 'pointer-events-none select-none cursor-not-allowed opacity-50 inline-flex items-center justify-center px-4 py-2 mt-3 text-sm font-medium text-white bg-blue-500 border border-gray-300 rounded-md shadow-sm hover:bg-blue-700 hover:text-gray-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 xl:ml-0 xl:mt-3 xl:w-full'
                                    : 'inline-flex items-center justify-center px-4 py-2 mt-3 text-sm font-medium hover:text-gray-200 text-white bg-blue-500 border border-gray-300 rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 xl:ml-0 xl:mt-3 xl:w-full'}}
                            ">
                                        {{__('New Project')}}
                                    </button></a>
                            </div>
                        </div>
                        <div
                            class="flex flex-col space-y-6 sm:flex-row sm:space-y-0 sm:space-x-8 xl:flex-col xl:space-x-0 xl:space-y-6">
                            <div class="flex items-center space-x-2">
                                <svg class="w-5 h-5 text-gray-400" xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path fill-rule="evenodd"
                                        d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                        clip-rule="evenodd" />
                                </svg>
                                <span class="text-sm font-medium text-gray-500">Free Member</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <svg class="w-5 h-5 text-gray-400" xmlns="http://www.w3.org/2000/svg"
                                    viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                    <path
                                        d="M7 3a1 1 0 000 2h6a1 1 0 100-2H7zM4 7a1 1 0 011-1h10a1 1 0 110 2H5a1 1 0 01-1-1zM2 11a2 2 0 012-2h12a2 2 0 012 2v4a2 2 0 01-2 2H4a2 2 0 01-2-2v-4z" />
                                </svg>
                                <span class="text-sm font-medium text-gray-500" aria-valuenow="{{count($projects)}}"
                                    aria-valuemax="15">{{count($projects)}}/
                                    {{(config('utilities.maxNumberOfStudies'))}}
                                    {{('Projects')}}</span>
                            </div>
                            @if(session('hasReachMaxNumberOfStudies'))
                            <h2 class="items-center w-full p-3 mr-4 text-base text-white bg-red-700">
                                {{__('You have reached the max number of projects! Contact us for solutions!')}}
                            </h2>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Studies List -->
        <div class="bg-white lg:min-w-0 lg:flex-1">

            @if(count($projects) === 0 && count($invited_projects) === 0)


            <div class="mt-12 text-center">
                <svg class="w-12 h-12 mx-auto text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"
                    aria-hidden="true">
                    <path vector-effect="non-scaling-stroke" stroke-linecap="round" stroke-linejoin="round"
                        stroke-width="2"
                        d="M9 13h6m-3-3v6m-9 1V7a2 2 0 012-2h6l2 2h6a2 2 0 012 2v8a2 2 0 01-2 2H5a2 2 0 01-2-2z" />
                </svg>
                <h3 class="mt-2 text-sm font-medium text-gray-900">{{__('No projects')}}</h3>
                <p class="mt-1 text-sm text-gray-500">{{__('Get started by creating a new project.')}}</p>
                <div class="mt-6">
                    <a title="{{__('Create a new Project')}}" href="{{url('/projects/new')}}">
                        <button type="button"
                            class="inline-flex items-center px-4 py-2 text-sm font-medium text-white bg-blue-500 border border-transparent rounded-md shadow-sm hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500">
                            <svg class="w-5 h-5 mr-2 -ml-1" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                fill="currentColor" aria-hidden="true">
                                <path fill-rule="evenodd"
                                    d="M10 3a1 1 0 011 1v5h5a1 1 0 110 2h-5v5a1 1 0 11-2 0v-5H4a1 1 0 110-2h5V4a1 1 0 011-1z"
                                    clip-rule="evenodd" />
                            </svg>
                            {{__('New Project')}}
                        </button>
                    </a>
                </div>
            </div>
        </div>
        @else
        <projects-list projects="{{$projects}}" user="{{auth()->user()}}"></projects-list>
        @endif
    </div>

</div>
</div>



@endsection