@extends('layouts.app')

@section('content')


<div class="flex flex-col h-full">
    <div>
        <div class="my-2">
            <h1 class="text-4xl font-extrabold text-gray-900 sm:text-5xl sm:tracking-tight lg:text-6xl">
                {{$project->name}}
            </h1>
            <p class="mt-5 text-xl text-gray-500">{{$project->description}}</p>
        </div>
        <div class="relative z-0 inline-flex mx-auto my-2 rounded-md">
            <a href="{{url($project->path().'/cases/new')}}">
                <button type="button"
                    class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-l-md hover:bg-gray-50 focus:z-10 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">Create
                    Case</button>
            </a>
            <a href="{{url($project->path().'/notifications')}}">
                <button type="button"
                    class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-700 bg-white border border-gray-300 hover:bg-gray-50 focus:z-10 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">Notification
                    Center</button>
            </a>
            <button type="button"
                class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-700 bg-white border border-gray-300 rounded-r-md hover:bg-gray-50 focus:z-10 focus:outline-none focus:ring-1 focus:ring-blue-500 focus:border-blue-500">Download
                all data</button>
        </div>

        <div class="mb-6 border-b border-gray-200">
            <nav class="flex -mb-px" aria-label="Tabs">
                <a href="#" @click="selectedProjectPage = 0"
                    :class="selectedProjectPage == 0? 'w-1/4 px-1 py-4 text-sm font-medium text-center text-black border-b-2 border-blue-500 border-solid hover:text-gray-700 hover:border-gray-300' : 'w-1/4 px-1 py-4 text-sm font-medium text-center text-gray-500 border-b-2 border-transparent border-solid hover:text-gray-700 hover:border-gray-300'">
                    {{__('Cases')}} </a>

                <a href="#" @click="selectedProjectPage = 1"
                    :class="selectedProjectPage == 1? 'w-1/4 px-1 py-4 text-sm font-medium text-center text-black border-b-2 border-blue-500 border-solid hover:text-gray-700 hover:border-gray-300' : 'w-1/4 px-1 py-4 text-sm font-medium text-center text-gray-500 border-b-2 border-transparent border-solid hover:text-gray-700 hover:border-gray-300'">
                    {{__('Inputs')}} </a>

                <a href="#" @click="selectedProjectPage = 2"
                    :class="selectedProjectPage == 2? 'w-1/4 px-1 py-4 text-sm font-medium text-center text-black border-b-2 border-blue-500 border-solid hover:text-gray-700 hover:border-gray-300' : 'w-1/4 px-1 py-4 text-sm font-medium text-center text-gray-500 border-b-2 border-transparent border-solid hover:text-gray-700 hover:border-gray-300'"
                    aria-current="page"> {{__('Invite Collaborator')}} </a>

                <a href="#" @click="selectedProjectPage = 3"
                    :class="selectedProjectPage == 3? 'w-1/4 px-1 py-4 text-sm font-medium text-center text-black border-b-2 border-blue-500 border-solid hover:text-gray-700 hover:border-gray-300' : 'w-1/4 px-1 py-4 text-sm font-medium text-center text-gray-500 border-b-2 border-transparent border-solid hover:text-gray-700 hover:border-gray-300'">
                    Users </a>
            </nav>
        </div>
        <div v-if="selectedProjectPage == 0">
            <div class="flex flex-1 min-h-0 overflow-hidden">

                <!-- Main area -->
                <main class="flex-1 min-w-0 xl:flex">
                    <!-- Cases list-->
                    <aside class="inline-block xl:block xl:flex-shrink-0 xl:order-first">
                        <div class="relative flex flex-col h-full bg-gray-100 border-r border-gray-200 w-96" v-if="">
                            <div class="flex-shrink-0">
                                <div class="flex flex-col justify-center h-16 px-6 bg-white">
                                    <div class="flex items-baseline space-x-3">
                                        <h2 @click="projectPages.currentPage = 0"
                                            :class="projectPages.currentPage == 0 ? 'cursor-pointer text-lg font-medium text-gray-900' : 'cursor-pointer text-lg font-medium text-gray-400'">
                                            {{__('Cases')}}
                                        </h2>
                                        <h2 @click="projectPages.currentPage = 1"
                                            :class="projectPages.currentPage == 1 ? 'cursor-pointer text-lg font-medium text-gray-900' : 'cursor-pointer text-lg font-medium text-gray-400'">
                                            {{__('Notifications')}}
                                        </h2>
                                        <p class="text-sm font-medium text-gray-500">{{$cases->count()}} {{__('Cases')}}
                                        </p>
                                    </div>
                                </div>
                                <div v-if="projectPages.currentPage == 0"
                                    class="px-6 py-2 text-sm font-medium text-gray-500 border-t border-b border-gray-200 bg-gray-50">
                                    Sorted by date</div>
                            </div>
                            <nav v-if="projectPages.currentPage == 0" aria-label="Cases list"
                                class="flex-1 min-h-0 overflow-y-auto">

                                <ul role="list" class="border-b border-gray-200 divide-y divide-gray-200">
                                    @foreach($casesWithEntries as $case)

                                    <li @click="updateSelectedCase({{$case}})"
                                        class="relative px-6 py-5 bg-white even:bg-slate-50 hover:bg-gray-50 focus-within:ring-2 focus-within:ring-inset focus-within:ring-blue-600">
                                        <div class="flex justify-between space-x-3">
                                            <div class="flex-1 min-w-0">
                                                <a href="#" class="block focus:outline-none">
                                                    <span class="absolute inset-0" aria-hidden="true"></span>
                                                    <p class="text-sm font-medium text-gray-900 truncate">
                                                        {{$case->name}}
                                                    </p>
                                                    <p class="text-sm text-gray-500 truncate">
                                                        {{$case->user? $case->user->email : 'no user assigned'}}
                                                    </p>
                                                </a>
                                            </div>
                                            <time datetime="2021-01-27T16:35"
                                                class="flex-shrink-0 text-sm text-gray-500 whitespace-nowrap">{{__('Started')}}:
                                                {{($case->firstDay() ? date("d.m.Y", strtotime($case->firstDay())) : date("d.m.Y", strtotime($case->created_at)))}}</time>
                                        </div>

                                    </li>
                                    @endforeach

                                </ul>
                            </nav>
                        </div>
                    </aside>

                    <notification-center v-if="projectPages.currentPage == 1" :cases="{{$casesWithUsers}}"
                        :notifications="{{$notifications}}" :plannednotifications="{{$plannedNotifications}}"
                        :admin="{{auth()->user()->isAdmin() ? "1" : "0"}}">
                    </notification-center>
                    <selected-case v-if="projectPages.currentPage == 0" :cases="selectedCase" ref="selectedcase">
                    </selected-case>
                </main>
            </div>
        </div>
        <div v-if="selectedProjectPage == 1">
            @if(!$project->isEditable())
            <div class="p-4 mb-4 rounded-md bg-red-50">
                <div class="flex">
                    <div class="flex-shrink-0">
                        <!-- Heroicon name: solid/x-circle -->
                        <svg class="w-5 h-5 text-red-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                            fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd"
                                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-red-800">
                            {{__('You created a case, your project is not editable')}}
                        </h3>
                    </div>
                </div>
            </div>

            @endif

            <edit-project :editable="{{$project->isEditable() ? 'true' : 'false'}}" :project="{{$project}}"
                :projectmedia="{{json_encode($projectmedia)}}"></edit-project>

        </div>
        <div v-if="selectedProjectPage == 2">
            <project-invites class="mt-4" :invitedlist="{{$invites}}" :project="{{$project->id}}"></project-invites>
        </div>

    </div>


</div>
<!--Modal-->
<div class="fixed top-0 left-0 flex items-center justify-center w-full h-auto opacity-0 pointer-events-none modal"
    v-show="newentry.modal">
    <div class="absolute w-full h-full bg-gray-900 opacity-50" @click="toggleModal()"></div>

    <div class="z-50 w-full mx-auto overflow-y-auto bg-white rounded shadow-lg modal-container md:max-w-md">

        <div @click="toggleModal()"
            class="absolute top-0 right-0 z-50 flex flex-col items-center mt-4 mr-4 text-sm text-white cursor-pointer">
            <svg class="text-white fill-current" xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                viewBox="0 0 18 18">
                <path
                    d="M14.53 4.53l-1.06-1.06L9 7.94 4.53 3.47 3.47 4.53 7.94 9l-4.47 4.47 1.06 1.06L9 10.06l4.47 4.47 1.06-1.06L10.06 9z">
                </path>
            </svg>
            <span class="text-sm">(Esc)</span>
        </div>

        <!-- Add margin if you want to see some of the overlay behind the modal-->
        <div class="w-auto h-auto px-6 py-4 text-left modal-content">
            <!--Title-->
            <div class="flex items-center justify-between pb-3">
                <p class="text-2xl font-bold">{{__('Add Entry')}}</p>
                <div @click="toggleModal()" class="z-50 cursor-pointer">
                    <svg class="text-black fill-current" xmlns="http://www.w3.org/2000/svg" width="18" height="18"
                        viewBox="0 0 18 18">
                        <path
                            d="M14.53 4.53l-1.06-1.06L9 7.94 4.53 3.47 3.47 4.53 7.94 9l-4.47 4.47 1.06 1.06L9 10.06l4.47 4.47 1.06-1.06L10.06 9z">
                        </path>
                    </svg>
                </div>
            </div>
            <p class="p-3 mt-3 font-bold text-center text-black bg-yellow-500">
                {{__('Please scroll if you don\'t see all the content.')}}
                {{__('To see the changes remember to reload the page.')}}
            </p>

            <!--Body-->
            <input type="hidden" :value="newentry.case_id" />
            <div class="my-2">
                <label class="text-base font-bold tracking-wide text-gray-700 uppercase">
                    Start Date/time *
                </label>
                <b-datetimepicker :placeholder="trans('Click to select...')" icon="calendar-today" name="begin"
                    v-model="newentry.data.start" @input="newentrydateselected('')">
                </b-datetimepicker>
            </div>
            <div class="my-2">

                <label class="text-base font-bold tracking-wide text-gray-700 uppercase">
                    End Date/time *
                </label>
                <b-datetimepicker :placeholder="trans('Click to select...')" icon="calendar-today" name="end"
                    v-model="newentry.data.end">
                </b-datetimepicker>
            </div>
            <div class="my-2">
                <label class="text-base font-bold tracking-wide text-gray-700 uppercase">
                    Media *
                </label>
                <input type="text" name="media_id" v-model="newentry.data.media_id"
                    class="block w-full px-4 py-2 leading-normal bg-white border border-gray-300 rounded-lg appearance-none focus:outline-none focus:ring"></input>

            </div>
            <h1 class="my-4 text-2xl font-bold tracking-wide text-center text-gray-700 uppercase">Inputs</h1>
            <div v-for="(value) in newentry.inputs">
                <label class="my-2 text-base font-bold tracking-wide text-gray-700 uppercase"
                    v-text="value.mandatory ? value.name +' *' : value.name">

                </label>
                <input type="text" v-if="value.type === 'text'" :name="'text'+value.name"
                    v-model="newentry.data.inputs[value.name]"
                    class="block w-full px-4 py-2 leading-normal bg-white border border-gray-300 rounded-lg appearance-none focus:outline-none focus:ring "></input>
                <b-taginput :data=value.answers autocomplete size="is-medium" open-on-focus
                    v-if="value.type === 'multiple choice'" v-model="newentry.data.inputs[value.name]">
                </b-taginput>
                <div class="relative" v-if="value.type === 'one choice'">
                    <select v-model="newentry.data.inputs[value.name]"
                        class="block w-full px-4 py-3 pr-8 leading-tight text-gray-700 bg-gray-200 border border-gray-200 rounded appearance-none focus:outline-none focus:bg-white focus:border-gray-500">
                        <option v-for="answer in value.answers" :value="answer">@{{answer}}</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center px-2 text-gray-700 pointer-events-none">
                        <svg class="w-4 h-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                            <path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z" />
                        </svg>
                    </div>
                </div>

                <div class="relative" v-if="value.type === 'scale'">
                    <select v-model="newentry.data.inputs[value.name]"
                        class="block w-full px-4 py-3 pr-8 leading-tight text-gray-700 bg-gray-200 border border-gray-200 rounded appearance-none focus:outline-none focus:bg-white focus:border-gray-500">
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                        <option value="5">5</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center px-2 text-gray-700 pointer-events-none">
                        <svg class="w-4 h-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                            <path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z" />
                        </svg>
                    </div>
                </div>

            </div>
            <div class="my-3 text-base">* {{__('required')}}</div>


            <!--Footer-->
            <div class="flex justify-end pt-2">
                <button
                    class="p-3 px-4 mr-2 text-blue-500 bg-transparent rounded-lg hover:bg-gray-100 hover:text-blue-400"
                    @click="entrySaveAndClose()">{{__('Save and Close')}}</button>
                <button
                    class="p-3 px-4 mr-2 text-blue-500 bg-transparent rounded-lg hover:bg-gray-100 hover:text-blue-400"
                    @click="entrySaveAndNewEntry()">{{__('Save and add new Entry')}}</button>
                <button class="p-3 px-4 text-white bg-blue-500 rounded-lg hover:bg-blue-400"
                    @click="toggleModal()">{{__('Close')}}</button>
            </div>

        </div>
    </div>
</div>
<!--End Modal-->

<h1 class="font-serif text-4xl font-bold break-words">{{$project->name}}</h1>

<p class="my-4 break-words">
    {{$project->description}}
</p>

<div class="block">
    <div class="inline">
        <a href="{{url($project->path().'/cases/new')}}">
            <button
                class="px-4 py-2 font-bold text-white bg-green-500 rounded hover:bg-green-700">{{__('Create Case')}}</button>
        </a>

    </div>
    <div class="inline">
        <a href="{{url($project->path().'/export')}}" title="{{__('from cases that are already closed.')}}">
            <button
                class="px-4 py-2 font-bold text-white bg-green-500 rounded hover:bg-green-700">{{__('Download all the data from this project')}}</button>
        </a>
    </div>

    <div class="inline">
        <a href="{{url($project->path().'/notifications')}}">
            <button
                class="px-4 py-2 font-bold text-white bg-green-500 rounded hover:bg-green-700">{{__('Notification Center')}}</button>
        </a>
    </div>

</div>

<b-tabs class="block w-full" expanded>
    <b-tab-item label="{{__('Cases List')}}">
        <div class="flex flex-wrap w-full">
            @forelse($cases as $key => $case)

            <div
                class="w-1/4 px-2 py-2 mt-2 overflow-hidden text-center border border-gray-400 border-solid rounded-none">
                <div class="flex items-center mb-2 text-xl font-bold">

                    @if(!$case->isConsultable())
                    <svg class="w-3 mb-2 mr-2 text-2xl font-medium leading-tight text-gray-500 fill-current"
                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                        <path
                            d="M4 8V6a6 6 0 1 1 12 0v2h1a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2H3a2 2 0 0 1-2-2v-8c0-1.1.9-2 2-2h1zm5 6.73V17h2v-2.27a2 2 0 1 0-2 0zM7 6v2h6V6a3 3 0 0 0-6 0z" />
                    </svg>
                    @endif
                    {{$case->name}}

                </div>
                <div class="py-2">
                    @if($case->isBackend())
                    @if($case->entries()->count() > 0)
                    <div class="mb-2">
                        <a href="{{$project->id.$case->distinctpath()}}" class="no-underline">
                            <button
                                class="block px-4 py-2 font-bold text-white no-underline bg-blue-500 rounded hover:bg-blue-700">
                                {{__('Distinct Entries Graph')}}
                            </button>
                        </a>
                    </div>
                    @if($project->inputs != "[]")
                    <div class="mb-2">
                        <a href="{{$project->id.$case->groupedEntriesPath()}}" class="no-underline">
                            <button
                                class="block px-4 py-2 font-bold text-white no-underline bg-blue-500 rounded hover:bg-blue-700">
                                {{__('Grouped Entries Graph')}}
                            </button>
                        </a>
                    </div>
                    @endif
                    @endif
                    <div class="mb-2">
                        <a @click.prevent="toggleModal({{$case->id}},{{$project->inputs}})" class="no-underline">
                            <button
                                class="block px-4 py-2 font-bold text-white no-underline bg-blue-500 rounded hover:bg-blue-700">
                                {{__('Add Entries')}}
                            </button>
                        </a>
                    </div>
                    @elseif(!$case->notYetStarted() && $case->entries()->count() == 0)
                    <p>{{__('Time has passed and user didn\'t register any entry')}}</p>
                    @elseif($case->notYetStarted())
                    <p>{{__('Case is not yet started.')}}</p>
                    @elseif($case->isConsultable() && $case->entries()->count() > 0)
                    <a href="{{$project->id.$case->distinctpath()}}" class="mb-2 no-underline">
                        <button
                            class="block px-4 py-2 mb-2 font-bold text-white no-underline bg-blue-500 rounded hover:bg-blue-700">
                            {{__('Distinct Entries Graph')}}
                        </button>
                    </a>
                    @if($project->inputs != "[]")
                    <a href="{{$project->id.$case->groupedEntriesPath()}}" class="no-underline">
                        <button
                            class="block px-4 py-2 font-bold text-white no-underline bg-blue-500 rounded hover:bg-blue-700">
                            {{__('Grouped Entries Graph')}}
                        </button>
                    </a>
                    @endif
                    @elseif(!$case->isConsultable() && $case->entries()->count() > 0 && !$case->notYetStarted())
                    <p>{{__('User is entering the data')}}</p>
                    @endif
                </div>


                @if($case->isConsultable() && $case->entries()->count() > 0)
                <div class="py-2">
                    <a href="{{url('cases/'.$case->id.'/export')}}" target="_blank">
                        <button class="block px-2 py-2 font-bold text-white bg-blue-500 rounded hover:bg-blue-700">
                            {{__('Download Case Data as xlsx')}}
                        </button>
                    </a>
                </div>
                @endif
                @if($case->isConsultable() && $case->files()->count() > 0 || (auth()->user()->isAdmin()))
                <div class="py-2">
                    <a href="{{url('cases/'.$case->id.'/files')}}" target="_blank">
                        <button class="block px-2 py-2 font-bold text-white bg-blue-500 rounded hover:bg-blue-700">
                            {{__('Check Files')}}
                        </button>
                    </a>
                </div>
                @endif

                <div class="py-2">
                    <button type="submit" class="block text-white bg-red-600 button"
                        @click="confirmdeletecase('{{url('/cases/'.$case->id)}}')">
                        {{__('Delete Case')}}
                    </button>


                </div>
                <div class="px-2 py-2">
                    <div class="block px-3 py-1 text-sm font-semibold text-gray-700 bg-gray-200 rounded-full">
                        {{__('User')}}: {{$case->user? $case->user->email : 'no user assigned'}}
                    </div>
                    <div class="block px-3 py-1 text-sm font-semibold text-gray-700 bg-gray-200 rounded-full">
                        {{__('Entries')}}: {{$case->entries->count()}}
                    </div>
                    <div class="block px-3 py-1 text-sm font-semibold text-gray-700 bg-gray-200 rounded-full">
                        @if($case->isBackend())
                        {{__('No last day.')}}
                        @else
                        {{__('Last day')}}
                        : {{ $case->lastDay() == "" ? __('Case not started') : $case->lastDay() }}
                        @endif
                    </div>
                </div>
            </div>

            @empty
            <p class="mt-2">{{__('There are no cases for this project')}}</p>
            @endforelse
        </div>
    </b-tab-item>

    <b-tab-item label="Inputs">

        @if(!$project->isEditable())
        <b-notification :active.sync="mainNotification" aria-close-label="Close notification" type="is-danger"
            role="alert">
            {{__('You created a case, project is not editable')}}
        </b-notification>

        @endif

        <edit-project :editable="{{$project->isEditable() ? 'true' : 'false'}}" :project="{{$project}}"
            :projectmedia="{{json_encode($projectmedia)}}"></edit-project>
    </b-tab-item>

    @if($project->created_by == auth()->user()->id || auth()->user()->isAdmin())
    <b-tab-item label="Invites">
        <project-invites :invitedlist="{{$invites}}" :project="{{$project->id}}"></project-invites>
    </b-tab-item>
    @endif
    <b-tab-item label="Users">
        <users-in-cases :cases="{{$casesWithUsers}}"></users-in-cases>
    </b-tab-item>
</b-tabs>
@endsection