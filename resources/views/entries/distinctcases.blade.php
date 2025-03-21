@extends('layouts.app')

@section('content')
    @include('layouts.breadcrumb')

    <!-- Modal Component -->
    <modal
        title="{{ __('Edit Entry') }}"
        :visible="editentry.modal"
        @confirm="editEntryAndClose"
        @cancel="toggleEntryModal"
    >
        <template v-slot>
            <p class="p-3 mt-3 font-bold text-center text-black bg-yellow-500">
                {{ __('Please scroll if you don\'t see all the content.') }}
            </p>

            <!-- Body -->
            <input type="hidden" :value="editentry.case_id" />
            <div class="my-2">
                <label class="text-base font-bold tracking-wide text-gray-700 uppercase">
                    Start Date/time *
                </label>
                <input
                    type="datetime-local"
                    name="begin"
                    v-model="editentry.data.start"
                    @input="newentrydateselected('edit')"
                    class="block w-full px-4 py-2 leading-normal bg-white border border-gray-300 rounded-lg appearance-none focus:outline-none focus:ring"
                />
            </div>
            <div class="my-2">
                <label class="text-base font-bold tracking-wide text-gray-700 uppercase">
                    End Date/time *
                </label>
                <input
                    type="datetime-local"
                    name="end"
                    v-model="editentry.data.end"
                    class="block w-full px-4 py-2 leading-normal bg-white border border-gray-300 rounded-lg appearance-none focus:outline-none focus:ring"
                />
            </div>
            <div class="my-2">
                <label class="text-base font-bold tracking-wide text-gray-700 uppercase">
                    Media *
                </label>
                <input
                    type="text"
                    name="media_id"
                    v-model="editentry.data.media_id"
                    class="block w-full px-4 py-2 leading-normal bg-white border border-gray-300 rounded-lg appearance-none focus:outline-none focus:ring"
                />
            </div>
            <h1 class="my-4 text-2xl font-bold tracking-wide text-center text-gray-700 uppercase">Inputs</h1>
            <div v-for="(value, index) in editentry.inputs" :key="index">
                <label class="my-2 text-base font-bold tracking-wide text-gray-700 uppercase">
                    @{{ value.mandatory ? value.name + ' *' : value.name }}
                </label>
                <!-- Text Input -->
                <input
                    type="text"
                    v-if="value.type === 'text'"
                    :name="'text' + value.name"
                    v-model="editentry.data.inputs[value.name]"
                    class="block w-full px-4 py-2 leading-normal bg-white border border-gray-300 rounded-lg appearance-none focus:outline-none focus:ring"
                />
                <!-- Multiple Choice -->
                <div v-if="value.type === 'multiple choice'">
                    <select
                        v-model="editentry.data.inputs[value.name]"
                        multiple
                        class="block w-full px-4 py-3 leading-tight text-gray-700 bg-gray-200 border border-gray-200 rounded appearance-none focus:outline-none focus:bg-white focus:border-gray-500"
                    >
                        <option
                            v-for="(answer, indexA) in value.answers"
                            :key="indexA"
                            :value="answer"
                            :selected="editentry.data.inputs[value.name] && editentry.data.inputs[value.name].includes(answer)"
                        >
                            @{{ answer }}
                        </option>
                    </select>
                </div>
                <!-- One Choice -->
                <div class="relative" v-if="value.type === 'one choice'">
                    <select
                        v-model="editentry.data.inputs[value.name]"
                        class="block w-full px-4 py-3 pr-8 leading-tight text-gray-700 bg-gray-200 border border-gray-200 rounded appearance-none focus:outline-none focus:bg-white focus:border-gray-500"
                    >
                        <option v-for="(answer, indexA) in value.answers" :key="indexA" :value="answer">
                            @{{ answer }}
                        </option>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center px-2 text-gray-700 pointer-events-none">
                        <svg
                            class="w-4 h-4 fill-current"
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 20 20"
                        >
                            <path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z" />
                        </svg>
                    </div>
                </div>
                <!-- Scale -->
                <div class="relative" v-if="value.type === 'scale'">
                    <select
                        v-model="editentry.data.inputs[value.name]"
                        class="block w-full px-4 py-3 pr-8 leading-tight text-gray-700 bg-gray-200 border border-gray-200 rounded appearance-none focus:outline-none focus:bg-white focus:border-gray-500"
                    >
                        <option value="1">1</option>
                        <option value="2">2</option>
                        <option value="3">3</option>
                        <option value="4">4</option>
                        <option value="5">5</option>
                    </select>
                    <div class="absolute inset-y-0 right-0 flex items-center px-2 text-gray-700 pointer-events-none">
                        <svg
                            class="w-4 h-4 fill-current"
                            xmlns="http://www.w3.org/2000/svg"
                            viewBox="0 0 20 20"
                        >
                            <path d="M9.293 12.95l.707.707L15.657 8l-1.414-1.414L10 10.828 5.757 6.586 4.343 8z" />
                        </svg>
                    </div>
                </div>
            </div>
            <div class="my-3 text-base">* {{ __('required') }}</div>
        </template>
    </modal>
    <!-- End Modal Component -->

    <div class="block text-center">
        <h1 class="inline my-2 mr-4 font-serif text-4xl font-bold text-center">Entries</h1>
        <button
            class="inline-flex items-center px-4 py-2 font-bold text-center text-white bg-blue-500 rounded hover:bg-blue-700"
            @click="showentriestable = !showentriestable"
        >
            {{ __('Toggle Entries') }}
        </button>
    </div>
    <table class="w-full table-fixed" v-show="showentriestable">
        <thead>
            <tr>
                <th class="px-4 py-2">{{ __('Begin') }}</th>
                <th class="px-4 py-2">{{ __('End') }}</th>
                <th class="px-6 py-2">{{ __('Inputs') }}</th>
                <th class="px-3 py-2">{{ __('Media') }}</th>
                <th class="px-3 py-2">{{ __('Actions') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach($entries['list'] as $entry)
                <tr>
                    <td class="px-4 py-2 border">{{ date('d.m.Y H:i:s', strtotime($entry->begin)) }}</td>
                    <td class="px-4 py-2 border">{{ date('d.m.Y H:i:s', strtotime($entry->end)) }}</td>
                    <td class="px-6 py-2 border">
                        @foreach($entry->inputs as $key => $input)
                            <div class="inline-block">
                                <div class="inline font-bold">
                                    {{ $key }}
                                </div>
                                <div class="inline">
                                    @if(is_array($input))
                                        @foreach($input as $answer)
                                            {{ $answer }}
                                        @endforeach
                                    @else
                                        @if($key !== 'firstValue')
                                            {{ $input }}
                                        @endif
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </td>
                    <td class="px-3 py-2 border">{{ $entry->media_id }}</td>
                    <td class="px-3 py-2 border">
                        <a
                            href="#"
                            @click="toggleEntryModal({{ $entry }}, {{ $project->inputs }})"
                            class="inline-flex items-center px-4 py-2 font-bold text-black bg-yellow-300 rounded hover:bg-yellow-400"
                        >
                            <svg class="w-4 h-4 mr-2" fill="none" height="20" stroke="currentColor"
                                stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24"
                                width="20" xmlns="http://www.w3.org/2000/svg">
                                <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2-2h14a2 2 0 0 0 2-2v-7"/>
                                <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                            </svg>
                            Edit
                        </a>
                        <a
                            href="#"
                            @click="confirmdelete({{ $entry->case_id }}, {{ $entry->id }}, {{ $entries['list']->count() == 1 ? 'true' : 'false' }})"
                            class="inline-flex items-center px-4 py-2 font-bold text-black bg-red-600 rounded hover:bg-red-700"
                        >
                            <svg class="w-4 h-4 mr-2" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                <path d="M6 2a1 1 0 00-1 1v1H2v2h1v11a2 2 0 002 2h10a2 2 0 002-2V6h1V4h-3V3a1 1 0 00-1-1H6zm1 2h6v1H7V4zm2 3v8H8V7h1zm3 0v8h-1V7h1z"/>
                            </svg>
                            Delete
                        </a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <h1 class="my-2 font-serif text-4xl font-bold text-center">{{ __('Graphs') }}</h1>

    <graph
        :info="{{ json_encode($entries['media']) }}"
        title="Media"
        :availabledata="{{ json_encode($entries['availablemedia']) }}"
    ></graph>

    @isset($entries['inputs'])
        @foreach($entries['inputs'] as $input)
            <graph
                :info="{{ json_encode($input) }}"
                :title="{{ json_encode($input['title']) }}"
                :availabledata="{{ json_encode($input['available']) }}"
            ></graph>
        @endforeach
    @endisset

@endsection
@section('pagespecificcss')
@endsection

