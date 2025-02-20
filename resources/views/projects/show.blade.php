@extends('layouts.app')

@section('content')
    @include('layouts.breadcrumb')

    <div class="flex flex-col h-full p-4">
        <!-- Project Header -->
        <div class="my-4">
            <h1 class="text-4xl font-extrabold text-gray-900 sm:text-5xl lg:text-6xl">
                {{$project->name}}
            </h1>
            <p class="mt-2 text-xl text-gray-500 break-words">{{$project->description}}</p>
        </div>

        <!-- Action Buttons -->
        <div class="flex space-x-2 my-4">
            <a href="{{url($project->path().'/cases/new')}}">
                <button type="button"
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-500 rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    {{trans('Create Case')}}
                </button>
            </a>
            <a href="{{url($project->path().'/notifications')}}">
                <button type="button"
                        class="px-4 py-2 text-sm font-medium text-blue-500 bg-white border border-blue-500 rounded-md hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    {{trans('Notification Center')}}
                </button>
            </a>
            <a href="{{url($project->path().'/export')}}" title="{{trans('from cases that are already closed.')}}">
                <button type="button"
                        class="px-4 py-2 text-sm font-medium text-white bg-blue-500 border border-transparent rounded-md hover:bg-blue-600 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    {{trans('Download all data')}}
                </button>
            </a>
        </div>

        <!-- Tabs Navigation -->
        <div class="border-b border-blue-500">
            <nav class="flex -mb-px" aria-label="Tabs">
                <a href="#" @click="selectedProjectPage = 0"
                   :class="selectedProjectPage == 0 ? 'flex-1 px-4 py-2 text-center text-blue-500 border-b-2 border-blue-500 font-medium' : 'flex-1 px-4 py-2 text-center text-gray-500 border-b-2 border-transparent font-medium hover:text-gray-700 hover:border-gray-300'">
                    {{trans('Cases')}}
                </a>

                <a href="#" @click="selectedProjectPage = 1"
                   :class="selectedProjectPage == 1 ? 'flex-1 px-4 py-2 text-center text-blue-500 border-b-2 border-blue-500 font-medium' : 'flex-1 px-4 py-2 text-center text-gray-500 border-b-2 border-transparent font-medium hover:text-gray-700 hover:border-gray-300'">
                    {{trans('Edit Project')}}
                </a>

                @if(auth()->user()->is($project->creator()))
                    <a href="#" @click="selectedProjectPage = 2"
                       :class="selectedProjectPage == 2 ? 'flex-1 px-4 py-2 text-center text-blue-500 border-b-2 border-blue-500 font-medium' : 'flex-1 px-4 py-2 text-center text-gray-500 border-b-2 border-transparent font-medium hover:text-gray-700 hover:border-gray-300'">
                        {{trans('Invite Collaborator')}}
                    </a>
                @endif
            </nav>
        </div>

        <!-- Tabs Content -->
        <div class="flex-1 overflow-hidden">
            <!-- Cases Tab -->
            <div v-if="selectedProjectPage == 0" class="flex h-full">
                <cases-list :cases="{{ $casesWithEntries }}" :url-to-create-case="'{{$project->path()}}'"
                            @select-case="handleSelectedCase"
                ></cases-list>
                <!-- Selected Case Details -->
                <main class="flex-1">
                    <selected-case
                            v-if="Object.keys(selectedCase).length > 0"
                            :projectinputs="{{ $project->inputs }}"
                            :cases="selectedCase"
                    >
                    </selected-case>
                </main>
            </div>
        </div>

        <!-- Edit Project Tab -->
        <div v-if="selectedProjectPage == 1">
            @if(!$project->isEditable())
                <div class="p-4 mb-4 text-center text-red-700 bg-red-100 border border-red-400 rounded">
                    <div class="flex justify-center items-center">
                        <svg class="w-5 h-5 mr-2" xmlns="http://www.w3.org/2000/svg" fill="none"
                             viewBox="0 0 20 20" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                  d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"/>
                        </svg>
                        <span>{{trans('You created a case, your project is not editable')}}</span>
                    </div>
                </div>
            @endif

            <edit-project
                    :editable="{{ $project->isEditable() ? 'true' : 'false' }}"
                    :project="{{ json_encode($project) }}"
                    :config='@json(config("inputs"))'
                    :projectmedia="{{ json_encode($projectmedia) }}"
            >
            </edit-project>

        </div>

        <!-- Invite Collaborator Tab -->
        <div v-if="selectedProjectPage == 2">
            <project-invites class="mt-4" :invitedlist="{{ json_encode($invites) }}"
                             :project="{{ $project->id }}"></project-invites>
        </div>
    </div>

    <Modal
            :title="trans('Add Entry')"
            :visible.sync="newentry.modal"
            @confirm="entrySaveAndClose"
            @cancel="toggleModal"
    >
        <!-- Modal Body -->
        <p class="p-3 mb-4 text-center text-yellow-700 bg-yellow-100 rounded">
            {{ trans('Please scroll if you don\'t see all the content. To see the changes remember to reload the page.') }}
        </p>

        <form @submit.prevent="entrySaveAndClose">
            <input type="hidden" :value="newentry.case_id"/>

            <!-- Start Date/Time -->
            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-700">{{ trans('Start Date/Time') }} *</label>
                <input
                        type="datetime-local"
                        name="begin"
                        v-model="newentry.data.start"
                        class="block w-full px-4 py-2 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring"
                />
            </div>

            <!-- End Date/Time -->
            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-700">{{ trans('End Date/Time') }} *</label>
                <input
                        type="datetime-local"
                        name="end"
                        v-model="newentry.data.end"
                        class="block w-full px-4 py-2 bg-white border border-gray-300 rounded-lg focus:outline-none focus:ring"
                />
            </div>

            <!-- Media -->
            <div class="mb-4">
                <label class="block text-sm font-semibold text-gray-700">{{ trans('Media') }} *</label>
                <input
                        type="text"
                        v-model="newentry.data.media_id"
                        name="media_id"
                        class="w-full px-4 py-2 border border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500"
                />
            </div>

            <!-- Inputs -->
            <h3 class="mb-2 text-xl font-bold text-center text-gray-700">{{ trans('Inputs') }}</h3>
            <div v-for="(value, index) in newentry.inputs" :key="index" class="mb-4">
                <label class="block text-sm font-semibold text-gray-700">
                    @{{ value.mandatory ? value.name + ' *' : value.name }}
                </label>

                <!-- Text Input -->
                <input
                        v-if="value.type === 'text'"
                        v-model="newentry.data.inputs[value.name]"
                        :name="'text' + value.name"
                        type="text"
                        class="w-full px-4 py-2 border border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500"
                />

                <!-- Multiple Choice -->
                <div v-if="value.type === 'multiple choice'">
                    <select
                            v-model="newentry.data.inputs[value.name]"
                            multiple
                            class="block w-full px-4 py-3 bg-gray-200 border border-gray-200 rounded focus:outline-none focus:bg-white focus:border-gray-500"
                    >
                        <option
                                v-for="(answer, indexA) in value.answers"
                                :key="indexA"
                                :value="answer"
                        >
                            @{{ answer }}
                        </option>
                    </select>
                </div>

                <!-- One Choice -->
                <div v-if="value.type === 'one choice'" class="relative mt-1">
                    <select
                            v-model="newentry.data.inputs[value.name]"
                            class="w-full px-4 py-2 border border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option disabled value="">{{ trans('Select an option') }}</option>
                        <option
                                v-for="answer in value.answers"
                                :key="answer"
                                :value="answer"
                        >
                            @{{ answer }}
                        </option>
                    </select>
                    <svg
                            class="absolute right-3 top-3 w-4 h-4 text-gray-400 pointer-events-none"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                    >
                        <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M19 9l-7 7-7-7"
                        />
                    </svg>
                </div>

                <!-- Scale -->
                <div v-if="value.type === 'scale'" class="relative mt-1">
                    <select
                            v-model="newentry.data.inputs[value.name]"
                            class="w-full px-4 py-2 border border-gray-300 rounded focus:ring-blue-500 focus:border-blue-500"
                    >
                        <option disabled value="">{{ trans('Select a scale') }}</option>
                        <option v-for="num in 5" :key="num" :value="num">@{{ num }}</option>
                    </select>
                    <svg
                            class="absolute right-3 top-3 w-4 h-4 text-gray-400 pointer-events-none"
                            xmlns="http://www.w3.org/2000/svg"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                    >
                        <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                stroke-width="2"
                                d="M19 9l-7 7-7-7"
                        />
                    </svg>
                </div>
            </div>

            <p class="text-sm text-gray-600">* {{ trans('required') }}</p>
        </form>

        <!-- Custom Footer Buttons -->
        <template #extra-buttons>
            <button
                    type="button"
                    @click="entrySaveAndNewEntry"
                    class="px-4 py-2 text-sm font-medium text-blue-500 bg-transparent border border-blue-500 rounded hover:bg-blue-50 focus:outline-none focus:ring-2 focus:ring-blue-500"
            >
                {{ trans('Save and add new Entry') }}
            </button>
            <button
                    type="button"
                    @click="toggleModal"
                    class="px-4 py-2 text-sm font-medium text-white bg-red-500 rounded hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500"
            >
                {{ trans('Close') }}
            </button>
        </template>
    </Modal>

@endsection

@section('pagespecificscripts')
    <!-- Add any page-specific scripts here -->
@endsection
