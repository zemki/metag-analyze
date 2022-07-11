@extends('layouts.app')

@section('content')

<div class="flex flex-col h-full">


    <!-- Bottom section -->
    <div class="flex flex-1 min-h-0 overflow-hidden">


        <!-- Main area -->
        <main class="flex-1 min-w-0 xl:flex">
            <section aria-labelledby="message-heading"
                class="flex flex-col flex-1 h-full min-w-0 overflow-hidden xl:order-last">
                <!-- Top section -->
                <div class="flex-shrink-0 bg-white border-b border-gray-200">
                    <!-- Toolbar-->
                    <div class="flex flex-col justify-center h-16">
                        <div class="px-4 sm:px-6 lg:px-8">
                            <div class="flex justify-between py-3">
                                <!-- Left buttons -->
                                <div>
                                    <div
                                        class="relative z-0 inline-flex rounded-md shadow-sm sm:shadow-none sm:space-x-3">
                                        <span class="inline-flex sm:shadow-sm">
                                            <button type="button"
                                                class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-300 rounded-l-md hover:bg-gray-50 focus:z-10 focus:outline-none focus:ring-1 focus:ring-blue-600 focus:border-blue-600">
                                                <!-- Heroicon name: solid/reply -->
                                                <svg class="mr-2.5 h-5 w-5 text-gray-400"
                                                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                                    fill="currentColor" aria-hidden="true">
                                                    <path fill-rule="evenodd"
                                                        d="M7.707 3.293a1 1 0 010 1.414L5.414 7H11a7 7 0 017 7v2a1 1 0 11-2 0v-2a5 5 0 00-5-5H5.414l2.293 2.293a1 1 0 11-1.414 1.414l-4-4a1 1 0 010-1.414l4-4a1 1 0 011.414 0z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                                <span>Reply</span>
                                            </button>
                                            <button type="button"
                                                class="relative items-center hidden px-4 py-2 -ml-px text-sm font-medium text-gray-900 bg-white border border-gray-300 sm:inline-flex hover:bg-gray-50 focus:z-10 focus:outline-none focus:ring-1 focus:ring-blue-600 focus:border-blue-600">
                                                <!-- Heroicon name: solid/pencil -->
                                                <svg class="mr-2.5 h-5 w-5 text-gray-400"
                                                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                                    fill="currentColor" aria-hidden="true">
                                                    <path
                                                        d="M13.586 3.586a2 2 0 112.828 2.828l-.793.793-2.828-2.828.793-.793zM11.379 5.793L3 14.172V17h2.828l8.38-8.379-2.83-2.828z" />
                                                </svg>
                                                <span>Note</span>
                                            </button>
                                            <button type="button"
                                                class="relative items-center hidden px-4 py-2 -ml-px text-sm font-medium text-gray-900 bg-white border border-gray-300 sm:inline-flex rounded-r-md hover:bg-gray-50 focus:z-10 focus:outline-none focus:ring-1 focus:ring-blue-600 focus:border-blue-600">
                                                <!-- Heroicon name: solid/user-add -->
                                                <svg class="mr-2.5 h-5 w-5 text-gray-400"
                                                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                                    fill="currentColor" aria-hidden="true">
                                                    <path
                                                        d="M8 9a3 3 0 100-6 3 3 0 000 6zM8 11a6 6 0 016 6H2a6 6 0 016-6zM16 7a1 1 0 10-2 0v1h-1a1 1 0 100 2h1v1a1 1 0 102 0v-1h1a1 1 0 100-2h-1V7z" />
                                                </svg>
                                                <span>Assign</span>
                                            </button>
                                        </span>

                                        <span class="hidden space-x-3 lg:flex">
                                            <button type="button"
                                                class="relative items-center hidden px-4 py-2 -ml-px text-sm font-medium text-gray-900 bg-white border border-gray-300 rounded-md sm:inline-flex hover:bg-gray-50 focus:z-10 focus:outline-none focus:ring-1 focus:ring-blue-600 focus:border-blue-600">
                                                <!-- Heroicon name: solid/archive -->
                                                <svg class="mr-2.5 h-5 w-5 text-gray-400"
                                                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                                    fill="currentColor" aria-hidden="true">
                                                    <path d="M4 3a2 2 0 100 4h12a2 2 0 100-4H4z" />
                                                    <path fill-rule="evenodd"
                                                        d="M3 8h14v7a2 2 0 01-2 2H5a2 2 0 01-2-2V8zm5 3a1 1 0 011-1h2a1 1 0 110 2H9a1 1 0 01-1-1z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                                <span>Archive</span>
                                            </button>
                                            <button type="button"
                                                class="relative items-center hidden px-4 py-2 -ml-px text-sm font-medium text-gray-900 bg-white border border-gray-300 rounded-md sm:inline-flex hover:bg-gray-50 focus:z-10 focus:outline-none focus:ring-1 focus:ring-blue-600 focus:border-blue-600">
                                                <!-- Heroicon name: solid/folder-download -->
                                                <svg class="mr-2.5 h-5 w-5 text-gray-400"
                                                    xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                                    fill="currentColor" aria-hidden="true">
                                                    <path fill-rule="evenodd"
                                                        d="M4 4a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2V8a2 2 0 00-2-2h-5L9 4H4zm7 5a1 1 0 10-2 0v1.586l-.293-.293a1 1 0 10-1.414 1.414l2 2 .002.002a.997.997 0 001.41 0l.002-.002 2-2a1 1 0 00-1.414-1.414l-.293.293V9z" />
                                                </svg>
                                                <span>Move</span>
                                            </button>
                                        </span>

                                        <div class="relative block -ml-px sm:shadow-sm lg:hidden">
                                            <div>
                                                <button type="button"
                                                    class="relative inline-flex items-center px-2 py-2 text-sm font-medium text-gray-900 bg-white border border-gray-300 rounded-r-md hover:bg-gray-50 focus:z-10 focus:outline-none focus:ring-1 focus:ring-blue-600 focus:border-blue-600 sm:rounded-md sm:px-3"
                                                    id="menu-2-button" aria-expanded="false" aria-haspopup="true">
                                                    <span class="sr-only sm:hidden">More</span>
                                                    <span class="hidden sm:inline">More</span>
                                                    <!-- Heroicon name: solid/chevron-down -->
                                                    <svg class="w-5 h-5 text-gray-400 sm:ml-2 sm:-mr-1"
                                                        xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                                        fill="currentColor" aria-hidden="true">
                                                        <path fill-rule="evenodd"
                                                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                            clip-rule="evenodd" />
                                                    </svg>
                                                </button>
                                            </div>

                                        </div>
                                    </div>
                                </div>

                                <!-- Right buttons -->
                                <nav aria-label="Pagination">
                                    <span class="relative z-0 inline-flex rounded-md shadow-sm">
                                        <a href="#"
                                            class="relative inline-flex items-center px-4 py-2 text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-l-md hover:bg-gray-50 focus:z-10 focus:outline-none focus:ring-1 focus:ring-blue-600 focus:border-blue-600">
                                            <span class="sr-only">Next</span>
                                            <!-- Heroicon name: solid/chevron-up -->
                                            <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                                fill="currentColor" aria-hidden="true">
                                                <path fill-rule="evenodd"
                                                    d="M14.707 12.707a1 1 0 01-1.414 0L10 9.414l-3.293 3.293a1 1 0 01-1.414-1.414l4-4a1 1 0 011.414 0l4 4a1 1 0 010 1.414z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </a>
                                        <a href="#"
                                            class="relative inline-flex items-center px-4 py-2 -ml-px text-sm font-medium text-gray-500 bg-white border border-gray-300 rounded-r-md hover:bg-gray-50 focus:z-10 focus:outline-none focus:ring-1 focus:ring-blue-600 focus:border-blue-600">
                                            <span class="sr-only">Previous</span>
                                            <!-- Heroicon name: solid/chevron-down -->
                                            <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20"
                                                fill="currentColor" aria-hidden="true">
                                                <path fill-rule="evenodd"
                                                    d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                        </a>
                                    </span>
                                </nav>
                            </div>
                        </div>
                    </div>
                    <!-- Message header -->
                </div>

                <!-- 
                change this in VUE to reflect the data from the case
                we can check the status of a case by checking the label that is now "open"
                
                -->
                <div class="flex-1 min-h-0 overflow-y-auto">
                    <div class="pt-5 pb-6 bg-white shadow">
                        <div class="px-4 sm:flex sm:justify-between sm:items-baseline sm:px-6 lg:px-8">
                            <div class="sm:w-0 sm:flex-1">
                                <h1 id="message-heading" class="text-lg font-medium text-gray-900">Re: New pricing for
                                    existing customers</h1>
                                <p class="mt-1 text-sm text-gray-500 truncate">joearmstrong@example.com</p>
                            </div>

                            <div
                                class="flex items-center justify-between mt-4 sm:mt-0 sm:ml-6 sm:flex-shrink-0 sm:justify-start">
                                <span
                                    class="inline-flex items-center px-3 py-0.5 rounded-full text-sm font-medium bg-cyan-100 text-cyan-800">
                                    Open
                                </span>

                            </div>
                        </div>
                    </div>
                    <!-- Thread section-->
                    <ul role="list" class="py-4 space-y-2 sm:px-6 sm:space-y-4 lg:px-8">
                        <li class="px-4 py-6 bg-white shadow sm:rounded-lg sm:px-6">
                            <div class="sm:flex sm:justify-between sm:items-baseline">
                                <h3 class="text-base font-medium">
                                    <span class="text-gray-900">Joe Armstrong</span>
                                    <span class="text-gray-600">wrote</span>
                                </h3>
                                <p class="mt-1 text-sm text-gray-600 whitespace-nowrap sm:mt-0 sm:ml-3">
                                    <time datetime="2021-01-28T19:24">Yesterday at 7:24am</time>
                                </p>
                            </div>
                            <div class="mt-4 space-y-6 text-sm text-gray-800">
                                <p>Thanks so much! Can't wait to try it out.</p>
                            </div>
                        </li>
                    </ul>
                </div>
            </section>

            <!-- Cases list-->
            <aside class="hidden xl:block xl:flex-shrink-0 xl:order-first">
                <div class="relative flex flex-col h-full bg-gray-100 border-r border-gray-200 w-96">
                    <div class="flex-shrink-0">
                        <div class="flex flex-col justify-center h-16 px-6 bg-white">
                            <div class="flex items-baseline space-x-3">
                                <h2 class="text-lg font-medium text-gray-900">{{__('Cases')}}</h2>
                                <p class="text-sm font-medium text-gray-500">{{$cases->count()}} {{__('Cases')}}</p>
                            </div>
                        </div>
                        <div
                            class="px-6 py-2 text-sm font-medium text-gray-500 border-t border-b border-gray-200 bg-gray-50">
                            Sorted by date</div>
                    </div>
                    <nav aria-label="Cases list" class="flex-1 min-h-0 overflow-y-auto">
                        <ul role="list" class="border-b border-gray-200 divide-y divide-gray-200">
                            @foreach($cases as $case)
                            <li
                                class="relative px-6 py-5 bg-white hover:bg-gray-50 focus-within:ring-2 focus-within:ring-inset focus-within:ring-blue-600">
                                <div class="flex justify-between space-x-3">
                                    <div class="flex-1 min-w-0">
                                        <a href="#" class="block focus:outline-none">
                                            <span class="absolute inset-0" aria-hidden="true"></span>
                                            <p class="text-sm font-medium text-gray-900 truncate">{{$case->name}}</p>
                                            <p class="text-sm text-gray-500 truncate">
                                                {{$case->user? $case->user->email : 'no user assigned'}}
                                            </p>
                                        </a>
                                    </div>
                                    <time datetime="2021-01-27T16:35"
                                        class="flex-shrink-0 text-sm text-gray-500 whitespace-nowrap">1d ago</time>
                                </div>
                                <div class="mt-1">
                                    <p class="text-sm text-gray-600 line-clamp-2">Doloremque dolorem maiores assumenda
                                        dolorem facilis. Velit vel in a rerum natus facere. Enim rerum eaque qui
                                        facilis. Numquam laudantium sed id dolores omnis in. Eos reiciendis deserunt
                                        maiores et accusamus quod dolor.</p>
                                </div>
                            </li>
                            @endforeach

                        </ul>
                    </nav>
                </div>
            </aside>
        </main>
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